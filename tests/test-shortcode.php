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

	public function test_renders_html_comment_for_authenticated_users_if_video_id_is_empty() {
		wp_set_current_user( $this->factory()->user->create( [
			'role' => 'editor',
		] ) );

		$this->assertStringMatchesFormat(
			'<!-- %s -->',
			(string) Shortcode\render_shortcode( [] ),
			'Authenticated users should see an HTML comment, hinting at what might be wrong.'
		);
	}
}
