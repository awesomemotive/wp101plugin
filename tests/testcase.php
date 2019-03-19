<?php
/**
 * Base test case for WP101 tests.
 *
 * @package WP101
 */

namespace WP101\Tests;

use Mockery;
use ReflectionMethod;
use ReflectionProperty;
use SteveGrunwell\PHPUnit_Markup_Assertions\MarkupAssertionsTrait;
use WP_UnitTestCase;
use WP101\API;

/**
 * Base test case, with a bit of extra logic.
 */
class TestCase extends WP_UnitTestCase {
	use MarkupAssertionsTrait;

	public function tearDown() {
		parent::tearDown();

		delete_option( 'wp101_api_key' );
		delete_transient( API::PUBLIC_API_KEY_OPTION );

		$instance = new ReflectionProperty( API::get_instance(), 'instance' );
		$instance->setAccessible( true );
		$instance->setValue( null );

		Mockery::close();
	}

	/**
	 * Dequeue all scripts and styles.
	 *
	 * @after
	 */
	public function dequeue_assets() {
		global $wp_styles, $wp_scripts;

		unset( $wp_styles, $wp_scripts );
	}

	/**
	 * Tear down any custom menus.
	 *
	 * @after
	 */
	public function reset_menus() {
		global $menu, $submenu, $_parent_pages;

		$menu          = null;
		$submenu       = null;
		$_parent_pages = [];
	}

	/**
	 * Clean up the WP101_API_KEY constant.
	 *
	 * @after
	 */
	public function remove_constants() {
		if ( function_exists( 'runkit_constant_remove' ) && defined( 'WP101_API_KEY' ) ) {
			runkit_constant_remove( 'WP101_API_KEY' );
		}
	}

	/**
	 * Determine if we're currently running in a WordPress Multisite environment.
	 *
	 * @return bool
	 */
	protected function skip_if_not_multisite() {
		if ( ! is_multisite() ) {
			$this->markTestSkipped( 'This test will only run under WordPress Multisite.' );
		}
	}

	/**
	 * Return a ReflectionMethod with given protected/private $method accessible.
	 *
	 * @param  object|string $class  A class name or instance that contains the given method.
	 * @param  string        $method The method that should be made accessible.
	 * @return ReflectionMethod
	 */
	protected function get_accessible_method( $class, $method ) {
		$reflection = new ReflectionMethod( $class, $method );
		$reflection->setAccessible( true );

		return $reflection;
	}

	/**
	 * Retrieve a Mockery version of the API class.
	 *
	 * @return Mockery\Mock A Mockery version of the API class.
	 */
	protected function mock_api() {
		$api      = API::get_instance();
		$mock     = Mockery::mock( $api )->shouldAllowMockingProtectedMethods()->makePartial();
		$instance = new ReflectionProperty( $api, 'instance' );
		$instance->setAccessible( true );
		$instance->setValue( $mock );

		return API::get_instance();
	}

	/**
	 * Set the environment's API key.
	 *
	 * @param string $api_key|bool Optional. The API key value to set. If equal to FALSE, a random
	 *                             key will be generated.
	 * @return string The API key stored.
	 */
	protected function set_api_key( $api_key = false ) {
		if ( false === $api_key ) {
			$api_key = md5( uniqid() );
		}

		update_option( 'wp101_api_key', $api_key );

		return $api_key;
	}
}
