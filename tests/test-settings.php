<?php
/**
 * Tests for the plugin settings.
 *
 * @package WP101
 */

namespace WP101\Settings;

use WP101_Plugin;
use WP_UnitTestCase;

/**
 * Tests for the plugin settings UI, defined in includes/settings.php.
 */
class SettingsTest extends WP_UnitTestCase {

	public function test_settings_link_is_injected_into_plugin_action_links() {
		$this->markTestSkipped( 'Plugin action links filter is not behaving correctly' );
		$actions = apply_filters( 'plugin_action_links_wp101/wp101.php', array() );

		$this->assertContains( get_admin_url( null, 'admin.php?page=wp101&configure=1' ), $actions[0] );
	}

	/**
	 * WP101 enables the user's API key to be set in-code two different ways:
	 * 1. By populating a $_wp101_api_key global variable.
	 * 2. By setting the WP101_API_KEY constant in the wp-config.php file.
	 *
	 * The value stored in the database always takes precedence, followed by the global variable and
	 * finally the constant.
	 *
	 * @runInSeparateProcess
	 */
	public function test_setting_api_key_via_global_variable() {
		global $_wp101_api_key;

		$this->assertEmpty( get_option( 'wp101_api_key' ), 'The test should start with an empty API key' );

		$_wp101_api_key = uniqid();

		$key = WP101_Plugin::get_instance()->get_key();

		$this->assertEquals( $_wp101_api_key, $key );
		$this->assertEquals( $_wp101_api_key, get_option( 'wp101_api_key' ) );
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_setting_api_key_via_global_variable_only_applies_if_option_is_empty() {
		global $_wp101_api_key;

		$stored = uniqid();
		update_option( 'wp101_api_key', $stored );

		$_wp101_api_key = uniqid();

		$key = WP101_Plugin::get_instance()->get_key();

		$this->assertEquals( $stored, $key );
		$this->assertEquals( $stored, get_option( 'wp101_api_key' ) );
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_setting_api_key_via_constant() {
		global $_wp101_api_key;

		$this->assertEmpty( get_option( 'wp101_api_key' ), 'The test should start with an empty API key' );
		$this->assertEmpty( $_wp101_api_key );
		$this->assertFalse( defined( 'WP101_API_KEY' ) );

		define( 'WP101_API_KEY', uniqid() );

		$key = WP101_Plugin::get_instance()->get_key();

		$this->assertEquals( WP101_API_KEY, $key );
		$this->assertEquals( WP101_API_KEY, get_option( 'wp101_api_key' ) );
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_setting_api_key_defaults_to_stored_database_value() {
		global $_wp101_api_key;

		$this->assertEmpty( $_wp101_api_key );
		$this->assertFalse( defined( 'WP101_API_KEY' ) );

		$stored = uniqid();
		update_option( 'wp101_api_key', $stored );

		$key = WP101_Plugin::get_instance()->get_key();

		$this->assertEquals( $stored, $key );
		$this->assertEquals( $stored, get_option( 'wp101_api_key' ) );
	}
}
