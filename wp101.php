<?php
/**
 * Plugin Name:       WP101 Video Tutorial Plugin
 * Plugin URI:        https://wp101plugin.com
 * Description:       A complete set of video tutorials for WordPress, WooCommerce, Jetpack, and more... delivered directly in the dashboard.
 * Version:           5.3
 * Author:            WP101®
 * Author URI:        https://wp101.com
 * Text Domain:       wp101
 * Requires at least: 4.1
 * Requires PHP:      5.4
 *
 * @package WP101
 */

define( 'WP101_INC', __DIR__ . '/includes' );
define( 'WP101_VIEWS', __DIR__ . '/views' );
define( 'WP101_URL', plugins_url( null, __FILE__ ) );
define( 'WP101_BASENAME', plugin_basename( __FILE__ ) );
define( 'WP101_VERSION', '5.1.0' );

require_once WP101_INC . '/admin.php';
require_once WP101_INC . '/class-api.php';
require_once WP101_INC . '/class-wp101-plugin.php';
require_once WP101_INC . '/deprecated.php';
require_once WP101_INC . '/migrate.php';
require_once WP101_INC . '/shortcode.php';
require_once WP101_INC . '/template-tags.php';
require_once WP101_INC . '/uninstall.php';

/**
 * Forego the add-ons include if the site owner has opted-out of these sorts of notifications.
 *
 * @link https://codex.wordpress.org/Plugin_API/Action_Reference/admin_notices#Disable_Nag_Notices
 */
if ( ! defined( 'DISABLE_NAG_NOTICES' ) || ! DISABLE_NAG_NOTICES ) {
    require_once WP101_INC . '/addons.php';
}

/**
 * When the plugin is activated, check to see if it needs migrating from earlier versions.
 */
register_activation_hook( __FILE__, 'WP101\Migrate\maybe_migrate' );

/**
 * When the plugin is deactivated, flush caches.
 */
register_deactivation_hook( __FILE__, 'WP101\Uninstall\clear_caches' );

/**
 * Register the uninstall callback.
 */
register_uninstall_hook( __FILE__, 'WP101\Uninstall\uninstall_plugin' );
