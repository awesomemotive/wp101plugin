<?php
/**
 * Tests for the plugin template tags.
 *
 * @package WP101
 */

namespace WP101;

use WP101\Admin;
use WP101\TestCase;

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
				'name'  => 'wp101[api-key]',
				'id'    => 'wp101-api-key',
				'value' => $key,
			],
			$output
		);
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_hides_api_key_form_if_set_via_constant() {
		define( 'WP101_API_KEY', uniqid() );

		ob_start();
		Admin\render_settings_page();
		$output = ob_get_clean();

		$this->assertNotContainsSelector('#wp101-api-key', $output);
	}
}
