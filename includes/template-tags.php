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
	return ( new API() )->get_api_key();
}

/**
 * Retrieve a topic by its slug.
 *
 * @param string $slug The topic slug.
 *
 * @return array|bool Either the corresponding topic array or a boolean false if either the API key
 *                    doesn't have access to the topic or the topic doesn't exist.
 */
function get_topic( $slug ) {
	return ( new API() )->get_topic( $slug );
}
