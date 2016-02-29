<?php

class CCF_Submission_CPT {
	public function __construct() {}

	/**
	 * Setup post type
	 *
	 * @since 6.0
	 */
	public function setup() {
		add_action( 'init', array( $this, 'setup_cpt' ) );
		add_action( 'before_delete_post', array( $this, 'action_before_delete_post' ) );
	}

	/**
	 * Clean up attachments when we delete a submission
	 *
	 * @param int $post_id
	 * @since 6.4
	 */
	public function action_before_delete_post( $post_id ) {
		if ( 'ccf_submission' === get_post_type( $post_id ) ) {
			$attachments = get_children( array( 'post_parent' => $post_id, 'numberposts' => apply_filters( 'ccf_max_submission_attachments', 5000, get_post( $post_id ) ) ) );

			if ( ! empty( $attachments ) ) {
				foreach ( $attachments as $attachment ) {
					wp_delete_attachment( $attachment->ID, true );
				}
			}
		}
	}

	/**
	 * Register source feed post type
	 *
	 * @since 6.0
	 */
	public function setup_cpt() {
		$args = array(
			'label' => esc_html__( 'Form Submissions', 'custom-contact-forms' ),
			'public' => false,
			'exclude_from_search' => true,
			'show_in_nav_menus' => false,
			'show_ui' => false,
			'publicly_queryable' => false,
			'query_var' => false,
			'rewrite' => false,
			'capability_type' => 'post',
			'hierarchical' => false,
			'supports' => false,
			'has_archive' => false,
		);

		register_post_type( 'ccf_submission', $args );
	}

	/**
	 * Get prettified field date
	 *
	 * @param array $value
	 * @since 6.0
	 * @return bool|string
	 */
	public function get_pretty_field_date( $value ) {
		$dateString = '';

		if ( ! empty( $value['hour'] ) && ! empty( $value['minute'] ) && ! empty( $value['am-pm'] ) ) {
			$dateString .= ' ' . $value['hour'] . ':' . $value['minute'] . ' ' . $value['am-pm'];
		}

		if ( ! empty( $value['date'] ) ) {
			if ( ! empty( $dateString ) ) {
				$dateString .= ' ';
			}

			$dateString .= $value['date'];
		}

		if ( empty( $dateString ) ) {
			return '-';
		}

		return $dateString;
	}

	/**
	 * Get a prettified name
	 *
	 * @param array $value
	 * @since 6.0
	 * @return string
	 */
	public function get_pretty_field_name( $value ) {
		$nameString = $value['first'];

		if ( ! empty( $nameString ) ) {
			$nameString .= ' ';
		}

		if ( ! empty( $value['last'] ) ) {
			$nameString .= $value['last'];
		}

		if ( empty( $nameString ) ) {
			$nameString = '-';
		}

		return $nameString;
	}

	/**
	 * Get a prettified address
	 *
	 * @param array $value
	 * @since 6.0
	 * @return string
	 */
	public function get_pretty_field_address( $value ) {
		if ( empty( $value['street'] ) || empty( $value['city'] ) ) {
			return '-';
		}

		$addressString = $value['street'];

		if ( ! empty( $value['line_two'] ) ) {
			$addressString .= ' ' . $value['line_two'];
		}

		$addressString .= ', ' . $value['city'];

		if ( ! empty( $value['state'] ) ) {
			$addressString .= ', ' . $value['state'];
		}

		if ( ! empty( $value['zipcode'] ) ) {
			$addressString .= ' ' . $value['zipcode'];
		}

		if ( ! empty( $value['country'] ) ) {
			$addressString .= ' ' . $value['country'];
		}

		return $addressString;
	}

	/**
	 * Return singleton instance of class
	 *
	 * @since 6.0
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
