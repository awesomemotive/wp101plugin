<?php
/**
 * Tests for the plugin settings.
 *
 * @package WP101
 */

namespace WP101\Tests;

use WP101\Admin as Admin;
use WP101\API;

/**
 * Tests for the plugin template tags, contained in includes/template-tags.php.
 */
class SettingsTest extends TestCase {

	public function test_shows_api_key_form() {
		$key = $this->set_api_key();

		ob_start();
		Admin\render_settings_page();
		$output = ob_get_clean();

		$this->assertHasElementWithAttributes(
			[
				'name'  => 'wp101_api_key',
				'id'    => 'wp101-api-key',
				'value' => $key,
			],
			$output
		);

		$this->assertEquals( 1, did_action( 'admin_notices' ) );
	}

	/**
	 * @requires extension runkit
	 */
	public function test_hides_api_key_form_if_set_via_constant() {
		define( 'WP101_API_KEY', md5( uniqid() ) );

		ob_start();
		Admin\render_settings_page();
		$output = ob_get_clean();

		$this->assertContainsSelector( '#wp101-api-key-set-via-constant-notice', $output );
		$this->assertNotContainsSelector( '#wp101-api-key', $output );
	}

	public function test_public_key_is_cleared_when_private_key_changes() {
		update_option( 'wp101_api_key', md5( uniqid() ) );
		update_option( API::PUBLIC_API_KEY_OPTION, uniqid() );

		// Change the private key.
		update_option( 'wp101_api_key', md5( uniqid() ) );

		$this->assertEmpty( get_option( API::PUBLIC_API_KEY_OPTION ) );
	}
}
