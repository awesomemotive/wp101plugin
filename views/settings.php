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

	<form method="post" action="options.php">
		<?php settings_fields( 'wp101' ); ?>

		<section id="api-key">
			<?php if ( defined( 'WP101_API_KEY' ) && ! Migrate\wp_config_requires_updating( WP101_API_KEY ) ) : ?>
				<div class="notice notice-info">
					<p><?php esc_html_e( 'Your API key has been set in your wp-config.php file.', 'wp101' ); ?></p>
				</div>

			<?php else : ?>
				<?php if ( Migrate\wp_config_requires_updating() ) : ?>
					<div id="wp101-api-key-constant-upgrade-notice" class="notice notice-warning">
						<p><?php esc_html_e( 'Your API key has been updated to work with the latest version of WP101, but your wp-config.php requires updating:', 'wp101' ); ?></p>
						<ol>
							<li><?php esc_html_e( 'Open your site\'s wp-config.php file in a text editor.', 'wp101' ); ?></li>
							<li>
								<?php
									echo wp_kses_post( sprintf(
										/* Translators: %1$s is a code snippet for "define( 'WP101_API_KEY', '...' );" in wp-config.php. */
										__( 'Find the line that reads %1$s and either remove it completely or replace it with the following:', 'wp101' ),
										sprintf( "<code>define( 'WP101_API_KEY', '%s' );</code>", WP101_API_KEY )
									) );
								?>
								<pre><code>define( 'WP101_API_KEY', '<?php echo esc_html( TemplateTags\get_api_key() ); ?>' );</code></pre>
							</li>
							<li><?php esc_html_e( 'Save the wp-config.php file on your web server.', 'wp101' ); ?></li>
					</div>
				<?php endif; ?>

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
			<?php endif; ?>
		</section>

		<?php submit_button(); ?>
	</form>
</div>
