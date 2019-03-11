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

	/**
	 * @testWith ["wp101_after_edit_help_topics"]
     *           ["wp101_after_edit_custom_help_topics"]
     *           ["wp101_after_help_topics"]
     *           ["wp101_after_custom_help_topics"]
     *           ["wp101_admin_action_add-video"]
     *           ["wp101_admin_action_update-video"]
     *           ["wp101_admin_action_restrict-admin"]
     *           ["wp101_pre_includes"]
     */
	public function test_discover_deprecated_actions( $action ) {
		add_action( $action, '__return_false' );

		$this->setExpectedIncorrectUsage( 'Action ' . $action );

		do_action( 'init' );
	}

	/**
	 * @testWith ["wp101_default_settings_role"]
     *           ["wp101_too_many_admins"]
     *           ["wp101_settings_management_user_args"]
     *           ["wp101_get_document"]
     *           ["wp101_get_help_topics"]
     *           ["wp101_get_custom_help_topics"]
     *           ["wp101_get_hidden_topics"]
     */
    public function test_discover_deprecated_filters( $filter ) {
		add_filter( $filter, '__return_false' );

		$this->setExpectedIncorrectUsage( 'Filter ' . $filter );

		do_action( 'init' );
	}
}
