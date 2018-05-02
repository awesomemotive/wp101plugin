<?php
/**
 * Tests for WP101 migrations.
 *
 * @package WP101
 */

namespace WP101\Tests;

use PHPUnit\Framework\Error\Warning;
use WP_Error;
use WP101\Migrate as Migrate;

/**
 * Tests for migrating from older versions of the WP101 plugin.
 */
class MigrateTest extends TestCase {

	const LEGACY_API_KEY = 'abcdefghijkl';

	const CURRENT_API_KEY = 'abcdefghijklmnopqrstuvwxyz123456';

	/**
	 * @after
	 */
	public function remove_actions() {
		remove_all_actions( 'admin_notices' );
	}

	public function test_maybe_migrate() {
		$api = $this->mock_api();
		$api->shouldReceive( 'exchange_api_key' )
			->once()
			->andReturn( [
				'apiKey' => self::CURRENT_API_KEY,
			] );
		$this->set_api_key( self::LEGACY_API_KEY );

		// Populate some other legacy options.
		update_option( 'wp101_hidden_topics', [ 1, 2, 3 ] );

		Migrate\maybe_migrate();

		$this->assertEquals( self::CURRENT_API_KEY, get_option( 'wp101_api_key' ) );
		$this->assertEquals( 10, has_action( 'admin_notices', 'WP101\Migrate\render_migration_success_notice' ) );
		$this->assertFalse(
			get_option( 'wp101_hidden_topics', false ),
			'Expected the wp101_hidden_topics option to be deleted.'
		);
	}

	public function test_maybe_migrate_returns_early_if_keys_do_not_require_migration() {
		$api = $this->mock_api();
		$api->shouldReceive( 'exchange_api_key' )->never();
		$this->set_api_key( self::CURRENT_API_KEY );

		Migrate\maybe_migrate();

		$this->assertFalse( has_action( 'admin_notices' ) );
	}

	public function test_maybe_migrate_handles_wp_errors() {
		$api = $this->mock_api();
		$api->shouldReceive( 'exchange_api_key' )
			->once()
			->andReturn( new WP_Error( 'some error' ) );
		$this->set_api_key( self::LEGACY_API_KEY );

		$this->expectException( Warning::class );

		Migrate\maybe_migrate();

		$this->assertEquals( self::LEGACY_API_KEY, get_option( 'wp101_api_key' ) );
		$this->assertTrue( has_action( 'admin_notices', 'WP101\Migrate\render_migration_failure_notice' ) );
	}

	/**
	 * @dataProvider api_key_provider()
	 */
	public function test_api_key_needs_migration( $key, $expected ) {
		update_option( 'wp101_api_key', $key, false );

		$this->assertEquals( $expected, Migrate\api_key_needs_migration() );
	}

	/**
	 * @dataProvider api_key_provider()
	 */
	public function test_api_key_needs_migration_with_passed_value( $key, $expected ) {
		$this->assertEquals( $expected, Migrate\api_key_needs_migration( $key ) );
	}

	public function api_key_provider() {
		return [
			'Legacy API key'  => [ self::LEGACY_API_KEY, true ],
			'Current API key' => [ self::CURRENT_API_KEY, false ],
		];
	}

	public function test_wp_config_requires_updating_without_constant() {
		$this->assertFalse( defined( 'WP101_API_KEY' ) );
		$this->assertFalse( Migrate\wp_config_requires_updating() );
	}

	/**
	 * @requires extension runkit
	 */
	public function test_wp_config_requires_updating_with_old_constant() {
		define( 'WP101_API_KEY', self::LEGACY_API_KEY );

		$this->assertTrue( Migrate\wp_config_requires_updating() );
	}

	/**
	 * @requires extension runkit
	 */
	public function test_wp_config_requires_updating_with_new_constant() {
		define( 'WP101_API_KEY', self::CURRENT_API_KEY );

		$this->assertFalse( Migrate\wp_config_requires_updating() );
	}

	public function test_render_migration_success_notice() {
		ob_start();
		Migrate\render_migration_success_notice();
		$output = ob_get_clean();

		$this->assertContainsSelector( 'div.notice.notice-success', $output );
	}

	public function test_render_migration_failure_notice() {
		ob_start();
		Migrate\render_migration_failure_notice();
		$output = ob_get_clean();

		$this->assertContainsSelector( 'div.notice.notice-error', $output );
	}
}
