<?php
/*
 * Add in our CSS for the admin panel, via the admin_print_styles action hook
 */

function wp_biographia_add_admin_styles() {
	global $pagenow;

	if ($pagenow == 'options-general.php' &&
			isset ($_GET['page']) &&
			strstr ($_GET['page'],"wp-biographia")) {
		wp_enqueue_style ('dashboard');
		wp_enqueue_style ('global');
		wp_enqueue_style ('wp-admin');
		wp_enqueue_style ('farbtastic');
		wp_enqueue_style ('wp-biographia-admin', WPBIOGRAPHIAURL_URL . 'css/wp-biographia-admin.css');	
	}
}

/*
 * Add in our scripts for the admin panel, via the admin_print_scripts action hook
 */

function wp_biographia_add_admin_scripts() {
	global $pagenow;
	
	if ($pagenow == 'options-general.php' &&
			isset ($_GET['page']) &&
			strstr ($_GET['page'],"wp-biographia")) {
		wp_enqueue_script ('postbox');
		wp_enqueue_script ('dashboard');
		wp_enqueue_script ('custom-background');
		wp_enqueue_script ('wp-biographia_admin-script', WPBIOGRAPHIAURL_URL . 'js/wp-biographia-admin.js');
	}
}

/*
 * Check for updating the configuration options after a plugin upgrade
 */

function wp_biographia_admin_init() {
	/*
	 * Check, and if needed, upgrade the plugin's configuration settings ...
	 */
	
	wp_biographia_upgrade ();
}

function wp_biographia_upgrade() {
	$wp_biographia_settings = NULL;
	$upgrade_settings = false;
	$current_plugin_version = NULL;
	
	/*
	 * Even if the plugin has only just been installed, the activation hook should have
	 * fired *before* the admin_init action so therefore we /should/ already have the
	 * plugin's configuration options defined in the database, but there's no harm in checking
	 * just to make sure ...
	 */
	
	$wp_biographia_settings = get_option ('wp_biographia_settings');

	/*
	 * Bale out early if there's no need to check for the need to upgrade the configuration
	 * settings ...
	 */
	
	if (is_array ($wp_biographia_settings) &&
			isset ($wp_biographia_settings['wp_biographia_version']) &&
			$wp_biographia_settings['wp_biographia_version'] == WPBIOGRAPHIA_VERSION) {
		return;
	}

	if (!is_array ($wp_biographia_settings)) {
		/*
		 * Something odd is going on, so define the default set of config settings ...
		 */
		wp_biographia_add_defaults ();
	}
	
	else {
		/*
		 * Versions of WP Biographia prior to v2.1 had a bug where some configuration
		 * settings that were created at initial installation of the plugin were not
		 * persisted after the configuration settings were updated; one of these is
		 * 'wp_biographia_version'. In this case, the "special" 00 version captures
		 * and remedies this.
		 */
		
		if (isset ($wp_biographia_settings['wp_biographia_version'])) {
			$current_plugin_version = $wp_biographia_settings['wp_biographia_version'];
		}
		else {
			$current_plugin_version = '00';
		}

		/*
		 * V1.0 configuration settings ...
		 *
		 * wp_biographia_installed
		 * wp_biographia_version = "01"
		 * wp_biographia_alert_bg
		 * wp_biographia_display_front
		 * wp_biographia_display_archives
		 * wp_biographia_display_posts
		 * wp_biographia_display_pages
		 * wp_biographia_display_feed
		 * wp_biographia_alert_border
		 * wp_biographia_content_prefix
		 * wp_biographia_content_name
		 * wp_biographia_content_image
		 * wp_biographia_content_bio
		 * wp_biographia_content_web
		 * wp_biographia_content_twitter
		 * wp_biographia_content_facebook
		 * wp_biographia_content_linkedin
		 * wp_biographia_content_googleplus
		 * wp_biographia_content_posts
		 *
		 * v2.0 added configuration settings ...
		 *
		 * wp_biographia_content_email = "on"
		 * wp_biographia_content_image_size = "100"
		 * wp_biographia_style_border (was wp_biographia_alert_border) = "top"
		 * wp_biographia_style_bg (was wp_biographia_alert_bg) = "#FFEAA8"
		 * wp_biographia_display_location = "bottom"
		 * wp_biographia_page_exclusions (no default value)
		 * wp_biographia_post_exclusions (no default value)
		 *
		 * v2.0 removed configuration settings
		 *
		 * wp_biographia_alert_border (replaced by wp_biographia_style_border)
		 * wp_biographia_alert_bg (replaced by wp_biographia_style_bg)
		 * 
		 * v2.0 changed default configuration settings ...
		 *
		 * wp_biographia_version = "20"
		 *
         * v2.1 added configuration settings ...
		 *
         * wp_biographia_beta_enabled = ""
         * wp_biographia_suppress_posts = "" (user profile extension)
         * wp_biographia_suppress_pages = "" (user profile extension)
		 *
		 * v2.1 changed default configuration settings ...
		 *
		 * wp_biographia_version = "21"
		 *
		 * v2.1.1 changed default configuration settings ...
		 *
		 * wp_biographia_version = "211"
		 *
		 * v2.2 added configuration settings ...
		 * wp_biographia_content_delicious = ""
		 * wp_biographia_content_flickr = ""
		 * wp_biographia_content_picasa = ""
		 * wp_biograpia_content_vimeo = ""
		 * wp_biographia_content_youtube = ""
		 * wp_biographia_content_reddit = ""
		 *
		 * v2.2 changed default configuration settings ...
		 *
		 * wp_biographia_version = "22"
		 *
		 * v2.2.1 changed default configuration settings ...
		 * Note: v2.2.1 was a private beta and never formally released.
		 *
		 * wp_biographia_version = "221"
		 *
		 * v2.3 changed default configuration settings ...
		 *
		 * wp_biographia_version = "23"
		 *
		 */

		switch ($current_plugin_version) {
			case '00':
				if (!isset ($wp_biographia_settings['wp_biographia_installed'])) {
					$wp_biographia_settings['wp_biographia_installed'] = "on";
				}
				if (!isset ($wp_biographia_settings['wp_biographia_style_bg'])) {
					$wp_biographia_settings['wp_biographia_style_bg'] = "#FFEAA8";
				}
				if (!isset ($wp_biographia_settings['wp_biographia_style_border'])) {
					$wp_biographia_settings['wp_biographia_style_border'] = "top";
				}
				if (!isset ($wp_biographia_settings['wp_biographia_display_front'])) {
					$wp_biographia_settings['wp_biographia_display_front'] = "";
				}
				if (!isset ($wp_biographia_settings['wp_biographia_display_archives'])) {
					$wp_biographia_settings['wp_biographia_display_archives'] = "";
				}
				if (!isset ($wp_biographia_settings['wp_biographia_display_posts'])) {
					$wp_biographia_settings['wp_biographia_display_posts'] = "";
				}
				if (!isset ($wp_biographia_settings['wp_biographia_display_pages'])) {
					$wp_biographia_settings['wp_biographia_display_pages'] = "";
				}
				if (!isset ($wp_biographia_settings['wp_biographia_display_feed'])) {
					$wp_biographia_settings['wp_biographia_display_feed'] = "";
				}
				if (!isset ($wp_biographia_settings['wp_biographia_content_prefix'])) {
					$wp_biographia_settings['wp_biographia_content_prefix'] = "About";
				}
				if (!isset ($wp_biographia_settings['wp_biographia_content_name'])) {
					$wp_biographia_settings['wp_biographia_content_name'] = "none";
				}
				if (!isset ($wp_biographia_settings['wp_biographia_content_image'])) {
					$wp_biographia_settings['wp_biographia_content_image'] = "";
				}
				if (!isset ($wp_biographia_settings['wp_biographia_content_bio'])) {
					$wp_biographia_settings['wp_biographia_content_bio'] = "";
				}
				if (!isset ($wp_biographia_settings['wp_biographia_content_web'])) {
					$wp_biographia_settings['wp_biographia_content_web'] = "";
				}
				if (!isset ($wp_biographia_settings['wp_biographia_content_twitter'])) {
					$wp_biographia_settings['wp_biographia_content_twitter'] = "";
				}
				if (!isset ($wp_biographia_settings['wp_biographia_content_facebook'])) {
					$wp_biographia_settings['wp_biographia_content_facebook'] = "";
				}
				if (!isset ($wp_biographia_settings['wp_biographia_content_linkedin'])) {
					$wp_biographia_settings['wp_biographia_content_linkedin'] = "";
				}
				if (!isset ($wp_biographia_settings['wp_biographia_content_googleplus'])) {
					$wp_biographia_settings['wp_biographia_content_googleplus'] = "";
				}
				if (!isset ($wp_biographia_settings['wp_biographia_content_posts'])) {
					$wp_biographia_settings['wp_biographia_content_posts'] = "none";
				}
				
			case '01':
				if (!isset ($wp_biographia_settings['wp_biographia_content_email'])) {
					$wp_biographia_settings["wp_biographia_content_email"] = "";
				}

				if (!isset ($wp_biographia_settings['wp_biographia_content_image_size'])) {
					$wp_biographia_settings["wp_biographia_content_image_size"] = "100";
				}

				if (isset ($wp_biographia_settings['wp_biographia_alert_border'])) {
					if (!isset ($wp_biographia_settings['wp_biographia_style_border'])) {
						$wp_biographia_settings['wp_biographia_style_border'] = $wp_biographia_settings['wp_biographia_alert_border'];
					}
					unset ($wp_biographia_settings['wp_biographia_alert_border']);
				}

				if (isset ($wp_biographia_settings['wp_biographia_alert_bg'])) {
					if (!isset ($wp_biographia_settings['wp_biographia_style_bg'])) {
						$wp_biographia_settings['wp_biographia_style_bg'] = $wp_biographia_settings['wp_biographia_alert_bg'];
					}
					unset ($wp_biographia_settings['wp_biographia_alert_bg']);
				}

				if (!isset ($wp_biographia_settings['wp_biographia_display_location'])) {
					$wp_biographia_settings["wp_biographia_display_location"] = "bottom";
				}

				$upgrade_settings = true;

			case '20':
/*
 *				if (!isset ($wp_biographia_settings['wp_biographia_beta_enabled'])) {
 *					$wp_biographia_settings['wp_biographia_beta_enabled'] = "";
 *				}
 */

				global $wpdb;

				$users = $wpdb->get_results ("SELECT ID from $wpdb->users ORDER BY ID");
	
				foreach ($users as $user) {
					if (!get_user_meta ($user->ID, 'wp_biographia_suppress_posts', true)) {
						update_user_meta ($user->ID, 'wp_biographia_suppress_posts', '');
					}
		
					if (!get_user_meta ($user->ID, 'wp_biographia_suppress_pages', true)) {
						update_user_meta ($user->ID, 'wp_biographia_suppress_pages', '');
					}
				}
				$upgrade_settings = true;

			case '21':
			case '211':
			case '22':
				if (!isset ($wp_biographia_settings['wp_biographia_content_delicious'])) {
					$wp_biographia_settings["wp_biographia_content_delicious"] = "";
				}
				if (!isset ($wp_biographia_settings['wp_biographia_content_flickr'])) {
					$wp_biographia_settings["wp_biographia_content_flickr"] = "";
				}
				if (!isset ($wp_biographia_settings['wp_biographia_content_picasa'])) {
					$wp_biographia_settings["wp_biographia_content_picasa"] = "";
				}
				if (!isset ($wp_biographia_settings['wp_biographia_content_vimeo'])) {
					$wp_biographia_settings["wp_biographia_content_vimeo"] = "";
				}
				if (!isset ($wp_biographia_settings['wp_biographia_content_youtube'])) {
					$wp_biographia_settings["wp_biographia_content_youtube"] = "";
				}
				if (!isset ($wp_biographia_settings['wp_biographia_content_reddit'])) {
					$wp_biographia_settings["wp_biographia_content_reddit"] = "";
				}

			case '221':
			case '23':
				$wp_biographia_settings['wp_biographia_version'] = WPBIOGRAPHIA_VERSION;
				$upgrade_settings = true;
				
			default:
				break;
		}	// end-switch

		if ($upgrade_settings) {
			update_option ('wp_biographia_settings', $wp_biographia_settings);
		}
	}
}

/*
 * Define the Colophon side box
 */

function wp_biographia_show_colophon() {
	$content = '<p><em>"When it comes to software, I much prefer free software, because I have very seldom seen a program that has worked well enough for my needs and having sources available can be a life-saver"</em>&nbsp;&hellip;&nbsp;Linus Torvalds</p>';

	$content .= '<p>For the inner nerd in you, the latest version of WP Biographia was written using <a href="http://macromates.com/">TextMate</a> on a MacBook Pro running OS X 10.7.2 Lion and tested on the same machine running <a href="http://mamp.info/en/index.html">MAMP</a> (Mac/Apache/MySQL/PHP) before being let loose on the author\'s <a href="http://www.vicchi.org/">blog</a>.<p>';

	$content .= '<p>The official home for WP Biographia is on <a href="http://www.vicchi.org/codeage/wp-biographia/">Gary\'s Codeage</a>; it\'s also available from the official <a href="http://wordpress.org/extend/plugins/wp-biographia/">WordPress plugins repository</a>. If you\'re interested in what lies under the hood, the code is also on <a href="https://github.com/vicchi/wp-biographia">GitHub</a> to download, fork and otherwise hack around.<p>';
	
	$content .= '<p>WP Biographia is named after the etymology of the modern English word <em>biography</em>. The word first appeared in the 1680s, probably from the latin <em>biographia</em> which itself derived from the Greek <em>bio</em>, meaning "life" and <em>graphia</em>, meaning "record" or "account" which derived from <em>graphein</em>, "to write".</p>';
	
	$content .= '<p><small>Dictionary.com, "biography," in <em>Online Etymology Dictionary</em>. Source location: Douglas Harper, Historian. <a href="http://dictionary.reference.com/browse/biography">http://dictionary.reference.com/browse/biography</a>. Available: <a href="http://dictionary.reference.com">http://dictionary.reference.com</a>. Accessed: July 27, 2011.</small></p>';

	return wp_biographia_postbox ('wp-biographia-colophon', 'Colophon', $content);
}

/*
 * Define the Help and Support side box
 */

function wp_biographia_show_help_and_support() {
	$content = '<p>For help and support with WP Biographia, here\'s what you can do:
<ul>
<li>Ask a question on the <a href="http://wordpress.org/tags/wp-biographia?forum_id=10">WordPress support forum</a>; this is by far the best way so that other users can follow the conversation.</li>
<li>Ask me a question on Twitter; I\'m <a href="http://twitter.com/vicchi">@vicchi</a>.</li>
<li>Drop me an <a href="mailto:';
	$content .= antispambot("gary@vicchi.org");
	$content .= '">email</a> instead.</li>
</ul>
</p>
<p>But help and support is a two way street; here\'s what you might want to do:
<ul>
<li>If you like this plugin and use it on your WordPress site, or if you write about it online, <a href="http://www.vicchi.org/codeage/wp-biographia/">link to plugin</a> and drop me an <a href="mailto:';
	$content .= antispambot("gary@vicchi.org");
	$content .= '">email</a> telling me about this.</li>
<li>Rate the plugin on the <a href="http://wordpress.org/extend/plugins/wp-biographia/">WordPress plugin repository</a>.</li>
<li>WP Biographia is both free as in speech and free as in beer. No donations are required; <a href="http://www.vicchi.org/codeage/donate/">here\'s why</a>.</li>
</ul>
</p>';

	return wp_biographia_postbox ('wp-biographia-support', 'Help &amp; Support', $content);
}

/*
 * Define the Acknowledgements side box
 */

function wp_biographia_show_acknowledgements() {
	$content = '<p>WP Biographia is inspired by and based on <a href="http://www.jonbishop.com">Jon Bishop\'s</a> <a href="http://wordpress.org/extend/plugins/wp-about-author/">WP About Author</a> plugin. Thanks and kudos must go to Jon for writing a well structured, working WordPress plugin released under a software license that enables other plugins such as this one to be written or derived in the first place. Jon\'s written other <a href="http://profiles.wordpress.org/users/JonBishop/">WordPress plugins</a> as well; you should take a look.</p>';

	return wp_biographia_postbox ('wo-biographia-acknowledgements', 'Acknowledgements', $content);
}

/*
 * Define the admin panel
 */

function wp_biographia_general_settings() {
	$wp_biographia_settings = wp_biographia_process_settings ();
	
	$wrapped_content = "";

	$display_settings = "";
	$user_settings = "";
	$style_settings = "";
	$content_settings = "";
/*
 *	$beta_settings = "";
 */
	
	$image_size = "";
	$avatars_enabled = (get_option ('show_avatars') == 1 ? true : false);
/*
 *	$beta_enabled = ($wp_biographia_settings['wp_biographia_beta_enabled'] == "on" ? true : false);
 */
	
	/*
 	 * Biography Box Display Settings
 	 */
	
	$display_settings .= '<p><strong>' . __("Display On Front Page") . '</strong><br /> 
				<input type="checkbox" name="wp_biographia_display_front" ' .checked($wp_biographia_settings['wp_biographia_display_front'], 'on', false) . ' />
				<small>Displays the Biography Box for each post on the front page.</small></p>';

	
	$display_settings .= '<p><strong>' . __("Display On Individual Posts") . '</strong><br /> 
				<input type="checkbox" name="wp_biographia_display_posts" ' .checked($wp_biographia_settings['wp_biographia_display_posts'], 'on', false) . ' />
				<small>Displays the Biography Box for individual posts.</small></p>';

	// Archives -> Post Archives
	$display_settings .= '<p><strong>' . __("Display In Post Archives") . '</strong><br /> 
				<input type="checkbox" name="wp_biographia_display_archives" ' .checked($wp_biographia_settings['wp_biographia_display_archives'], 'on', false) . ' />
				<small>Displays the Biography Box for each post on archive pages.</small></p>';	

	// Add Post ID Exclusion
	$display_settings .= '<p><strong>' . __("Exclude From Single Posts (via Post ID)") . '</strong><br />
			<input type="text" name="wp_biographia_post_exclusions" id="wp_biographia_post_exclusions" value="'.$wp_biographia_settings['wp_biographia_post_exclusions'].'" /><br />
			<small>Suppresses the Biography Box when a post is displayed using the Single Post Template. Enter the Post IDs to suppress, comma separated with no spaces, e.g. 54,33,55</small></p>';

	$display_settings .= '<p><strong>' . __("Globally Exclude From Posts (via Post ID)") . '</strong><br />
		<input type="text" name="wp_biographia_global_post_exclusions" id="wp_biographia_global_post_exclusions" value="' . $wp_biographia_settings['wp_biographia_global_post_exclusions'] . '" /><br />
		<small>Suppresses the Biography Box whenever a post is displayed; singly, on archive pages or on the front page. Enter the Post IDs to globally suppress, comma separated with no spaces, e.g. 54,33,55.</small></p>';
		
	$display_settings .= '<p><strong>' . __("Display On Individual Pages") . '</strong><br /> 
				<input type="checkbox" name="wp_biographia_display_pages" ' .checked($wp_biographia_settings['wp_biographia_display_pages'], 'on', false) . ' />
				<small>Displays the Biography Box for individual pages.</small></p>';

	// Add Page ID Exclusion
	$display_settings .= '<p><strong>' . __("Exclude Pages (via Page ID)") . '</strong><br />
		<input type="text" name="wp_biographia_page_exclusions" id="wp_biographia_page_exclusions" value="'.$wp_biographia_settings['wp_biographia_page_exclusions'].'" /><br />
		<small>Suppresses the Biography Box when a page is displayed using the Page Template. Enter the Page IDs to suppress, comma separated with no spaces, e.g. 54,33,55.</small></p>';
	

	// Add Custom Post Types for Single & Archives
	//'wp_biographia_display_archives_'.$pt->name

	$args = array (
		'public' => true,
		'_builtin' => false
	);

	$pts = get_post_types ($args, 'objects');

	foreach ($pts as $pt) {
		$display_settings .= '<p><strong>' . __("Display On Individual ".$pt->labels->name) . '</strong><br /> 
					<input type="checkbox" name="wp_biographia_display_'.$pt->name.'" ' .checked($wp_biographia_settings['wp_biographia_display_'.$pt->name], 'on', false) . ' />
					<small>Displays the Biography Box on individual instances of custom post type '.$pt->labels->name.'.</small></p>';

		$display_settings .= '<p><strong>' . __("Display In ".$pt->labels->singular_name." Archives") . '</strong><br /> 
					<input type="checkbox" name="wp_biographia_display_archives_'.$pt->name.'" ' .checked($wp_biographia_settings['wp_biographia_display_archives_'.$pt->name], 'on', false) . ' />
					<small>Displays the Biography Box on archive pages for custom post type '.$pt->labels->name.'.</small></p>';	

		$display_settings .= '<p><strong>' . __("Exclude From Single {$pt->labels->name} (via {$pt->labels->singular_name} ID)") . '</strong><br />
			<input type="text" name="wp_biographia_'.$pt->name.'_exclusions" id="wp_biographia_'.$pt->name.'_exclusions" value="'.$wp_biographia_settings['wp_biographia_'.$pt->name.'_exclusions'].'" /><br />
			<small>Suppresses the Biography Box whenever a '. $pt->labels->singular_name . ' is displayed; singly, on archive pages or on the front page. Enter the ' . $pt->labels->singular_name . ' IDs to globally suppress, comma separated with no spaces, e.g. 54,33,55.</small></p>';

		$display_settings .= '<p><strong>' . __("Globally Exclude From {$pt->labels->name} (via {$pt->labels->singular_name} ID)") . '</strong><br />
			<input type="text" name="wp_biographia_global_' . $pt->name . '_exclusions" id="wp_biographia_global_' . $pt->name . '_exclusions" value="' . $wp_biographia_settings['wp_biographia_global_' . $pt->name . '_exclusions'] . '" /><br />
			<small>Suppresses the Biography Box whenever a ' . $pt->labels->singular_name . ' is displayed. Enter the ' . $pt->labels->singular_name . ' IDs to globally suppress, comma separated with no spaces, e.g. 54,33,55.</small></p>';
	}

	$wp_biographia_settings['wp_biographia_display_location'] = (
		 isset($wp_biographia_settings['wp_biographia_display_location']) ) ?
		 $wp_biographia_settings['wp_biographia_display_location'] : 'bottom';

	// Add Display Location: Top/Bottom
	$display_settings .= '<p><strong>' . __("Display Location") . '</strong><br />
		<input type="radio" name="wp_biographia_display_location" id="wp-biographia-content-name" value="top" '
		. checked ($wp_biographia_settings['wp_biographia_display_location'], 'top', false)
		.' />&nbsp;Display the Biography Box before the post or page content<br />
		<input type="radio" name="wp_biographia_display_location" id="wp-biographia-content-name" value="bottom" '
		. checked ($wp_biographia_settings['wp_biographia_display_location'], 'bottom', false)
		. ' />&nbsp;Display the Biography Box after the post or page content<br />';
    
	$display_settings .= '<p><strong>' . __("Display In RSS Feeds") . '</strong><br />
				<input type="checkbox" name="wp_biographia_display_feed" ' .checked($wp_biographia_settings['wp_biographia_display_feed'], 'on', false) . ' />
				<small>Displays the Biography Box in feeds for each entry.</small></p>';

	/*
	 * Biography Box User Settings
	 */

	// Add per user suppression of the Biography Box on posts and on pages

	global $wpdb;

	$users = $wpdb->get_results ("SELECT ID, user_login from $wpdb->users ORDER BY user_login");

	$post_enabled = array ();
	$post_suppressed = array ();
	$page_enabled = array ();
	$page_suppressed = array ();

	foreach ($users as $user) {
		if (get_user_meta ($user->ID, 'wp_biographia_suppress_posts', true) === 'on') {
			$post_suppressed[$user->ID] = $user->user_login;
		}

		else {
			$post_enabled[$user->ID] = $user->user_login;
		}

		if (get_user_meta ($user->ID, 'wp_biographia_suppress_pages', true) === 'on') {
 			$page_suppressed[$user->ID] = $user->user_login;
		}

		else {
			$page_enabled[$user->ID] = $user->user_login;
		}
	}

	$user_settings .= '<p><strong>Per User Suppression Of The Biography Box On Posts</strong><br />';
	$user_settings .= '<span class="wp-biographia-users">';
	$user_settings .= '<strong>Enabled Users</strong><br />';
	$user_settings .= '<select multiple id="wp-biographia-enabled-post-users" name="wp-biographia-enabled-post-users[]">';

	foreach ($post_enabled as $user_id => $user_login) {
		$user_settings .= '<option value="' . $user_id . '">' . $user_login . '</option>';
	}

	$user_settings .= '</select>';
	$user_settings .= '<a href="#" id="wp-biographia-user-post-add">Add &raquo;</a>';
	$user_settings .= '</span>';
	$user_settings .= '<span class="wp-biographia-users">';
	$user_settings .= '<strong>Suppressed Users</strong><br />';
	$user_settings .= '<select multiple id="wp-biographia-suppressed-post-users" name="wp-biographia-suppressed-post-users[]">';

	foreach ($post_suppressed as $user_id => $user_login) {
		$user_settings .= '<option value="' . $user_id . '">' . $user_login . '</option>';
	}

	$user_settings .= '</select>';
	$user_settings .= '<a href="#" id="wp-biographia-user-post-rem">&laquo; Remove</a>';
	$user_settings .= '</span>';
	$user_settings .= '<br />';
	$user_settings .= '<div style="clear: both";><small>Select the users who should not display the Biography Box on their authored posts. Selecting a user for suppression of the Biography Box affects all posts and custom post types by that user, on single post display, on archive pages and on the front page. This setting over-rides the individual user profile settings, providing the user has permission to edit their profile.</small></div></p>';

	$user_settings .= '<p><strong>Per User Suppression Of The Biography Box On Pages</strong><br />';
	$user_settings .= '<span class="wp-biographia-users">';
	$user_settings .= '<strong>Enabled Users</strong><br />';
	$user_settings .= '<select multiple id="wp-biographia-enabled-page-users" name="wp-biographia-enabled-page-users[]">';

	foreach ($page_enabled as $user_id => $user_login) {
		$user_settings .= '<option value="' . $user_id . '">' . $user_login . '</option>';
	}

	$user_settings .= '</select>';
	$user_settings .= '<a href="#" id="wp-biographia-user-page-add">Add &raquo;</a>';
	$user_settings .= '</span>';
	$user_settings .= '<span class="wp-biographia-users">';
	$user_settings .= '<strong>Suppressed Users</strong><br />';
	$user_settings .= '<select multiple id="wp-biographia-suppressed-page-users" name="wp-biographia-suppressed-page-users[]">';

	foreach ($page_suppressed as $user_id => $user_login) {
		$user_settings .= '<option value="' . $user_id . '">' . $user_login . '</option>';
	}

	$user_settings .= '</select>';
	$user_settings .= '<a href="#" id="wp-biographia-user-page-rem">&laquo; Remove</a>
</span>';
	$user_settings .= '<br />';
	$user_settings .= '<div style="clear: both";><small>Select the users who should not display the Biography Box on their authored pages. This setting over-rides the individual user profile settings, providing the user has permission to edit their profile.</small></div></p>';
	
	/*
	 * Biography Box Style Settings
	 */
	
	$style_settings .= '<p><strong>' . __("Box Background Color") . '</strong><br /> 
				<input type="text" name="wp_biographia_style_bg" id="background-color" value="' . $wp_biographia_settings['wp_biographia_style_bg'] . '" />
				<a class="hide-if-no-js" href="#" id="pickcolor">' . __('Select a Color') . '</a>
				<div id="colorPickerDiv" style="z-index: 100; background:#eee; border:1px solid #ccc; position:absolute; display:none;"></div>
				<small>By default, the background color of the Biography Box is a yellowish tone.</small></p>';
	$style_settings .= '<p><strong>' . __("Box Border") . '</strong><br /> 
                <select name="wp_biographia_style_border">
                  <option value="top" ' .selected($wp_biographia_settings['wp_biographia_style_border'], 'top', false) . '>Thick Top Border</option>
                  <option value="around" ' .selected($wp_biographia_settings['wp_biographia_style_border'], 'around', false) . '>Thin Surrounding Border</option>
                  <option value="none" ' .selected($wp_biographia_settings['wp_biographia_style_border'], 'none', false) . '>No Border</option>
                </select><br /><small>By default, a thick black line is displayed above the Biography Box.</small></p>';

	/*
	 * Biography Box Content Settings
	 */
	
	$content_settings .= '<p><strong>' . __("Biography Prefix") . '</strong><br />
		<input type="text" name="wp_biographia_content_prefix" id="wp-biographia-content-name" value="'
		. $wp_biographia_settings["wp_biographia_content_prefix"]
		. '" /><br />
		<small>Prefix text to be prepended to the author\'s name</small></p>';

	$content_settings .= '<p><strong>' . __("Author's Name") . '</strong><br />
		<input type="radio" name="wp_biographia_content_name" id="wp-biographia-content-name" value="first-last-name" '
		. checked ($wp_biographia_settings['wp_biographia_content_name'], 'first-last-name', false)
		.' />&nbsp;First/Last Name<br />
		<input type="radio" name="wp_biographia_content_name" id="wp-biographia-content-name" value="account-name" '
		. checked ($wp_biographia_settings['wp_biographia_content_name'], 'account-name', false)
		. ' />&nbsp;Account Name<br />
		<input type="radio" name="wp_biographia_content_name" id="wp-biographia-content-name" value="nickname" '
		. checked ($wp_biographia_settings['wp_biographia_content_name'], 'nickname', false)
		. ' />&nbsp;Nickname<br />
		<input type="radio" name="wp_biographia_content_name" id="wp-biographia-content-name" value="display-name" '
		. checked ($wp_biographia_settings['wp_biographia_content_name'], 'display-name', false)
		. ' />&nbsp;Display Name<br />
		<input type="radio" name="wp_biographia_content_name" id="wp-biographia-content-name" value="none" '
		. checked ($wp_biographia_settings['wp_biographia_content_name'], 'none', false)
		. ' />&nbsp;Don\'t Show The Name<br />
		<small>How you want to see the author\'s name displayed (if at all)</small></p>';

	if (!$avatars_enabled) {
		$content_settings .= '<div class="wp-biographia-warning">'
			. 'It looks like Avatars are not currently enabled; this means that the '
			. 'author\'s image won\'t be able to be displayed. If you want this to happen'
			. ' then go to <a href="'
			. admin_url('options-discussion.php')
			. '">Settings &rsaquo; Discussions</a> and set Avatar Display to Show Avatars.'
			. '</div>';
	}
	
	$content_settings .= '<p><strong>' . __("Author's Image") . '</strong><br />
		<input type="checkbox" name="wp_biographia_content_image" '
		. checked ($wp_biographia_settings['wp_biographia_content_image'], 'on', false)
		. disabled ($avatars_enabled, false, false)
		. '/>
		<small>Display the author\'s image?</small></p>';

	if (!isset ($wp_biographia_settings['wp_biographia_content_image_size']) ||
			$wp_biographia_settings['wp_biographia_content_image_size'] === '' ||
			$wp_biographia_settings['wp_biographia_content_image_size'] === 0) {
		$image_size = '100';
	}

	else {
		$image_size = $wp_biographia_settings['wp_biographia_content_image_size'];
	}

	$content_settings .= '<p><strong>' . __("Image Size") . '</strong><br />
		<input type="text" name="wp_biographia_content_image_size" id="wp_biographia_content_image_size" value="'. $image_size .'"'
		. disabled ($avatars_enabled, false, false)
		. '/><br />'
		. '<small>Enter image size, e.g. 32 for a 32x32 image, 70 for a 70x70 image, etc. Defaults to a 100x100 size image.</small></p>';
	$content_settings .= '<p><strong>' . __("Show Author's Biography") . '</strong><br />
		<input type="checkbox" name="wp_biographia_content_bio" '
		. checked ($wp_biographia_settings['wp_biographia_content_bio'], 'on', false)
		. '/>
		<small>Display the author\'s biography?</small></p>';

	$content_settings .= '<p><strong>' . __("Show Author's Email Address") . '</strong><br />
		<input type="checkbox" name="wp_biographia_content_email" '
		. checked ($wp_biographia_settings['wp_biographia_content_email'], 'on', false)
		. '/>
		<small>Display the author\'s email address?</small></p>';


	$content_settings .= '<p><strong>' . __("Show Author's Website Link") . '</strong><br />
		<input type="checkbox" name="wp_biographia_content_web" '
		. checked ($wp_biographia_settings['wp_biographia_content_web'], 'on', false)
		. '/>
		<small>Display the author\'s website details?</small></p>';

	$content_settings .= '<p><strong>' . __("Show Author's Twitter Link") . '</strong><br />
		<input type="checkbox" name="wp_biographia_content_twitter" '
		. checked ($wp_biographia_settings['wp_biographia_content_twitter'], 'on', false)
		. '/>
		<small>Display the author\'s Twitter details?</small></p>';
	
	$content_settings .= '<p><strong>' . __("Show Author's Facebook Link") . '</strong><br />
		<input type="checkbox" name="wp_biographia_content_facebook" '
		. checked ($wp_biographia_settings['wp_biographia_content_facebook'], 'on', false)
		. '/>
		<small>Display the author\'s Facebook details?</small></p>';

	$content_settings .= '<p><strong>' . __("Show Author's LinkedIn Link") . '</strong><br />
		<input type="checkbox" name="wp_biographia_content_linkedin" '
		. checked ($wp_biographia_settings['wp_biographia_content_linkedin'], 'on', false)
		. '/>
		<small>Display the author\'s LinkedIn details?</small></p>';

	$content_settings .= '<p><strong>' . __("Show Author's Google+ Link") . '</strong><br />
		<input type="checkbox" name="wp_biographia_content_googleplus" '
		. checked ($wp_biographia_settings['wp_biographia_content_googleplus'], 'on', false)
		. '/>
		<small>Display the author\'s Google+ details?</small></p>';

	$content_settings .= '<p><strong>' . __("Show Author's Delicious Link") . '</strong><br />
		<input type="checkbox" name="wp_biographia_content_delicious" '
		. checked ($wp_biographia_settings['wp_biographia_content_delicious'], 'on', false)
		. '/>
		<small>Display the author\'s Delicious details?</small></p>';

	$content_settings .= '<p><strong>' . __("Show Author's Flickr Link") . '</strong><br />
		<input type="checkbox" name="wp_biographia_content_flickr" '
		. checked ($wp_biographia_settings['wp_biographia_content_flickr'], 'on', false)
		. '/>
		<small>Display the author\'s Flickr details?</small></p>';

	$content_settings .= '<p><strong>' . __("Show Author's Picasa Link") . '</strong><br />
		<input type="checkbox" name="wp_biographia_content_picasa" '
		. checked ($wp_biographia_settings['wp_biographia_content_picasa'], 'on', false)
		. '/>
		<small>Display the author\'s Picasa details?</small></p>';

	$content_settings .= '<p><strong>' . __("Show Author's Vimeo Link") . '</strong><br />
		<input type="checkbox" name="wp_biographia_content_vimeo" '
		. checked ($wp_biographia_settings['wp_biographia_content_vimeo'], 'on', false)
		. '/>
		<small>Display the author\'s Vimeo details?</small></p>';

	$content_settings .= '<p><strong>' . __("Show Author's YouTube Link") . '</strong><br />
		<input type="checkbox" name="wp_biographia_content_youtube" '
		. checked ($wp_biographia_settings['wp_biographia_content_youtube'], 'on', false)
		. '/>
		<small>Display the author\'s YouTube details?</small></p>';

	$content_settings .= '<p><strong>' . __("Show Author's Reddit Link") . '</strong><br />
		<input type="checkbox" name="wp_biographia_content_reddit" '
		. checked ($wp_biographia_settings['wp_biographia_content_reddit'], 'on', false)
		. '/>
		<small>Display the author\'s Reddit details?</small></p>';

	
	$content_settings .= '<p><strong>' . __("Show More Posts Link") . '</strong><br />
		<input type="radio" name="wp_biographia_content_posts" id="wp-biographia-content-posts" value="basic" '
		. checked ($wp_biographia_settings['wp_biographia_content_posts'], 'basic', false)
		. ' />&nbsp;Basic More Posts Link<br />
		<input type="radio" name="wp_biographia_content_posts" id="wp-biographia-content-posts" value="extended" '
		. checked ($wp_biographia_settings['wp_biographia_content_posts'], 'extended', false)
		. ' />&nbsp;Extended More Posts Link<br />
		<input type="radio" name="wp_biographia_content_posts" id="wp-biographia-content-posts" value="none" '
		. checked ($wp_biographia_settings['wp_biographia_content_posts'], 'none', false)
		. ' />&nbsp;Don\'t Show The More Posts Link<br />
		<small>How you want to display and format the <em>More Posts By This Author</em> link</small></p>';

	/*
	 * Biography Box Experimental Settings
	 */

/*
	$beta_settings .= '<div class="wp-biographia-warning">'
		. '<em>Here be dragons.</em>These are new and/or experimental features. '
		. 'While they\'ve been requested by WP Biographia users and have '
		. 'been tested, a WordPress install is a complex beast and these settings may break other '
		. 'plugins, do strange and unexpected things or generally make you scratch your head and '
		. 'go <em>Huh?</em>. So if you enable these settings and odd things happen, then '
		. 'please disable them and <a href="http://wordpress.org/tags/wp-biographia?forum_id=10">'
		. 'let me know</a>. If you enable them and they do just what they\'re supposed to do, '
		. 'then <a href="http://wordpress.org/tags/wp-biographia?forum_id=10">please let me know'
		. '</a> as well!'
		. '</div>';

	$beta_settings .= '<p><strong>' . __("Enable Experimental Features") . '</strong><br />
		<input type="checkbox" name="wp_biographia_beta_enabled" '
		. checked ($wp_biographia_settings['wp_biographia_beta_enabled'], 'on', false)
		. '/>
		<small>Enable setting and use of WP Biographia experimental features</small></p>';
*/
	
	if (function_exists ('wp_nonce_field')) {
		$wrapped_content .= wp_nonce_field (
			'wp-biographia-update-options',
			'_wpnonce',
			true,
			false);
	}

	$wrapped_content .= wp_biographia_postbox ('wp-biographia-display-settings', 'Biography Box Display Settings', $display_settings);

	$wrapped_content .= wp_biographia_postbox ('wp-biographia-user-settings', 'Biography Box Per User Settings', $user_settings);
	
	$wrapped_content .= wp_biographia_postbox ('wp-biographia-style-settings', 'Biography Box Style Settings', $style_settings);

	$wrapped_content .= wp_biographia_postbox ('wp-biographia-settings-content', 'Biography Box Content Settings', $content_settings);

/*
 *	$wrapped_content .= wp_biographia_postbox ('wp-biographia-settings-beta', 'Biography Box Experimental Settings', $beta_settings);
 */	

	wp_biographia_admin_wrap ('WP Biographia Settings And Options', $wrapped_content);
}

/*
 * Save the submitted admin options
 */

function wp_biographia_option($field) {
	return (isset ($_POST[$field]) ? $_POST[$field] : "");
}

function wp_biographia_meta_option($user_array, $meta_key, $meta_value) {
	if ($user_array) {
		foreach ($user_array as $id) {
			update_user_meta ($id, $meta_key, $meta_value);
		}
	}
}

function wp_biographia_process_settings() {
	$wp_biographia_settings = get_option ('wp_biographia_settings');
	
	if (!empty ($_POST['wp_biographia_option_submitted'])) {
		if (strstr ($_GET['page'], "wp-biographia") &&
		 		check_admin_referer ('wp-biographia-update-options')) {

			/*
			 * Biography Box Display Settings
			 */

			$wp_biographia_settings['wp_biographia_display_front'] =
				wp_biographia_option ('wp_biographia_display_front');

			$wp_biographia_settings['wp_biographia_display_archives'] =
				wp_biographia_option ('wp_biographia_display_archives');

			$wp_biographia_settings['wp_biographia_display_posts'] =
				wp_biographia_option ('wp_biographia_display_posts');
				
			// Add Custom Post Types for Archives & Single
			$args = array (
				'public' => true,
				'_builtin' => false
			);

			$pts = get_post_types ($args, 'objects');
			foreach ($pts as $pt) {
				$wp_biographia_settings['wp_biographia_display_archives_' . $pt->name] =
					wp_biographia_option ('wp_biographia_display_archives_' . $pt->name);


				$wp_biographia_settings['wp_biographia_display_' . $pt->name] =
					wp_biographia_option ('wp_biographia_display_' . $pt->name);

				$wp_biographia_settings['wp_biographia_' . $pt->name . '_exclusions'] =
					wp_biographia_option ('wp_biographia_' . $pt->name . '_exclusions');

				$wp_biographia_settings['wp_biographia_global_' . $pt->name . '_exclusions'] =
					wp_biographia_option ('wp_biographia_global_' . $pt->name . '_exclusions');
			}

			// Post exclusions 
			$wp_biographia_settings['wp_biographia_post_exclusions'] =
				wp_biographia_option ('wp_biographia_post_exclusions');

			$wp_biographia_settings['wp_biographia_global_post_exclusions'] =
				wp_biographia_option ('wp_biographia_global_post_exclusions');

			$wp_biographia_settings['wp_biographia_display_pages'] =
					wp_biographia_option ('wp_biographia_display_pages');

			// Page exclusions 
			$wp_biographia_settings['wp_biographia_display_exclusions'] =
				wp_biographia_option ('wp_biographia_display_exclusions');

			// Per user suppression of the Biography Box on posts and on pages

			$enabled_post_users = $_POST['wp-biographia-enabled-post-users'];
			$suppressed_post_users = $_POST['wp-biographia-suppressed-post-users'];
			$enabled_page_users = $_POST['wp-biographia-enabled-page-users'];
			$suppressed_page_users = $_POST['wp-biographia-suppressed-page-users'];

			wp_biographia_meta_option ($enabled_post_users,
										'wp_biographia_suppress_posts',
										'');
			wp_biographia_meta_option ($suppressed_post_users,
										'wp_biographia_suppress_posts',
										'on');
			wp_biographia_meta_option ($enabled_page_users,
										'wp_biographia_suppress_pages',
										'');
			wp_biographia_meta_option ($suppressed_page_users,
										'wp_biographia_suppress_pages',
										'on');

			// Add my additions: location-top/bottom
			$wp_biographia_settings['wp_biographia_display_location'] =
				wp_biographia_option ('wp_biographia_display_location');

			$wp_biographia_settings['wp_biographia_display_feed'] =
				wp_biographia_option ('wp_biographia_display_feed');

			/*
			 * Biography Box Style Settings
			 */

			$color = preg_replace ('/[^0-9a-fA-F]/', '', $_POST['wp_biographia_style_bg']);

			if ((strlen ($color) == 6 || strlen ($color) == 3) &&
 				isset($_POST['wp_biographia_style_bg'])) {
					$wp_biographia_settings['wp_biographia_style_bg'] = $_POST['wp_biographia_style_bg'];
			}

			$wp_biographia_settings['wp_biographia_style_border'] = 
				wp_biographia_option ('wp_biographia_style_border');

			/*
			 * Biography Box Content Settings
			 */
			$wp_biographia_settings['wp_biographia_content_prefix'] = 
				wp_biographia_option ('wp_biographia_content_prefix');

			$wp_biographia_settings['wp_biographia_content_name'] = 
				wp_biographia_option ('wp_biographia_content_name');

			$wp_biographia_settings['wp_biographia_content_image'] = 
				wp_biographia_option ('wp_biographia_content_image');
			
			// Add Image Size
			$wp_biographia_settings['wp_biographia_content_image_size'] = 
				wp_biographia_option ('wp_biographia_content_image_size');

			$wp_biographia_settings['wp_biographia_content_bio'] = 
				wp_biographia_option ('wp_biographia_content_bio');

			$wp_biographia_settings['wp_biographia_content_email'] = 
				wp_biographia_option ('wp_biographia_content_email');

			$wp_biographia_settings['wp_biographia_content_web'] = 
				wp_biographia_option ('wp_biographia_content_web');

			$wp_biographia_settings['wp_biographia_content_twitter'] = 
				wp_biographia_option ('wp_biographia_content_twitter');

			$wp_biographia_settings['wp_biographia_content_facebook'] = 
				wp_biographia_option ('wp_biographia_content_facebook');

			$wp_biographia_settings['wp_biographia_content_linkedin'] = 
				wp_biographia_option ('wp_biographia_content_linkedin');

			$wp_biographia_settings['wp_biographia_content_googleplus'] = 
				wp_biographia_option ('wp_biographia_content_googleplus');

			$wp_biographia_settings['wp_biographia_content_delicious'] =
				wp_biographia_option ('wp_biographia_content_delicious');

			$wp_biographia_settings['wp_biographia_content_flickr'] =
				wp_biographia_option ('wp_biographia_content_flickr');

			$wp_biographia_settings['wp_biographia_content_picasa'] =
				wp_biographia_option ('wp_biographia_content_picasa');

			$wp_biographia_settings['wp_biograpia_content_vimeo'] =
				wp_biographia_option ('wp_biograpia_content_vimeo');

			$wp_biographia_settings['wp_biographia_content_youtube'] =
				wp_biographia_option ('wp_biographia_content_youtube');

			$wp_biographia_settings['wp_biographia_content_reddit'] =
				wp_biographia_option ('wp_biographia_content_reddit');

			$wp_biographia_settings['wp_biographia_content_posts'] = 
				wp_biographia_option ('wp_biographia_content_posts');

			/*
			 * Biography Box Beta/Experimental Settings
			 */

			/*
			$wp_biographia_settings['wp_biographia_beta_enabled'] = 
				wp_biographia_option ('wp_biographia_beta_enabled');
			*/
			
			echo "<div id=\"updatemessage\" class=\"updated fade\"><p>WP Biographia Settings And Options Updated.</p></div>\n";
			echo "<script type=\"text/javascript\">setTimeout(function(){jQuery('#updatemessage').hide('slow');}, 3000);</script>";	
			
			update_option ('wp_biographia_settings', $wp_biographia_settings);
		}
	}
	
	$wp_biographia_settings = get_option ('wp_biographia_settings');
	
	return $wp_biographia_settings;
}

/*
 * Add in our admin panel, via the admin_menu action hook
 */

function wp_biographia_add_options_subpanel() {
	if (function_exists ('add_options_page')) {
		add_options_page ('WP Biographia', 'WP Biographia', 'manage_options', __FILE__,
			'wp_biographia_general_settings');
	}
}

/*
 * Add in a single admin panel sub-box
 */

function wp_biographia_postbox($id, $title, $content) {
	$postbox_wrap = "";
	$postbox_wrap .= '<div id="' . $id . '" class="postbox">';
	$postbox_wrap .= '<div class="handlediv" title="Click to toggle"><br /></div>';
	$postbox_wrap .= '<h3 class="hndle"><span>' . $title . '</span></h3>';
	$postbox_wrap .= '<div class="inside">' . $content . '</div>';
	$postbox_wrap .= '</div>';

	return $postbox_wrap;
}	

/*
 * Wrap up all the constituent components of our admin panel
 */

function wp_biographia_admin_wrap($title, $content) {
?>
    <div class="wrap">
        <h2><?php echo $title; ?></h2>
        <form method="post" action="">
            <div class="postbox-container wp-biographia-postbox-settings">
                <div class="metabox-holder">	
                    <div class="meta-box-sortables">
                    <?php
                        echo $content;
                    ?>
                    <p class="submit"> 
                        <input type="submit" name="wp_biographia_option_submitted" class="button-primary" value="Save Changes" /> 
                    </p> 
                    <br /><br />
                    </div>
                  </div>
                </div>
                <div class="postbox-container wp-biographia-postbox-sidebar">
                  <div class="metabox-holder">	
                    <div class="meta-box-sortables">
                    <?php
						echo wp_biographia_show_help_and_support ();
						echo wp_biographia_show_colophon ();
						echo wp_biographia_show_acknowledgements ();
                    ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
<?php
}

function wp_biographia_add_profile_extensions($user) {
	?>
	<h3>Biography Box</h3>
	<table class="form-table">
		<tr>
			<th scope="row">Suppress From Posts</th>
			<td>
				<label for="wp_biographia_suppress_posts">
					<input type="checkbox" name="wp_biographia_suppress_posts" id="wp-biographia-suppress-posts" <?php checked (get_user_meta ($user->ID, 'wp_biographia_suppress_posts', true), 'on'); ?> <?php disabled (current_user_can ('manage_options'), false); ?> /> Don't show the Biography Box on your posts
				</label>
			</td>
		</tr>
		<tr>
			<th scope="row">Suppress From Pages</th>
			<td>
				<label for="wp_biographia_suppress_pages">
					<input type="checkbox" name="wp_biographia_suppress_pages" id="wp-biographia-suppress-pages" <?php checked (get_user_meta ($user->ID, 'wp_biographia_suppress_pages', true), 'on'); ?> <?php disabled (current_user_can ('manage_options'), false); ?> /> Don't show the Biography Box on your pages
				</label>
			</td>
		</tr>
	</table>
	<?php
}

function wp_biographia_save_profile_extensions($user_id) {
	update_user_meta ($user_id, 'wp_biographia_suppress_posts',
		wp_biographia_option ('wp_biographia_suppress_posts'));
	update_user_meta ($user_id, 'wp_biographia_suppress_pages',
		wp_biographia_option ('wp_biographia_suppress_pages'));
}

function wp_biographia_settings_link($links) {
	$settings_link = '<a href="options-general.php?page=wp-biographia/includes/wp-biographia-admin.php">Settings</a>';
	array_unshift ($links, $settings_link);
	return $links;
}

?>
