<?php
/**
 * Tests for the plugin template tags.
 *
 * @package WP101
 */

namespace WP101\TemplateTags;

use WP101\TestCase;

/**
 * Tests for the plugin template tags, contained in includes/template-tags.php.
 */
class TemplateTagsTest extends TestCase {

	public function test_get_api_key() {
		$key = $this->set_api_key();

		$this->assertEquals( $key, get_api_key() );
	}

	public function test_get_api_key_can_return_empty_string() {
		$this->assertEmpty( get_api_key() );
	}

	public function test_current_user_can_purchase_addons() {
		$this->assertFalse( current_user_can_purchase_addons(), 'User is not authenticated.' );

		wp_set_current_user( $this->factory()->user->create( [
			'role' => 'author',
		] ) );

		$this->assertTrue( current_user_can_purchase_addons() );

		add_filter( 'wp101_addon_capability', function () {
			return 'unfiltered_html';
		} );

		$this->assertFalse( current_user_can( 'unfiltered_html' ) );
		$this->assertFalse( current_user_can_purchase_addons(), 'Required capability has changed.' );
	}
}
