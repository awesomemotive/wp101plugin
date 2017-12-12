<?php
/**
 * Tests for the plugin settings.
 *
 * @package WP101
 */

namespace WP101\Admin;

use WP101\Admin;
use WP101\API;
use WP101\TestCase;

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

		enqueue_scripts( 'some-hook' );

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
		enqueue_scripts( 'some-hook' );

		$this->assertFalse(
			wp_style_is( 'wp101', 'enqueued' ),
			'WP101 styles should only be enqueued on WP101 pages.'
		);
		$this->assertFalse(
			wp_script_is( 'wp101', 'enqueued' ),
			'WP101 scripts should only be enqueued on WP101 pages.'
		);

		enqueue_scripts( 'toplevel_page_wp101' );

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
		wp_set_current_user( $this->factory()->user->create( [
			'role' => 'administrator',
		] ) );

		$this->set_api_key();

		$menu = $this->get_menu_items();

		$this->assertEquals( 'wp101', $menu['parent'][2], 'Expected "wp101" as the menu slug.' );
		$this->assertEquals( 'wp101', $menu['children'][0][2], 'The first child should be "wp101".' );
		$this->assertEquals( 'wp101-settings', $menu['children'][1][2], 'The second child should be "wp101-settings".' );

		// Ensure WordPress can generate corresponding menu page URLs.
		$this->assertNotEmpty( menu_page_url( 'wp101', false ) );
		$this->assertNotEmpty( menu_page_url( 'wp101-settings', false ) );
	}

	/**
	 * If an API key hasn't been set, only the WP101 Settings page should be shown.
	 */
	public function test_register_menu_pages_hides_listings_if_no_api_key_is_set() {
		wp_set_current_user( $this->factory()->user->create( [
			'role' => 'administrator',
		] ) );

		$this->set_api_key( '' );

		$menu = $this->get_menu_items();

		$this->assertEquals( 'wp101-settings', $menu['parent'][2], 'Expected "wp101-settings" as the menu slug.' );
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

	/**
	 * Retrieve the WP101 menu node(s) visible for the current user.
	 *
	 * @return array
	 */
	protected function get_menu_items() {
		global $menu, $submenu;

		do_action( 'admin_menu' );

		return [
			'parent'   => $menu[0],
			'children' => $submenu['wp101'],
		];
	}
}
