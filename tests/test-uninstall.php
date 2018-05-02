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

	public function test_uninstall_script_clears_known_options() {
		$options = array(
			'wp101_api_key',
			'wp101_db_version',
			'wp101_hidden_topics',
			'wp101_custom_topics',
			'wp101_admin_restriction',
			API::PUBLIC_API_KEY_OPTION,
		);

		foreach ( $options as $option ) {
			add_option( $option, uniqid() );
		}

		Uninstall\cleanup_plugin();

		foreach ( $options as $option ) {
			$this->assertEmpty(
				get_option( $option ),
				"Option '$option' was not removed along with the plugin"
			);
		}
	}

	public function test_uninstall_script_clears_known_transients() {
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

		Uninstall\cleanup_plugin();

		foreach ( $transients as $transient ) {
			$this->assertEmpty(
				get_transient( $transient ),
				"Transient '$transient' was not cleared along with the plugin"
			);
		}
	}
}
