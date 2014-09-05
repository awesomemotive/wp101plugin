<?php

class WP101_WPSEO_Videos {
	public static $instance;

	public function __construct() {
		self::$instance = $this;
		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
		add_filter( 'wp101_get_document'          , array( self::$instance, 'get_document' ), 10, 3 );
		add_action( 'wp101_after_help_topics'     , array( self::$instance, 'wpseo_help_topics_html' ) );
		add_action( 'wp101_after_edit_help_topics', array( self::$instance, 'wpseo_edit_help_topics_html' ) );
	}

	public function get_wpseo_help_topics( $wp_101 ) {

		$help_topics = false;

		if ( 'valid' == $wp_101->validate_api_key() ) {

			if ( $topics = get_transient( 'wp101_wpseo_topics' ) ) {
				$help_topics = $topics;
			} else {
				$result = wp_remote_get( $wp_101::$api_base . 'action=get_wpseo_topics&api_key=' . $wp_101->get_key(), array( 'timeout' => 45, 'sslverify' => false, 'user-agent' => 'WP101Plugin' ) );
				$result = json_decode( $result['body'], true );
				if ( ! $result['error'] && count( $result['data'] ) ) {
					set_transient( 'wp101_wpseo_topics', $result['data'], 30 ); // Good for a day.
					$help_topics = $result['data'];
				}
			}
		}

		return apply_filters( 'wp101_get_wpseo_help_topics', $help_topics, self::$instance );
	}

	public function wpseo_help_topics_html( $wp_101 ) {

		if ( 'valid' !== $wp_101->validate_api_key() ) {
			return;
		}

		echo self::$instance->get_wpseo_help_topics_html( $wp_101 );
	}

	public function wpseo_edit_help_topics_html( $wp_101 ) {
		echo self::$instance->get_wpseo_help_topics_html( $wp_101, true );
	}

	public function get_wpseo_help_topics_html( $wp_101, $edit_mode = false ) {

		$topics = $this->get_wpseo_help_topics( $wp_101 );

		if ( ! $topics ) {
			return false;
		}

		$output = '<h3 class="title">' . __( 'WP SEO Tutorial Videos', 'wp101' ) . '</h3>';

		$output .= '<ul class="wp101-topic-ul">';

		foreach ( $topics as $topic ) {
			if ( $edit_mode ) {
				$edit_links = '&nbsp;&nbsp;<small class="wp101-show">[<a data-nonce="' . wp_create_nonce( 'wp101-showhide-' . $topic['id'] ) . '" data-topic-id="' . $topic['id'] . '" href="#">show</a>]</small><small class="wp101-hide">[<a data-nonce="' . wp_create_nonce( 'wp101-showhide-' . $topic['id'] ) . '" data-topic-id="' . $topic['id'] . '" href="#">hide</a>]</small>';
				if ( $wp_101->is_hidden( $topic['id'] ) ) {
					$addl_class = 'wp101-hidden';
				} else {
					$addl_class = 'wp101-shown';
				}
			} else {
				if ( $wp_101->is_hidden( $topic['id'] ) ) {
					continue;
				}
				$edit_links = $addl_class = '';
			}
			$output .= '<li class="' . $addl_class . ' page-item-' . $topic['id'] . '"><span><a href="' . esc_url( admin_url( 'admin.php?page=wp101&document=' . $topic['id'] ) ) . '">' . esc_html( $topic['title'] ) . '</a></span>' . $edit_links . '</li>';
		}

		$output .= '</ul>';

		return $output;
	}

	public function get_document( $document, $id, $wp_101 ) {

		if ( ! $document ) {

			$topics = $this->get_wpseo_help_topics( $wp_101 );

			if ( isset( $topics[ $id ] ) ) {
				$document = $topics[ $id ];
			}
		}

		return apply_filters( 'wp101_wpseo_get_document', $document, $id, self::$instance );
	}

}

add_action( 'plugins_loaded', 'wp101_maybe_activate_wpseo_videos' );

function wp101_maybe_activate_wpseo_videos() {
	if ( function_exists( 'wpseo_auto_load' ) ) {
		return new WP101_WPSEO_Videos;
	}
}