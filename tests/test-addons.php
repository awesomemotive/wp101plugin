<?php
/**
 * Tests for the plugin settings.
 *
 * @package WP101
 */

namespace WP101\Tests;

use WP101\Addons as Addons;

/**
 * Tests for the plugin settings UI, defined in includes/settings.php.
 */
class AddonTest extends TestCase {

	public function test_check_plugins() {
		update_option( 'active_plugins', [
			'some-plugin/some-plugin.php',
			'another-plugin/another-plugin.php',
		] );

		$api = $this->mock_api();
		$api->shouldReceive( 'get_addons' )
			->once()
			->andReturn([
				'addons' => [
					[
						'title'                  => 'Learning Some Plugin',
						'url'                    => 'https://wp101plugin.com/series/some-plugin',
						'includedInSubscription' => false,
						'restrictions'           => [
							'plugins' => [
								'some-plugin/some-plugin.php',
							],
						],
					],
				],
			]);

		Addons\check_plugins();

		$this->assertEquals([
			[
				'title'  => 'Learning Some Plugin',
				'url'    => 'https://wp101plugin.com/series/some-plugin',
				'plugin' => 'some-plugin/some-plugin.php',
			],
		], get_option( 'wp101-available-series', [] ) );
	}

	public function test_check_plugins_excludes_addons_included_in_subscription() {
		update_option( 'active_plugins', [
			'some-plugin/some-plugin.php',
		] );

		$api = $this->mock_api();
		$api->shouldReceive( 'get_addons' )
			->andReturn([
				'addons' => [
					[
						'title'                  => 'Learning Some Plugin',
						'url'                    => 'https://wp101plugin.com/series/some-plugin',
						'includedInSubscription' => true,
						'restrictions'           => [
							'plugins' => [
								'some-plugin/some-plugin.php',
							],
						],
					],
				],
			]);

		Addons\check_plugins();

		$this->assertEmpty( get_option( 'wp101-available-series', [] ) );
	}
}
