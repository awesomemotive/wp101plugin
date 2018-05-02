<?php
/**
 * Define a dummy WP101_Plugin class.
 *
 * @package WP101
 */

use WP101\Deprecated as Deprecated;

class WP101_Plugin {
	public static $db_version    = 2;
	public static $api_base      = 'https://wp101plugin.com/?wp101-api-server&';
	public static $subscribe_url = 'https://wp101plugin.com/';
	public static $renew_url     = 'https://wp101plugin.com/';

	public function __construct() {
		self::deprecated( __CLASS__ );
	}

	public function __call( $name, $args ) {
		self::deprecated( $name );
	}

	public static function __callStatic( $name, $args ) {
		self::deprecated( __CLASS__ . '::' . $name );
	}

	public function __get( $name ) {
		self::deprecated( __CLASS__ . '::$' . $name );
	}

	protected static function deprecated( $feature ) {
		Deprecated\mark_deprecated( $feature, 'The WP101_Plugin class is no longer used.', '5.0.0' );
	}
}
