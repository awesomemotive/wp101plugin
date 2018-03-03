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

	public function test_can_show_series() {
		wp_set_current_user( $this->factory()->user->create() );
		Shortcode\register_scripts_styles();

		$post = $this->factory()->post->create( [
			'post_status' => 'private',
		] );
		$api  = $this->mock_api();

		$api->shouldReceive( 'account_can' )->andReturn( true );
		$api->shouldReceive( 'get_series' )
			->with( 'test-series' )
			->andReturn( [
				'title' => 'Title',
				'topics' => [],
			] );

		$this->go_to( get_permalink( $post ) );

		$this->assertNotEmpty(
			Shortcode\render_shortcode( [
				'series' => 'test-series',
			] )
		);
		$this->assertTrue( wp_style_is( 'wp101', 'enqueued' ), 'Expected the styles to be enqueued.' );
	}

	public function test_can_show_topic() {
		wp_set_current_user( $this->factory()->user->create() );
		Shortcode\register_scripts_styles();

		$post = $this->factory()->post->create( [
			'post_status' => 'private',
		] );
		$api  = $this->mock_api();

		$api->shouldReceive( 'account_can' )->andReturn( true );
		$api->shouldReceive( 'get_topic' )
			->with( 'test-topic' )
			->andReturn( [
				'title'       => 'Title',
				'slug'        => 'test-topic',
				'url'         => 'http://example.com/test-topic',
				'description' => 'Foo bar baz',
			] );

		$this->go_to( get_permalink( $post ) );

		$this->assertNotEmpty(
			Shortcode\render_shortcode( [
				'video' => 'test-topic',
			] )
		);
		$this->assertTrue( wp_style_is( 'wp101', 'enqueued' ), 'Expected the styles to be enqueued.' );
	}

	public function test_gives_precedence_to_series_over_videos() {
		wp_set_current_user( $this->factory()->user->create() );
		$post = $this->factory()->post->create( [
			'post_status' => 'private',
		] );
		$api  = $this->mock_api();

		$api->shouldReceive( 'account_can' )->andReturn( true );
		$api->shouldReceive( 'get_series' )
			->once()
			->with( 'test-series' )
			->andReturn( [
				'title' => 'Title',
				'topics' => [],
			] );

		$this->go_to( get_permalink( $post ) );

		$this->assertNotEmpty(
			Shortcode\render_shortcode( [
				'series' => 'test-series',
				'video'  => 'test-topic',
			] )
		);
	}

	public function test_hides_content_on_public_pages() {
		$post = $this->factory()->post->create( [
			'post_status' => 'publish',
		] );
		$api  = $this->mock_api();

		$api->shouldReceive( 'account_can' )->andReturn( true );

		$this->go_to( get_permalink( $post ) );

		$this->assertEmpty(
			Shortcode\render_shortcode( [
				'video' => 'test-video',
			] )
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
