<?php
/**
 * WP101 settings page.
 *
 * @package WP101
 */

use WP101\Migrate as Migrate;
use WP101\TemplateTags as TemplateTags;

$api_key = TemplateTags\get_api_key();

/*
 * Mask the API key, showing only the first four characters.
 *
 * The rest should be replaced with "&#9679;", a Unicode black circle.
 *
 * Masked keys will look something like: "ABCD●●●●●●●●●●●●●●●●●●●●●●●●●●●●".
 */
$masked = str_pad(
	substr( $api_key, 0, 4 ),
	4 + 7 * ( strlen( $api_key ) - 4 ),
	'&#9679;',
	STR_PAD_RIGHT
);

?>

<div class="wrap wp101-settings">
	<h1><?php echo esc_html( _x( 'WP101 Settings', 'settings page title', 'wp101' ) ); ?></h1>

	<?php settings_errors(); ?>

	<h2 id="api-key"><?php echo esc_html_x( 'WP101Plugin.com API Key', 'settings section heading', 'wp101' ); ?></h2>
	<p><?php esc_html_e( 'Your API key enables your WordPress site to connect to WP101 and retrieve all of your videos.', 'wp101' ); ?></p>
	<p><strong><?php esc_html_e( 'Don\'t have an API key?', 'wp101' ); ?></strong></p>
	<p><a href="https://wp101plugin.com" class="button" target="_blank"><?php esc_html_e( 'Get your key now!', 'wp101' ); ?></a></p>

	<?php if ( defined( 'WP101_API_KEY' ) ) : ?>

		<div id="wp101-api-key-set-via-constant-notice" class="notice notice-info">
			<p><strong><?php esc_html_e( 'Your API key is defined in your wp-config.php file.', 'wp101' ); ?></strong></p>
			<p><?php esc_html_e( 'To make changes, please open your wp-config.php file in a text editor and look for the line that includes:', 'wp101' ); ?></p>
			<pre><code>define( 'WP101_API_KEY', '...' );</code></pre>
		</div>

	<?php else : ?>

		<div id="wp101-settings-api-key-form" <?php echo ! empty( $api_key ) ? 'class="hide-if-js"' : ''; ?>>
			<form method="post" action="options.php">
				<?php settings_fields( 'wp101' ); ?>

				<table class="form-table">
					<th scope="row">
						<label for="wp101-api-key"><?php esc_html_e( 'API key', 'wp101' ); ?></label>
					</th>
					<td>
						<input name="wp101_api_key" id="wp101-api-key" type="text" class="regular-text code" />
					</td>
				</table>

				<?php submit_button(); ?>
			</form>
		</div>

	<?php endif; ?>

	<?php if ( ! empty( $api_key ) ) : ?>

		<div id="wp101-settings-api-key-display">
			<table class="form-table">
				<th scope="row">
					<label for="wp101-api-key"><?php esc_html_e( 'API key', 'wp101' ); ?></label>
				</th>
				<td>
					<code><?php echo esc_html( $masked ); ?></code>
					<?php if ( ! defined( 'WP101_API_KEY' ) ) : ?>
						<button id="wp101-settings-replace-api-key" class="button" style="vertical-align: baseline;"><?php esc_html_e( 'Replace my API Key', 'wp101' ); ?></button>
					<?php endif; ?>
				</td>
			</table>
		</div>

	<?php endif; ?>
</div>
