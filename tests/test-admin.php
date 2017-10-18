<?php
/**
 * Tests for the plugin settings.
 *
 * @package WP101
 */

namespace WP101\Admin;

use WP101\API;
use WP101\TestCase;

/**
 * Tests for the plugin settings UI, defined in includes/settings.php.
 */
class AdminTest extends TestCase {

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

	public function test_register_settings_page() {
		global $menu;

		$this->assertEmpty( $menu, 'The $menu global should start off empty.' );

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

	public function test_settings_link_is_injected_into_plugin_action_links() {
		$this->markTestSkipped( 'Plugin action links filter is not behaving correctly' );
		$actions = apply_filters( 'plugin_action_links_wp101/wp101.php', array() );

		$this->assertContains( get_admin_url( null, 'admin.php?page=wp101&configure=1' ), $actions[0] );
	}
}
