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

?>

<div class="wrap wp101-settings">
	<h1>
		<?php echo esc_html_x( 'WordPress Video Tutorials', 'listings page title', 'wp101' ); ?>
	</h1>

	<main class="wp101-media">
		<h2 id="wp101-player-title"></h2>
		<div class="wp101-player-wrap">
			<iframe id="wp101-player" allowfullscreen></iframe>
		</div>
	</main>

	<?php if ( ! empty( $playlist['series'] ) ) : ?>
		<nav class="wp101-playlist card">
			<?php foreach ( $playlist['series'] as $series ) : ?>

				<?php

				/*
				 * Potentially skip over a series if there are restrictions which the current site
				 * does not meet (e.g. "don't show Jetpack videos on a site not running Jetpack.").
				 */
				if ( ! empty( $series['restrictions'] ) && ! empty( $series['restrictions']['plugins'] ) ) {
					if ( empty( array_filter( $series['restrictions']['plugins'], 'is_plugin_active' ) ) ) {
						continue;
					}
				} elseif ( empty( $series['topics'] ) ) {
					continue;
				}
				?>
				<div class="wp101-series">
					<h2><?php echo esc_html( $series['title'] ); ?></h2>
					<ol class="wp101-topics-list">
						<?php foreach ( $series['topics'] as $topic ) : ?>

							<li>
								<a href="#<?php echo esc_attr( $topic['slug'] ); ?>" data-media-title="<?php echo esc_attr( $topic['title'] ); ?>" data-media-slug="<?php echo esc_attr( $topic['slug'] ); ?>" data-media-src="<?php echo esc_attr( $topic['url'] ); ?>"><?php echo esc_html( $topic['title'] ); ?></a>
							</li>

						<?php endforeach; ?>
					</ol>
				</div>

			<?php endforeach; ?>

			<?php if ( TemplateTags\current_user_can_purchase_addons() ) : ?>
				<div class="wp101-addon-notice">
					<h2><?php echo esc_html_e( 'More from WP101', 'wp101' ); ?></h2>
					<p><?php esc_html_e( 'Get the most out of WP101 with even more content!', 'wp101' ); ?></p>
					<p><a href="<?php echo esc_url( menu_page_url( 'wp101-addons', false ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Get more videos from WP101', 'wp101' ); ?></a></p>
				</div>
			<?php endif; ?>
		</nav>
	<?php endif; ?>
</div>
