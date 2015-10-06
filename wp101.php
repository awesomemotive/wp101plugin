<?php
/*
Plugin Name: WP101
Description: A complete set of WordPress video tutorials for beginners, delivered directly in the dashboard.
Version: 3.2.3
Author: WP101Plugin.com
Author URI: https://wp101plugin.com/
Text Domain: wp101
*/

// API KEY
// You can hardcode the API key here, and it will be used as a starting value for the key
$_wp101_api_key = '';

class WP101_Plugin {
	public static $db_version    = 2;
	private static $instance     = false;
	public static $api_base      = 'https://wp101plugin.com/?wp101-api-server&';
	public static $subscribe_url = 'https://wp101plugin.com/';
	public static $renew_url     = 'https://wp101plugin.com/';

	public static function get_instance() {

		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;

	}

	public function __construct() {

		self::$instance = $this;

		self::$instance->includes();

		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
		// Translations
		load_plugin_textdomain( 'wp101', false, basename( dirname( __FILE__ ) ) . '/languages' );

		// Actions and filters
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_head', array( $this, 'wp101_admin_icon') );
		add_action( 'wp_ajax_wp101-showhide-topic', array( $this, 'ajax_handler' ) );
		add_action( 'wp_ajax_wp101-delete-topic'  , array( $this, 'ajax_delete_topic' ) );

		$this->register_settings_hooks();

		// Upgrades
		$db_version = get_option( 'wp101_db_version' );
		if ( $db_version < 2 ) {
			// Flush it out
			delete_transient( 'wp101_topics' );
			update_option( 'wp101_db_version', 2 );
		}

		delete_transient( 'wp101_topics' );
	}

	public function get_api_base() {
		return self::$api_base;
	}

	public function includes() {
		do_action( 'wp101_pre_includes', self::$instance );

		include_once 'integrations/class.wpseo.php';
	}
	public function register_settings_hooks() {
		add_action( 'wp101_admin_action_api-key'       , array( $this, 'update_api_key' ) );
		add_action( 'wp101_admin_action_add-video'     , array( $this, 'add_video' ) );
		add_action( 'wp101_admin_action_update-video'  , array( $this, 'update_video' ) );
		add_action( 'wp101_admin_action_restrict-admin', array( $this, 'admin_restriction' ) );
	}

	public function admin_restriction() {
		check_admin_referer( 'wp101-admin_restriction' );

		update_option( 'wp101_admin_restriction', absint( $_POST['wp101_admin_restriction'] ) );

		wp_redirect( admin_url( 'admin.php?page=wp101&configure=1' ) );
		exit();
	}

	public function update_api_key() {

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
	}

	public function add_video() {
		check_admin_referer( 'wp101-add-video' );
		$this->add_custom_help_topic( stripslashes( $_POST['wp101_video_title'] ), stripslashes( $_POST['wp101_video_code'] ) );
		set_transient( 'wp101_message', 'added_video', 300 );
		wp_redirect( admin_url( 'admin.php?page=wp101&configure=1' ) );
		exit();
	}

	public function update_video() {
		check_admin_referer( 'wp101-update-video-' . $_POST['document'] );
		$this->update_custom_help_topic( $_POST['document'], stripslashes( $_POST['wp101_video_title'] ), stripslashes( $_POST['wp101_video_code'] ) );
		set_transient( 'wp101_message', 'updated_video', 300 );
		wp_redirect( admin_url( 'admin.php?page=wp101&configure=1' ) );
		exit();
	}

	public function admin_menu() {
		$hook = add_menu_page( _x( 'WP101', 'page title', 'wp101' ), _x( 'Video Tutorials', 'menu title', 'wp101' ), 'read', 'wp101', array( $this, 'render_listing_page' ) );
		add_action( "load-{$hook}", array( $this, 'load' ) );
	}

    public function wp101_admin_icon() {
	    echo '<style>#adminmenu #toplevel_page_wp101 div.wp-menu-image:before { content: "\f236" !important; }</style>';
    }

	private function validate_api_key_with_server( $key = null ) {
		if ( null === $key ) {
			$key = $this->get_key();
		}

		$query = wp_remote_get( esc_url_raw( self::$api_base . 'action=check_key&api_key=' . $key ), array( 'timeout' => 45, 'sslverify' => false, 'user-agent' => 'WP101Plugin' ) );

		if ( is_wp_error( $query ) ) {
			return false; // Failed to query the server
		}

		$result = json_decode( wp_remote_retrieve_body( $query ) );

		return $result->data->status;
	}

	public function get_key() {
			global $_wp101_api_key;

			$db = get_option( 'wp101_api_key' );

			if ( empty( $db ) && isset( $_wp101_api_key ) && ! empty( $_wp101_api_key ) ) {
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

		if ( isset( $_POST['wp101-action'] ) && $this->is_user_authorized() ) {
			do_action( 'wp101_admin_action_' . $_POST['wp101-action'] );
		}

		if ( $message = get_transient( 'wp101_message' ) ) {
			delete_transient( 'wp101_message' );
			add_action( 'admin_notices', array( $this, 'api_key_' . $message . '_message' ) );
		} else if ( ! isset( $_GET['configure'] ) ) {
			$result = $this->validate_api_key();
			if ( 'valid' !== $result && $this->is_user_authorized() ) {
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

	public function api_key_notset_message(){ /* no message needed */ }

	private function enqueue() {
		wp_enqueue_script( 'wp101', plugins_url( "js/wp101.js", __FILE__ ), array( 'jquery' ), '20140905b' );
		wp_enqueue_style( 'wp101', plugins_url( "css/wp101.css", __FILE__ ), array(), '20140922b' );
	}

	public function validate_api_key() {
		if ( ! get_transient( 'wp101_api_key_valid' ) ) {
			if ( ! $this->get_key() ) {
				// Hasn't set API key yet
				return 'notset';
			} else {
				// Check the API key against the server
				$response = $this->validate_api_key_with_server();
				if ( 'valid' == $response ) {
					set_transient( 'wp101_api_key_valid', 1, 24 * 3600 ); // Good for a day.
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

		$document      = false;

		$topics        = $this->get_help_topics();
		$custom_topics = $this->get_custom_help_topics();

		if ( isset( $topics[ $id ] ) ) {
			$document = $topics[ $id ];
		} else if ( isset( $custom_topics[ $id ] ) ) {
			$document = $custom_topics[ $id ];
		}

		return apply_filters( 'wp101_get_document', $document, $id, self::$instance );
	}

	private function get_custom_help_topics() {
		return (array) apply_filters( 'wp101_get_custom_help_topics', get_option( 'wp101_custom_topics' ), self::$instance );
	}

	private function get_custom_help_topic( $id ) {

		$topics = $this->get_custom_help_topics();

		if ( isset( $topics[ $id ] ) ) {
			return $topics[ $id ];
		} else {
			return false;
		}
	}

	private function update_custom_help_topics( $topics ) {
		update_option( 'wp101_custom_topics', $topics );
	}

	private function update_custom_help_topic( $id, $title, $content ) {
		$topics        = $this->get_custom_help_topics();
		$topics[ $id ] = array( 'title' => $title, 'content' => $content );
		$this->update_custom_help_topics( $topics );
	}

	private function remove_custom_help_topic( $id ) {
		$topics = $this->get_custom_help_topics();
		unset( $topics[ $id ] );
		$this->update_custom_help_topics( $topics );
	}

	private function add_custom_help_topic( $title, $content ) {
		$topics = $this->get_custom_help_topics();
		$topics[ sanitize_title( $title ) ] = array( 'title' => $title, 'content' => $content );
		$this->update_custom_help_topics( $topics );
	}

	private function get_help_topics() {

		$help_topics = false;

		if ( 'valid' == $this->validate_api_key() ) {

			if ( $topics = get_transient( 'wp101_topics' ) ) {
				$help_topics = $topics;
			} else {
				$result = wp_remote_get( self::$api_base . 'action=get_topics&api_key=' . $this->get_key(), array( 'timeout' => 45, 'sslverify' => false, 'user-agent' => 'WP101Plugin' ) );
				$result = json_decode( $result['body'], true );
				if ( ! $result['error'] && count( $result['data'] ) ) {
					set_transient( 'wp101_topics', $result['data'], 30 ); // Good for a day.
					$help_topics =  $result['data'];
				}
			}
		}

		return apply_filters( 'wp101_get_help_topics', $help_topics, self::$instance );
	}

	public function ajax_handler() {

		if ( isset( $_REQUEST['topic'] ) ) {
			do_action( 'wp101_ajax_handler_' . $_REQUEST['topic'], self::$instance, $_REQUEST['direction'] );
		}

		if ( ! isset( $_REQUEST['topic_id'] ) ) {
			die( '0' );
		}

		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'wp101-showhide-' . $_REQUEST['topic_id'] ) ) {
			die( '-1' );
		}

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

		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'wp101-delete-topic-' . $_REQUEST['topic_id'] ) ) {
			die( '-1' );
		}

		$this->remove_custom_help_topic( $_REQUEST['topic_id'] );

		die( '1' );
	}

	public function get_hidden_topics() {
		return (array) apply_filters( 'wp101_get_hidden_topics', get_option( 'wp101_hidden_topics' ), self::$instance );
	}

	public function is_hidden( $topic_id ) {
		$hidden_topics = $this->get_hidden_topics();
		return in_array( $topic_id, $hidden_topics );
	}

	public function hide_topic( $topic_id ) {
		$hidden_topics = $this->get_hidden_topics();
		$hidden_topics[] = $topic_id;
		return update_option( 'wp101_hidden_topics', $hidden_topics );
	}

	public function show_topic( $topic_id ) {
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

		if ( ! $topics ) {
			return false;
		}

		$output = '<ul class="wp101-topic-ul">';

		foreach ( $topics as $topic ) {
			if ( $edit_mode ) {
				$edit_links = '&nbsp;&nbsp;<small class="wp101-show">[<a data-nonce="' . wp_create_nonce( 'wp101-showhide-' . $topic['id'] ) . '" data-topic-id="' . $topic['id'] . '" href="#">show</a>]</small><small class="wp101-hide">[<a data-nonce="' . wp_create_nonce( 'wp101-showhide-' . $topic['id'] ) . '" data-topic-id="' . $topic['id'] . '" href="#">hide</a>]</small>';
				if ( $this->is_hidden( $topic['id'] ) ) {
					$addl_class = 'wp101-hidden';
				} else {
					$addl_class = 'wp101-shown';
				}
			} else {
				if ( $this->is_hidden( $topic['id'] ) ) {
					continue;
				}
				$edit_links = $addl_class = '';
			}
			$output .= '<li class="' . $addl_class . ' page-item-' . $topic['id'] . '"><span><a href="' . esc_url( admin_url( 'admin.php?page=wp101&document=' . $topic['id'] ) ) . '">' . esc_html( $topic['title'] ) . '</a></span>' . $edit_links . '</li>';
		}

		$output .= '</ul>';

		return $output;
	}

	private function get_custom_help_topics_html( $edit_mode = false ) {

		$output = '';

		if ( $custom_topics = $this->get_custom_help_topics() ) {

			$output .= '<ul class="wp101-topic-ul">';

			foreach ( $custom_topics as $id => $topic ) {

				if ( $edit_mode ) {
					$output .= '<li class="page-item-' . $id . '"><span><a href="' . esc_url( admin_url( 'admin.php?page=wp101&configure=1&document=' . $id ) ) . '">' . esc_html( $topic['title'] ) . '</a></span> <small class="wp101-delete">[<a href="#" data-topic-id="' . $id . '" data-nonce="' . wp_create_nonce( 'wp101-delete-topic-' . $id ) . '">delete</a>]</small></li>';
				} else {
					$output .= '<li class="page-item-' . $id . '"><span><a href="' . esc_url( admin_url( 'admin.php?page=wp101&document=' . $id ) ) . '">' . esc_html( $topic['title'] ) . '</a></span></li>';
				}
			}
			$output .= '</ul>';
		}

		return $output;
	}

	/**
	 * Checks if current user is authorized to change WP101 settings.
	 *
	 * Prior to 3.0.5, anyone with `manage_options` caps could edit settings.
	 * Now, settings management can be limited to a single user.
	 *
	 * @since  3.0.5
	 * @return bool Whether or not user can access settings management area.
	 */
	private function is_user_authorized() {

		$is_user_authorized = current_user_can( 'manage_options' );
		$restriction        = get_option( 'wp101_admin_restriction' );

		if ( ! empty( $restriction ) ) {
			$is_user_authorized = get_current_user_id() == $restriction;
		}

		return apply_filters( 'wp101_is_user_authorized', $is_user_authorized, self::$instance );
	}

	/**
	 * Gets admin count.
	 *
	 * Useful for our settings management restriction routine.
	 * If a site has an unseemly amount of administrators, it's not going to be helpful
	 * to have them all in a dropdown to select from.
	 *
	 * @since  3.0.5
	 * @return int Number of administrators on site.
	 */
	public function get_admin_count() {

		if ( false === ( $admins = get_transient( 'wp101_get_admin_count' ) ) ) {

			$users        = count_users();
			$default_role = apply_filters( 'wp101_default_settings_role', 'administrator' );
			$admins       = isset( $users['avail_roles'][ $default_role ] ) ? $users['avail_roles'][ $default_role ] : 0;

			// When min. version bumps to 3.5, we can use DAY_IN_SECONDS.
			set_transient( 'wp101_get_admin_count', $admins, 60 * 60 * 24 );
		}

		return absint( $admins );
	}

	public function render_listing_page() {
		$document_id = isset( $_GET['document'] ) ? sanitize_text_field( $_GET['document'] ) : 1;

		while ( $this->is_hidden( $document_id ) ) {
			$document_id++;
		}

		if ( $document_id ) : ?>
			<style>
			div#wp101-topic-listing .page-item-<?php echo $document_id; ?> > span a {
				font-weight: bold;
			}
			</style>
		<?php endif; ?>
<div class="wrap" id="wp101-settings">
	<h2 class="wp101title"><?php _ex( 'WordPress Video Tutorials', 'h2 title', 'wp101' ); ?></h2>

	<?php if ( $this->is_user_authorized() && isset( $_GET['configure'] ) && $_GET['configure'] ) : ?>

	<?php if ( ! isset( $_GET['document'] ) ) : ?>
		<h3 class="title"><?php _e( 'API Key', 'wp101' ); ?></h3>

		<?php if ( 'valid' !== $this->validate_api_key() ) : ?>
			<div class="updated">
			<p><?php _e( 'The WP101 Plugin requires a valid API key to provide access to the latest WordPress tutorial videos.', 'wp101' ); ?> <a class="button" href="<?php echo esc_url( self::$subscribe_url ); ?>" title="<?php esc_attr_e( 'WP101 Tutorial Plugin', 'wp101' ); ?>" target="_blank"><?php esc_html_e( 'Subscription Info' ); ?></a></p>
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
		<h3 class="title"><?php _e( 'WordPress Tutorial Videos', 'wp101' ); ?></h3>
		<p><?php _e( 'If there are specific videos or topics that don&#8217;t apply to this site, you can hide them.', 'wp101' ); ?></p>
		<?php
			echo $this->get_help_topics_html( true );
			do_action( 'wp101_after_edit_help_topics', self::$instance );
		?>

		<?php endif; ?>
	<?php endif; ?>

	<?php if ( $this->is_user_authorized() && current_user_can( 'unfiltered_html' ) ) : ?>
		<?php $editable_video = isset( $_GET['document'] ) ? $this->get_custom_help_topic( $_GET['document'] ) : false; ?>
		<?php if ( $editable_video ) : ?>
			<h3 class="title"><?php _e( 'Edit Custom Video', 'wp101' ); ?></h3>
		<?php else : ?>
			<h3 class="title"><?php _e( 'Custom Videos', 'wp101' ); ?></h3>
		<?php endif; ?>
		<?php if ( !isset( $_GET['document'] ) ) : ?>
			<p><?php _e( 'If you have your own videos, you can add them here. They will display in a separate section, below the WordPress tutorial videos.', 'wp101' ); ?></p>
			<?php if ( $this->get_custom_help_topics() ) : ?>
				<?php
					echo $this->get_custom_help_topics_html( true );
					do_action( 'wp101_after_edit_custom_help_topics', self::$instance );
				?>
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
			<th scope="row"><label for="wp101-video-title"><?php _e( 'Video Title:', 'wp101' ); ?></label></th>
			<td><input type="text" id="wp101-video-title" name="wp101_video_title" class="regular-text" value="<?php echo $editable_video ? esc_attr( $editable_video['title'] ) : ''; ?>"/></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="wp101-video-code"><?php _e( 'Embed Code:', 'wp101' ); ?></label></th>
			<td><textarea rows="5" cols="50" id="wp101-video-code" name="wp101_video_code" class="large-text" placeholder="Example: <iframe src='//player.vimeo.com/video/123456789' width='1280' height='720' frameborder='0' webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>"><?php echo $editable_video ? esc_textarea( $editable_video['content'] ) : ''; ?></textarea></td>
		</tr>
		</table>
		<?php
		if ( $editable_video )
			submit_button( __( 'Update Video', 'wp101' ) );
		else
			submit_button( __( 'Add Video', 'wp101' ) );
		?>
		</form>
	<?php
		endif;

		$admin_count = $this->get_admin_count();

		if ( apply_filters( 'wp101_too_many_admins', ( $admin_count < 100 ), $admin_count ) ) :

			$args   = apply_filters( 'wp101_settings_management_user_args', array(
				'role' => 'administrator'
			) );

			$admins = get_users( $args );
	?>
		<h3 class="title"><?php _e( 'Settings Management', 'wp101' ); ?></h3>
		<p class="description"><?php _e( 'By default, all administrators can change the settings above. Optionally, choose a specific admin who alone will have access to this settings panel.', 'wp101' ); ?></p>
		<form action="" method="post">
		<input type="hidden" name="wp101-action" value="restrict-admin" />
		<?php wp_nonce_field( 'wp101-admin_restriction' ); ?>
		<table class="form-table">
		<tr valign="top">
			<th scope="row"><label for="wp101-admin-restriction"><?php _e( 'Settings Access:', 'wp101' ); ?></label></th>
			<td>
				<select class="regular-text" type="text" id="wp101-admin-restriction" name="wp101_admin_restriction">
					<option value=''><?php _e( 'All Administrators', 'wp101' ); ?></option>
					<?php
						foreach ( $admins as $admin ) {
							echo '<option value="' . $admin->ID . '" ' . selected( $admin->ID, get_option( 'wp101_admin_restriction' ), false ) . '>' . esc_html( $admin->display_name ) . '</option>';
						}
					?>
				</select>
			</td>
		</tr>
		</table>
		<?php submit_button(); ?>
		</form>

	<?php
		endif;
	else :
		$pages        = $this->get_help_topics_html();
		$custom_pages = $this->get_custom_help_topics_html();

		if ( trim( $pages ) ) :
?>
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
		<script>
		jQuery(function($){
			var video = $( '#wp101-topic iframe' ), ratio = video.attr( 'height' ) / video.attr( 'width' );

			var wp101Resize = function() {
				video.css( 'height', ( video.width() * ratio ) + 'px' );
			};

			var $win = $(window);
			$win.ready( wp101Resize );
			$win.resize( wp101Resize );
		});
		</script>
		<div id="wp101-topic-listing">
			<h3><?php _e( 'Video Tutorials', 'wp101' ); ?><?php if ( $this->is_user_authorized() ) : ?><span><a class="button" href="<?php echo admin_url( 'admin.php?page=wp101&configure=1' ); ?>"><?php _ex( 'Settings', 'Button with limited space', 'wp101' ); ?></a></span><?php endif; ?></h3>
			<?php
				echo $pages;
				do_action( 'wp101_after_help_topics', self::$instance );
			?>
			<?php if ( trim( $custom_pages ) ) : ?>
			<h3><?php _e( 'Custom Video Tutorials', 'wp101' ); ?></h3>
			<?php
				echo $custom_pages;
				do_action( 'wp101_after_custom_help_topics', self::$instance );
			?>
			<?php endif; ?>
		</div>
		<?php else : ?>
			<?php if ( $this->is_user_authorized() ) : ?>
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

WP101_Plugin::get_instance();