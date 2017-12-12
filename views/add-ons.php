<?php
/**
 * Show available add-ons from WP101.
 *
 * @global $api    An instance of WP101\API;
 * @global $addons An array of add-ons from the WP101 API.
 *
 * @package WP101
 */

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

				<div class="card wp101-addon">
					<h2><?php echo esc_html( $addon['title'] ); ?></h2>
					<?php if ( ! empty( $addon['description'] ) ) : ?>
						<div class="wp101-addon-description">
							<?php echo wp_kses_post( wpautop( $addon['description'] ) ); ?>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $addon['topics'] ) ) : ?>
						<h3><?php esc_html_e( 'In this series:', 'wp101' ); ?></h3>
						<ol>
							<?php foreach ( $addon['topics'] as $topic ) : ?>

								<li><?php echo esc_html( $topic['title'] ); ?></li>

							<?php endforeach; ?>
						</ol>
					<?php endif; ?>

					<p class="wp101-addon-button"><a href="" class="button button-primary"><?php echo esc_html_e( 'Get Add-on', 'wp101' ); ?></a></p>
				</div>

			<?php endforeach; ?>
		</div>

	<?php endif; ?>
</main>
