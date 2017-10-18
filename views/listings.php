<?php
/**
 * Show available content from WP101.
 *
 * @package WP101
 */

?>

<div class="wrap" class="wp101-settings">
	<h1>
		<?php echo esc_html( _x( 'WordPress Video Tutorials', 'listings page title', 'wp101' ) ); ?>
	</h1>

	<main id="wp101-media">
		<p>The main content will go here.</p>
	</main>

	<nav class="wp101-playlist card">
		<?php foreach ( $playlist as $series ) : ?>

			<div class="wp101-series">
				<h2><?php echo esc_html( $series['title'] ); ?></h2>
				<ol class="wp101-topics-list">
					<?php foreach ( $series['topics'] as $topic ) : ?>

						<li>
							<a href="#"><?php echo esc_html( $topic['title'] ); ?></a>
						</li>

					<?php endforeach; ?>
				</ol>
			</div>

		<?php endforeach; ?>
	</nav>
</div>
