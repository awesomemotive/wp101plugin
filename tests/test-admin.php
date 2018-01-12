<?php
/**
 * Tests for the plugin settings.
 *
 * @package WP101
 */

namespace WP101\Tests;

use WP101\Admin as Admin;

/**
 * Tests for the plugin settings UI, defined in includes/settings.php.
 */
class AdminTest extends TestCase {

	public function tearDown() {
		global $menu;

		parent::tearDown();

		$menu = [];
	}

	public function test_enqueue_scripts() {
		$this->assertFalse( wp_style_is( 'wp101', 'registered' ) );
		$this->assertFalse( wp_script_is( 'wp101', 'registered' ) );

		Admin\enqueue_scripts( 'some-hook' );

		$this->assertTrue(
			wp_style_is( 'wp101', 'registered' ),
			'Calling register_scripts() should register the "wp101" style.'
		);
		$this->assertTrue(
			wp_script_is( 'wp101', 'registered' ),
			'Calling register_scripts() should register the "wp101" script.'
		);
	}

	public function test_enqueue_scripts_only_enqueues_on_wp101_pages() {
		Admin\enqueue_scripts( 'some-hook' );

		$this->assertFalse(
			wp_style_is( 'wp101', 'enqueued' ),
			'WP101 styles should only be enqueued on WP101 pages.'
		);
		$this->assertFalse(
			wp_script_is( 'wp101', 'enqueued' ),
			'WP101 scripts should only be enqueued on WP101 pages.'
		);

		Admin\enqueue_scripts( 'toplevel_page_wp101' );

		$this->assertTrue(
			wp_style_is( 'wp101', 'enqueued' ),
			'WP101 should be enqueued on WP101 pages.'
		);
		$this->assertTrue(
			wp_script_is( 'wp101', 'enqueued' ),
			'WP101 should be enqueued on WP101 pages.'
		);
	}

	public function test_register_menu_pages() {
		global $menu;

		$this->assertEmpty( $menu, 'The $menu global should start off empty.' );
		$this->set_api_key();

		do_action( 'admin_menu' );

		$menu_item = $menu[0];

		$this->assertEquals(
			'wp101',
			$menu_item[2],
			'Expected "wp101" as the menu slug.'
		);
		$this->assertNotEmpty(
			menu_page_url( 'wp101', false ),
			'WordPress should be able to generate the menu page URL.'
		);
	}

	public function test_register_menu_pages_shows_settings_page_as_only_link_if_api_key_not_set() {
		global $menu;

		do_action( 'admin_menu' );

		$menu_item = $menu[0];

		$this->assertEquals(
			'wp101-settings',
			$menu_item[2],
			'Expected "wp101-settings" as the menu slug.'
		);
		$this->assertNotEmpty(
			menu_page_url( 'wp101-settings', false ),
			'WordPress should be able to generate the menu page URL.'
		);
	}

	public function test_register_settings() {
		Admin\register_settings();

		$settings = get_registered_settings();

		$this->assertArrayHasKey( 'wp101_api_key', $settings );
		$this->assertEquals( 'wp101', $settings['wp101_api_key']['group'] );
		$this->assertEquals( 'sanitize_text_field', $settings['wp101_api_key']['sanitize_callback'] );
		$this->assertFalse( $settings['wp101_api_key']['show_in_rest'], 'The API key should never be exposed via the WP REST API.' );
	}

	public function test_settings_link_is_injected_into_plugin_action_links() {
		$this->markTestSkipped( 'Plugin action links filter is not behaving correctly' );

		$actions = apply_filters( 'plugin_action_links_wp101/wp101.php', array() );

		$this->assertContains( get_admin_url( null, 'admin.php?page=wp101&configure=1' ), $actions[0] );
	}
}
