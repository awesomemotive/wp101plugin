<?php
/**
 * Shortcode handling for WP101 on the front-end of a site.
 *
 * @package WP101
 */

namespace WP101\Shortcode;

/**
 * Handle requests for the [wp101] shortcode.
 *
 * @param array $atts {
 *   Shortcode attributes.
 *
 *   @var string $video The video/topic slug.
 * }
 * @return string The rendered shortcode content.
 */
function render_shortcode( $atts ) {
	$atts  = shortcode_atts( [
		'video' => null,
	], $atts, 'wp101' );
	$debug = current_user_can( 'edit_posts' );

	if ( ! $atts['video'] ) {
		if ( $debug ) {
			return sprintf(
				'<!-- %s -->',
				esc_html( __( 'No WP101 video ID was provided', 'wp101' ) )
			);
		} else {
			return;
		}
	}

	return 'Embed';
}
add_shortcode( 'wp101', __NAMESPACE__ . '\render_shortcode' );
