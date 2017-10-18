<?php
/**
 * PHPUnit bootstrap file
 *
 * @package WP101
 */

define( 'PROJECT_DIR', dirname( __DIR__ ) );

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/wp101.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

/**
 * Block the default HTTP libraries to prevent leaking any real requests.
 */
function _mock_http_library( $transports ) {
	return [ 'mock' ];
}
tests_add_filter( 'http_api_transports', '_mock_http_library' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
require_once __DIR__ . '/testcase.php';
