<?php
/**
 * Base test case for WP101 tests.
 *
 * @package WP101
 */

namespace WP101;

use Mockery;
use ReflectionMethod;
use SteveGrunwell\PHPUnit_Markup_Assertions\MarkupAssertionsTrait;
use WP_UnitTestCase;

/**
 * Base test case, with a bit of extra logic.
 */
class TestCase extends WP_UnitTestCase {
	use MarkupAssertionsTrait;

	public function tearDown() {
		parent::tearDown();

		Mockery::close();
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
	 * Set the environment's API key.
	 *
	 * @param string $api_key|bool Optional. The API key value to set. If equal to FALSE, a random
	 *                             key will be generated.
	 * @return string The API key stored.
	 */
	protected function set_api_key( $api_key = false ) {
		if ( false === $api_key ) {
			$api_key = uniqid();
		}

		update_option( 'wp101_api_key', $api_key );

		return $api_key;
	}
}
