<?php
/**
 * Migrations for previous versions of WP101.
 *
 * @package WP101
 */

namespace WP101\Migrate;

use WP101\TemplateTags as TemplateTags;

/**
 * Determine whether the API key option requires migration.
 *
 * @param string $api_key Optional. The current API key to evaluate. Default is null.
 *
 * @return bool Whether or not the API key needs exchanged.
 */
function api_key_needs_migration( $api_key = null ) {
	if ( ! $api_key ) {
		$api_key = TemplateTags\api()->get_api_key();
	}

	return 32 !== strlen( $api_key );
}

/**
 * Notify the user if the WP101_API_KEY constant in wp-config.php requires updating.
 *
 * If the constant is undefined, this will always return false.
 *
 * @return bool Whether or not the WP101_API_KEY constant requires updating.
 */
function wp_config_requires_updating() {
	if ( ! defined( 'WP101_API_KEY' ) ) {
		return false;
	}

	return api_key_needs_migration( WP101_API_KEY );
}
