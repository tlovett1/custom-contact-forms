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
		add_action( 'plugins_loaded', array( $this, 'manually_load_api' ), 100 );
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_filter( 'plugin_action_links', array( $this, 'filter_plugin_action_links' ), 10, 2 );
		add_action( 'admin_notices', array( $this, 'permalink_warning' ) );
		add_action( 'admin_init', array( $this, 'flush_rewrites' ), 10000 );
	}

	/**
	 * Flush rewrites if necessary
	 *
	 * @since 6.0
	 */
	public function flush_rewrites() {
		$flush_rewrites = get_option( 'ccf_flush_rewrites' );

		if ( ! empty( $flush_rewrites ) ) {
			flush_rewrite_rules();

			delete_option( 'ccf_flush_rewrites' );
		}
	}

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
	 * @param array $plugin_actions
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
	 * Right now we are including the JSON REST API (http://github.com/wp-api/wp-api) in the plugin itself
	 * since the API is not yet stable. Eventually, the API will be moved into WP and it will no longer need
	 * to be manually included by the plugin. See composer.json for specific information on what version of the
	 * API is being loaded.
	 *
	 * @since 6.0
	 */
	public function manually_load_api() {
		if ( ! class_exists( 'WP_JSON_Server' ) ) {
			add_filter( 'json_url', 'set_url_scheme' );

			require( dirname( __FILE__ ) . '/../vendor/wp-api/wp-api/plugin.php' );

			add_action( 'wp_json_server_before_serve', array( $this, 'api_init' ) );
		}
	}

	/**
	 * Manually initialize API.
	 *
	 * @param object $server
	 * @since 6.0
	 */
	public function api_init( $server ) {
		global $ccf_api;

		require_once( dirname( __FILE__ ) . '/../classes/class-ccf-api.php' );

		$ccf_api = new CCF_API( $server );
		add_filter( 'json_endpoints', array( $ccf_api, 'register_routes' ) );
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