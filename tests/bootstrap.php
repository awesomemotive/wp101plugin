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

if ( ! function_exists( 'runkit_constant_remove' ) ) {
	echo "\033[0;33mWARNING: Runkit is not active in the current environment, so not all tests can be run.\033[0;0m" . PHP_EOL;
	echo 'You may install Runkit easily by running:' . PHP_EOL;
	echo "  ./vendor/bin/install-runkit.sh" . PHP_EOL . PHP_EOL;
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
require_once PROJECT_DIR . '/vendor/autoload.php';
require_once __DIR__ . '/testcase.php';
