<?php

if (defined('WP_UNINSTALL_PLUGIN')) {
	global $wpdb;
	
	// Remove the general WP Biographia options
	delete_option ('wp_biographia_settings');
	
	// Remove the extended user profile metadata added by WP Biographia
	$users = $wpdb->get_results ("SELECT ID from $wpdb->users ORDER BY ID");

	foreach ($users as $user) {
		delete_user_meta ($user->ID, 'wp_biographia_suppress_posts');
		delete_user_meta ($user->ID, 'wp_biographia_suppress_pages');
		delete_user_meta ($user->ID, 'twitter');
		delete_user_meta ($user->ID, 'facebook');
		delete_user_meta ($user->ID, 'linkedin');
		delete_user_meta ($user->ID, 'googleplus');
	}
}

else {
	exit ();
}

?>
