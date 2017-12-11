<?php
/**
 * Admin UI for WP101.
 *
 * @package WP101
 */

namespace WP101\Admin;

use WP101\API;

/**
 * Register scripts and styles to be used in WP admin.
 *
 * @param string $hook
 */
function enqueue_scripts( $hook ) {
	wp_register_style(
		'wp101',
		WP101_URL . '/assets/css/wp101.css',
		null,
		WP101_VERSION,
		'all'
	);

	wp_register_script(
		'wp101',
		WP101_URL . '/assets/js/wp101.js',
		array( 'jquery-ui-accordion' ),
		WP101_VERSION,
		true
	);

	// Only enqueue on WP101 pages.
	if ( 'toplevel_page_wp101' === $hook ) {
		wp_enqueue_style( 'wp101' );
		wp_enqueue_script( 'wp101' );
	}
}
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\enqueue_scripts' );

/**
 * Register the WP101 settings page.
 */
function register_menu_pages() {
	$api = new API;

	// If the API key hasn't been configured, *only* show the settings page.
	if ( ! $api->has_api_key() ) {
		add_menu_page(
			_x( 'WP101', 'page title', 'wp101' ),
			_x( 'Video Tutorials', 'menu title', 'wp101' ),
			'manage_options',
			'wp101-settings',
			__NAMESPACE__ . '\render_settings_page',
			'dashicons-video-alt3'
		);

	} else {
		add_menu_page(
			_x( 'WP101', 'page title', 'wp101' ),
			_x( 'Video Tutorials', 'menu title', 'wp101' ),
			'read',
			'wp101',
			__NAMESPACE__ . '\render_listings_page',
			'dashicons-video-alt3'
		);

		add_submenu_page(
			'wp101',
			_x( 'WP101 Settings', 'page title', 'wp101' ),
			_x( 'Settings', 'menu title', 'wp101' ),
			'manage_options',
			'wp101-settings',
			__NAMESPACE__ . '\render_settings_page'
		);
	}
}
add_action( 'admin_menu', __NAMESPACE__ . '\register_menu_pages' );

/**
 * Register the settings within WordPress.
 */
function register_settings() {
	register_setting( 'wp101', 'wp101_api_key', [
		'description'       => _x( 'The key used to authenticate with WP101plugin.com.', 'wp101' ),
		'sanitize_callback' => 'sanitize_text_field',
		'show_in_rest'      => false,
	] );
}
add_action( 'admin_init', __NAMESPACE__ . '\register_settings' );

/**
 * Render the WP101 listings page.
 */
function render_listings_page() {
	$api = new API;
	$playlist = $api->get_playlist();

	require_once WP101_VIEWS . '/listings.php';
}

/**
 * Render the WP101 settings page.
 */
function render_settings_page() {
	require_once WP101_VIEWS . '/settings.php';
}
