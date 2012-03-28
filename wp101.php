<?php
/*
Plugin Name: WP101
Description: WordPress tutorial videos, delivered directly to your WordPress admin
Version: 1.1.1
Author: WP101plugin.com
Author URI: http://wp101plugin.com/
*/

// API KEY
// You can hardcode the API key here, and it will be used as a starting value for the key
$_wp101_api_key = '';

class WP101_Plugin {
	public static $db_version = 2;
	public static $instance;
	public static $api_base = 'http://wp101plugin.com/?wp101-api-server&';
	public static $subscribe_url = 'http://wp101plugin.com/';
	public static $renew_url = 'http://wp101plugin.com/';

	public function __construct() {
		self::$instance = $this;
		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
		// Translations
		load_plugin_textdomain( 'wp101', false, basename( dirname( __FILE__ ) ) . '/languages' );

		// Actions and filters
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		// Upgrades
		$db_version = get_option( 'wp101_db_version' );
		if ( $db_version < 2 ) {
			// Flush it out
			delete_transient( 'wp101_topics' );
			update_option( 'wp101_db_version', 2 );
		}
	}

	public function admin_menu() {
		$wp101_icon_url = plugin_dir_url( __FILE__ ) . '/images/icon.png';
		$hook = add_menu_page( _x( 'WP101', 'page title', 'wp101' ), _x( 'WP101', 'menu title', 'wp101' ), 'read', 'wp101', array( $this, 'render_listing_page' ), $wp101_icon_url );
		add_action( "load-{$hook}", array( $this, 'load' ) );
	}

	private function validate_api_key_with_server( $key=NULL ) {
		if ( NULL === $key )
			$key = $this->get_key();
		$result = wp_remote_get( self::$api_base . 'action=check_key&api_key=' . $key );
		$result = json_decode( $result['body'] );
		return $result->data->status;
	}

	private function get_key() {
			global $_wp101_api_key;
			$db = get_option( 'wp101_api_key' );
			if ( empty( $db ) && isset( $_wp101_api_key ) && !empty( $_wp101_api_key ) ) {
				update_option( 'wp101_api_key', $_wp101_api_key );
				return $_wp101_api_key;		
			} else {
				return $db;
			}
	}

	public function load() {
		$this->enqueue();
		if ( $_POST ) {
			check_admin_referer( 'wp101-update_key' );
			$new_key = preg_replace( '#[^a-f0-9]#', '', stripslashes( $_POST['wp101_api_key'] ) );
			$result = $this->validate_api_key_with_server( $new_key );
			if ( 'valid' == $result ) {
				update_option( 'wp101_api_key', $new_key );
				set_transient( 'wp101_message', 'valid', 300 );
				wp_redirect( admin_url( 'admin.php?page=wp101' ) );
				exit();
			} elseif ( 'expired' == $result ) {
				set_transient( 'wp101_message', 'expired', 300 );
			} else {
				set_transient( 'wp101_message', 'error', 300 );
			}
			wp_redirect( admin_url( 'admin.php?page=wp101&configure=1' ) );
			exit();
		} elseif ( $message = get_transient( 'wp101_message' ) ) {
			delete_transient( 'wp101_message' );
			add_action( 'admin_notices', array( $this, 'api_key_' . $message . '_message' ) );
		} elseif ( !isset( $_GET['configure'] ) ) {
			$result = $this->validate_api_key();
			if ( 'valid' !== $result && current_user_can( 'manage_options' ) ) {
				set_transient( 'wp101_message', $result, 300 );
				wp_redirect( admin_url( 'admin.php?page=wp101&configure=1' ) );
				exit();
			}
		}
	}

	public function api_key_valid_message() {
		echo '<div class="updated"><p>' . __( 'Your WP101.com API key has been updated and verified!', 'wp101' ) . '</p></div>';
	}

	public function api_key_error_message() {
		echo '<div class="updated"><p>' . __( 'The API key you provided is not valid.', 'wp101' ) . '</p></div>';
	}

	public function api_key_notset_message() {
		echo '<div class="updated"><p>' . __( 'You need to provide an API key!', 'wp101' ) . '</p></div>';
	}

	public function api_key_expired_message() {
		echo '<div class="updated"><p>' . sprintf( __( 'The API key you provided has expired. Please <a href="%s">renew your subscription</a>!', 'wp101' ), esc_url( self::$renew_url ) ) . '</p></div>';
	}

	private function enqueue() {
		wp_enqueue_style( 'wp101', plugins_url( "css/wp101.css", __FILE__ ), array(), '20110904' );
	}

	private function validate_api_key() {
		if ( !get_transient( 'wp101_api_key_valid' ) ) {
			if ( !$this->get_key() ) {
				// Hasn't set API key yet
				return 'notset';
			} else {
				// Check the API key against the server
				$response = $this->validate_api_key_with_server();
				if ( 'valid' == $response ) {
					set_transient( 'wp101_api_key_valid', true, 24*3600 ); // Good for a day.
					return $response;
				} else {
					return $response;
				}
			}
		} else {
			return 'valid'; // Cached response
		}
	}

	private function get_document( $id ) {
		$topics = $this->get_help_topics();
		if ( isset( $topics[$id] ) )
			return $topics[$id];
		else
			return false;
	}

	private function get_help_topics() {
		if ( $this->validate_api_key() ) {
			if ( $topics = get_transient( 'wp101_topics' ) ) {
				return $topics;
			} else {
				$result = wp_remote_get( self::$api_base . 'action=get_topics&api_key=' . $this->get_key() );
				$result = json_decode( $result['body'], true );
				if ( !$result['error'] && count( $result['data'] ) ) {
					set_transient( 'wp101_topics', $result['data'], 24*3600 ); // Good for a day.
					return $result['data'];
				}
			}
		}
	}

	private function get_help_topics_html() {
		$topics = $this->get_help_topics();
		if ( !$topics )
			return false;
		$return = '<ul>';
		foreach ( $topics as $topic ) {
			$return .= '<li class="page-item-' . $topic['id'] . '"><a href="' . admin_url( 'admin.php?page=wp101&document=' . $topic['id'] ) . '">' . esc_html( $topic['title'] ) . '</a></li>';
		}
		$return .= '</ul>';
		return $return;
	}

	public function render_listing_page() {
		$document_id = absint( isset( $_GET['document'] ) ? $_GET['document'] : 1 );
		if ( $document_id ) : ?>
			<style>
			div#wp101-topic-listing .page-item-<?php echo $document_id; ?> > a {
				font-weight: bold;
			}
			</style>
		<?php endif; ?>
<div class="wrap">
	<h2 style="font-weight: bold;"><?php _ex( 'WordPress 101 Video Tutorials', 'h2 title', 'wp101' ); ?></h2>

<?php if ( isset( $_GET['configure'] ) && $_GET['configure'] ) : ?>
	<p><?php _e( 'WP101 requires an API key to provide access to the latest WordPress tutorial videos.', 'wp101' ); ?></p>
	<h3><?php _e( 'Need an API Key?', 'wp101' ); ?></h3>
	<p><a class="button" href="<?php echo esc_url( self::$subscribe_url ); ?>" title="<?php esc_attr_e( 'WP101 Tutorial Plugin', 'wp101' ); ?>" target="_blank"><?php esc_html_e( 'View Subscription Details' ); ?></a></p>

	<h3 style="margin-top: 30px;"><?php _e( 'Have an API Key?' ); ?></h3>
	<form action="" method="post">
	<?php wp_nonce_field( 'wp101-update_key' ); ?>
	<p><label for="wp101-api-key">WP101Plugin.com API KEY: </label><input type="password" id="wp101-api-key" name="wp101_api_key" value="<?php echo esc_attr( $this->get_key() ); ?>" /></p>
	<?php submit_button( 'Save API Key' ); ?>
	</form>
<?php else : ?>

<?php $pages = $this->get_help_topics_html(); ?>
<?php if ( trim( $pages ) ) : ?>
<div id="wp101-topic-listing">
<h3><?php _e( 'Tutorials', 'wp101' ); ?><?php if ( current_user_can( 'publish_pages' ) ) : ?><span><a class="button" href="<?php echo admin_url( 'admin.php?page=wp101&configure=1' ); ?>"><?php _ex( 'Settings', 'Button with limited space', 'wp101' ); ?></a></span><?php endif; ?></h3>
<ul>
<?php echo $pages; ?>
</ul>
</div>

<div id="wp101-topic">
<?php if ( $document_id ) : ?>
	<?php $document = $this->get_document( $document_id ); ?>
	<?php if ( $document ) : ?>
		<h2><?php echo esc_html( $document['title'] ); ?></h2>
		<?php echo $document['content']; ?>
	<?php else : ?>
	<p><?php _e( 'The requested tutorial could not be found', 'wp101' ); ?>
	<?php endif; ?>
<?php endif; ?>
</div>
<?php else : ?>
	<?php if ( current_user_can( 'manage_options' ) ) : ?>
		<p><?php printf( __( 'No help topics found. <a href="%s">Configure your API key</a>.', 'wp101' ), admin_url( 'admin.php?page=wp101&configure=1' ) ); ?></p>
	<?php else : ?>
		<p><?php _e( 'No help topics found. Contact the site administrator to configure your API key.', 'wp101' ); ?></p>
	<?php endif; ?>
<?php endif; ?>

<?php endif; ?>

</div>
<?php
	}
}

new WP101_Plugin;
