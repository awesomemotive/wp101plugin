<?php
/**
 * Tests for the plugin settings.
 *
 * @package WP101
 */

namespace WP101\Tests;

use WP_Error;
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
	 * @testWith ["some-hook", false]
	 *           ["wp101", true]
	 *           ["toplevel_page_wp101", true]
	 *           ["video-tutorials_page_wp101-settings", true]
	 *           ["video-tutorials_page_wp101-addons", true]
	 *           ["random-wp101", true]
	 *
	 * @param string $hook     The hook to be executed.
	 * @param bool   $enqueued Whether or not the assets should be enqueued for this hook.
	 */
	public function test_enqueue_scripts_enqueues_on_wp101_pages( $hook, bool $enqueued ) {
		Admin\enqueue_scripts( $hook );

		$this->assertSame( $enqueued, wp_style_is( 'wp101-admin', 'enqueued' ) );
		$this->assertSame( $enqueued, wp_script_is( 'wp101-admin', 'enqueued' ) );

		if ( $enqueued ) {
			$this->assertSame( 10, has_action( 'admin_notices', 'WP101\Admin\display_api_errors' ) );
		}
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

		$api = $this->mock_api();
		$api->shouldReceive( 'get_playlist' )
			->once()
			->andReturn( [
				'series' => [
					'some series',
				],
			] );
		$api->shouldReceive( 'get_addons' )
			->once()
			->andReturn( [
				'addons' => [
					[
						'slug' => 'some-add-on',
					],
				],
			] );

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
	public function test_register_menu_pages_hides_listings_if_listings_are_available() {
		wp_set_current_user( $this->factory()->user->create( [
			'role' => 'administrator',
		] ) );

		$api = $this->mock_api();
		$api->shouldReceive( 'get_playlist' )
			->once()
			->andReturn( [
				'series' => [],
			] );

		$menu = $this->get_menu_items();

		$this->assertEquals( 'wp101-settings', $menu['parent'][2], 'Expected "wp101-settings" as the menu slug.' );
	}

	public function test_register_menu_pages_hides_empty_addon_pages() {
		wp_set_current_user( $this->factory()->user->create( [
			'role' => 'administrator',
		] ) );

		$api = $this->mock_api();
		$api->shouldReceive( 'get_playlist' )
			->andReturn( [
				'series' => [
					'some series',
				],
			] );
		$api->shouldReceive( 'get_addons' )
			->once()
			->andReturn( [
				'addons' => [],
			] );

		$menu = $this->get_menu_items();

		$this->assertCount( 2, $menu['children'] );
		$this->assertEmpty( menu_page_url( 'wp101-addons', false ) );
	}

	public function test_register_settings() {
		Admin\register_settings();

		$settings = get_registered_settings();

		$this->assertArrayHasKey( 'wp101_api_key', $settings );
		$this->assertEquals( 'wp101', $settings['wp101_api_key']['group'] );
		$this->assertEquals( 'WP101\Admin\sanitize_api_key', $settings['wp101_api_key']['sanitize_callback'] );
		$this->assertFalse( $settings['wp101_api_key']['show_in_rest'], 'The API key should never be exposed via the WP REST API.' );
	}

	/**
	 * Work around the well-known "when storing a new setting, the sanitize callback filter is
	 * is called twice" issue with the WordPress Settings API.
	 *
	 * @link   https://developer.wordpress.org/reference/functions/register_setting/#comment-content-2488
	 * @ticket https://github.com/liquidweb/wp101plugin/issues/73
	 */
	public function test_sanitize_api_key_is_only_called_once() {
		Admin\register_settings();

		$api = $this->mock_api();
		$api->shouldReceive( 'get_account' )
			->once()
			->andReturn( [ 'some-account-details' ] );

		// Call it twice, to mimic what we'd see when calling update_option() for a new option.
		sanitize_option( 'wp101_api_key', 'someKey' );
		sanitize_option( 'wp101_api_key', 'someKey' );

		ob_start();
		settings_errors();
		$output = ob_get_clean();

		$this->assertSelectorCount( 1, '#setting-error-api_key', $output );
	}

	public function test_settings_link_is_injected_into_plugin_action_links() {
		$actions = apply_filters( 'plugin_action_links_' . WP101_BASENAME, array() );

		$this->assertContains( get_admin_url( null, 'admin.php?page=wp101' ), $actions['settings'] );
	}

	public function test_is_relevant_series_without_restrictions() {
		$series = [
			'restrictions' => [
				'plugins' => [],
			],
		];

		$this->assertTrue(
			Admin\is_relevant_series( $series ),
			'Series without restrictions are always relevant.'
		);
	}

	public function test_is_relevant_series_with_unmet_restrictions() {
		$series = [
			'restrictions' => [
				'plugins' => [
					'some-plugin/some-plugin.php',
					'some-other-plugin/some-other-plugin.php',
				],
			],
		];

		update_option( 'active_plugins', [] );

		$this->assertFalse(
			Admin\is_relevant_series( $series ),
			'If requirements aren\'t met, the series is not relevant.'
		);
	}

	public function test_is_relevant_series_with_some_met_restrictions() {
		$series = [
			'restrictions' => [
				'plugins' => [
					'some-plugin/some-plugin.php',
					'some-other-plugin/some-other-plugin.php',
				],
			],
		];

		update_option( 'active_plugins', [
			'some-plugin/some-plugin.php',
		] );

		$this->assertTrue(
			Admin\is_relevant_series( $series ),
			'Only meeting a single requirement is necessary for relevancy.'
		);
	}

	public function test_is_relevant_series_with_all_met_restrictions() {
		$series = [
			'restrictions' => [
				'plugins' => [
					'some-plugin/some-plugin.php',
					'some-other-plugin/some-other-plugin.php',
				],
			],
		];

		update_option( 'active_plugins', [
			'some-plugin/some-plugin.php',
			'some-other-plugin/some-other-plugin.php',
		] );

		$this->assertTrue( Admin\is_relevant_series( $series ) );
	}

	/**
	 * @link https://github.com/liquidweb/wp101plugin/issues/40
	 */
	public function test_render_addons_page_indicates_when_available_series_are_visible() {
		$api = $this->mock_api();
		$api->shouldReceive( 'get_playlist' )
			->andReturn( [
				'series' => [
					[
						'slug' => 'example-series',
					],
				],
			] );
		$api->shouldReceive( 'get_addons' )
			->andReturn( [
				'addons' => [
					[
						'title'              => 'Example Series',
						'slug'               => 'example-series',
						'meets_requirements' => true,
					],
				]
			] );

		ob_start();
		Admin\render_addons_page();
		$output = ob_get_clean();

		$this->assertContains( get_admin_url( null, 'admin.php?page=wp101' ), $output );
	}

	/**
	 * @link https://github.com/liquidweb/wp101plugin/issues/40
	 */
	public function test_render_addons_page_indicates_when_available_series_are_hidden() {
		$api = $this->mock_api();
		$api->shouldReceive( 'get_playlist' )
			->andReturn( [
				'series' => [
					[
						'slug' => 'example-series',
					],
				],
			] );
		$api->shouldReceive( 'get_addons' )
			->andReturn( [
				'addons' => [
					[
						'title'              => 'Example Series',
						'slug'               => 'example-series',
						'meets_requirements' => false,
					],
				]
			] );

		ob_start();
		Admin\render_addons_page();
		$output = ob_get_clean();

		$this->assertElementNotContains(
			get_admin_url( null, 'admin.php?page=wp101' ),
			'.wp101-addon-button a',
			$output
		);
		$this->assertSelectorCount( 1, '.wp101-addon .notice-info', $output );
	}

	/**
	 * @testWith ["wp101-no-api-key"]
	 * @link https://github.com/liquidweb/wp101plugin/issues/67
	 */
	public function test_special_wp_errors_are_skipped_in_error_output( $code ) {
		$api    = API::get_instance();
		$method = $this->get_accessible_method( $api, 'handle_error' );
		$method->invoke( $api, new WP_Error( $code, 'My error message' ) );

		ob_start();
		Admin\display_api_errors();
		$output = ob_get_clean();

		$this->assertNotContains( 'My error message', $output );
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
			'children' => isset( $submenu['wp101'] ) ? $submenu['wp101'] : [],
		];
	}
}
