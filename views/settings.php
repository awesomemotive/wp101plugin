<?php
/**
 * WP101 settings page.
 *
 * @package WP101
 */

use WP101\Migrate as Migrate;
use WP101\TemplateTags as TemplateTags;

?>

<div class="wrap wp101-settings">
	<h1><?php echo esc_html( _x( 'WP101 Settings', 'settings page title', 'wp101' ) ); ?></h1>

	<?php settings_errors(); ?>

	<?php if ( defined( 'WP101_API_KEY' ) ) : ?>
		<div id="wp101-api-key-set-via-constant-notice" class="notice notice-info">
			<p><strong><?php esc_html_e( 'Your API key is defined in your wp-config.php file.', 'wp101' ); ?></strong></p>
			<p><?php esc_html_e( 'To make changes, please open your wp-config.php file in a text editor and look for the line that includes:', 'wp101' ); ?></p>
			<pre><code>define( 'WP101_API_KEY', '...' );</code></pre>
		</div>

	<?php else : ?>

		<form method="post" action="options.php">
			<?php settings_fields( 'wp101' ); ?>

			<section id="api-key">
				<h2><?php echo esc_html( _x( 'WP101Plugin.com API Key', 'settings section heading', 'wp101' ) ); ?></h2>
				<p><?php esc_html_e( 'Your API key enables your WordPress site to connect to WP101 and retrieve all of your videos.', 'wp101' ); ?></p>
				<p><strong><?php esc_html_e( 'Don\'t have an API key?', 'wp101' ); ?></strong></p>
				<p><a href="https://wp101plugin.com" target="_blank"><?php esc_html_e( 'Sign up to get your key now!', 'wp101' ); ?></a></p>
				<table class="form-table">
					<th scope="row">
						<label for="wp101-api-key"><?php esc_html_e( 'API key', 'wp101' ); ?></label>
					</th>
					<td>
						<input name="wp101_api_key" id="wp101-api-key" type="text" value="<?php echo esc_attr( TemplateTags\get_api_key() ); ?>" class="regular-text code" />
					</td>
				</table>

			</section>
			<?php submit_button(); ?>
		</form>
	<?php endif; ?>
</div>
