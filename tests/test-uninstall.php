<?php
/**
 * Tests for the plugin uninstallation procedure.
 *
 * @package WP101
 */

namespace WP101\Tests;

/**
 * Tests for the plugin's uninstallation procedure, defined in uninstall.php.
 *
 * @runTestsInSeparateProcesses To avoid pollution of the WP_UNINSTALL_PLUGIN constant.
 */
class UninstallTest extends TestCase {

	public function test_script_checks_uninstall_plugin_constant() {
		add_option( 'wp101_api_key', uniqid() );
		set_transient( 'wp101_topics', uniqid() );

		$this->assertFalse(
			defined( 'WP_UNINSTALL_PLUGIN' ),
			'This test is predicated on WP_UNINSTALL_PLUGIN not being defined.'
		);

		require PROJECT_DIR . '/uninstall.php';

		$this->assertNotEmpty( get_option( 'wp101_api_key' ) );
		$this->assertNotEmpty( get_transient( 'wp101_topics' ) );
	}

	public function test_uninstall_script_clears_known_options() {
		define( 'WP_UNINSTALL_PLUGIN', true );

		$options = array(
			'wp101_api_key',
			'wp101_db_version',
			'wp101_hidden_topics',
			'wp101_custom_topics',
			'wp101_admin_restriction',
		);

		foreach ( $options as $option ) {
			add_option( $option, uniqid() );
		}

		require PROJECT_DIR . '/uninstall.php';

		foreach ( $options as $option ) {
			$this->assertEmpty(
				get_option( $option ),
				"Option '$option' was not removed along with the plugin"
			);
		}
	}

	public function test_uninstall_script_clears_known_transients() {
		define( 'WP_UNINSTALL_PLUGIN', true );

		$transients = array(
			'wp101_topics',
			'wp101_jetpack_topics',
			'wp101_woocommerce_topics',
			'wp101_wpseo_topics',
			'wp101_message',
			'wp101_get_admin_count',
			'wp101_api_key_valid',
		);

		foreach ( $transients as $transient ) {
			set_transient( $transient, uniqid() );
		}

		require PROJECT_DIR . '/uninstall.php';

		foreach ( $transients as $transient ) {
			$this->assertEmpty(
				get_transient( $transient ),
				"Transient '$transient' was not cleared along with the plugin"
			);
		}
	}
}
