<?php
/**
 * Tests for the plugin template tags.
 *
 * @package WP101
 */

namespace WP101\Tests;

use WP101\TemplateTags as TemplateTags;

/**
 * Tests for the plugin template tags, contained in includes/template-tags.php.
 */
class TemplateTagsTest extends TestCase {

	public function test_get_api_key() {
		$key = $this->set_api_key();

		$this->assertEquals( $key, TemplateTags\get_api_key() );
	}

	public function test_get_api_key_can_return_empty_string() {
		$this->assertEmpty( TemplateTags\get_api_key() );
	}
}
