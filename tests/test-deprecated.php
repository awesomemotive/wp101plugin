<?php
/**
 * Tests for deprecated features.
 *
 * @package WP101
 */

namespace WP101\Tests;

use PHPUnit\Framework\Error\Warning;
use WP_Error;
use WP101\Migrate as Migrate;
use WP101_Plugin;

/**
 * Tests for catching deprecated functionality.
 */
class DeprecatedTest extends TestCase {

	public function test_wp101_plugin_class_constructor_is_deprecated() {
		$this->setExpectedIncorrectUsage( 'WP101_Plugin' );

		new WP101_Plugin;
	}

	public function test_wp101_plugin_static_methods_are_deprecated() {
		$this->setExpectedIncorrectUsage( 'WP101_Plugin::get_instance' );

		WP101_Plugin::get_instance();
	}
}
