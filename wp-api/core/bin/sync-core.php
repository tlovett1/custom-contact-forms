<?php

if ( empty( getenv( 'WP_DEVELOP_DIR' ) ) ) {
	echo "Please set the WP_DEVELOP_DIR environment variable to your local core checkout";
	exit( 1 );
}

$wp_develop_dir = getenv( 'WP_DEVELOP_DIR' );
$dest_dir = dirname( dirname( __FILE__ ) );

$test_files = array(
	'rest-api.php',
	'rest-api/rest-server.php',
	'rest-api/rest-request.php',
	);
foreach( $test_files as $test_file ) {
	copy( $wp_develop_dir . '/tests/phpunit/tests/' . $test_file, $dest_dir . '/phpunit/tests/' . $test_file );
	echo 'Copied: ' . $test_file . PHP_EOL;
}

$core_files = array(
	'rest-api.php',
	'class-wp-http-response.php',
	'rest-api/class-wp-rest-request.php',
	'rest-api/class-wp-rest-response.php',
	'rest-api/class-wp-rest-server.php',
	'rest-api/rest-functions.php',
	);
foreach( $core_files as $core_file ) {
	copy( $wp_develop_dir . '/src/wp-includes/' . $core_file, $dest_dir . '/wp-includes/' . $core_file );
	echo 'Copied: ' . $core_file . PHP_EOL;
}
