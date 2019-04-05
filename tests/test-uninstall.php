<?php
/**
 * Tests for the plugin uninstallation procedure.
 *
 * @package WP101
 */

namespace WP101\Tests;

use WP101\API as API;
use WP101\Uninstall as Uninstall;

/**
 * Tests for the plugin's uninstallation procedure, defined in uninstall.php.
 */
class UninstallTest extends TestCase {

	public function test_deactivation_hook_registered() {
		$this->assertEquals( 10, has_action(
			'deactivate_' . substr( dirname( __DIR__ ) . '/wp101.php', 1 ),
			'WP101\\Uninstall\\clear_caches'
		), 'Expected to see clear_caches called on plugin deactivation.' );
	}

	public function test_uninstall_plugin() {
		$options = array(
			API::API_KEY_OPTION,
			'wp101_db_version',
			'wp101_hidden_topics',
			'wp101_custom_topics',
			'wp101_admin_restriction',
		);

		foreach ( $options as $option ) {
			add_option( $option, uniqid() );
		}

		Uninstall\uninstall_plugin();

		foreach ( $options as $option ) {
			$this->assertEmpty(
				get_option( $option ),
				"Option '$option' was not removed along with the plugin."
			);
		}
	}

	public function test_clear_caches() {
		$transients = array(
			API::get_instance()->get_public_api_key_name(),
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

		Uninstall\clear_caches();

		foreach ( $transients as $transient ) {
			$this->assertEmpty(
				get_transient( $transient ),
				"Transient '$transient' was not cleared."
			);
		}
	}
}
