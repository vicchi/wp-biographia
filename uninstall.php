<?php

if (defined('WP_UNINSTALL_PLUGIN')) {
	$fields = array (0 => 'ID');
	$search = new WP_User_Query (array ('fields' => $fields));
	$users = $search->get_results ();
	
	// Remove the general WP Biographia options
	delete_option ('wp_biographia_settings');
	
	foreach ($users as $user) {
		// Remove the extended user profile metadata for each user
		
		delete_user_meta ($user->ID, 'wp_biographia_suppress_posts');
		delete_user_meta ($user->ID, 'wp_biographia_suppress_pages');
		delete_user_meta ($user->ID, 'wpb_twitter');
		delete_user_meta ($user->ID, 'wpb_facebook');
		delete_user_meta ($user->ID, 'wpb_linkedin');
		delete_user_meta ($user->ID, 'wpb_googleplus');
		delete_user_meta ($user->ID, 'wpb_delicious');
		delete_user_meta ($user->ID, 'wpb_flickr');
		delete_user_meta ($user->ID, 'wpb_picasa');
		delete_user_meta ($user->ID, 'wpb_vimeo');
		delete_user_meta ($user->ID, 'wpb_youtube');
		delete_user_meta ($user->ID, 'wpb_reddit');
		delete_user_meta ($user->ID, 'wp_biographia_short_bio');
		
		// Remove the 'dismissed pointers' flag for each user
		$dismissed = explode (',', get_user_meta ($user->ID, 'dismissed_wp_pointers', true));
		$key = array_search ('wp_biographia_pointer', $dismissed);
		if ($key !== false) {
			unset ($dismissed[$key]);
			update_user_meta ($user->ID, 'dismissed_wp_pointers', implode (',', $dismissed));
		}
	}
}

else {
	exit ();
}

?>
