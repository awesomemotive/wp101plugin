<?php

// If uninstall is not called from WordPress, exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

// Delete Options
delete_option( 'wp101_api_key' );
delete_option( 'wp101_db_version' );
delete_option( 'wp101_hidden_topics' );
delete_option( 'wp101_custom_topics' );
delete_option( 'wp101_admin_restriction' );

// Delete Transients
delete_transient( 'wp101_topics' );
delete_transient( 'wp101_jetpack_topics' );
delete_transient( 'wp101_woocommerce_topics' );
delete_transient( 'wp101_wpseo_topics' );
delete_transient( 'wp101_message' );
delete_transient( 'wp101_get_admin_count' );
delete_transient( 'wp101_api_key_valid' );
