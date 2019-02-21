<?php
/**
 * Catch deprecated functionality from older versions of the plugin.
 *
 * @package WP101
 */

namespace WP101\Deprecated;

/**
 * Inform a user when an older function, action, or filter has been deprecated.
 *
 * @param string $feature The function, action, or filter.
 * @param string $message A message to show the user.
 * @param string $version The plugin version in which the feature was deprecated.
 */
function mark_deprecated( $feature, $message, $version ) {
	// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
	return _doing_it_wrong( $feature, $message, $version );
	// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Check for anything hooked into deprecated actions.
 */
function discover_deprecated_actions() {
	$deprecated = [
		'wp101_after_edit_help_topics'        => '5.0.0',
		'wp101_after_edit_custom_help_topics' => '5.0.0',
		'wp101_after_help_topics'             => '5.0.0',
		'wp101_after_custom_help_topics'      => '5.0.0',
		'wp101_admin_action_add-video'        => '5.0.0',
		'wp101_admin_action_update-video'     => '5.0.0',
		'wp101_admin_action_restrict-admin'   => '5.0.0',
		'wp101_pre_includes'                  => '5.0.0',
	];

	foreach ( $deprecated as $hook => $version ) {
		if ( has_action( $hook ) ) {
			mark_deprecated( 'Action ' . $hook, "The {$hook} hook has been deprecated.", $version );
		}
	}
}
add_action( 'init', __NAMESPACE__ . '\discover_deprecated_actions' );

/**
 * Check for anything hooked into deprecated filters.
 */
function discover_deprecated_filters() {
	$deprecated = [
		'wp101_default_settings_role'         => '5.0.0',
		'wp101_too_many_admins'               => '5.0.0',
		'wp101_settings_management_user_args' => '5.0.0',
		'wp101_get_document'                  => '5.0.0',
		'wp101_get_help_topics'               => '5.0.0',
		'wp101_get_custom_help_topics'        => '5.0.0',
		'wp101_get_hidden_topics'             => '5.0.0',
	];

	foreach ( $deprecated as $hook => $version ) {
		if ( has_filter( $hook ) ) {
			mark_deprecated( 'Filter ' . $hook, "The {$hook} hook has been deprecated.", $version );
		}
	}
}
add_action( 'init', __NAMESPACE__ . '\discover_deprecated_filters' );
