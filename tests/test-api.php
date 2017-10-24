<?php
/**
 * Tests for the WP101 API integration.
 *
 * @package WP101
 */

namespace WP101;

use ReflectionProperty;
use WP_Error;
use WP101_Plugin;
use WP101\API;
use WP101\TestCase;

/**
 * Tests for the core plugin functionality, contained in includes/class-api.php.
 */
class ApiTest extends TestCase {

	public function tearDown() {
		parent::tearDown();

		// Clean up after any send_request() tests.
		remove_all_filters( 'pre_http_request' );
	}

	public function test_constructor_sets_api_key() {
		$key = uniqid();
		$api = new API( $key );

		$this->assertEquals( $key, $api->get_api_key() );
	}

	public function test_get_api_key_returns_from_cache() {
		$api = new API;
		$key = uniqid();

		$prop = new ReflectionProperty( $api, 'api_key' );
		$prop->setAccessible( true );
		$prop->setValue( $api, $key );

		$this->assertEquals( $key, $api->get_api_key() );
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_get_api_key_reads_constant() {
		define( 'WP101_API_KEY', uniqid() );

		$api = new API;
		$this->assertEquals( WP101_API_KEY, $api->get_api_key() );
	}

	public function test_get_playlist() {
		$api  = new API;
		$json = [ 'foo' => 'bar' ];

		$this->set_expected_response([
			'body' => wp_json_encode( $json ),
		]);

		$this->assertEquals( $json, $api->get_playlist() );
	}

	public function test_get_playlist_surfaces_wp_errors() {
		$api  = new API;
		$error = new WP_Error( 'Some message' );

		$this->set_expected_response( function () use ( $error ) {
			return $error;
		} );

		$this->assertSame( $error, $api->get_playlist() );
	}

	/**
	 * @dataProvider build_uri_provider()
	 */
	public function test_build_uri( $path, $args, $expected ) {
		$api    = new API;
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

	/**
	 * @runInSeparateProcess
	 */
	public function test_build_uri_enables_base_to_be_changed_via_constant() {
		define( 'WP101_API_URL', 'http://example.com' );

		$api    = new API;
		$method = $this->get_accessible_method( $api, 'build_uri' );

		$this->assertEquals(
			'http://example.com/path',
			$method->invoke( $api, '/path' ),
			'When the WP101_API_URL is set, it should take precedence over the default URL.'
		);
	}

	public function test_send_request() {
		$key    = uniqid();
		$api    = new API( $key );
		$method = $this->get_accessible_method( $api, 'send_request' );

		$response = $this->mock_http_response([
			'body' => uniqid(),
		]);

		$this->set_expected_response( function ( $preempt, $args, $url ) use ( $key, $response ) {
			$this->assertEquals( 'GET', $args['method'] );
			$this->assertContains( '/test-endpoint', $url );
			$this->assertEquals( 'Bearer ' . $key, $args['headers']['Authorization'] );
			$this->assertEquals( site_url(), $args['headers']['X-Forwarded-Host'] );
			$this->assertEquals( API::USER_AGENT, $args['user-agent']);

			return $response;
		} );

		$this->assertEquals(
			$response,
			$method->invoke( $api, 'GET', '/test-endpoint' ),
			'Did not receive expected response from send_request().'
		);
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
				'code'    => false,
				'message' => false,
			],
			'cookies'  => [],
			'filename' => '',
		], (array) $props );
	}
}
