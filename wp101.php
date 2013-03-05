<?php
/*
Plugin Name: WP101
Description: WordPress tutorial videos, delivered directly to your WordPress admin
Version: 2.0.3
Author: WP101Plugin.com
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
		add_action( 'wp_ajax_wp101-showhide-topic', array( $this, 'ajax_handler' ) );
		add_action( 'wp_ajax_wp101-delete-topic', array( $this, 'ajax_delete_topic' ) );

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
		$query = wp_remote_get( self::$api_base . 'action=check_key&api_key=' . $key, array( 'timeout' => 45, 'sslverify' => false, 'user-agent' => 'WP101Plugin' ) );

		if ( is_wp_error( $query ) )
			return false; // Failed to query the server

		$result = json_decode( wp_remote_retrieve_body( $query ) );

		return $result->data->status;
	}

	private function get_key() {
			global $_wp101_api_key;
			$db = get_option( 'wp101_api_key' );
			if ( empty( $db ) && isset( $_wp101_api_key ) && !empty( $_wp101_api_key ) ) {
				update_option( 'wp101_api_key', $_wp101_api_key );
				return $_wp101_api_key;
			} elseif ( empty( $db ) && defined( 'WP101_API_KEY' ) ) {
				update_option( 'wp101_api_key', WP101_API_KEY );
			} else {
				return $db;
			}
	}

	public function load() {
		add_option( 'wp101_hidden_topics', array() );
		add_option( 'wp101_custom_topics', array() );
		$this->enqueue();
		if ( isset( $_POST['wp101-action'] ) && 'api-key' == $_POST['wp101-action'] ) {
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
		} elseif ( isset( $_POST['wp101-action'] ) && 'add-video' == $_POST['wp101-action'] ) {
			check_admin_referer( 'wp101-add-video' );
			$this->add_custom_help_topic( stripslashes( $_POST['wp101_video_title'] ), stripslashes( $_POST['wp101_video_code'] ) );
			set_transient( 'wp101_message', 'added_video', 300 );
			wp_redirect( admin_url( 'admin.php?page=wp101&configure=1' ) );
			exit();
		} elseif ( isset( $_POST['wp101-action'] ) && 'update-video' == $_POST['wp101-action'] ) {
			check_admin_referer( 'wp101-update-video-' . $_POST['document'] );
			$this->update_custom_help_topic( $_POST['document'], stripslashes( $_POST['wp101_video_title'] ), stripslashes( $_POST['wp101_video_code'] ) );
			set_transient( 'wp101_message', 'updated_video', 300 );
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

	public function api_key_updated_video_message() {
		echo '<div class="updated"><p>' . __( 'Your custom video was updated!', 'wp101' ) . '</p></div>';
	}

	public function api_key_added_video_message() {
		echo '<div class="updated"><p>' . __( 'Your custom video was added!', 'wp101' ) . '</p></div>';
	}

	public function api_key_valid_message() {
		echo '<div class="updated"><p>' . __( 'Your WP101Plugin.com API key has been updated and verified!', 'wp101' ) . '</p></div>';
	}

	public function api_key_error_message() {
		echo '<div class="updated"><p>' . __( 'The WP101Plugin.com API key you provided is not valid.', 'wp101' ) . '</p></div>';
	}

	public function api_key_expired_message() {
		echo '<div class="updated"><p>' . sprintf( __( 'The WP101Plugin.com API key you provided has expired. Please <a href="%s">renew your subscription</a>!', 'wp101' ), esc_url( self::$renew_url ) ) . '</p></div>';
	}

	private function enqueue() {
		wp_enqueue_script( 'wp101', plugins_url( "js/wp101.js", __FILE__ ), array( 'jquery' ), '20120418b' );
		wp_enqueue_style( 'wp101', plugins_url( "css/wp101.css", __FILE__ ), array(), '20120418b' );
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
					set_transient( 'wp101_api_key_valid', 1, 24*3600 ); // Good for a day.
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
		$custom_topics = $this->get_custom_help_topics();
		if ( isset( $topics[$id] ) )
			return $topics[$id];
		elseif ( isset( $custom_topics[$id] ) )
			return $custom_topics[$id];
		else
			return false;
	}

	private function get_custom_help_topics() {
		return (array) get_option( 'wp101_custom_topics' );
	}

	private function get_custom_help_topic( $id ) {
		$topics = $this->get_custom_help_topics();
		if ( isset( $topics[$id] ) )
			return $topics[$id];
		else
			return false;
	}

	private function update_custom_help_topics( $topics ) {
		update_option( 'wp101_custom_topics', $topics );
	}

	private function update_custom_help_topic( $id, $title, $content ) {
		$topics = $this->get_custom_help_topics();
		$topics[$id] = array( 'title' => $title, 'content' => $content );
		$this->update_custom_help_topics( $topics );
	}

	private function remove_custom_help_topic( $id ) {
		$topics = $this->get_custom_help_topics();
		unset( $topics[$id] );
		$this->update_custom_help_topics( $topics );
	}

	private function add_custom_help_topic( $title, $content ) {
		$topics = $this->get_custom_help_topics();
		$topics[sanitize_title( $title )] = array( 'title' => $title, 'content' => $content );
		$this->update_custom_help_topics( $topics );
	}

	private function get_help_topics() {
		if ( 'valid' == $this->validate_api_key() ) {
			if ( $topics = get_transient( 'wp101_topics' ) ) {
				return $topics;
			} else {
				$result = wp_remote_get( self::$api_base . 'action=get_topics&api_key=' . $this->get_key(), array( 'timeout' => 45, 'sslverify' => false, 'user-agent' => 'WP101Plugin' ) );
				$result = json_decode( $result['body'], true );
				if ( !$result['error'] && count( $result['data'] ) ) {
					set_transient( 'wp101_topics', $result['data'], 24*3600 ); // Good for a day.
					return $result['data'];
				}
			}
		}
	}

	public function ajax_handler() {
		if ( !wp_verify_nonce( $_POST['_wpnonce'], 'wp101-showhide-' . $_REQUEST['topic_id'] ) )
			die( '-1' );
		if ( 'hide' == $_REQUEST['direction'] ) {
			$this->hide_topic( $_REQUEST['topic_id'] );
			die( '1' );
		} elseif ( 'show' == $_REQUEST['direction'] ) {
			$this->show_topic( $_REQUEST['topic_id'] );
			die( '1' );
		}
		die( '-1' );
	}

	public function ajax_delete_topic() {
		if ( !wp_verify_nonce( $_POST['_wpnonce'], 'wp101-delete-topic-' . $_REQUEST['topic_id'] ) )
			die( '-1' );
		$this->remove_custom_help_topic( $_REQUEST['topic_id'] );
		die( '1' );
	}

	private function get_hidden_topics() {
		return (array) get_option( 'wp101_hidden_topics' );
	}

	private function is_hidden( $topic_id ) {
		$hidden_topics = $this->get_hidden_topics();
		return in_array( $topic_id, $hidden_topics );
	}

	private function hide_topic( $topic_id ) {
		$hidden_topics = $this->get_hidden_topics();
		$hidden_topics[] = $topic_id;
		return update_option( 'wp101_hidden_topics', $hidden_topics );
	}

	private function show_topic( $topic_id ) {
		$hidden_topics = $this->get_hidden_topics();
		if ( $this->is_hidden( $topic_id ) ) {
			unset( $hidden_topics[array_search( $topic_id, $hidden_topics )] );
			return update_option( 'wp101_hidden_topics', $hidden_topics );
		} else {
			return false;
		}
	}

	private function get_help_topics_html( $edit_mode = false ) {
		$topics = $this->get_help_topics();
		if ( !$topics )
			return false;
		$return = '<ul class="wp101-topic-ul">';
		foreach ( $topics as $topic ) {
			if ( $edit_mode ) {
				$edit_links = '&nbsp;&nbsp;<small class="wp101-show">[<a data-nonce="' . wp_create_nonce( 'wp101-showhide-' . $topic['id'] ) . '" data-topic-id="' . $topic['id'] . '" href="#">show</a>]</small><small class="wp101-hide">[<a data-nonce="' . wp_create_nonce( 'wp101-showhide-' . $topic['id'] ) . '" data-topic-id="' . $topic['id'] . '" href="#">hide</a>]</small>';
				if ( $this->is_hidden( $topic['id'] ) )
					$addl_class = 'wp101-hidden';
				else
					$addl_class = 'wp101-shown';
			} else {
				if ( $this->is_hidden( $topic['id'] ) )
					continue;
				$edit_links = $addl_class = '';
			}
			$return .= '<li class="' . $addl_class . ' page-item-' . $topic['id'] . '"><span><a href="' . admin_url( 'admin.php?page=wp101&document=' . $topic['id'] ) . '">' . esc_html( $topic['title'] ) . '</a></span>' . $edit_links . '</li>';
		}
		$return .= '</ul>';
		return $return;
	}

	private function get_custom_help_topics_html( $edit_mode = false ) {
		$return = '';
		if ( $custom_topics = $this->get_custom_help_topics() ) {
			$return .= '<ul class="wp101-topic-ul">';
			foreach ( $custom_topics as $id => $topic ) {
				if ( $edit_mode )
					$return .= '<li class="page-item-' . $id . '"><span><a href="' . admin_url( 'admin.php?page=wp101&configure=1&document=' . $id ) . '">' . esc_html( $topic['title'] ) . '</a></span> <small class="wp101-delete">[<a href="#" data-topic-id="' . $id . '" data-nonce="' . wp_create_nonce( 'wp101-delete-topic-' . $id ) . '">delete</a>]</small></li>';
				else
					$return .= '<li class="page-item-' . $id . '"><span><a href="' . admin_url( 'admin.php?page=wp101&document=' . $id ) . '">' . esc_html( $topic['title'] ) . '</a></span></li>';
			}
			$return .= '</ul>';
		}
		return $return;
	}

	public function render_listing_page() {
		$document_id = isset( $_GET['document'] ) ? sanitize_title( $_GET['document'] ) : 1;
		if ( $document_id ) : ?>
			<style>
			div#wp101-topic-listing .page-item-<?php echo $document_id; ?> > span a {
				font-weight: bold;
			}
			</style>
		<?php endif; ?>
<div class="wrap" id="wp101-settings">
	<?php screen_icon('wp101'); ?><h2><?php _ex( 'WordPress 101 Video Tutorials', 'h2 title', 'wp101' ); ?></h2>

	<?php if ( current_user_can( 'manage_options' ) && isset( $_GET['configure'] ) && $_GET['configure'] ) : ?>
	<?php if ( !isset( $_GET['document'] ) ) : ?>
		<h3 class="title"><?php _e( 'API Key', 'wp101' ); ?></h3>

		<?php if ( 'valid' !== $this->validate_api_key() ) : ?>
			<div class="updated">
			<p><?php _e( 'WP101 requires a WP101Plugin.com API key to provide access to the latest WordPress tutorial videos.', 'wp101' ); ?> <a class="button" href="<?php echo esc_url( self::$subscribe_url ); ?>" title="<?php esc_attr_e( 'WP101 Tutorial Plugin', 'wp101' ); ?>" target="_blank"><?php esc_html_e( 'Subscription Info' ); ?></a></p>
			</div>
		<?php else : ?>
			<p><?php _e( '<strong class="wp101-valid-key">Your WP101Plugin.com API key is valid!</strong>', 'wp101' ); ?>
		<?php endif; ?>


		<form action="" method="post">
		<input type="hidden" name="wp101-action" value="api-key" />
		<?php wp_nonce_field( 'wp101-update_key' ); ?>
		<table class="form-table">
		<tr valign="top">
			<th scope="row"><label for="wp101-api-key"><?php _e( 'WP101Plugin.com API Key:', 'wp101' ); ?></label></th>
			<td><input class="regular-text" type="text" id="wp101-api-key" name="wp101_api_key" value="" /></td>
		</tr>
		</table>
		<?php if ( 'valid' === $this->validate_api_key() ) : ?>
			<?php submit_button( __( 'Change API Key', 'wp101' ) ); ?>
		<?php else : ?>
			<?php submit_button( __( 'Set API Key', 'wp101' ) ); ?>
		<?php endif; ?>
		</form>

		<?php if ( 'valid' === $this->validate_api_key() ) : ?>
		<h3 class="title"><?php _e( 'WP101 Videos' ); ?></h3>
		<p><?php _e( 'If there are WP101 videos for topics that don&#8217;t apply to this site, you can hide them.' ); ?></p>
		<?php echo $this->get_help_topics_html( true ); ?>
		<?php endif; ?>
	<?php endif; ?>

	<?php if ( current_user_can( 'unfiltered_html' ) ) : ?>
		<?php $editable_video = isset( $_GET['document'] ) ? $this->get_custom_help_topic( $_GET['document'] ) : false; ?>
		<?php if ( $editable_video ) : ?>
			<h3 class="title"><?php _e( 'Edit Custom Video' ); ?></h3>
		<?php else : ?>
			<h3 class="title"><?php _e( 'Custom Videos' ); ?></h3>
		<?php endif; ?>
		<?php if ( !isset( $_GET['document'] ) ) : ?>
			<p><?php _e( 'If you have your own videos, you can add them here. They will display in a separate section, below the WP101 videos.', 'wp101' ); ?></p>
			<?php if ( $this->get_custom_help_topics() ) : ?>
				<?php echo $this->get_custom_help_topics_html( true ); ?>
			<?php endif; ?>
		<?php endif; ?>
		<form action="" method="post">
		<?php if ( $editable_video ) : ?>
			<input type="hidden" name="document" value="<?php echo esc_attr( $_GET['document'] ); ?>" />
			<input type="hidden" name="wp101-action" value="update-video" />
			<?php wp_nonce_field( 'wp101-update-video-' . $_GET['document'] ); ?>
		<?php else : ?>
			<input type="hidden" name="wp101-action" value="add-video" />
			<?php wp_nonce_field( 'wp101-add-video' ); ?>
		<?php endif; ?>
		<table class="form-table">
		<tr valign="top">
			<th scope="row"><label for="wp101-video-title">Video Title:</label></th>
			<td><input type="text" id="wp101-video-title" name="wp101_video_title" class="regular-text" value="<?php echo $editable_video ? esc_attr( $editable_video['title'] ) : ''; ?>"/></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="wp101-video-code">Embed Code:</label></th>
			<td><textarea rows="5" cols="50" id="wp101-video-code" name="wp101_video_code" class="large-text" placeholder="Example: <iframe src='http://player.vimeo.com/video/33767000' width='640' height='360' frameborder='0' webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>"><?php echo $editable_video ? esc_textarea( $editable_video['content'] ) : ''; ?></textarea></td>
		</tr>
		</table>
		<?php
		if ( $editable_video )
			submit_button( __( 'Update Video', 'wp101' ) );
		else
			submit_button( __( 'Add Video', 'wp101' ) );
		?>
		</form>
	<?php endif; ?>
<?php else : ?>

<?php $pages = $this->get_help_topics_html(); ?>
<?php $custom_pages = $this->get_custom_help_topics_html(); ?>
<?php if ( trim( $pages ) ) : ?>
<div id="wp101-topic-listing">
<h3><?php _e( 'Tutorials', 'wp101' ); ?><?php if ( current_user_can( 'manage_options' ) ) : ?><span><a class="button" href="<?php echo admin_url( 'admin.php?page=wp101&configure=1' ); ?>"><?php _ex( 'Settings', 'Button with limited space', 'wp101' ); ?></a></span><?php endif; ?></h3>
<?php echo $pages; ?>
<?php if ( trim( $custom_pages ) ) : ?>
<h3><?php _e( 'Custom Tutorials', 'wp101' ); ?></h3>
<?php echo $custom_pages; ?>
<?php endif; ?>
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
		<p><?php printf( __( 'No help topics found. <a href="%s">Configure your WP101Plugin.com API key</a>.', 'wp101' ), admin_url( 'admin.php?page=wp101&configure=1' ) ); ?></p>
	<?php else : ?>
		<p><?php _e( 'No help topics found. Contact the site administrator to configure your WP101Plugin.com API key.', 'wp101' ); ?></p>
	<?php endif; ?>
<?php endif; ?>

<?php endif; ?>

</div>
<?php
	}
}

new WP101_Plugin;
