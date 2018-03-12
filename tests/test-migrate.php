<?php
/**
 * Tests for WP101 migrations.
 *
 * @package WP101
 */

namespace WP101\Tests;

use WP101\Migrate as Migrate;

/**
 * Tests for migrating from older versions of the WP101 plugin.
 */
class MigrateTest extends TestCase {

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
			'Legacy API key'  => [ 'abcdefghijkl', true ],
			'Current API key' => [ 'abcdefghijklmnopqrstuvwxyz123456', false ],
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
		define( 'WP101_API_KEY', 'abcdefghijkl' );

		$this->assertTrue( Migrate\wp_config_requires_updating() );
	}

	/**
	 * @requires extension runkit
	 */
	public function test_wp_config_requires_updating_with_new_constant() {
		define( 'WP101_API_KEY', 'abcdefghijklmnopqrstuvwxyz123456' );

		$this->assertFalse( Migrate\wp_config_requires_updating() );
	}
}
