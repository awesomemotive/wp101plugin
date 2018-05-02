<?php
/**
 * Tests for the plugin settings.
 *
 * @package WP101
 */

namespace WP101\Tests;

use WP101\Admin as Admin;
use WP101\API as API;

/**
 * Tests for the plugin settings UI, defined in includes/settings.php.
 */
class AdminTest extends TestCase {

	public function test_enqueue_scripts_registers_scripts() {
		$this->assertFalse( wp_style_is( 'wp101-admin', 'registered' ) );
		$this->assertFalse( wp_script_is( 'wp101-admin', 'registered' ) );

		Admin\enqueue_scripts( 'some-hook' );

		$this->assertTrue(
			wp_style_is( 'wp101-admin', 'registered' ),
			'Calling register_scripts() should register the "wp101-admin" style.'
		);
		$this->assertTrue(
			wp_script_is( 'wp101-admin', 'registered' ),
			'Calling register_scripts() should register the "wp101-admin" script.'
		);
	}

	/**
	 * Ensure that WP101 assets are only enqueued on WP101 pages.
	 *
	 * @dataProvider enqueue_hook_provider()
	 *
	 * @param string $hook     The hook to be executed.
	 * @param bool   $enqueued Whether or not the assets should be enqueued for this hook.
	 */
	public function test_enqueue_scripts_enqueues_on_wp101_pages( $hook, bool $enqueued ) {
		Admin\enqueue_scripts( $hook );

		$this->assertEquals( $enqueued, wp_style_is( 'wp101-admin', 'enqueued' ) );
		$this->assertEquals( $enqueued, wp_script_is( 'wp101-admin', 'enqueued' ) );
	}

	/**
	 * Data provider for test_enqueue_scripts_enqueues_on_wp101_pages().
	 */
	public function enqueue_hook_provider() {
		return [
			'Generic page'   => [ 'some-hook', false ],
			'WP101 listings' => [ 'toplevel_page_wp101', true ],
			'WP101 settings' => [ 'video-tutorials_page_wp101-settings', true ],
			'WP101 add-ons'  => [ 'video-tutorials_page_wp101-addons', true ],
		];
	}

	public function test_get_addon_capability() {
		$this->assertEquals(
			'publish_posts',
			Admin\get_addon_capability(),
			'Default should be "publish_posts".'
		);

		add_filter( 'wp101_addon_capability', function () {
			return 'my_capability';
		} );

		$this->assertEquals(
			'my_capability',
			Admin\get_addon_capability(),
			'Value should be overridden via the wp101_addon_capability filter.'
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
		$this->assertEquals( 'manage_options', $menu['children'][1][1], 'The wp101-settings page requires manage_options.' );
		$this->assertEquals( 'wp101-settings', $menu['children'][1][2], 'The second child should be "wp101-settings".' );
		$this->assertEquals( 'wp101-addons', $menu['children'][2][2], 'The third child should be "wp101-addons".' );

		// Ensure WordPress can generate corresponding menu page URLs.
		$this->assertNotEmpty( menu_page_url( 'wp101', false ) );
		$this->assertNotEmpty( menu_page_url( 'wp101-settings', false ) );
		$this->assertNotEmpty( menu_page_url( 'wp101-addons', false ) );
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
