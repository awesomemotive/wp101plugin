<?php
/**
 * Template tags for use in WP101 views.
 *
 * @package WP101
 */

namespace WP101\TemplateTags;

use WP101\API;

/**
 * Shortcut for retrieving the current API key.
 *
 * @see WP101\API::get_api_key()
 *
 * @return string The current API key, or an empty string if one is not set.
 */
function get_api_key() {
	return (new API)->get_api_key();
}
