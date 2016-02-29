<?php

class CCF_Export {

	/**
	 * Placeholder method
	 *
	 * @since 6.5
	 */
	public function __construct() {}

	/**
	 * Keep track of post types when we change them
	 *
	 * @var $old_post_types
	 * @since 6.5
	 */
	public $old_post_types = false;

	/**
	 * Setup form screen with actions and filters
	 *
	 * @since 6.5
	 */
	public function setup() {
		add_action( 'admin_init', array( $this, 'action_handle_export' ) );
		add_filter( 'export_args', array( $this, 'filter_export_args' ) );
		add_action( 'rss2_head', array( $this, 'action_rss2_head' ) );
		add_action( 'import_end', array( $this, 'action_import_end' ) );
		add_action( 'wp_import_insert_post', array( $this, 'action_wp_import_insert_post' ), 10, 1 );
		add_action( 'admin_menu', array( $this, 'action_admin_menu' ) );
		add_action( 'all_admin_notices', array( $this, 'action_all_admin_notices' ) );
		add_action( 'export_filters', array( $this, 'action_export_filters' ) );
	}

	/**
	 * Hackishly hide some CCF post types on the export screen
	 *
	 * @since 6.5
	 */
	public function action_all_admin_notices() {
		global $pagenow;

		if ( 'export.php' === $pagenow && empty( $_GET['download'] ) ) {
			global $wp_post_types;
			$this->old_post_types = $wp_post_types;

			$ccf_post_types = array( 'ccf_field', 'ccf_choice', 'ccf_submission' );

			foreach ( $wp_post_types as $slug => $post_type ) {
				if ( in_array( $slug, $ccf_post_types ) ) {
					$this->old_post_types[ $slug ] = clone $post_type;
					$post_type->can_export = false;
				}

				if ( 'ccf_form' === $slug ) {
					$this->old_post_types[ $slug ] = clone $post_type;
					$post_type->label = esc_html__( 'Forms and Submissions', 'cutom-contact-forms' );
				}
			}
		}
	}

	/**
	 * Restore global post types on export page
	 *
	 * @since 6.5
	 */
	public function action_export_filters() {
		global $pagenow;

		if ( 'export.php' === $pagenow && empty( $_GET['download'] ) ) {
			global $wp_post_types;

			if ( false !== $this->old_post_types ) {
				$wp_post_types = $this->old_post_types;
			}
		}
	}

	/**
	 * Hackishly add import link to forms menu
	 *
	 * @since 6.5
	 */
	public function action_admin_menu() {
		if ( current_user_can( 'edit_posts' ) ) {
			global $submenu;

			$submenu['edit.php?post_type=ccf_form'][] = array( esc_html__( 'Import', 'custom-contact-forms' ), 'manage_options', esc_url( admin_url( 'import.php' ) ) );
		}
	}

	/**
	 * Add import cleanup meta value
	 *
	 * @param int $post_id
	 * @since 6.5
	 */
	public function action_wp_import_insert_post( $post_id ) {
		$types = array( 'ccf_form', 'ccf_field' );

		if ( in_array( get_post_type( $post_id ), $types ) ) {
			// Mark post for cleanup later
			update_post_meta( $post_id, 'ccf_import_cleanup', true );
		}
	}

	/**
	 * We need to reattach form fields and field choices
	 *
	 * @since 6.5
	 */
	public function action_import_end() {
		$forms = new WP_Query( array(
			'post_type' => 'ccf_form',
			'posts_per_page' => 1000,
			'no_found_rows' => true,
		));

		if ( $forms->have_posts() ) {
			foreach ( $forms->posts as $form ) {
				$cleanup = get_post_meta( $form->ID, 'ccf_import_cleanup', true );

				if ( ! empty( $cleanup ) ) {
					$fields = wp_list_pluck( get_children( array( 'post_type' => 'ccf_field', 'post_parent' => $form->ID, 'numberposts' => 500 ) ), 'ID' );
					if ( ! empty( $fields ) ) {
						$fields = array_values( $fields );
					}

					update_post_meta( $form->ID, 'ccf_attached_fields', $fields );

					delete_post_meta( $form->ID, 'ccf_import_cleanup' );
				}
			}
		}

		$fields = new WP_Query( array(
			'post_type' => 'ccf_field',
			'posts_per_page' => 2000,
			'no_found_rows' => true,
		));

		if ( $fields->have_posts() ) {
			foreach ( $fields->posts as $field ) {
				$cleanup = get_post_meta( $field->ID, 'ccf_import_cleanup', true );

				if ( ! empty( $cleanup ) ) {
					$choices = wp_list_pluck( get_children( array( 'post_type' => 'ccf_choice', 'post_parent' => $field->ID, 'numberposts' => 500 ) ), 'ID' );
					if ( ! empty( $choices ) ) {
						$choices = array_values( $choices );
					}

					update_post_meta( $field->ID, 'ccf_attached_fields', $choices );

					delete_post_meta( $field->ID, 'ccf_import_cleanup' );
				}
			}
		}
	}

	/**
	 * Filter query for exporting a single form
	 *
	 * @param string $query
	 * @since 6.5
	 * @return string
	 */
	public function filter_query( $query ) {
		global $wpdb;

		if ( isset( $_GET['post'] ) && stripos( $query, 'ccf_form' ) ) {
			remove_filter( 'query', array( $this, 'filter_query' ) );

			$form_id = (int) $_GET['post'];

			$post_ids = array( $form_id );

			// First get submissions
			$submissions = wp_list_pluck( get_children( array( 'post_parent' => $_GET['post'], 'post_type' => 'ccf_submission' ) ), 'ID' );
			$post_ids = array_merge( $post_ids, $submissions );

			// Now get fields
			$fields = get_post_meta( $form_id, 'ccf_attached_fields', true );

			if ( ! empty( $fields ) ) {
				foreach ( $fields as $field_id ) {
					$post_ids[] = $field_id;

					$type = get_post_meta( $field_id, 'ccf_field_type', true );

					if ( 'dropdown' === $type || 'radio' === $type || 'checkboxes' === $type ) {
						$choices = get_post_meta( $field_id, 'ccf_attached_choices', true );

						if ( ! empty( $choices ) ) {
							$post_ids = array_merge( $post_ids, $choices );
						}
					}
				}
			}

			if ( ! empty( $post_ids ) ) {
				$post_ids = implode( ',', array_map( 'intval', $post_ids ) );

				$query = preg_replace( "#post_type.*=.*('|\").*?('|\")#i", "ID in ({$post_ids}) ", $query );
			}
		}

		return $query;
	}

	/**
	 * Output export file for single form
	 *
	 * @since 6.5
	 */
	public function action_handle_export() {
		if ( ! empty( $_GET['post'] ) && ! empty( $_GET['export'] ) && wp_verify_nonce( $_GET['nonce'], 'ccf_form_export' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/export.php' );

			/**
			 * We use ccf_form so we can be sure we are referring to the
			 * right query later.
			 */
			add_filter( 'query', array( $this, 'filter_query' ) );
			export_wp( array( 'content' => 'ccf_form' ) );

			exit;
		}
	}

	/**
	 * Restore global post types variable if necessary
	 *
	 * @since 6.5
	 */
	public function action_rss2_head() {
		if ( isset( $_GET['content'] ) && 'ccf_form' === $_GET['content'] && defined( 'WXR_VERSION' ) && WXR_VERSION ) {
			global $wp_post_types;

			if ( false !== $this->old_post_types ) {
				$wp_post_types = $this->old_post_types;
			}
		}
	}

	/**
	 * Hack all non-ccf post types to be not exportable if someone tries to export the ccf_form
	 * post type
	 *
	 * @param array $args
	 * @since 6.5
	 * @return array
	 */
	public function filter_export_args( $args ) {
		if ( isset( $_GET['content'] ) && 'ccf_form' === $_GET['content'] && defined( 'WXR_VERSION' ) && WXR_VERSION ) {
			$args['content'] = 'all';

			global $wp_post_types;
			$this->old_post_types = $wp_post_types;

			$ccf_post_types = array( 'ccf_form', 'ccf_field', 'ccf_choice', 'ccf_submission' );

			foreach ( $wp_post_types as $slug => $post_type ) {
				if ( ! in_array( $slug, $ccf_post_types ) ) {
					$this->old_post_types[ $slug ] = clone $post_type;
					$post_type->can_export = false;
				}
			}
		}

		return $args;
	}

	/**
	 * Return singleton instance of class
	 *
	 * @since 6.5
	 * @return object
	 */
	public static function factory() {
		static $instance;

		if ( ! $instance ) {
			$instance = new self();
			$instance->setup();
		}

		return $instance;
	}
}
