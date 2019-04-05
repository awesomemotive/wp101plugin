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
 *
 * @return WP_Error|null
 */
function maybe_migrate() {
	$api = TemplateTags\api();
	$key = $api->get_api_key();

	// Empty key is set via constant.
	if ( defined( 'WP101_API_KEY' ) && ! WP101_API_KEY ) {
		add_action( 'admin_notices', __NAMESPACE__ . '\render_constant_empty_notice' );

		return;
	}

	// Schedule additional migrations if this is a multisite network.
	if ( is_multisite() && is_super_admin() && ! get_site_option( 'wp101-bulk-migration-lock', false ) ) {
		if ( ! wp_next_scheduled( 'wp101-bulk-migration' ) ) {
			wp_schedule_single_event( time(), 'wp101-bulk-migration' );
		}

		add_site_option( 'wp101-bulk-migration-lock', true );
	}

	// Key is either empty or already good to go.
	if ( ! api_key_needs_migration( $key ) ) {
		return;
	}

	$key = $api->exchange_api_key();

	if ( is_wp_error( $key ) ) {
		// phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
		trigger_error( esc_html( $key->get_error_message() ), E_USER_WARNING );
		// phpcs:enable

		$api->set_api_key( null );

		add_action( 'admin_notices', __NAMESPACE__ . '\render_migration_failure_notice' );

		return $key;
	}

	update_option( 'wp101_api_key', $key['apiKey'], false );
	$api->set_api_key( $key['apiKey'] );

	// Display a notice if the wp-config.php file needs updating.
	if ( defined( 'WP101_API_KEY' ) ) {
		add_action( 'admin_notices', __NAMESPACE__ . '\render_constant_upgrade_notice' );
	} else {
		add_action( 'admin_notices', __NAMESPACE__ . '\render_migration_success_notice' );
	}

	// Clean up old data.
	delete_option( 'wp101_custom_topics' );
	delete_option( 'wp101_hidden_topics' );
}
add_action( 'wp101_migrate_site', __NAMESPACE__ . '\maybe_migrate' );

/**
 * Determine whether the API key option requires migration.
 *
 * @param string $api_key Optional. The current API key to evaluate. Default is null.
 *
 * @return bool Whether or not the API key needs exchanged.
 */
function api_key_needs_migration( $api_key ) {
	return $api_key && 32 !== mb_strlen( $api_key );
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

	return ! WP101_API_KEY || api_key_needs_migration( WP101_API_KEY );
}

/**
 * Display a notification when an automatic migration has succeeded.
 */
function render_migration_success_notice() {
	// phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact
?>

	<div id="wp101-api-key-upgraded" class="notice notice-success">
		<p><?php esc_html_e( 'WP101 has automatically upgraded your API key to work with the latest version!', 'wp101' ); ?></p>
	</div>

<?php // phpcs:enable Generic.WhiteSpace.ScopeIndent.IncorrectExact
}

/**
 * Display a notification when an automatic migration has failed.
 */
function render_migration_failure_notice() {
	// phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact
?>

	<div id="wp101-api-key-upgrade-failed" class="notice notice-error">
		<p><?php esc_html_e( 'WP101 was unable to automatically migrate your API key for the latest version.', 'wp101' ); ?></p>
	</div>

<?php // phpcs:enable Generic.WhiteSpace.ScopeIndent.IncorrectExact
}

/**
 * Display a notification when the WP101_API_KEY constant in wp-config.php needs updating.
 */
function render_constant_upgrade_notice() {
	// phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact
?>

	<div id="wp101-api-key-constant-upgrade-notice" class="notice notice-warning">
		<p><?php esc_html_e( 'Your API key has been updated to work with the latest version of WP101, but your wp-config.php requires updating:', 'wp101' ); ?></p>
		<ol>
			<li><?php esc_html_e( 'Open your site\'s wp-config.php file in a text editor.', 'wp101' ); ?></li>
			<li>
				<?php
					echo wp_kses_post(
						sprintf(
							/* Translators: %1$s is a code snippet for "define( 'WP101_API_KEY', '...' );" in wp-config.php. */
							__( 'Find the line that reads %1$s and either remove it completely or replace it with the following:', 'wp101' ),
							sprintf( "<code>define( 'WP101_API_KEY', '%s' );</code>", WP101_API_KEY )
						)
					);
				?>
				<pre><code>define( 'WP101_API_KEY', '<?php echo esc_html( TemplateTags\get_api_key() ); ?>' );</code></pre>
			</li>
			<li><?php esc_html_e( 'Save the wp-config.php file on your web server.', 'wp101' ); ?></li>
	</div>

<?php // phpcs:enable Generic.WhiteSpace.ScopeIndent.IncorrectExact
}

/**
 * Display a notification when the WP101_API_KEY constant in wp-config.php is blocking progress.
 */
function render_constant_empty_notice() {
	// phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact
?>

	<div id="wp101-api-key-constant-empty-notice" class="notice notice-warning">
		<p><?php echo wp_kses_post( __( 'An empty <code>WP101_API_KEY</code> constant has been defined in wp-config.php and should be removed:', 'wp101' ) ); ?></p>
		<ol>
			<li><?php esc_html_e( 'Open your site\'s wp-config.php file in a text editor.', 'wp101' ); ?></li>
			<li>
				<?php
					echo wp_kses_post(
						sprintf(
							/* Translators: %1$s is a code snippet for "define( 'WP101_API_KEY', '...' );" in wp-config.php. */
							__( 'Find the line that reads %1$s and either remove it completely or set the value to your WP101 Plugin API key.', 'wp101' ),
							sprintf( "<code>define( 'WP101_API_KEY', '%s' );</code>", WP101_API_KEY )
						)
					);
				?>
			</li>
			<li><?php esc_html_e( 'Save the wp-config.php file on your web server.', 'wp101' ); ?></li>
	</div>

<?php // phpcs:enable Generic.WhiteSpace.ScopeIndent.IncorrectExact
}

/**
 * Iterate through a WordPress Multisite installation and migrate each site.
 *
 * @param int $batch_size Optional. The number of sites to update in each pass. Default is 100.
 *
 * @return int The number of sites that were migrated.
 */
function migrate_multisite( $batch_size = 100 ) {
	$api      = TemplateTags\api();
	$migrated = 0;

	// Abort early if this isn't a multisite instance.
	if ( ! is_multisite() ) {
		return $migrated;
	}

	$plugin    = plugin_basename( dirname( __DIR__ ) . '/wp101.php' );
	$site_args = [
		'fields'       => 'ids',
		'site__not_in' => [ get_current_blog_id() ],
		'number'       => $batch_size,
		'offset'       => 0,
	];
	$blogs     = get_sites( $site_args );

	// Iterate over the sites, triggering migrations.
	while ( ! empty( $blogs ) ) {
		$blog = array_shift( $blogs );

		switch_to_blog( $blog );

		// Ensure each site is loading its API key fresh.
		$api->clear_api_key();

		// Trigger a migration.
		$migration = maybe_migrate();

		// If we ran into an issue, remove the lock so it can try again.
		if ( is_wp_error( $migration ) ) {
			delete_site_option( 'wp101-bulk-migration-lock' );

			return $migrated;
		}

		// Restore the previous site context.
		restore_current_blog();

		// Increment the counter.
		$migrated++;

		// Reset the query at the end of the batch.
		if ( empty( $blogs ) ) {
			$site_args['offset'] = $migrated;

			$blogs = get_sites( $site_args );
		}
	}

	return $migrated;
}
add_action( 'wp101-bulk-migration', __NAMESPACE__ . '\migrate_multisite' );
