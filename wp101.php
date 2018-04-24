<?php
/*
Plugin Name: WP101
Description: A complete set of WordPress video tutorials for beginners, delivered directly in the dashboard.
Version: 4.0.2
Author: WP101Plugin.com
Author URI: https://wp101plugin.com/
Text Domain: wp101
*/

define( 'WP101_INC', __DIR__ . '/includes' );
define( 'WP101_VIEWS', __DIR__ . '/views' );
define( 'WP101_URL', plugins_url( null, __FILE__ ) );
define( 'WP101_BASENAME', plugin_basename( __FILE__ ) );
define( 'WP101_VERSION', '5.0.0' );

require_once WP101_INC . '/admin.php';
require_once WP101_INC . '/class-api.php';
require_once WP101_INC . '/class-wp101-plugin.php';
require_once WP101_INC . '/deprecated.php';
require_once WP101_INC . '/migrate.php';
require_once WP101_INC . '/shortcode.php';
require_once WP101_INC . '/template-tags.php';
require_once WP101_INC . '/uninstall.php';

/**
 * When the plugin is activated, check to see if it needs migrating from earlier versions.
 */
register_activation_hook( __FILE__, 'WP101\Migrate\maybe_migrate' );

/**
 * Register the uninstall callback.
 */
register_uninstall_hook( __FILE__, 'WP101\Uninstall\cleanup_plugin' );
