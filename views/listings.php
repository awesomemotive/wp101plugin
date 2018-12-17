<?php
/**
 * Show available content from WP101.
 *
 * @global $api      An instance of WP101\API;
 * @global $playlist An array of media from the WP101 API.
 *
 * @package WP101
 */

use WP101\TemplateTags as TemplateTags;

$query_args = array(
	'apiKey' => $public_key,
	'host'   => site_url(),
);
$addons     = $api->get_addons();

?>

<div class="wrap wp101-settings">
	<h1>
		<?php echo esc_html_x( 'WordPress Video Tutorials', 'listings page title', 'wp101' ); ?>
	</h1>

	<?php if ( ! empty( $playlist['series'] ) ) : ?>

		<main class="wp101-media">
			<h2 id="wp101-player-title"></h2>
			<div class="wp101-player-wrap">
				<iframe id="wp101-player" allowfullscreen></iframe>
			</div>
		</main>

		<nav class="wp101-playlist card">
			<?php foreach ( $playlist['series'] as $series ) : ?>
				<div class="wp101-series">
					<h2><?php echo esc_html( $series['title'] ); ?></h2>
					<ol class="wp101-topics-list">
						<?php foreach ( $series['topics'] as $topic ) : ?>

							<li>
								<a href="#<?php echo esc_attr( $topic['slug'] ); ?>" data-media-title="<?php echo esc_attr( $topic['title'] ); ?>" data-media-slug="<?php echo esc_attr( $topic['slug'] ); ?>" data-media-src="<?php echo esc_attr( add_query_arg( $query_args, $topic['url'] ) ); ?>"><?php echo esc_html( $topic['title'] ); ?></a>
							</li>

						<?php endforeach; ?>
					</ol>
				</div>

			<?php endforeach; ?>

			<?php if ( TemplateTags\current_user_can_purchase_addons() && ! empty( $addons['addons'] ) ) : ?>
				<div class="wp101-addon-notice">
					<h2><?php echo esc_html_e( 'More from WP101', 'wp101' ); ?></h2>
					<p><?php esc_html_e( 'Get the most out of WP101 with even more content!', 'wp101' ); ?></p>
					<p><a href="<?php echo esc_url( menu_page_url( 'wp101-addons', false ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Get more videos from WP101', 'wp101' ); ?></a></p>
				</div>
			<?php endif; ?>
		</nav>

	<?php else : ?>

		<div class="notice notice-error">
			<p><strong><?php esc_html_e( 'There was a problem retrieving content from WP101plugin.com.', 'wp101' ); ?></strong></p>
			<p>
				<?php
				if ( current_user_can( 'manage_options' ) ) {
					echo wp_kses_post(
						sprintf(
							/* Translators: %1$s is the "WP101 Settings" admin page. */
							__( '<a href="%1$s">Please verify your API key</a> and ensure your WP101plugin.com account has access to the desired content.', 'wp101' ),
							esc_url( menu_page_url( 'wp101-settings', false ) )
						)
					);
				} else {
					esc_html_e( 'Please contact a site administrator for further assistance.', 'wp101' );
				}
				?>
			</p>
		</div>

	<?php endif; ?>
</div>
