<?php

class CCF_Custom_Contact_Forms {

	/**
	 * Placeholder method
	 *
	 * @since 6.0
	 */
	public function __construct() {}

	/**
	 * Setup general plugin stuff. Load API
	 *
	 * @since 6.0
	 */
	public function setup() {
		add_action( 'rest_api_init', array( $this, 'api_init' ), 1000 );
		add_action( 'plugins_loaded', array( $this, 'manually_load_api' ), 1000 );
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_filter( 'plugin_action_links', array( $this, 'filter_plugin_action_links' ), 10, 2 );
		add_action( 'admin_notices', array( $this, 'permalink_warning' ) );
		add_action( 'registered_post_type', array( $this, 'make_post_types_public' ), 11, 2 );
		add_action( 'admin_init', array( $this, 'flush_rewrites' ), 10000 );

	}

	/**
	 * Trick API into thinking non publically queryable post types are queryable
	 *
	 * @param string $post_type
	 * @param array  $args
	 * @since 6.8.1
	 */
	public function make_post_types_public( $post_type, $args ) {
		global $wp_post_types;

		$type = &$wp_post_types[ $post_type ];

		$json_post_types = array( 'ccf_form', 'ccf_submission' );

		if ( in_array( $post_type, $json_post_types ) ) {
			$type->show_in_json = true;
		}
	}

	/**
	 * Flush rewrites if necessary
	 *
	 * @since 6.0
	 */
	public function flush_rewrites() {
		$flush_rewrites = get_option( 'ccf_flush_rewrites' );

		if ( ! empty( $flush_rewrites ) ) {
			add_action( 'shutdown', 'flush_rewrite_rules' );

			delete_option( 'ccf_flush_rewrites' );
		}
	}

	/**
	 * Output permalink warning
	 */
	public function permalink_warning() {
		$permalink_structure = get_option( 'permalink_structure' );

		if ( empty( $permalink_structure ) ) {
			?>
			<div class="update-nag">
				<?php printf( __( 'Custom Contact Forms will not work unless pretty permalinks (not default) are enabled. Please update your <a href="%s">permalinks settings</a>.', 'custom-contact-forms' ), esc_url( admin_url( 'options-permalink.php' ) ) ); ?>
			</div>
		<?php
		}
	}

	/**
	 * Add forms and form submissions link to plugin actions
	 *
	 * @param array  $plugin_actions
	 * @param string $plugin_file
	 * @since 6.1.4
	 * @return array
	 */
	public function filter_plugin_action_links( $plugin_actions, $plugin_file ) {
		$new_actions = array();

		if ( basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/custom-contact-forms.php' === $plugin_file ) {
			$new_actions['ccf_forms'] = sprintf( __( '<a href="%s">Forms and Submissions</a>', 'custom-contact-forms' ), esc_url( admin_url( 'edit.php?post_type=ccf_form' ) ) );
		}

		return array_merge( $new_actions, $plugin_actions );
	}

	/**
	 * Load translation
	 *
	 * @since 6.0
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'custom-contact-forms', false, dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/' );
	}

	/**
	 * Manually register rest scripts
	 *
	 * @since  7.8.3
	 */
	public function rest_register_scripts_manual() {
		wp_register_script( 'wp-api', plugins_url( 'wp-api.js', __FILE__ ), array( 'jquery', 'backbone', 'underscore' ), '1.1', true );

		$settings = array( 'root' => esc_url_raw( get_rest_url() ), 'nonce' => wp_create_nonce( 'wp_rest' ) );
		wp_localize_script( 'wp-api', 'WP_API_Settings', $settings );
	}

	/**
	 * Right now we are including the JSON REST API (http://github.com/wp-api/wp-api) in the plugin itself
	 * since the API is not yet stable. Eventually, the API will be moved into WP and it will no longer need
	 * to be manually included by the plugin. See composer.json for specific information on what version of the
	 * API is being loaded.
	 *
	 * @since 6.0
	 */
	public function manually_load_api() {
		global $pagenow;

		if ( ! empty( $pagenow ) ) {
			if ( 'plugins.php' === $pagenow && ( ! empty( $_GET['action'] ) && 'activate' === $_GET['action'] || ! empty( $_POST['checked'] ) ) ) {

				if ( ! empty( $_POST['checked'] ) ) {
					foreach ( $_POST['checked'] as $plugin ) {
						if ( preg_match( '#(json-rest-api|wp-api|rest-api)#i', $plugin ) ) {
							return;
						}
					}
				} elseif ( ! empty( $_GET['plugin'] ) ) {
					if ( preg_match( '#(json-rest-api|wp-api|rest-api)#i', $_GET['plugin'] ) ) {
						return;
					}
				}
			}
		}

		if ( function_exists( 'create_initial_rest_routes' ) || class_exists( 'WP_REST_Controller' ) ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'rest_register_scripts_manual' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'rest_register_scripts_manual' ) );
			return;
		}

		require_once( dirname( __FILE__ ) . '/../wp-api/plugin.php' );
	}

	/**
	 * Manually initialize API.
	 *
	 * @param object $server
	 * @since 6.0
	 */
	public function api_init( $server ) {
		require_once( dirname( __FILE__ ) . '/../classes/class-ccf-api-form-controller.php' );

		$form_controller = new CCF_API_Form_Controller;
		$form_controller->register_routes();
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
