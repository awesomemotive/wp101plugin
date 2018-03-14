<?php
/**
 * Migrations for previous versions of WP101.
 *
 * @package WP101
 */

namespace WP101\Migrate;

use WP101\TemplateTags as TemplateTags;

/**
 * Apply any necessary migrations.
 */
function maybe_migrate() {
	if ( ! api_key_needs_migration() ) {
		return;
	}

	$api = TemplateTags\api();
	$key = $api->exchange_api_key();

	if ( is_wp_error( $key ) ) {
		// phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
		trigger_error( esc_html( $key->get_error_message() ), E_USER_WARNING );
		// phpcs:enable

		add_action( 'admin_notices', __NAMESPACE__ . '\render_migration_failure_notice' );

		return;
	}

	update_option( 'wp101_api_key', $key['apiKey'], false );
	$api->set_api_key( $key['apiKey'] );

	add_action( 'admin_notices', __NAMESPACE__ . '\render_migration_success_notice' );
}

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

/**
 * Display a notification when an automatic migration has succeeded.
 */
function render_migration_success_notice() {
?>

	<div class="notice notice-success">
		<p><?php esc_html_e( 'WP101 has automatically upgraded your API key to work with the latest version!', 'wp101' ); ?></p>
	</div>

<?php
}

/**
 * Display a notification when an automatic migration has failed.
 */
function render_migration_failure_notice() {
?>

	<div class="notice notice-error">
		<p><?php esc_html_e( 'WP101 was unable to automatically migrate your API key for the latest version.', 'wp101' ); ?></p>
	</div>

<?php
}
