<?php
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit ();
}

delete_option( 'restrict_user_content_settings');
//do whatever needs to be done when the plugin is deleted
?>