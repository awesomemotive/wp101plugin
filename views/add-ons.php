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

<div class="wrap wp101-addons">
	<h1>
		<?php echo esc_html( _x( 'WP101 Add-ons', 'listings page title', 'wp101' ) ); ?>
	</h1>

	<pre><?php print_r( $addons ); ?></pre>
</div>
