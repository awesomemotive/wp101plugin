<?php
/**
 * API integration with WP101.
 *
 * @package WP101
 */

namespace WP101;

use WP_Error;

class API {

	/**
	 * The user's WP101 API key.
	 *
	 * @var string
	 */
	protected $api_key;

	/**
	 * Base URL for the WP101 plugin API.
	 *
	 * This value can be overridden via the WP101_API_URL constant.
	 *
	 * @var string
	 */
	const API_URL = 'https://wp101plugin.com/api';

	/**
	 * The User-Agent string that will be passed with API requests.
	 *
	 * @var string
	 */
	const USER_AGENT = 'WP101-Plugin';

	/**
	 * Construct a new instance of the API.
	 *
	 * @param string $api_key Optional. The API key to use for requests. Default is null.
	 */
	public function __construct( $api_key = null ) {
		$this->api_key = $api_key;
	}

	/**
	 * Retrieve the API key.
	 *
	 * @return string The API key.
	 */
	public function get_api_key() {
		if ( $this->api_key ) {
			return $this->api_key;
		}

		if ( defined( 'WP101_API_KEY' ) ) {
			$this->api_key = WP101_API_KEY;
		} else {
			$this->api_key = get_option( 'wp101_api_key', '' );
		}

		return $this->api_key;
	}

	/**
	 * Retrieve all series available to the user, based on API key.
	 *
	 * @return array An array of all available series and topics.
	 */
	public function get_playlist() {
		$response = $this->send_request( 'GET', '/playlist' );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		return json_decode( wp_remote_retrieve_body( $response ), true );
	}

	/**
	 * Build an API request URI.
	 *
	 * @param string $path Optional. The API endpoint. Default is '/'.
	 * @param array  $args Optional. Query string arguments for the URI. Default is empty.
	 * @return string The URI for the API request.
	 */
	protected function build_uri( $path = '/', array $args = [] ) {
		$base = defined( 'WP101_API_URL' ) ? WP101_API_URL : self::API_URL;

		// Ensure the $path has a leading slash.
		if ( '/' !== substr( $path, 0, 1 ) ) {
			$path = '/' . $path;
		}

		return add_query_arg( $args, $base . $path );
	}

	/**
	 * Send a request to the WP101 API.
	 *
	 * @param string $method The HTTP method.
	 * @param string $path   The API request path.
	 * @param array  $query  Optional. Query string arguments. Default is empty.
	 * @param array  $args   Optional. Additional HTTP arguments. For a full list of options,
	 *                       @see wp_remote_request().
	 *
	 * @return array|WP_Error The HTTP response array or a WP_Error object if something went wrong.
	 */
	protected function send_request( $method, $path, $query = [], $args = [] ) {
		$uri  = $this->build_uri( $path, $query );
		$args = wp_parse_args( $args, [
			'method'     => $method,
			'timeout'    => 30,
			'user-agent' => self::USER_AGENT,
			'headers'    => [
				'Authorization'    => 'Bearer ' . $this->get_api_key(),
				'X-Forwarded-Host' => site_url(),
			],
		]);

		return wp_remote_request( $uri, $args );
	}
}
