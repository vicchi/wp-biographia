<?php

if (defined('WP_UNINSTALL_PLUGIN')) {
	$fields = array (0 => 'ID');
	$search = new WP_User_Query (array ('fields' => $fields));
	$users = $search->get_results ();
	
	// Remove the general WP Biographia options
	delete_option ('wp_biographia_settings');
	
	// Remove the extended user profile metadata added by WP Biographia
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
