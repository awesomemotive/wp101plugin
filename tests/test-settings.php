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
		$this->set_api_key( '' );

		ob_start();
		Admin\render_settings_page();
		$output = ob_get_clean();

		$this->assertContainsSelector( '#wp101-settings-api-key-form', $output );
		$this->assertNotContainsSelector( '#wp101-settings-api-key-display', $output );
		$this->assertHasElementWithAttributes(
			[
				'name'  => 'wp101_api_key',
				'id'    => 'wp101-api-key',
			],
			$output
		);

		$this->assertEquals( 1, did_action( 'admin_notices' ) );
	}

	public function test_hides_api_key_form_if_already_set() {
		$key    = $this->set_api_key();
		$masked = substr( $key, 0, 4 );

		ob_start();
		Admin\render_settings_page();
		$output = ob_get_clean();

		$this->assertHasElementWithAttributes(
			[
				'id'    => 'wp101-settings-api-key-form',
				'class' => 'hide-if-js',
			],
			$output
		);

		$this->assertHasElementWithAttributes(
			[
				'id'    => 'wp101-settings-api-key-display',
			],
			$output
		);

		$this->assertRegExp(
			'/\<code\>' . preg_quote( substr( $key, 0, 4 ) ) . '(&#9679;)+\<\/code\>/',
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
		$this->assertNotContainsSelector(' #wp101-settings-replace-api-key', $output );
	}

	public function test_public_key_is_cleared_when_private_key_changes() {
		update_option( 'wp101_api_key', md5( uniqid() ) );
		update_option( API::PUBLIC_API_KEY_OPTION, uniqid() );

		$api = $this->mock_api();
		$api->shouldReceive( 'clear_api_key' )->once();
		$api->shouldReceive( 'get_public_api_key' )->once();

		// Change the private key.
		update_option( 'wp101_api_key', md5( uniqid() ) );

		$this->assertEmpty( get_option( API::PUBLIC_API_KEY_OPTION ) );
	}
}
