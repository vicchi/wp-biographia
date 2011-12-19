<?php
/*
Plugin Name: WP Biographia
Plugin URI: http://www.vicchi.org/codeage/wp-biographia/
Description: Add and display a customizable author biography for individual posts, in RSS feeds, on pages, in archives and on each entry on the landing page.
Version: 2.1
Author: Gary Gale & Travis Smith
Author URI: http://www.garygale.com/
License: GPL2
*/

define ('WPBIOGRAPHIA_VERSION', '21');
define ('WPBIOGRAPHIAURL_URL', plugin_dir_url(__FILE__));
define ('WPBIOGRAPHIAURL_PATH', plugin_dir_path(__FILE__));

require_once (WPBIOGRAPHIAURL_PATH."includes/wp-biographia-admin.php");

/*
 * Produce and format the Biography Box according to the currently defined options
 */

function wp_biographia_display($for_feed = false) {
  	$wp_biographia_settings = array ();
	$wp_biographia_settings = get_option ('wp_biographia_settings');
	
	$wp_biographia_content = "";
	$wp_biographia_author_pic =  "";
	$wp_biographia_formatted_name = "";
	
	$wp_biographia_author = array ();

	$wp_biographia_author['account-name'] = get_the_author ();
	$wp_biographia_author['first-last-name'] = get_the_author_meta ('first_name')
		. ' '
		. get_the_author_meta ('last_name');
	$wp_biographia_author['nickname'] = get_the_author_meta ('nickname');
	$wp_biographia_author['display-name'] = get_the_author_meta ('display_name');
	
	$wp_biographia_author['bio'] = get_the_author_meta ('description');

	$wp_biographia_author['email'] = get_the_author_meta ('user_email');
	$wp_biographia_author['twitter'] = get_the_author_meta ('twitter');
	$wp_biographia_author['facebook'] = get_the_author_meta ('facebook');
	$wp_biographia_author['linkedin'] = get_the_author_meta ('linkedin');
	$wp_biographia_author['googleplus'] = get_the_author_meta ('googleplus');
	$wp_biographia_author['website'] = get_the_author_meta ('url');

	$wp_biographia_author['posts'] = (int)get_the_author_posts ();
  	$wp_biographia_author['posts_url'] = get_author_posts_url (get_the_author_meta ('ID'));

  	// Add Image Size Output
	$wp_biographia_author_pic_size =
		(isset($wp_biographia_settings['wp_biographia_content_image_size'])) ?
	 		$wp_biographia_settings['wp_biographia_content_image_size'] : '100';
	$wp_biographia_author_pic = get_avatar (get_the_author_email (),
	 										$wp_biographia_author_pic_size);

	if (!empty ($wp_biographia_settings['wp_biographia_content_prefix']) ||
		!empty ($wp_biographia_settings['wp_biographia_content_name'])) {
		$wp_biographia_content .= '<h3>';
		if (!empty ($wp_biographia_settings['wp_biographia_content_prefix'])) {
			$wp_biographia_content .= $wp_biographia_settings['wp_biographia_content_prefix']
				. ' ';
		}
		if (!empty ($wp_biographia_settings['wp_biographia_content_name']) &&
			($wp_biographia_settings['wp_biographia_content_name'] != 'none')) {

			switch($wp_biographia_settings['wp_biographia_content_name']) {
				case 'first-last-name':
					$wp_biographia_formatted_name .= $wp_biographia_author['first-last-name'];
					break;
				case 'account-name':
					$wp_biographia_formatted_name .= $wp_biographia_author['account-name'];
					break;
				case 'nickname':
					$wp_biographia_formatted_name .= $wp_biographia_author['nickname'];
					break;
				case 'display-name':
					$wp_biographia_formatted_name .= $wp_biographia_author['display-name'];
					break;
			}
			
			$wp_biographia_content .= '<a href="' .
				$wp_biographia_author['posts_url'] .
				'" title="' .
				$wp_biographia_formatted_name .
				'">' .
				$wp_biographia_formatted_name .
				'</a>';
		}
		$wp_biographia_content .= '</h3>';
	}

	if (!empty ($wp_biographia_settings['wp_biographia_content_bio'])) {
		$wp_biographia_content .= "<p>"  .$wp_biographia_author['bio'] . "</p>";
	}
	
	$wp_biographia_links = array ();
	$wp_biographia_link_item = "";

	if (!empty ($wp_biographia_settings['wp_biographia_content_email']) &&
			($wp_biographia_settings['wp_biographia_content_email'] == 'on')) {
		if (!empty ($wp_biographia_author['email'])) {
			$wp_biographia_link_item = '<a href="mailto:'
				. antispambot ($wp_biographia_author['email'])
				. '" title="Send ';

			if (!empty ($wp_biographia_settings['wp_biographia_content_name']) &&
				($wp_biographia_settings['wp_biographia_content_name'] != 'none')) {
				$wp_biographia_link_item .= $wp_biographia_formatted_name .  ' ';
			}

			$wp_biographia_link_item .= 'Mail">Mail</a>';
			$wp_biographia_links[] = $wp_biographia_link_item;
		}
	}
	
	if (!empty ($wp_biographia_settings['wp_biographia_content_web']) &&
			($wp_biographia_settings['wp_biographia_content_web'] == 'on')) {
		if (!empty ($wp_biographia_author['website'])) {
			$wp_biographia_link_item = '<a href="'
				. $wp_biographia_author['website']
				. '" title="';

			if (!empty ($wp_biographia_settings['wp_biographia_content_name']) &&
				($wp_biographia_settings['wp_biographia_content_name'] != 'none')) {
				$wp_biographia_link_item .= $wp_biographia_formatted_name .  ' ';
			}

			$wp_biographia_link_item .= 'On The Web">Web</a>';
			$wp_biographia_links[] = $wp_biographia_link_item;
		}
	}

	if (!empty ($wp_biographia_settings['wp_biographia_content_twitter']) &&
			($wp_biographia_settings['wp_biographia_content_twitter'] == 'on')) {
		if (!empty ($wp_biographia_author['twitter'])) {
			$wp_biographia_link_item = '<a href="'
				. $wp_biographia_author['twitter']
				. '" title="';

			if (!empty ($wp_biographia_settings['wp_biographia_content_name']) &&
				($wp_biographia_settings['wp_biographia_content_name'] != 'none')) {
				$wp_biographia_link_item .= $wp_biographia_formatted_name .  ' ';
			}

			$wp_biographia_link_item .= 'On Twitter">Twitter</a>';
			$wp_biographia_links[] = $wp_biographia_link_item;
		}
	}

	if (!empty ($wp_biographia_settings['wp_biographia_content_facebook']) &&
			($wp_biographia_settings['wp_biographia_content_facebook'] == 'on')) {
		if (!empty ($wp_biographia_author['facebook'])) {
			$wp_biographia_link_item = '<a href="'
				. $wp_biographia_author['facebook']
				. '" title="';

			if (!empty ($wp_biographia_settings['wp_biographia_content_name']) &&
				($wp_biographia_settings['wp_biographia_content_name'] != 'none')) {
				$wp_biographia_link_item .= $wp_biographia_formatted_name .  ' ';
			}

			$wp_biographia_link_item .= 'On Facebook">Facebook</a>';
			$wp_biographia_links[] = $wp_biographia_link_item;
		}
	}

	if (!empty ($wp_biographia_settings['wp_biographia_content_linkedin']) &&
			($wp_biographia_settings['wp_biographia_content_linkedin'] == 'on')) {
		if (!empty ($wp_biographia_author['linkedin'])) {
			$wp_biographia_link_item = '<a href="'
				. $wp_biographia_author['linkedin']
				. '" title="';

			if (!empty ($wp_biographia_settings['wp_biographia_content_name']) &&
				($wp_biographia_settings['wp_biographia_content_name'] != 'none')) {
				$wp_biographia_link_item .= $wp_biographia_formatted_name .  ' ';
			}

			$wp_biographia_link_item .= 'On LinkedIn">LinkedIn</a>';
			$wp_biographia_links[] = $wp_biographia_link_item;
		}
	}

	if (!empty ($wp_biographia_settings['wp_biographia_content_googleplus']) &&
			($wp_biographia_settings['wp_biographia_content_googleplus'] == 'on')) {
		if (!empty ($wp_biographia_author['googleplus'])) {
			$wp_biographia_link_item = '<a href="'
				. $wp_biographia_author['googleplus']
				. '" title="';

			if (!empty ($wp_biographia_settings['wp_biographia_content_name']) &&
				($wp_biographia_settings['wp_biographia_content_name'] != 'none')) {
				$wp_biographia_link_item .= $wp_biographia_formatted_name .  ' ';
			}

			$wp_biographia_link_item .= 'On Google+">Google+</a>';
			$wp_biographia_links[] = $wp_biographia_link_item;
		}
	}

	if (!empty ($wp_biographia_settings['wp_biographia_content_posts']) &&
			($wp_biographia_settings['wp_biographia_content_posts'] != 'none')) {
		$wp_biographia_link_item = '<a href="'
			. $wp_biographia_author['posts_url'] 
			. '"';

		if (!empty ($wp_biographia_settings['wp_biographia_content_name']) &&
			($wp_biographia_settings['wp_biographia_content_name'] != 'none')) {
			$wp_biographia_link_item .= ' title="More Posts By '
				. $wp_biographia_formatted_name
				. '"';
		}

		$wp_biographia_link_item .= '>';
		
		switch ($wp_biographia_settings['wp_biographia_content_posts']) {
			case 'basic':
				$wp_biographia_link_item .= 'More Posts';
				break;
			case 'extended':
				$wp_biographia_link_item .= 'More Posts ('
					. $wp_biographia_author['posts']
					. ')';
				break;
		}
		$wp_biographia_link_item .= '</a>';
		$wp_biographia_links[] = $wp_biographia_link_item;
	}

	if (!empty ($wp_biographia_links)) {
		$wp_biographia_content .= '<small>'
			. implode (" | ", $wp_biographia_links)
			. '</small>';
	}

	$wp_biographia_biography = "";
	
	if (!$for_feed) {
		$wp_biographia_biography = '<div class="wp-biographia-container-'
			. $wp_biographia_settings['wp_biographia_style_border']
			. '" style="background-color:'
			. $wp_biographia_settings['wp_biographia_style_bg']
			. ';">';

		if (!empty ($wp_biographia_settings['wp_biographia_content_image']) &&
				($wp_biographia_settings['wp_biographia_content_image'] == 'on')) {
					$wp_biographia_biography .= '<div class="wp-biographia-pic">'.$wp_biographia_author_pic.'</div>';
		}

		$wp_biographia_biography .= '<div class="wp-biographia-text">'
			. $wp_biographia_content
			. '</div></div>';
	}
	
	else {
		$wp_biographia_biography = '<p>';

		if (!empty ($wp_biographia_settings['wp_biographia_content_image']) &&
				($wp_biographia_settings['wp_biographia_content_image'] == 'on')) {
					$wp_biographia_biography .= '<div style="float:left; text-align:left;>'.$wp_biographia_author_pic.'</div>';
		}
		$wp_biographia_biography .= $wp_biographia_content.'</p>';		
	}
	
	return $wp_biographia_biography;
}

/*
 * Add in the formatted Biography Box for the defined page types, via the the_content filter hook
 */

function wp_biographia_insert($content) {
	global $post;
	$wp_biographia_settings = array ();
	$wp_biographia_settings = get_option ('wp_biographia_settings');
	$new_content = $content;
	$user_id = get_the_author_meta ('ID');

	// Add pattern to determine output at top/bottom
	// changed all $content .= wp_biographia_display (); to $content = sprintf( $pattern , $content , $bio_content );
	//defaults to top

	if ((isset ($wp_biographia_settings['wp_biographia_display_location'])) &&
	 		($wp_biographia_settings['wp_biographia_display_location'] == 'top'))
		$pattern = apply_filters ('wp_biographia_pattern', '%2$s %1$s'); 
	else
		$pattern = apply_filters ('wp_biographia_pattern', '%1$s %2$s');
	
	// allow short circuit
	if (($pattern == '') || ($pattern == '%1s'))
		return $content;

//	$bio_content = wp_biographia_display ();

	if (is_front_page () &&
			isset ($wp_biographia_settings['wp_biographia_display_front']) &&
			$wp_biographia_settings['wp_biographia_display_front']) {
		$bio_content = wp_biographia_display ();
		$new_content = sprintf ($pattern, $content, $bio_content);
		//$content .= wp_biographia_display ();
	}
	
	elseif (is_archive () &&
			isset ($wp_biographia_settings['wp_biographia_display_archives']) &&
			$wp_biographia_settings['wp_biographia_display_archives']) {
		$bio_content = wp_biographia_display ();
		$new_content = sprintf ($pattern, $content, $bio_content);
		//$content .= wp_biographia_display ();
	}
	
	elseif (is_page () &&
			isset($wp_biographia_settings['wp_biographia_display_pages']) &&
			$wp_biographia_settings['wp_biographia_display_pages'] &&
			get_user_meta ($user_id, 'wp_biographia_suppress_pages', true) !== 'on') {
		$bio_content = wp_biographia_display ();
		$new_content = 'test true' . $content;
		if (isset ($wp_biographia_settings['wp_biographia_page_exclusions'])) {
			$exclusions = explode(',',$wp_biographia_settings['wp_biographia_page_exclusions']);
			if (! in_array ($post->ID, $exclusions)) {
				$new_content = sprintf ($pattern, $content, $bio_content);
				//$content .= wp_biographia_display ();
			}
			else
				$new_content = $content;
		}
		else
			$new_content = sprintf ($pattern, $content, $bio_content );
			//$content .= wp_biographia_display ();
	}
	
	elseif (is_single ()) {
		if (get_user_meta ($user_id, 'wp_biographia_suppress_posts', true) !== 'on') {
			// Cycle through Custom Post Types
			$bio_content = wp_biographia_display ();
			$pts = get_post_types (array (), 'objects');
		
			foreach ($pts as $pt => $data) {
				if (($data->_builtin) && ($pt != 'post')) {
					continue;
				}
			
				//Adjust post to posts
				if ($pt == 'post')
					$pt_name = 'posts';
				else
					$pt_name = $pt;

				if ($post->post_type == $pt) {
					if (isset ($wp_biographia_settings['wp_biographia_display_'.$pt_name])) {
						// check exclusions
						if (isset ($wp_biographia_settings['wp_biographia_'.$pt.'_exclusions'])) {
							$exclusions = explode (',',
							$wp_biographia_settings['wp_biographia_'.$pt.'_exclusions']);
						
							if (! in_array ($post->ID , $exclusions)) {
								$new_content = sprintf ($pattern, $content, $bio_content);
								break;
								//$content .= wp_biographia_display ();
							}
							else
								$new_content = $content;
						}
						else
							$new_content = sprintf ($pattern, $content, $bio_content);
							//$content .= wp_biographia_display ();
					}
				}
			}
		}
	}

	elseif (is_feed () &&
			isset($wp_biographia_settings['wp_biographia_display_feed']) &&
			$wp_biographia_settings['wp_biographia_display_feed']) {
		$bio_content = wp_biographia_display ();
		$new_content = sprintf ($pattern, $content, $bio_content);
		//$content .= wp_biographia_display (true);
	}
	else
		$new_content = $content;
	
	return $new_content;
}

/*
 * Add/enqueue the Biography Box CSS for the generated page, via the wp_print_styles action hook
 */

function wp_biographia_style() {
	wp_enqueue_style ('wp-biographia-bio', WPBIOGRAPHIAURL_URL . 'css/wp-biographia.css');	
}

/*
 * Sanitize/filter the author's profile contact info, via the user_contactmethods filter hook
 */

function wp_biographia_filter_contact($contactmethods) {

	unset ($contactmethods['yim']);
	unset ($contactmethods['aim']);
	unset ($contactmethods['jabber']);
	
	$contactmethods['twitter'] = 'Twitter';
	$contactmethods['facebook'] = 'Facebook';
	$contactmethods['linkedin'] = 'LinkedIn';
	$contactmethods['googleplus'] = 'Google+';
	$contactmethods['yim'] = 'Yahoo IM';
	$contactmethods['aim'] = 'AIM';
	$contactmethods['msn'] = 'Windows Live Messenger';
	$contactmethods['jabber'] = 'Jabber / Google Talk';

	return $contactmethods;
}

/*
 * Define and set up the default settings and options for formatting the Biography Box
 */

function wp_biographia_add_defaults() {
	$wp_biographia_settings = NULL;

	$wp_biographia_settings = get_option ('wp_biographia_settings');
    if(!is_array ($wp_biographia_settings)) {
		$wp_biographia_settings = array (
			"wp_biographia_installed" => "on",
			"wp_biographia_version" => "20",
			"wp_biographia_style_bg" => "#FFEAA8",
			"wp_biographia_style_border" => "top",
			"wp_biographia_display_front" => "on",
			"wp_biographia_display_archives" => "on",
			"wp_biographia_display_posts" => "on",
			"wp_biographia_display_pages" => "on",
			"wp_biographia_display_feed" => "",
			"wp_biographia_content_prefix" => "About",
			"wp_biographia_content_name" => "first-last-name",
			"wp_biographia_content_image" => "on",
			"wp_biographia_content_bio" => "on",
			"wp_biographia_content_email" => "on",
			"wp_biographia_content_web" => "on",
			"wp_biographia_content_twitter" => "on",
			"wp_biographia_content_facebook" => "on",
			"wp_biographia_content_linkedin" => "on",
			"wp_biographia_content_googleplus" => "on",
			"wp_biographia_content_posts" => "extended"
/*
 *			"wp_biographia_beta_enabled" => ""
 */
		);
		update_option ('wp_biographia_settings', $wp_biographia_settings);
	}

	if (!$wp_biographia_settings['wp_biographia_display_feed']) {
		$wp_biographia_settings['wp_biographia_display_feed'] = "";
		update_option('wp_biographia_settings', $wp_biographia_settings);
	}
}

/*
 * Display the biography box when the [wp_biographia] short-code is detected
 */

function wp_biographia_shortcode($atts) {
	extract (shortcode_atts (array(
	      //coming soon
     ), $atts));
	 wp_biographia_display ();
}

/*
 * Define plugin activation hook
 */

register_activation_hook(__FILE__, 'wp_biographia_add_defaults');

/*
 * Define plugin specific core action hooks
 *
 * 1) Add in our admin panel
 * 2) Add in our scripts for the admin panel
 * 3) Add in our CSS for the admin panel
 * 4) Add in our CSS for the generated page
 * 5) Add in checking for updating the configuration options after a plugin upgrade
 * 6/7) Add in user profile extensions for excluding the Biography Box
 * 8/9) Save user profile extensions for exclusing the Biography Box
 */

add_action ('admin_menu','wp_biographia_add_options_subpanel');
add_action ('admin_print_scripts', 'wp_biographia_add_admin_scripts');
add_action ('admin_print_styles', 'wp_biographia_add_admin_styles');
add_action ('wp_print_styles', 'wp_biographia_style' );
add_action ('admin_init', 'wp_biographia_admin_init');

add_action ('show_user_profile', 'wp_biographia_add_profile_extensions');
add_action ('edit_user_profile', 'wp_biographia_add_profile_extensions');
add_action ('personal_options_update', 'wp_biographia_save_profile_extensions');
add_action ('edit_user_profile_update', 'wp_biographia_save_profile_extensions');

/*
 * Define plugin specific core filter hooks
 *
 * 1) Sanitize/filter the author's profile contact info
 * 2) Add in post processing to add the Biography Box to the page content
 * 3) Add in plugin settings link
 */

add_filter ('user_contactmethods', 'wp_biographia_filter_contact');
add_filter ('the_content', 'wp_biographia_insert');
add_filter ('plugin_action_links_' . plugin_basename (__FILE__), 'wp_biographia_settings_link');

/*
 * Define plugin specific short-code hooks
 *
 * 1) [wp_biographia] short-code
 */

add_shortcode ('wp_biographia', 'wp_biographia_shortcode');


?>