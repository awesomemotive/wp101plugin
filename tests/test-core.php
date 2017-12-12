<?php
/**
 * Tests for the core plugin functionality.
 *
 * @package WP101
 */

namespace WP101\Core;

use WP101_Plugin;
use WP_UnitTestCase;

/**
 * Tests for the core plugin functionality, contained in includes/core.php.
 */
class CoreTest extends WP_UnitTestCase {

	public function test_plugin_textdomain_is_loaded() {
		$this->markTestSkipped( 'Even in the current production version, the text-domain is never actually loaded' );

		$this->assertFalse( is_textdomain_loaded( 'wp101' ) );

		do_action( 'init' );

		$this->assertTrue( is_textdomain_loaded( 'wp101' ) );
	}

	public function test_database_upgrade_procedure() {
		$current_version = WP101_Plugin::$db_version;

		update_option( 'wp101_db_version', $current_version - 1 );
		set_transient( 'wp101_topics', uniqid() );

		WP101_Plugin::get_instance()->init();

		$this->assertEquals(
			$current_version,
			get_option( 'wp101_db_version' ),
			'The stored wp101_db_version option should be equal to ' . $current_version
		);
		$this->assertEmpty(
			get_transient( 'wp101_topics' ),
			'The wp101_topics transient should be flushed following a database upgrade'
		);
	}
}
