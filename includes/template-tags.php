<?php
/**
 * Template tags for use in WP101 views.
 *
 * @package WP101
 */

namespace WP101\TemplateTags;

use WP101\Admin as Admin;
use WP101\API;

/**
 * Enqueue scripts used for front-end display.
 */
function enqueue_scripts_styles() {
	wp_register_style(
		'wp101',
		WP101_URL . '/assets/css/wp101.css',
		null,
		WP101_VERSION
	);

	wp_register_script(
		'wp101',
		WP101_URL . '/assets/js/wp101.js',
		null,
		WP101_VERSION,
		true
	);

	wp_localize_script(
		'wp101',
		'wp101',
		[
			'apiKey' => api()->get_public_api_key(),
		]
	);
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_scripts_styles' );
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\enqueue_scripts_styles' );

/**
 * Grant access to the API Singleton.
 *
 * @return API The API Singleton instance.
 */
function api() {
	return API::get_instance();
}

/**
 * Shortcut for retrieving the current API key.
 *
 * @see WP101\API::get_api_key()
 *
 * @return string The current API key, or an empty string if one is not set.
 */
function get_api_key() {
	return api()->get_api_key();
}

/**
 * Determine if the current user can purchase add-ons.
 *
 * @return bool
 */
function current_user_can_purchase_addons() {
	return current_user_can( Admin\get_addon_capability() );
}

/**
 * Retrieve a series by its slug.
 *
 * @param string $slug The series slug.
 *
 * @return array|bool Either the corresponding series array or a boolean false if either the API
 *                    key doesn't have access to the series or the series doesn't exist.
 */
function get_series( $slug ) {
	return api()->get_series( $slug );
}

/**
 * Retrieve a topic by its slug.
 *
 * @param string $slug The topic slug.
 *
 * @return array|bool Either the corresponding topic array or a boolean false if either the API key
 *                    doesn't have access to the topic or the topic doesn't exist.
 */
function get_topic( $slug ) {
	return api()->get_topic( $slug );
}

/**
 * Loop over a list of videos, optionally truncating to the first $limit topics.
 *
 * @param array  $topics The topics within the add-on.
 * @param int    $limit  Optional. The maximum number of topics to show. Default is 0 (all).
 * @param string $link   Optional. A URL to link the "and X more!" string to. Default is null.
 */
function list_topics( $topics, $limit = 0, $link = null ) {
	$counter = 0;
	$items   = [];

	foreach ( $topics as $topic ) {
		$counter++;

		// Append the list item.
		$items[] = sprintf( '<li>%s</li>', esc_html( $topic['title'] ) );

		// We've reached our limit.
		if ( $limit <= $counter ) {
			$remaining = count( $topics ) - $counter;

			if ( 1 > $remaining ) {
				continue;

			} elseif ( 1 === $remaining ) {
				$label = __( '&hellip;and one more video!', 'wp101' );

			} else {
				/* Translators: %1$d is the number of videos in the series not shown. */
				$label = sprintf( __( '&hellip; and %1$d more videos!', 'wp101' ), $remaining );
			}

			if ( $link ) {
				$items[] = sprintf(
					'<li class="wp101-addon-more-topics"><a href="%1$s" target="_blank">%2$s</a></li>',
					esc_url( $link ),
					esc_html( $label )
				);

			} else {
				$items[] = sprintf( '<li class="wp101-addon-more-topics">%s</li>', esc_html( $label ) );
			}

			break;
		}
	}

	if ( ! empty( $items ) ) {
		echo wp_kses_post(
			sprintf(
				'<ol class="wp101-addon-topic-list">%s</ol>',
				implode( '', $items )
			)
		);
	}
}
