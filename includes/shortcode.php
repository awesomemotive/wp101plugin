<?php
/**
 * Shortcode handling for WP101 on the front-end of a site.
 *
 * @package WP101
 */

namespace WP101\Shortcode;

use WP101\TemplateTags as TemplateTags;

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
	$atts = shortcode_atts( [
		'video' => null,
	], $atts, 'wp101' );

	if ( ! $atts['video'] ) {
		return shortcode_debug( __( 'No WP101 video ID was provided', 'wp101' ) );
	}

	$topic = TemplateTags\get_topic( $atts['video'] );

	return '<pre>' . print_r( $topic, true ) . '</pre>';
}
add_shortcode( 'wp101', __NAMESPACE__ . '\render_shortcode' );

/**
 * If the current user is logged in and has the ability to edit_posts, print a debug message in the
 * form of an HTML comment. Otherwise, return an empty string.
 *
 * @param string $message The debug message to *maybe* display.
 * @return string Either an HTML comment with the $message or an empty string.
 */
function shortcode_debug( $message ) {
	$output = '';

	if ( current_user_can( 'edit_posts' ) ) {
		$output = sprintf( '<!-- %1$s -->', esc_html( $message ) );
	}

	return $output;
}
