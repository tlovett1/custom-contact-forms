<?php

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

require_once( $_tests_dir . '/includes/functions.php' );

function _manually_load_plugin() {
	require(dirname(__FILE__) . '/../../custom-contact-forms.php');

	if ( ! class_exists( 'WP_JSON_Server' ) ) {
		require(dirname(__FILE__) . '/../../vendor/wp-api/wp-api/plugin.php');
	}

	require_once(dirname(__FILE__) . '/../../classes/class-ccf-api.php');
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require_once( $_tests_dir . '/includes/bootstrap.php' );

require_once( dirname( __FILE__ ) . '/includes/class-ccf-test-base.php' );
