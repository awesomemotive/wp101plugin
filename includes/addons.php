<?php
/**
 * Assist subscribers in finding add-ons relevant to their sites.
 *
 * Note that this file will not be loaded if the user has defined DISABLE_NAG_NOTICES,
 * so the functionality contained here should be limited to notices about add-ons.
 *
 * @package WP101
 */

namespace WP101\Addons;

use WP101\Admin as Admin;
use WP101\TemplateTags as TemplateTags;

/**
 * Scan the active plugins for anything that might have a WP101 add-on series available.
 *
 * @param array $previous The previously-active plugins. This value is not used.
 * @param array $plugins  An array of active site plugins.
 */
function check_plugins( $previous, $plugins ) {
	if ( ! TemplateTags\api()->has_api_key() ) {
		return;
	}

	$api       = TemplateTags\api();
	$addons    = $api->get_addons();
	$available = [];

	foreach ( $addons['addons'] as $series ) {
		if ( empty( $series['restrictions']['plugins'] ) ) {
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

	// Filter out any purchased add-ons.
	if ( ! empty( $available ) ) {
		$purchased = wp_list_pluck( $api->get_playlist()['series'], 'slug', 'slug' );
		$available = array_diff_key( $available, $purchased );
	}

	update_option( 'wp101-available-series', $available, false );
}
add_action( 'update_option_active_plugins', __NAMESPACE__ . '\check_plugins', 10, 2 );

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

	// Filter out items that have been dismissed and/or purchased.
	$ignore = array_merge(
		(array) get_user_meta( get_current_user_id(), 'wp101-dismissed-notifications', true ),
		wp_list_pluck( TemplateTags\api()->get_playlist()['series'], 'slug', 'slug' )
	);

	$available = array_diff_key(
		get_option( 'wp101-available-series', [] ),
		array_fill_keys( $ignore, '' )
	);

	// Abort if we have nothing to say.
	if ( empty( $available ) ) {
		return;
	}

	// Register the callback to render the notification.
	add_action(
		'admin_notices',
		function () use ( $available ) {
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
					$link .= _x( ',', 'Oxford comma', 'wp101' );
				}
				$link .= _x( ' and ', 'separator between the last two items in a list', 'wp101' ) . $and;
			}

			wp_enqueue_script( 'wp101-addons' );

			render_notification(
				sprintf(
					/* Translators: %1$s is the add-on title(s). */
					__( 'Would you like to add the tutorial videos for %1$s from WP101?', 'wp101' ),
					$link
				),
				array_keys( $available )
			);
		}
	);
}
add_action( 'current_screen', __NAMESPACE__ . '\show_notifications' );

/**
 * Render a notification based on the WordPress standards.
 *
 * @param string $message The unescaped message contents.
 * @param array  $slug    An array of one or more add-on slugs, to be flattened into a data attribute.
 */
function render_notification( $message, $slug ) {
// phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact
?>

	<div class="notice notice-info is-dismissible" data-wp101-addon-slug="<?php echo esc_attr( implode( ',', (array) $slug ) ); ?>">
		<p><?php echo wp_kses_post( $message ); ?></p>
	</div>

<?php // phpcs:enable Generic.WhiteSpace.ScopeIndent.IncorrectExact
}

/**
 * Register the scripts necessary to save dismissed notifications.
 */
function register_scripts() {
	wp_register_script(
		'wp101-addons',
		WP101_URL . '/assets/js/wp101-addons.min.js',
		array( 'jquery' ),
		WP101_VERSION,
		true
	);

	wp_localize_script(
		'wp101-addons',
		'wp101Addons',
		[
			'nonce' => wp_create_nonce( 'dismiss-notice' ),
		]
	);
}
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\register_scripts' );

/**
 * Ajax handler for dismissal of add-on notices.
 */
function dismiss_notice() {
	if ( ! isset( $_POST['addons'], $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'dismiss-notice' ) ) {
		wp_send_json_error();
	}

	$user_id   = get_current_user_id();
	$dismissed = array_filter(
		array_merge(
			(array) get_user_meta( $user_id, 'wp101-dismissed-notifications', true ),
			(array) $_POST['addons']
		)
	);

	update_user_meta( $user_id, 'wp101-dismissed-notifications', array_unique( array_values( $dismissed ) ) );

	wp_send_json_success();
}
add_action( 'wp_ajax_wp101_dismiss_notice', __NAMESPACE__ . '\dismiss_notice' );
