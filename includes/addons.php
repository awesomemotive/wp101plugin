<?php
/**
 * Assist subscribers in finding add-ons relevant to their sites.
 *
 * @package WP101
 */

namespace WP101\Addons;

use WP101\TemplateTags as TemplateTags;

/**
 * Scan the active plugins for anything that might have a WP101 add-on series available.
 */
function check_plugins() {
	$plugins   = get_option( 'active_plugins', array() );
	$addons    = TemplateTags\api()->get_addons();
	$available = [];

	foreach ( $addons['addons'] as $series ) {
		if ( empty( $series['restrictions']['plugins'] ) || $series['includedInSubscription'] ) {
			continue;
		}

		foreach ( $series['restrictions']['plugins'] as $plugin ) {
			if ( in_array( $plugin, $plugins, true ) ) {
				$available[] = [
					'title'  => $series['title'],
					'url'    => $series['url'],
					'plugin' => $plugin,
				];
			}
		}
	}

	update_option( 'wp101-available-series', $available, false );
}
add_action( 'activated_plugin', __NAMESPACE__ . '\check_plugins' );
