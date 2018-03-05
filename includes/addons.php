<?php
/**
 * Assist subscribers in finding add-ons relevant to their sites.
 *
 * @package WP101
 */

namespace WP101\Addons;

use WP101\Admin as Admin;
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
				$available[ $series['slug'] ] = [
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

/**
 * In the administration area, alert users who are capable of purchasing add-ons to any new series
 * that may be of interest.
 *
 * @param WP_Screen $screen The current WP admin screen.
 */
function show_notifications( $screen ) {
	$screens = [ 'plugins', 'toplevel_page_wp101', 'video-tutorials_page_wp101-settings' ];

	if ( ! in_array( $screen->id, $screens, true ) || ! current_user_can( Admin\get_addon_capability() ) ) {
		return;
	}

	$available = get_option( 'wp101-available-series', [] );
	$dismissed = (array) get_user_meta( get_current_user_id(), 'wp101-dismissed-notifications', true );

	// Abort if we have nothing to say.
	if ( empty( $available ) ) {
		return;
	}

	// Register the callback to render the notification.
	add_action( 'admin_notices', function () use ( $available ) {
		$links = [];

		foreach ( (array) $available as $addon ) {
			$links[] = sprintf( '<strong><a href="%1$s" target="_blank">%2$s</a></strong>', $addon['url'], $addon['title'] );
		}

		// Flatten the $links array into a single string.
		if ( 1 === count( $links ) ) {
			$link = array_shift( $links );
		} else {
			$and  = array_pop( $links );
			$link = implode( _x( ', ', 'separator for multiple series in a sentence', 'wp101' ), $links );
			if ( 2 <= count( $links ) ) {
				$link .= _x( ', ', 'Oxford comma', 'wp101' );
			}
			$link .= _x( 'and ', 'separator between the last two items in a list', 'wp101' ) . $and;
		}

		render_notification( sprintf(
			/* Translators: %1$s is the add-on title(s). */
			__( 'Get the most out of your site with %1$s from WP101.', 'wp101' ),
			$link
		) );
	} );
}
add_action( 'current_screen', __NAMESPACE__ . '\show_notifications' );

/**
 * Render a notification based on the WordPress standards.
 *
 * @param string $message The unescaped message contents.
 */
function render_notification( $message ) {
?>

	<div class="notice notice-info is-dismissible">
		<p><?php echo wp_kses_post( $message ); ?></p>
	</div>

<?php
}
