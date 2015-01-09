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