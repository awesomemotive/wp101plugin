<?php
/**
 * Show available add-ons from WP101.
 *
 * @global $api       An instance of WP101\API;
 * @global $addons    An array of add-ons from the WP101 API.
 * @global $purchased Slugs of any Series this site already has access to.
 *
 * @package WP101
 */

use WP101\TemplateTags as TemplateTags;

?>

<main class="wrap wp101-addons">
	<h1>
		<?php echo esc_html( _x( 'WP101 Add-ons', 'listings page title', 'wp101' ) ); ?>
	</h1>

	<?php settings_errors(); ?>

	<?php if ( empty( $addons['addons'] ) ) : ?>

		<div class="notice notice-warning">
			<p><?php esc_html_e( 'There are no add-ons currently available for WP101!', 'wp101' ); ?></p>
		</div>

	<?php else : ?>

		<p><?php esc_html_e( 'Enhance your WP101 experience with these add-ons:', 'wp101' ); ?></p>

		<div class="wp101-addon-list">
			<?php foreach ( $addons['addons'] as $addon ) : ?>
				<?php $has_addon = isset( $addon['slug'] ) && in_array( $addon['slug'], $purchased, true ); ?>

				<div class="card wp101-addon">
					<h2>
						<?php echo esc_html( $addon['title'] ); ?>
						<?php if ( $has_addon ) : ?>
							<span class="wp101-addon-tag subscribed"><?php esc_html_e( 'Subscribed', 'wp101' ); ?></span>
						<?php endif; ?>
					</h2>
					<?php if ( ! empty( $addon['excerpt'] ) ) : ?>
						<div class="wp101-addon-description">
							<?php echo wp_kses_post( wpautop( $addon['excerpt'] ) ); ?>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $addon['topics'] ) ) : ?>
						<h3><?php esc_html_e( 'In this series:', 'wp101' ); ?></h3>
						<?php TemplateTags\list_topics( $addon['topics'], 3, $addon['url'] ); ?>
					<?php endif; ?>

					<?php if ( $has_addon && ! empty( $addon['meets_requirements'] ) ) : ?>
						<p class="wp101-addon-button">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=wp101' ) ); ?>" class="button button-secondary">
								<?php
									/* Translators: %1$s is the add-on name. */
									echo esc_html( sprintf( __( 'Watch %1$s Videos', 'wp101' ), $addon['title'] ) );
								?>
							</a>
						</p>

					<?php elseif ( $has_addon ) : ?>
						<div class="notice notice-info inline">
							<p><?php esc_html_e( 'Your WP101 Plugin subscription includes access to this course, but it looks like it might not be useful on this site.', 'wp101' ); ?></p>
						</div>

					<?php elseif ( ! empty( $addon['url'] ) ) : ?>
						<p class="wp101-addon-button">
							<a href="<?php echo esc_url( $addon['url'] ); ?>" class="button button-primary" target="_blank">
								<?php
									/* Translators: %1$s is the add-on name. */
									echo esc_html( sprintf( __( 'Get the %1$s Add-on', 'wp101' ), $addon['title'] ) );
								?>
							</a>
						</p>
					<?php endif; ?>
				</div>

			<?php endforeach; ?>
		</div>

	<?php endif; ?>
</main>
