<?php
/**
 * Tests for the WP101 API integration.
 *
 * @package WP101
 */

namespace WP101\Tests;

use WP_Error;
use WP101\API;
use WP101_Plugin;

/**
 * Tests for the core plugin functionality, contained in includes/class-api.php.
 */
class ApiTest extends TestCase {

	public function setUp() {
		parent::setUp();

		$this->set_api_key( 'default-api-key' );
	}

	public function tearDown() {
		parent::tearDown();

		// Clean up after any send_request() tests.
		remove_all_filters( 'pre_http_request' );
	}

	public function test_get_api_key_returns_from_cache() {
		$api = API::get_instance();
		$key = md5( uniqid() );

		$prop = new \ReflectionProperty( $api, 'api_key' );
		$prop->setAccessible( true );
		$prop->setValue( $api, $key );

		$this->assertEquals( $key, $api->get_api_key() );
	}

	/**
	 * @requires extension runkit
	 */
	public function test_get_api_key_reads_constant() {
		define( 'WP101_API_KEY', md5( uniqid() ) );

		$this->assertEquals( WP101_API_KEY, API::get_instance()->get_api_key() );
	}

	public function test_get_api_key_reads_from_options() {
		$key = md5( uniqid() );
		$this->set_api_key( $key );

		$this->assertFalse(
			defined( 'WP101_API_KEY' ),
			'This test is predicated on the WP101_API_KEY constant not being set.'
		);

		$this->assertEquals( $key, API::get_instance()->get_api_key() );
	}

	public function test_set_api_key() {
		$api = API::get_instance();
		$key = md5( uniqid() );

		$api->set_api_key( $key );

		$this->assertEquals( $key, $api->get_api_key() );
	}

	public function test_clear_api_key() {
		$api  = API::get_instance();
		$prop = new \ReflectionProperty( $api, 'api_key' );
		$prop->setAccessible( true );

		$api->set_api_key( md5( uniqid() ) );
		$api->clear_api_key();

		$this->assertNull( $prop->getValue( $api ) );
	}

	public function test_has_api_key() {
		$api = API::get_instance();

		delete_option( 'wp101_api_key' );

		$this->assertFalse( $api->has_api_key() );

		$this->set_api_key( 'some-api-key' );

		$this->assertTrue( $api->has_api_key() );
	}

	public function test_get_errors() {
		$errors = [ 'foo' => new WP_Error( 'foo', uniqid() ) ];
		$api    = API::get_instance();
		$prop   = new \ReflectionProperty( $api, 'errors' );
		$prop->setAccessible( true );
		$prop->setValue( $api, $errors );

		$this->assertSame( $errors, $api->get_errors() );
	}

	public function test_get_account() {
		$json = [
			'status' => 'success',
			'data'   => [
				'publicKey' => uniqid(),
			],
		];

		$this->set_expected_response( [
			'body' => wp_json_encode( $json ),
		] );

		$this->assertEquals(
			$json['data'],
			API::get_instance()->get_account(),
			'The account node should be returned.'
		);
	}

	public function test_get_account_suppresses_wp_errors() {
		$this->set_expected_response( function () {
			return new WP_Error( 'msg' );
		} );

		$this->assertEmpty(
			API::get_instance()->get_account(),
			'If an error occurs, get_account() should return an empty array.'
		);
	}

	public function test_get_public_api_key() {
		$this->assertFalse( get_transient( API::PUBLIC_API_KEY_OPTION ) );

		$json = [
			'status' => 'success',
			'data'   => [
				'publicKey' => uniqid(),
			],
		];

		$this->set_expected_response( [
			'body' => wp_json_encode( $json ),
		] );

		$key = API::get_instance()->get_public_api_key();

		$this->assertEquals(
			$json['data']['publicKey'],
			$key,
			'The public API should be determined by the WP101 API.'
		);
		$this->assertEquals( $key, get_transient( API::PUBLIC_API_KEY_OPTION ) );
	}

	public function test_get_public_api_key_returns_from_transients_if_populated() {
		$key = uniqid();

		set_transient( API::PUBLIC_API_KEY_OPTION, $key, 0 );

		$this->assertEquals( $key, API::get_instance()->get_public_api_key() );
	}

	public function test_get_public_api_key_surfaces_wp_errors() {
		$error = new WP_Error( 'msg' );

		$this->set_expected_response( function () use ( $error ) {
			return $error;
		} );

		$this->assertSame( $error, API::get_instance()->get_public_api_key() );
	}

	public function test_get_public_api_key_returns_wp_error_if_no_key_was_found() {
		$this->set_expected_response( [
			'body' => wp_json_encode( [
				'status' => 'error',
				'data' => 'some message',
			] ),
		] );

		$this->assertTrue(
			is_wp_error( API::get_instance()->get_public_api_key() ),
			'If a public key can\'t be determined, return a WP_Error object.'
		);
	}

	public function test_get_addons() {
		$json = [
			'status' => 'success',
			'data'   => [
				'addons' => [],
			],
		];

		$this->set_expected_response([
			'body' => wp_json_encode( $json ),
		]);

		$this->assertEquals( $json['data'], API::get_instance()->get_addons() );
	}

	public function test_get_addons_handles_wp_error() {
		$this->set_expected_response( function () {
			return new WP_Error( 'code', 'some message' );
		} );

		$this->assertEquals(
			[
				'addons' => [],
			],
			API::get_instance()->get_addons()
		);
	}

	public function test_get_addons_handles_malformed_responses() {
		$json = [
			'status' => 'success',
			'data'   => [
				'some data that has nothing to do with add-ons.',
			],
		];

		$this->set_expected_response([
			'body' => wp_json_encode( $json ),
		]);

		$this->assertEquals( [
			'addons' => [],
		], API::get_instance()->get_addons() );
	}

	public function test_get_addons_updates_add_on_urls() {
		$api_key = uniqid();

		set_transient( API::PUBLIC_API_KEY_OPTION, $api_key );
		$this->set_expected_response([
			'body' => wp_json_encode( [
				'status' => 'success',
				'data'   => [
					'addons' => [
						[
							'url' => 'http://example.com',
						],
					],
				],
			] ),
		]);

		$this->assertEquals(
			'http://example.com?apiKey=' . $api_key,
			API::get_instance()->get_addons()['addons'][0]['url'],
			'The public API key should be appended to all add-on URLs.'
		);
	}

	public function test_get_addons_updates_adds_meets_requirements() {
		update_option( 'active_plugins', [
			'some-plugin/some-plugin.php',
		] );

		$this->set_expected_response([
			'body' => wp_json_encode( [
				'status' => 'success',
				'data'   => [
					'addons' => [
						[
							'url'          => 'http://example.com',
							'restrictions' => [
								'plugins' => [
									'some-plugin/some-plugin.php',
								],
							],
						],
						[
							'url'          => 'http://example.com',
							'restrictions' => [
								'plugins' => [
									'some-other-plugin/some-other-plugin.php',
								],
							],
						],
						[
							'url'          => 'http://example.com',
							'restrictions' => [
								'plugins' => [
									'some-plugin/some-plugin.php',
									'some-other-plugin/some-other-plugin.php',
								],
							],
						],
					],
				],
			] ),
		]);

		$this->assertTrue( API::get_instance()->get_addons()['addons'][0]['meets_requirements'] );
		$this->assertFalse( API::get_instance()->get_addons()['addons'][1]['meets_requirements'] );

		// If multiple requirements are listed, only one must be met.
		$this->assertTrue( API::get_instance()->get_addons()['addons'][2]['meets_requirements'] );
	}

	public function test_get_playlist() {
		$json = [
			'status' => 'success',
			'data'   => [],
		];

		$this->set_expected_response( [
			'body' => wp_json_encode( $json ),
		] );

		$this->assertEquals( $json['data'], API::get_instance()->get_playlist() );
	}

	public function test_get_playlist_handles_wp_error() {
		$this->set_expected_response( function () {
			return new WP_Error( 'code', 'some message' );
		} );

		$this->assertEquals( [ 'series' => [] ], API::get_instance()->get_playlist() );
	}

	public function test_get_series() {
		$json = [
			'status' => 'success',
			'data'   => [
				'series' => [
					[
						'slug' => 'first-series',
					],
					[
						'slug' => 'second-series',
					],
				]
			],
		];

		$this->set_expected_response( [
			'body' => wp_json_encode( $json ),
		] );

		$this->assertEquals(
			$json['data']['series'][1],
			API::get_instance()->get_series( 'second-series' )
		);
	}

	public function test_get_series_returns_false_if_no_matching_series_found() {
		$this->set_expected_response( [
			'body' => wp_json_encode( [
				'status' => 'success',
				'data'   => [
					'series' => [],
				],
			] ),
		] );

		$this->assertFalse( API::get_instance()->get_series( 'first-series' ) );
	}

	public function test_get_topic() {
		$json = [
			'status' => 'success',
			'data'   => [
				'series' => [
					[
						'topics' => [
							[
								'slug' => 'first-topic',
							],
							[
								'slug' => 'second-topic',
							],
						],
					],
				]
			],
		];

		$this->set_expected_response( [
			'body' => wp_json_encode( $json ),
		] );

		$this->assertEquals(
			$json['data']['series'][0]['topics'][1],
			API::get_instance()->get_topic( 'second-topic' )
		);
	}

	public function test_get_topic_can_traverse_series() {
		$json = [
			'status' => 'success',
			'data'   => [
				'series' => [
					[
						'topics' => [
							[
								'slug' => 'first-topic',
							],
						],
					],
					[
						'topics' => [
							[
								'slug' => 'second-topic',
							],
						],
					],
				]
			],
		];

		$this->set_expected_response( [
			'body' => wp_json_encode( $json ),
		] );

		$this->assertEquals(
			$json['data']['series'][1]['topics'][0],
			API::get_instance()->get_topic( 'second-topic' )
		);
	}

	public function test_get_topic_returns_false_if_no_matching_topic_found() {
		$this->set_expected_response( [
			'body' => wp_json_encode( [
				'status' => 'success',
				'data'   => [
					'series' => [
						[
							'topics' => [
								[
									'slug' => 'first-topic',
								],
							],
						],
					]
				],
			] ),
		] );

		$this->assertFalse( API::get_instance()->get_topic( 'second-topic' ) );
	}

	public function test_account_can() {
		$api = API::get_instance();

		$this->set_expected_response( [
			'body' => wp_json_encode( [
				'status' => 'success',
				'data'   => [
					'capabilities' => [ 'some-capability' ],
				],
			] ),
		] );

		$this->assertTrue( $api->account_can( 'some-capability' ) );
		$this->assertFalse( $api->account_can( 'some-other-capability' ) );
	}

	public function test_account_can_resolves_errors_to_false() {
		$this->set_expected_response( function () {
			return new WP_Error( 'msg' );
		} );

		$this->assertFalse( API::get_instance()->account_can( 'some-capability' ) );
	}

	public function test_exchange_api_key() {
		$api = API::get_instance();
		$api->set_api_key( uniqid() );
		$new = md5( uniqid() ); // Easy way to get a random, 32 character string.

		$this->set_expected_response( [
			'body' => wp_json_encode( [
				'status' => 'success',
				'data'   => [
					'apiKey' => $new,
				],
			] ),
		] );

		$this->assertEquals( $new, $api->exchange_api_key()['apiKey'] );
	}

	public function test_exchange_api_key_returns_early_for_empty_key() {
		$api = API::get_instance();
		$api->set_api_key( '' );

		$this->assertTrue( is_wp_error( $api->exchange_api_key() ) );
	}

	public function test_exchange_api_key_passes_hidden_topic_ids() {
		$hidden = [ 4, 8, 15, 16, 23, 42 ];
		update_option( 'wp101_hidden_topics', $hidden );

		$api = API::get_instance();
		$api->set_api_key( uniqid() );

		$this->set_expected_response( function ( $preempt, $args ) use ( $hidden ) {
			$this->assertEquals( $hidden, $args['body']['hiddenTopics'] );

			return $this->mock_http_response( [
				'body' => wp_json_encode( [
					'status' => 'success',
					'data'   => 'Hidden topics were passed!',
				] ),
			] );
		} );

		$this->assertEquals( 'Hidden topics were passed!', $api->exchange_api_key() );
	}

	public function test_exchange_api_key_respects_wp101_get_hidden_topics_filter() {
		update_option( 'wp101_hidden_topics', [ 4, 8, 15, 16, 23, 42 ] );

		add_filter( 'wp101_get_hidden_topics', function () {
			return [ 4, 8, 15 ];
		} );

		$api = API::get_instance();
		$api->set_api_key( uniqid() );

		$this->set_expected_response( function ( $preempt, $args ) {
			$this->assertEquals( [ 4, 8, 15 ], $args['body']['hiddenTopics'] );

			return $this->mock_http_response( [
				'body' => wp_json_encode( [
					'status' => 'success',
					'data'   => 'Hidden topics were passed!',
				] ),
			] );
		} );

		$this->assertEquals( 'Hidden topics were passed!', $api->exchange_api_key() );
	}

	public function test_exchange_api_key_passes_custom_topics() {
		$custom_topics = [
			'custom-topic' => [
				'title'   => 'This is a custom topic',
				'content' => '<iframe src="//player.vimeo.com/video/123456789" width="1280" height="720" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>',
			],
		];
		update_option( 'wp101_custom_topics', $custom_topics );

		$api = API::get_instance();
		$api->set_api_key( uniqid() );

		$this->set_expected_response( function ( $preempt, $args ) use ( $custom_topics ) {
			$this->assertEquals( $custom_topics, $args['body']['customTopics'] );

			return $this->mock_http_response( [
				'body' => wp_json_encode( [
					'status' => 'success',
					'data'   => 'Custom topics were passed!',
				] ),
			] );
		} );

		$this->assertEquals( 'Custom topics were passed!', $api->exchange_api_key() );
	}

	public function test_exchange_api_key_respects_wp101_get_custom_help_topics_filter() {
		update_option( 'wp101_custom_topics', [ 'custom-topic' => [] ] );

		add_filter( 'wp101_get_custom_help_topics', function () {
			return [ 'different-topic' => [] ];
		} );

		$api = API::get_instance();
		$api->set_api_key( uniqid() );

		$this->set_expected_response( function ( $preempt, $args ) {
			$this->assertEquals( [ 'different-topic'], array_keys( $args['body']['customTopics'] ) );

			return $this->mock_http_response( [
				'body' => wp_json_encode( [
					'status' => 'success',
					'data'   => 'Custom topics were passed!',
				] ),
			] );
		} );

		$this->assertEquals( 'Custom topics were passed!', $api->exchange_api_key() );
	}

	public function test_exchange_api_key_surfaces_wp_errors() {
		$error = new WP_Error( 'msg' );

		$api = API::get_instance();
		$api->set_api_key( uniqid() );

		$this->set_expected_response( function () use ( $error ) {
			return $error;
		} );

		$this->assertSame( $error, $api->exchange_api_key() );
	}

	public function test_exchange_api_key_returns_wp_error_on_api_connection_error() {
		$this->set_expected_response( [
			'response' => [
				'code'    => 403,
				'message' => 'Forbidden',
			],
		] );

		$this->assertTrue( is_wp_error( API::get_instance()->exchange_api_key() ) );
	}

	public function test_exchange_api_key_returns_wp_error_on_api_error() {
		$this->set_expected_response( [
			'body' => wp_json_encode( [
				'status' => 'fail',
				'data' => 'some message',
			] ),
		] );

		$this->assertTrue( is_wp_error( API::get_instance()->exchange_api_key() ) );
	}

	/**
	 * @dataProvider build_uri_provider()
	 */
	public function test_build_uri( $path, $args, $expected ) {
		$api    = API::get_instance();
		$method = $this->get_accessible_method( $api, 'build_uri' );

		$this->assertEquals(
			API::API_URL . $expected,
			$method->invoke( $api, $path, $args )
		);
	}

	public function build_uri_provider() {
		return [
			'Simple path'            => [
				'/test-path',
				[],
				'/test-path',
			],
			'Missing leading slash'  => [
				'test-path',
				[],
				'/test-path',
			],
			'Query string arguments' => [
				'/test-path',
				[ 'foo' => 'bar' ],
				'/test-path?foo=bar',
			],
			'Multiple query string arguments' => [
				'/test-path',
				[ 'foo' => 'FooValue', 'bar' => 'BarValue' ],
				'/test-path?foo=FooValue&bar=BarValue',
			],
		];
	}

	public function test_build_uri_enables_base_to_be_changed_via_constant() {
		define( 'WP101_API_URL', 'http://example.com' );

		$api    = API::get_instance();
		$method = $this->get_accessible_method( $api, 'build_uri' );

		$this->assertEquals(
			'http://example.com/path',
			$method->invoke( $api, '/path' ),
			'When the WP101_API_URL is set, it should take precedence over the default URL.'
		);
	}

	public function test_send_request() {
		$key    = uniqid();
		$api    = API::get_instance();
		$method = $this->get_accessible_method( $api, 'send_request' );

		$this->set_api_key( $key );

		$response = $this->mock_http_response( [
			'body' => wp_json_encode( [
				'status' => 'success',
				'data'   => [],
			] )
		] );

		$this->set_expected_response( function ( $preempt, $args, $url ) use ( $key, $response ) {
			$this->assertEquals( 'GET', $args['method'] );
			$this->assertContains( '/test-endpoint', $url );
			$this->assertEquals( 'Bearer ' . $key, $args['headers']['Authorization'] );
			$this->assertEquals( site_url(), $args['headers']['X-Forwarded-Host'] );
			$this->assertEquals( API::USER_AGENT, $args['user-agent']);

			return $response;
		} );

		$this->assertEquals(
			[], // Value of $response['body']['data'] after being decoded.
			$method->invoke( $api, 'GET', '/test-endpoint' ),
			'Did not receive expected response from send_request().'
		);
	}

	/**
	 * @link https://github.com/liquidweb/wp101plugin/issues/67
	 */
	public function test_send_request_aborts_if_no_api_key_is_set() {
		$api    = API::get_instance();
		$method = $this->get_accessible_method( $api, 'send_request' );

		$this->set_api_key( '' );

		$response = $method->invoke( $api, 'GET', '/test-endpoint' );

		$this->assertTrue( is_wp_error( $response ) );
		$this->assertSame( 'wp101-no-api-key', $response->get_error_code() );
	}

	public function test_send_request_checks_response_status() {
		$api    = API::get_instance();
		$method = $this->get_accessible_method( $api, 'send_request' );

		$response = $this->set_expected_response( [
			'body' => wp_json_encode( [
				'status'  => 'fail',
				'data'    => [
					'apiKey' => 'Invalid API key.',
				],
			] ),
		] );

		$this->assertTrue( is_wp_error( $method->invoke( $api, 'GET', '/test-endpoint' ) ) );
	}

	public function test_handle_error() {
		$api    = Api::get_instance();
		$method = $this->get_accessible_method( $api, 'handle_error' );
		$prop   = new \ReflectionProperty( $api, 'errors' );
		$prop->setAccessible( true );
		$error1 = new WP_Error( 'test1', 'Foo' );
		$error2 = new WP_Error( 'test2', 'Bar' );

		$this->assertEmpty( $prop->getValue( $api ) );

		$method->invoke( $api, $error1 );
		$method->invoke( $api, $error2 );

		$this->assertContains( $error1, $prop->getValue( $api ) );
		$this->assertContains( $error2, $prop->getValue( $api ) );
	}

	public function test_handle_error_overwrites_for_duplicates() {
		$api    = Api::get_instance();
		$method = $this->get_accessible_method( $api, 'handle_error' );
		$prop   = new \ReflectionProperty( $api, 'errors' );
		$prop->setAccessible( true );
		$error1 = new WP_Error( 'test', 'Foo' );
		$error2 = new WP_Error( 'test', 'Bar' );

		$this->assertEmpty( $prop->getValue( $api ) );

		$method->invoke( $api, $error1 );
		$method->invoke( $api, $error2 );

		$this->assertNotContains( $error1, $prop->getValue( $api ) );
		$this->assertContains( $error2, $prop->getValue( $api ) );
	}

	/**
	 * Mock the expected HTTP response.
	 *
	 * @param array|callable $response The response that should be returned from the HTTP request.
	 */
	protected function set_expected_response( $response ) {
		if ( is_callable( $response ) ) {
			$callback = $response;
		} else {
			$callback = function ( $preempt, $args, $url ) use ( $response ) {
				return $this->mock_http_response( $response );
			};
		}

		add_filter( 'pre_http_request', $callback, 1, 3 );
	}

	/**
	 * The pre_http_request filter requires an array be returned that contains the same keys as a
	 * standard WordPress HTTP response. This fills in the gaps, enabling our tests to override only
	 * the properties needed.
	 *
	 * @link https://developer.wordpress.org/reference/hooks/pre_http_request
	 *
	 * @param array $props Optional. Properties that should be set on the response, from the list of
	 *                     keys containing 'headers', 'body', 'response', 'cookies', and 'filename'.
	 *                     Default is empty.
	 * @return array A mocked HTTP response.
	 */
	protected function mock_http_response( $props = [] ) {
		return array_merge( [
			'headers'  => [],
			'body'     => '',
			'response' => [
				'code'    => 200,
				'message' => 'OK',
			],
			'cookies'  => [],
			'filename' => '',
		], (array) $props );
	}
}
