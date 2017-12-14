<?php
/**
 * Tests for the [wp101] shortcode.
 *
 * @package WP101
 */

namespace WP101\Tests;

use WP101\Shortcode as Shortcode;

class ShortcodeTest extends TestCase {

	public function test_shortcode_is_registered() {
		$this->assertTrue(
			shortcode_exists( 'wp101' ),
			'The [wp101] shortcode has not been registered.'
		);
	}

	public function test_returns_early_if_video_is_undefined() {
		$this->assertEmpty(
			Shortcode\render_shortcode( [] ),
			'There should be no output if no video ID is provided.'
		);
	}

	public function test_shortcode_debug() {
		$this->assertEmpty(
			Shortcode\shortcode_debug( 'Foo bar' ),
			'Unauthenticated users should see an empty string.'
		);

		wp_set_current_user( $this->factory()->user->create( [
			'role' => 'editor',
		] ) );

		$this->assertEquals(
			'<!-- Foo bar -->',
			Shortcode\shortcode_debug( 'Foo bar' ),
			'Authenticated users should see the debug message.'
		);
	}
}
