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
	 * A roll-up of any and all API errors that have occurred.
	 *
	 * @var array
	 */
	protected $errors = [];

	/**
	 * The Singleton instance.
	 *
	 * @var API
	 */
	protected static $instance;

	/**
	 * Base URL for the WP101 plugin API.
	 *
	 * This value can be overridden via the WP101_API_URL constant.
	 *
	 * @var string
	 */
	const API_URL = 'https://app.wp101plugin.com/api';

	/**
	 * Option key for the site's (private) API key.
	 *
	 * @var string
	 */
	const API_KEY_OPTION = 'wp101_api_key';

	/**
	 * The User-Agent string that will be passed with API requests.
	 *
	 * @var string
	 */
	const USER_AGENT = 'WP101-Plugin';

	/**
	 * Construct a new instance of the API.
	 */
	protected function __construct() {}

	/**
	 * Prevent the object from being cloned.
	 */
	private function __clone() {}

	/**
	 * Prevent the object from being deserialized.
	 */
	private function __wakeup() {}

	/**
	 * Retrieve the singular instance of the class.
	 *
	 * @return API The API instance.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new API();
		}

		return self::$instance;
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
			$this->api_key = get_option( self::API_KEY_OPTION, null );
		}

		return $this->api_key;
	}

	/**
	 * Explicitly set the API key.
	 *
	 * @param string $key The API key to use.
	 */
	public function set_api_key( $key ) {
		$this->api_key = $key;
	}

	/**
	 * Clear the current value for $this->api_key.
	 */
	public function clear_api_key() {
		$this->api_key = null;
	}

	/**
	 * Retrieve any API errors that have occurred.
	 *
	 * @return array An array of WP_Error objects.
	 */
	public function get_errors() {
		return $this->errors;
	}

	/**
	 * Retrieve an *uncached* response from the /account endpoint.
	 *
	 * @return array An array of all account attributes or an empty array if no account was found.
	 */
	public function get_account() {
		$response = $this->send_request( 'GET', '/account' );

		if ( is_wp_error( $response ) ) {
			return [];
		}

		return $response;
	}

	/**
	 * Retrieve the public API key from WP101.
	 *
	 * Public API keys are generated on a per-domain basis by the WP101 API, and thus are safe for
	 * using client-side without fear of compromising the private key.
	 *
	 * @return string|WP_Error The public API key or any WP_Error that occurred.
	 */
	public function get_public_api_key() {
		$key_name   = $this->get_public_api_key_name();
		$public_key = get_transient( $key_name );

		if ( $public_key ) {
			return $public_key;
		}

		$response = $this->send_request( 'GET', '/account' );

		if ( is_wp_error( $response ) ) {
			return $response;

		} elseif ( ! isset( $response['publicKey'] ) || empty( $response['publicKey'] ) ) {
			return new WP_Error( 'missing-public-key', __( 'Unable to retrieve a valid public key from WP101.' ) );
		}

		$public_key = $response['publicKey'];

		set_transient( $key_name, $public_key, 0 );

		return $public_key;
	}

	/**
	 * Get the public API key name.
	 *
	 * The name consists of a static prefix followed by the first 8 characters of an md5 hash of
	 * the site URL.
	 *
	 * @return string
	 */
	public function get_public_api_key_name() {
		return 'wp101-public-api-key-' . substr( md5( site_url( '/' ) ), 0, 8 );
	}

	/**
	 * Retrieve all available add-ons for WP101.
	 *
	 * @return array An array of all available add-ons.
	 */
	public function get_addons() {
		$response = $this->send_request( 'GET', '/add-ons', [], [], 12 * HOUR_IN_SECONDS );

		if ( is_wp_error( $response ) ) {
			$this->handle_error( $response );

			return [
				'addons' => [],
			];
		}

		// Catch responses that don't contain an array of add-ons.
		if ( ! isset( $response['addons'] ) ) {
			return [
				'addons' => [],
			];
		}

		// Append the public API key to add-on URLs.
		$api_key = $this->get_public_api_key();

		array_walk(
			$response['addons'],
			function ( &$addon ) use ( $api_key ) {
				$addon['url']                = add_query_arg( 'apiKey', $api_key, $addon['url'] );
				$addon['meets_requirements'] = true;

				// Does the current site configuration meet requirements?
				if ( ! empty( $addon['restrictions']['plugins'] ) ) {
					$requirements = array_filter( $addon['restrictions']['plugins'], 'is_plugin_active' );

					$addon['meets_requirements'] = ! empty( $requirements );
				}
			}
		);

		return $response;
	}

	/**
	 * Retrieve all series available to the user, based on API key.
	 *
	 * @return array An array of all available series and topics.
	 */
	public function get_playlist() {
		$response = $this->send_request( 'GET', '/playlist', [], [], MINUTE_IN_SECONDS );

		if ( is_wp_error( $response ) || ! isset( $response['series'] ) ) {
			if ( is_wp_error( $response ) ) {
				$this->handle_error( $response );
			}

			return [
				'series' => [],
			];
		}

		/**
		 * Filter the topics that should be displayed in the playlist.
		 *
		 * @param array $excluded An array of topic slugs and/or legacy IDs that should be excluded
		 *                        from display in the playlist.
		 */
		$excluded = apply_filters( 'wp101_excluded_topics', [] );

		if ( ! empty( $excluded ) ) {
			foreach ( $response['series'] as $key => $series ) {
				$response['series'][ $key ]['topics'] = array_filter(
					$series['topics'],
					function ( $topic ) use ( $excluded ) {
						return ! in_array( $topic['slug'], $excluded, true )
							&& ! in_array( $topic['legacy_id'], $excluded, true );
					}
				);
			}
		}

		return $response;
	}

	/**
	 * Retrieve a single series by its slug.
	 *
	 * @param string $series The series slug.
	 * @return array|bool The series array for the given slug, or false if the given series was not
	 *                    found in the API-provided playlist.
	 */
	public function get_series( $series ) {
		$playlist = $this->get_playlist();

		// Iterate through the series and their topics to find a match.
		foreach ( (array) $playlist['series'] as $single_series ) {
			if ( $series === $single_series['slug'] ) {
				return $single_series;
			}
		}

		return false;
	}

	/**
	 * Retrieve a single topic by its slug.
	 *
	 * @param string $topic The topic slug.
	 * @return array|bool The topic array for the given slug, or false if the given topic was not
	 *                    found in the API-provided playlist.
	 */
	public function get_topic( $topic ) {
		$playlist = $this->get_playlist();

		// Iterate through the series and their topics to find a match.
		foreach ( (array) $playlist['series'] as $series ) {
			foreach ( $series['topics'] as $single_topic ) {
				if ( $topic === $single_topic['slug'] ) {
					return $single_topic;
				}
			}
		}

		return false;
	}

	/**
	 * Determine if an API key has been set.
	 *
	 * @return bool
	 */
	public function has_api_key() {
		return (bool) $this->get_api_key();
	}

	/**
	 * Determine if the current account has the given capability.
	 *
	 * @param string $cap The capability to check.
	 *
	 * @return bool Whether or not the user's account has the given capability.
	 */
	public function account_can( $cap ) {
		$response = $this->send_request( 'GET', '/account', [], [], 12 * HOUR_IN_SECONDS );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		return isset( $response['capabilities'] ) && in_array( $cap, (array) $response['capabilities'], true );
	}

	/**
	 * Exchange a legacy API key for a 5.x API key.
	 */
	public function exchange_api_key() {
		$api_key = $this->get_api_key();

		if ( empty( $api_key ) ) {
			return new WP_Error( 'wp101-api', __( 'Cannot exchange an empty API key.', 'wp101' ) );
		}

		$response = wp_remote_post(
			$this->build_uri( '/key-exchange' ),
			[
				'timeout'    => 30,
				'user-agent' => self::USER_AGENT,
				'body'       => [
					'apiKey'       => $api_key,
					'domain'       => site_url(),

					/**
					 * Pass along custom topics to the key exchange, enabling these to be created
					 * within WP101 automatically.
					 *
					 * @deprecated 5.0.0
					 *
					 * @param array $custom_topics An array of custom WP101 topics.
					 */
					'customTopics' => apply_filters( 'wp101_get_custom_help_topics', get_option( 'wp101_custom_topics' ) ),

					/**
					 * Filter legacy WP101 topic IDs.
					 *
					 * This filter was available in WP101 4.x and below, and is only being applied so
					 * that hidden topics are preserved during the API key exchange process.
					 *
					 * @deprecated 5.0.0
					 *
					 * @param array $topic_ids An array of WP101 topics that should be hidden.
					 */
					'hiddenTopics' => apply_filters( 'wp101_get_hidden_topics', get_option( 'wp101_hidden_topics' ) ),
				],
			]
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response_code = wp_remote_retrieve_response_code( $response );

		if ( ! in_array( $response_code, [ 200, 201 ], true ) ) {
			return new WP_Error(
				'wp101-api',
				__( 'The WP101 API request failed.', 'wp101' ),
				$response
			);
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( 'fail' === $body['status'] ) {
			return new WP_Error(
				'wp101-api',
				__( 'The WP101 API request failed.', 'wp101' ),
				$body['data']
			);
		}

		return $body['data'];
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
	 *                       see wp_remote_request().
	 * @param int    $cache  Optional. The number of seconds for which the result should be cached.
	 *                       Default is 0 seconds (no caching).
	 *
	 * @return array|WP_Error The HTTP response body or a WP_Error object if something went wrong.
	 */
	protected function send_request( $method, $path, $query = [], $args = [], $cache = 0 ) {
		$api_key = $this->get_api_key();

		if ( empty( $api_key ) ) {
			return new WP_Error(
				'wp101-no-api-key',
				__( 'No API key has been set, unable to make request.', 'wp101' )
			);
		}

		$uri       = $this->build_uri( $path, $query );
		$args      = wp_parse_args(
			$args,
			[
				'timeout'    => 30,
				'user-agent' => self::USER_AGENT,
				'headers'    => [
					'Authorization'    => 'Bearer ' . $api_key,
					'Method'           => $method,
					'X-Forwarded-Host' => site_url(),
				],
			]
		);
		$cache_key = self::generate_cache_key( $uri, $args );
		$cached    = get_transient( $cache_key );

		// Return the cached version, if we have it.
		if ( $cache && $cached ) {
			return $cached;
		}

		$response = wp_remote_request( $uri, $args );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( 'fail' === $body['status'] ) {
			return new WP_Error(
				'wp101-api',
				/* Translators: %1$s is the first error message from the API response. */
				sprintf( __( 'The WP101 API request failed: %1$s', 'wp101' ), current( (array) $body['data'] ) ),
				$body['data']
			);
		}

		// Cache the result.
		if ( $cache ) {
			set_transient( $cache_key, $body['data'], $cache );
		}

		return $body['data'];
	}

	/**
	 * Trigger an error and optionally block subsequent API requests.
	 *
	 * @param WP_Error $error The WP_Error object.
	 */
	protected function handle_error( $error ) {
		$this->errors[ $error->get_error_code() ] = $error;
	}

	/**
	 * Given a URI and arguments, generate a cache key for use with WP101's internal caching system.
	 *
	 * @param string $uri  The API URI, with any query string arguments.
	 * @param array  $args Optional. An array of HTTP arguments used in the request. Default is empty.
	 * @return string A cache key.
	 */
	public static function generate_cache_key( $uri, $args = [] ) {
		return 'wp101_' . substr( md5( $uri . wp_json_encode( $args ) ), 0, 12 );
	}
}
