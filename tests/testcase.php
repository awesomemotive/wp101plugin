<?php
/**
 * Base test case for WP101 tests.
 *
 * @package WP101
 */

namespace WP101;

use Mockery;
use ReflectionMethod;
use WP_UnitTestCase;

/**
 * Base test case, with a bit of extra logic.
 */
class TestCase extends WP_UnitTestCase {

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
}
