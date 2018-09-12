<?php
/**
 * Uninstallation procedure for WP101.
 *
 * @package WP101
 */

namespace WP101\Uninstall;

use WP101\Admin as Admin;

/**
 * Clean up WP101 configuration when the plugin is uninstalled.
 */
function uninstall_plugin() {
	delete_option( 'wp101_api_key' );
	delete_option( 'wp101_db_version' );
	delete_option( 'wp101_hidden_topics' );
	delete_option( 'wp101_custom_topics' );
	delete_option( 'wp101_admin_restriction' );

	clear_caches();
}

/**
 * Delete all known WP101 transients + caches.
 */
function clear_caches() {
	Admin\clear_public_api_key();

	// Delete WP101 transients.
	delete_transient( 'wp101_topics' );
	delete_transient( 'wp101_jetpack_topics' );
	delete_transient( 'wp101_woocommerce_topics' );
	delete_transient( 'wp101_wpseo_topics' );
	delete_transient( 'wp101_message' );
	delete_transient( 'wp101_get_admin_count' );
	delete_transient( 'wp101_api_key_valid' );
}
