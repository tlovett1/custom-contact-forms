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
		add_filter( 'plugin_row_meta', array( $this, 'filter_plugin_row_meta' ), 10, 4 );
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
	 * Add forms and form submissions link to meta
	 *
	 * @param array $plugin_meta
	 * @param string $plugin_file
	 * @param string $plugin_data
	 * @param string $status
	 * @since 6.0
	 * @return array
	 */
	public function filter_plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {
		$new_meta = array();

		if ( 'custom-contact-forms/custom-contact-forms.php' === $plugin_file ) {
			$new_meta['ccf_forms'] = sprintf( __( '<a href="%s">Forms and Submissions</a>', 'custom-contact-forms' ), esc_url( admin_url( 'edit.php?post_type=ccf_form' ) ) );
		}

		return array_merge( $new_meta, $plugin_meta );
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