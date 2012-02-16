<?php
/*
Plugin Name: WP Biographia
Plugin URI: http://www.vicchi.org/codeage/wp-biographia/
Description: Add and display a customizable author biography for individual posts, in RSS feeds, on pages, in archives and on each entry on the landing page and much more.
Version: 2.4
Author: Gary Gale & Travis Smith
Author URI: http://www.garygale.com/
License: GPL2
Text Domain: wp-biographia
*/

define ('WPBIOGRAPHIA_VERSION', '24');
define ('WPBIOGRAPHIAURL_URL', plugin_dir_url(__FILE__));
define ('WPBIOGRAPHIAURL_PATH', plugin_dir_path(__FILE__));

require_once (WPBIOGRAPHIAURL_PATH . "includes/wp-biographia-admin.php");

function wp_biographia_is_last_page() {
	global$page;
	global $numpages;
	global $multipage;
	
	if ($multipage) {
		return ($page == $numpages) ? true : false;
	}
	
	return true;
}

/*
 * Produce and format the Biography Box according to the currently defined options
 */

function wp_biographia_display($for_feed=false, $author_id=NULL, $override=NULL) {
	if (!isset ($author_id)) {
		$author_id = get_the_author_meta ('ID');
	}
	
  	$wp_biographia_settings = array ();
	$wp_biographia_settings = get_option ('wp_biographia_settings');
	
	$wp_biographia_content = "";
	$wp_biographia_author_pic =  "";
	$wp_biographia_formatted_name = "";
	
	$wp_biographia_author = array ();

	$wp_biographia_author['account-name'] = get_the_author_meta ('user_login', $author_id);
	$wp_biographia_author['first-last-name'] = get_the_author_meta ('first_name', $author_id)
		. ' '
		. get_the_author_meta ('last_name', $author_id);
	$wp_biographia_author['nickname'] = get_the_author_meta ('nickname', $author_id);
	$wp_biographia_author['display-name'] = get_the_author_meta ('display_name', $author_id);
	
	$wp_biographia_author['bio'] = get_the_author_meta ('description', $author_id);
	$wp_biographia_author['web'] = get_the_author_meta ('url', $author_id);
	$wp_biographia_author['email'] = get_the_author_meta ('user_email', $author_id);
	$wp_biographia_author['twitter'] = get_the_author_meta ('twitter', $author_id);
	$wp_biographia_author['facebook'] = get_the_author_meta ('facebook', $author_id);
	$wp_biographia_author['linkedin'] = get_the_author_meta ('linkedin', $author_id);
	$wp_biographia_author['googleplus'] = get_the_author_meta ('googleplus', $author_id);
	$wp_biographia_author['delicious'] = get_the_author_meta ('delicious', $author_id);
	$wp_biographia_author['flickr'] = get_the_author_meta ('flickr', $author_id);
	$wp_biographia_author['picasa'] = get_the_author_meta ('picasa', $author_id);
	$wp_biographia_author['vimeo'] = get_the_author_meta ('vimeo', $author_id);
	$wp_biographia_author['youtube'] = get_the_author_meta ('youtube', $author_id);
	$wp_biographia_author['reddit'] = get_the_author_meta ('reddit', $author_id);
	$wp_biographia_author['posts'] = (int)count_user_posts ($author_id);
  	$wp_biographia_author['posts_url'] = get_author_posts_url ($author_id);

  	// Add Image Size Output
	$wp_biographia_author_pic_size =
		(isset($wp_biographia_settings['wp_biographia_content_image_size'])) ?
	 		$wp_biographia_settings['wp_biographia_content_image_size'] : '100';
	$wp_biographia_author_pic = get_avatar ($wp_biographia_author['email'],
	 										$wp_biographia_author_pic_size);

	if (!empty ($wp_biographia_settings['wp_biographia_content_prefix']) ||
		!empty ($wp_biographia_settings['wp_biographia_content_name'])) {
		$wp_biographia_content .= '<h3>';

		$name_prefix = "";
		if ((!empty ($override)) && (!empty($override['prefix']))) {
			$name_prefix = $override['prefix'];
		}

		elseif (!empty ($wp_biographia_settings['wp_biographia_content_prefix'])) {
			$name_prefix = $wp_biographia_settings['wp_biographia_content_prefix'];
		}
		
		if (!empty ($name_prefix)) {
			$wp_biographia_content .= $name_prefix . ' ';
		}

		$display_name = "";
		if ((!empty ($override)) && (!empty($override['name']))) {
			$display_name = $override['name'];
		}

		elseif (!empty ($wp_biographia_settings['wp_biographia_content_name'])) {
			$display_name = $wp_biographia_settings['wp_biographia_content_name'];
		}

		if (!empty ($display_name) && $display_name != 'none') {
			switch ($display_name) {
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
			
			if (!empty ($wp_biographia_settings['wp_biographia_content_authorpage']) &&
					($wp_biographia_settings['wp_biographia_content_authorpage'] == 'on')) {
				$wp_biographia_content .= '<a href="'
					. $wp_biographia_author['posts_url']
					. '" title="'
					. $wp_biographia_formatted_name
					. '">'
					. $wp_biographia_formatted_name
					. '</a>';
			}
			
			else {
				$wp_biographia_content .= $wp_biographia_formatted_name;	
			}
		}
		$wp_biographia_content .= '</h3>';
	}

	if (!empty ($wp_biographia_settings['wp_biographia_content_bio'])) {
		$wp_biographia_content .= "<p>" . $wp_biographia_author['bio'] . "</p>";
	}
	
	$wp_biographia_links = array ();
	$wp_biographia_link_item = "";


	$display_icons = (!empty ($wp_biographia_settings['wp_biographia_content_icons']) &&
		($wp_biographia_settings['wp_biographia_content_icons'] == 'on'));

	$icon_dir_url = WPBIOGRAPHIAURL_URL . 'images/';

	if ($display_icons) {
		if (!empty ($wp_biographia_settings['wp_biographia_content_alt_icons']) &&
				$wp_biographia_settings['wp_biographia_content_alt_icons'] == 'on' &&
				!empty ($wp_biographia_settings['wp_biographia_content_icon_url'])) {
			$icon_dir_url = $wp_biographia_settings['wp_biographia_content_icon_url'];
		}
	}

	$item_stub = '<li><a href="%s" title="%s" class="%s">%s</a></li>';
	$title_name_stub = __('%1$s On %2$s', 'wp-biographia');
	$title_noname_stub = __('On %s', 'wp-biographia');
	$list_class = "wp-biographia-list-text";
	$item_class = "wp-biographia-item-text";
	$link_class = "wp-biographia-link-text";
	$item_glue = " | ";

	if ($display_icons) {
		$item_stub = '<li><a href="%s" title="%s" class="%s"><img src="%s" class="%s" /></a></li>';
		$list_class = "wp-biographia-list-icon";
		$item_class = "wp-biographia-item-icon";
		$link_class = "wp-biographia-link-icon";
		$item_glue = "";
	}

	$wp_biographia_links = array ();
	$wp_biographia_link_item = "";

	$link_items = array (
		"web" => array (
			"link_title" => __('The Web', 'wp-biographia'),
			"link_text" => __('Web', 'wp-biographia'),
			"link_icon" => $icon_dir_url . 'web.png'
			),
		"twitter" => array (
			"link_title" => __('Twitter', 'wp-biographia'),
			"link_text" => __('Twitter', 'wp-biographia'),
			"link_icon" => $icon_dir_url . 'twitter.png'
			),
		"facebook" => array (
			"link_title" => __('Facebook', 'wp-biographia'),
			"link_text" => __('Facebook', 'wp-biographia'),
			"link_icon" => $icon_dir_url . 'facebook.png'
			),
		"linkedin" => array (
			"link_title" => __('LinkedIn', 'wp-biographia'),
			"link_text" => __('LinkedIn', 'wp-biographia'),
			"link_icon" => $icon_dir_url . 'linkedin.png'
			),
		"googleplus" => array (
			"link_title" => __('Google+', 'wp-biographia'),
			"link_text" => __('Google+', 'wp-biographia'),
			"link_icon" => $icon_dir_url . 'google.png'
			),
		"delicious" => array (
			"link_title" => __('Delicous', 'wp-biographia'),
			"link_text" => __('Delicous', 'wp-biographia'),
			"link_icon" => $icon_dir_url . 'delicious.png'
			),
		"flickr" => array (
			"link_title" => __('Flickr', 'wp-biographia'),
			"link_text" => __('Flickr', 'wp-biographia'),
			"link_icon" => $icon_dir_url . 'flickr.png'
			),
		"picasa" => array (
			"link_title" => __('Picasa', 'wp-biographia'),
			"link_text" => __('Picasa', 'wp-biographia'),
			"link_icon" => $icon_dir_url . 'picasa.png'
			),
		"vimeo" => array (
			"link_title" => __('Vimeo', 'wp-biographia'),
			"link_text" => __('Vimeo', 'wp-biographia'),
			"link_icon" => $icon_dir_url . 'vimeo.png'
			),
		"youtube" => array (
			"link_title" => __('YouTube', 'wp-biographia'),
			"link_text" => __('YouTube', 'wp-biographia'),
			"link_icon" => $icon_dir_url . 'youtube.png'
			),
		"reddit" => array (
			"link_title" => __('Reddit', 'wp-biographia'),
			"link_text" => __('Reddit', 'wp-biographia'),
			"link_icon" => $icon_dir_url . 'reddit.png'
			),
	);

	// Deal with the email link first as a special case ...

	if (!empty ($wp_biographia_settings['wp_biographia_content_email']) &&
			($wp_biographia_settings['wp_biographia_content_email'] == 'on')) {
		if (!empty ($wp_biographia_author['email'])) {
			if (!empty ($wp_biographia_formatted_name)) {
				$link_title = sprintf (__('Send %s Mail', 'wp-biographia'), $wp_biographia_formatted_name);
			}
			else {
				$link_title = __('Send Mail', 'wp-biographia');
			}

			$link_text = __('Mail', 'wp-biographia');
			
			if ($display_icons) {
				$wp_biographia_link_item = sprintf ($item_stub,
					'mailto:' . antispambot ($wp_biographia_author['email']),
					$link_title,
					$link_class,
					$icon_dir_url . 'mail.png',
					$item_class
					);
			}
			
			else {
				$wp_biographia_link_item = sprintf ($item_stub,
					'mailto:' . antispambot ($wp_biographia_author['email']),
					$link_title,
					$link_class,
					$link_text
					);
			}	

			$wp_biographia_links[] = $wp_biographia_link_item;
		}
	}

	// Now deal with the other links that follow the same format and can be "templatised" ...

	foreach ($link_items as $link_key => $link_attrs) {
		$option_name = 'wp_biographia_content_' . $link_key;
		if (!empty ($wp_biographia_settings[$option_name]) &&
				!empty($wp_biographia_author[$link_key]) &&
				($wp_biographia_settings[$option_name] == 'on')) {
			if (!empty ($wp_biographia_formatted_name)) {
				$link_title = sprintf ($title_name_stub, $wp_biographia_formatted_name, $link_attrs['link_title']);
			}
			else {
				$link_title = sprintf ($title_noname_stub, $link_attrs['link_title']);
			}

			if ($display_icons) {
				$wp_biographia_link_item = sprintf ($item_stub,
					$wp_biographia_author[$link_key],
					$link_title,
					$link_class,
					$link_attrs['link_icon'],
					$item_class
					);
			}
		
			else {
				$wp_biographia_link_item = sprintf ($item_stub,
					$wp_biographia_author[$link_key],
					$link_title,
					$link_class,
					$link_attrs['link_text']
					);
			}	
		
			$wp_biographia_links[] = $wp_biographia_link_item;
		}
	}

	// Finally, deal with the "More Posts" link

	if (!empty ($wp_biographia_settings['wp_biographia_content_posts']) &&
			($wp_biographia_settings['wp_biographia_content_posts'] != 'none') &&
			($wp_biographia_author['posts'] > 0)) {
		if (!empty ($wp_biographia_formatted_name)) {
			$link_title = sprintf (__('More Posts By %s', 'wp-biographia'), $wp_biographia_formatted_name);
		}
		else {
			$link_title = __('More Posts', 'wp-biographia');
		}

		switch ($wp_biographia_settings['wp_biographia_content_posts']) {
			case 'basic':
				$link_text = __('More Posts', 'wp-biographia');
				break;
			case 'extended':
				$link_text = __('More Posts', 'wp-biographia') . ' ('
					. $wp_biographia_author['posts']
					. ')';
				break;
		}

		if ($display_icons) {
			$wp_biographia_link_item = sprintf ($item_stub,
				$wp_biographia_author['posts_url'],
				$link_title,
				$link_class,
				$icon_dir_url . 'wordpress.png',
				$item_class
				);
		}
		
		else {
			$wp_biographia_link_item = sprintf ($item_stub,
				$wp_biographia_author['posts_utl'],
				$link_title,
				$link_class,
				$link_text
				);
		}	

		$wp_biographia_links[] = $wp_biographia_link_item;
		
	}
	
	if (!empty ($wp_biographia_links)) {
		$wp_biographia_content .= '<div class="wp-biographia-links">'
			. '<small><ul class="' . $list_class . '">'
			. implode ($item_glue, $wp_biographia_links)
			. '</ul></small>'
			. '</div>';
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
			$wp_biographia_biography .= '<div class="wp-biographia-pic" style="height:'
				. $wp_biographia_author_pic_size
				. 'px; width:'
				. $wp_biographia_author_pic_size
				. 'px;">'
				. $wp_biographia_author_pic
				. '</div>';
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

function wp_biographia_insert($content, $is_shortcode=false, $author_id=NULL, $override=NULL) {
	global $post;

	$wp_biographia_settings = array ();
	$wp_biographia_settings = get_option ('wp_biographia_settings');
	$new_content = $content;

	if (!isset ($author_id)) {
		$author_id = get_the_author_meta ('ID');
	}

	if (!$is_shortcode) {
		if ((isset ($wp_biographia_settings['wp_biographia_display_location'])) &&
	 			($wp_biographia_settings['wp_biographia_display_location'] == 'top')) {
			$pattern = apply_filters ('wp_biographia_pattern', '%2$s %1$s');
		}

		else {
			$pattern = apply_filters ('wp_biographia_pattern', '%1$s %2$s');
		}
	
		// allow short circuit
		if (($pattern == '') || ($pattern == '%1s')) {
			return $content;
		}
	}

	if (is_front_page ()) {
		$new_content = wp_biographia_insert_frontpage ($content,
													   $pattern,
													   $wp_biographia_settings,
													   $is_shortcode,
													   $author_id,
													   $override);
	}
	
	elseif (is_archive ()) {
		$new_content = wp_biographia_insert_archive ($content,
												     $pattern,
												     $wp_biographia_settings,
												     $is_shortcode,
												     $author_id,
													 $override);
	}
	
	elseif (is_page ()) {
		$new_content = wp_biographia_insert_page ($content,
												  $pattern,
											      $wp_biographia_settings,
											      $is_shortcode,
											      $author_id,
												  $override);
	}
	
	elseif (is_single ()) {
		$new_content = wp_biographia_insert_single ($content,
												    $pattern,
											        $wp_biographia_settings,
											        $is_shortcode,
													$author_id,
													$override);
	}
	
	elseif (is_feed ()) {
		$new_content = wp_biographia_insert_feed ($content,
												  $pattern,
											      $wp_biographia_settings,
											      $is_shortcode);
	}

	return $new_content;
}

function wp_biographia_insert_frontpage ($content, $pattern, $options, $is_shortcode, $author_id, $override=NULL) {
	global $post;
	$display_bio = false;
	$for_feed = false;
	$new_content = $content;

	if (!isset ($author_id)) {
		$author_id = get_the_author_meta ('ID');
	}
	
	if (get_user_meta ($author_id, 'wp_biographia_suppress_posts', true) !== 'on') {
		if (isset ($options['wp_biographia_display_front']) &&
				$options['wp_biographia_display_front']) {
			$display_bio = true;
		}
	
		else {
			$display_bio = $is_shortcode;
		}
	
		if ($display_bio) {
			$bio_content = wp_biographia_display ($for_feed, $author_id, $override);
			$post_types = get_post_types (array (), 'objects');

			foreach ($post_types as $post_type => $post_data) {
				if (($post_data->_builtin) && ($post_type != 'post')) {
					continue;
				}
				
				if ($post_type == 'post') {
					$post_type_name = 'posts';
				}
				else {
					$post_type_name = $post_type;
				}

				if ($post->post_type == $post_type) {
					$option = 'wp_biographia_global_' . $post_type . '_exclusions';
					if (isset ($options[$option])) {
						$exclusions = explode (',',	$options[$option]);
						if (!in_array ($post->ID, $exclusions)) {
							$new_content = sprintf ($pattern, $content, $bio_content);
							break;
						}
					
						else {
							$new_content = $content;
							break;
						}
					}
				
					else {
						$new_content = sprintf ($pattern, $content, $bio_content);
					}
				}
			}	// end-foreach ()
		}
	}

	return $new_content;
}

function wp_biographia_insert_archive ($content, $pattern, $options, $is_shortcode, $author_id, $override=NULL) {
	global $post;
	$display_bio = false;
	$for_feed = false;
	$new_content = $content;

	if (!isset ($author_id)) {
		$author_id = get_the_author_meta ('ID');
	}
	
	if (get_user_meta ($author_id, 'wp_biographia_suppress_posts', true) !== 'on') {
		if (isset ($options['wp_biographia_display_archives']) &&
				$options['wp_biographia_display_archives']) {
			$display_bio = true;
		}
	
		else {
			$display_bio = $is_shortcode;
		}
	
		if ($display_bio) {
			$bio_content = wp_biographia_display ($for_feed, $author_id, $override);
			$post_types = get_post_types (array (), 'objects');

			foreach ($post_types as $post_type => $post_data) {
				if (($post_data->_builtin) && ($post_type != 'post')) {
					continue;
				}
				
				if ($post_type == 'post') {
					$post_type_name = 'posts';
				}
				else {
					$post_type_name = $post_type;
				}
				
				if ($post->post_type == $post_type) {
					$option = 'wp_biographia_global_' . $post_type . '_exclusions';
					if (isset ($options[$option])) {
						$exclusions = explode (',', $options[$option]);
						if (!in_array ($post->ID, $exclusions)) {
							$new_content = sprintf ($pattern, $content, $bio_content);
							break;
						}
					
						else {
							$new_content = $content;
						}
					}
				
					else {
						$new_content = sprintf ($pattern, $content, $bio_content);
					}
				}
			}	// end-foreach ()
		}
	}
	
	return $new_content;
}

function wp_biographia_insert_page ($content, $pattern, $options, $is_shortcode, $author_id, $override=NULL) {
	global $post;
	$display_bio = false;
	$for_feed = false;
	$new_content = $content;

	if (!isset ($author_id)) {
		$author_id = get_the_author_meta ('ID');
	}
	
	if (isset ($options['wp_biographia_display_pages']) &&
			$options['wp_biographia_display_pages'] &&
			get_user_meta ($author_id, 'wp_biographia_suppress_pages', true) !== 'on') {
		$display_bio = true;
	}
	
	elseif ($is_shortcode &&
			get_user_meta ($author_id, 'wp_biographia_suppress_pages', true) !== 'on') {
		$display_bio = true;
	}
	
	if ($display_bio) {
		if (isset ($options['wp_biographia_page_exclusions'])) {
			$exclusions = explode (',', $options['wp_biographia_page_exclusions']);
			$display_bio = !in_array ($post->ID, $exclusions);
		}
	}

	if ($display_bio) {
		$bio_content = wp_biographia_display ($for_feed, $author_id, $override);
		$new_content = sprintf ($pattern, $content, $bio_content);
	}

	return $new_content;
}

function wp_biographia_insert_single ($content, $pattern, $options, $is_shortcode, $author_id, $override=NULL) {
	global $post;
	$display_bio = false;
	$for_feed = false;
	$new_content = $content;

	if (!isset ($author_id)) {
		$author_id = get_the_author_meta ('ID');
	}

	if (get_user_meta ($author_id, 'wp_biographia_suppress_posts', true) !== 'on') {
		// Cycle through Custom Post Types
		$bio_content = wp_biographia_display ($for_feed, $author_id, $override);
		$post_types = get_post_types (array (), 'objects');
	
		foreach ($post_types as $post_type => $post_data) {
			if (($post_data->_builtin) && ($post_type != 'post')) {
				continue;
			}
		
			//Adjust post to posts
			if ($post_type == 'post') {
				$post_type_name = 'posts';
			}

			else {
				$post_type_name = $post_type;
			}

			if ($post->post_type == $post_type) {
				if (((isset ($options['wp_biographia_display_' . $post_type_name]) &&
						$options['wp_biographia_display_' . $post_type_name])) || 
						$is_shortcode) {
					// check exclusions
					$post_option = 'wp_biographia_' . $post_type . '_exclusions';
					$global_option = 'wp_biographia_global_' . $post_type . '_exclusions';
					
					if (isset ($options[$post_option]) || isset ($options[$global_option])) {
						$post_exclusions = array ();
						$global_exclusions = array ();
						
						if (isset ($options[$post_option])) {
							$post_exclusions = explode (',', $options[$post_option]);
						}
						if (isset ($options[$global_option])) {
							$global_exclusions = explode (',', $options[$global_option]);
						}
						
						if (!in_array ($post->ID, $post_exclusions) &&
								!in_array ($post->ID, $global_exclusions)) {
							if (wp_biographia_is_last_page ()) {
								$new_content = sprintf ($pattern, $content, $bio_content);
								break;
							}
						}
						
						else {
							$new_content = $content;
						}
					}

					else {
						if (wp_biographia_is_last_page ()) {
							$new_content = sprintf ($pattern, $content, $bio_content);
						}
					}
				}
			}
		}
	}

	return $new_content;
}

function wp_biographia_insert_feed ($content, $pattern, $options, $is_shortcode) {
	$display_bio = false;
	$new_content = $content;
	
	if (isset ($options['wp_biographia_display_feed']) &&
			$options['wp_biographia_display_feed']) {
		$display_bio = true;
	}
	
	else {
		$display_bio = $is_shortcode;
	}
	
	if ($display_bio) {
		$is_feed = true;
		$bio_content = wp_biographia_display ($is_feed);
		$new_content = sprintf ($pattern, $content, $bio_content);
	}
	
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
	
	$contactmethods['twitter'] = __('Twitter', 'wp-biographia');
	$contactmethods['facebook'] = __('Facebook', 'wp-biographia');
	$contactmethods['linkedin'] = __('LinkedIn', 'wp-biographia');
	$contactmethods['googleplus'] = __('Google+', 'wp-biographia');
	$contactmethods['delicious'] = __('Delicious', 'wp-biographia');
	$contactmethods['flickr'] = __('Flickr', 'wp-biographia');
	$contactmethods['picasa'] = __('Picasa', 'wp-biographia');
	$contactmethods['vimeo'] = __('Vimeo', 'wp-biographia');
	$contactmethods['youtube'] = __('YouTube', 'wp-biographia');
	$contactmethods['reddit'] = __('Reddit', 'wp-biographia');
	$contactmethods['yim'] = __('Yahoo IM', 'wp-biographia');
	$contactmethods['aim'] = __('AIM', 'wp-biographia');
	$contactmethods['msn'] = __('Windows Live Messenger', 'wp-biographia');
	$contactmethods['jabber'] = __('Jabber / Google Talk', 'wp-biographia');

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
			"wp_biographia_version" => WPBIOGRAPHIA_VERSION,
			"wp_biographia_style_bg" => "#FFEAA8",
			"wp_biographia_style_border" => "top",
			"wp_biographia_display_front" => "on",
			"wp_biographia_display_archives" => "on",
			"wp_biographia_display_posts" => "on",
			"wp_biographia_display_pages" => "on",
			"wp_biographia_display_feed" => "",
			"wp_biographia_content_prefix" => __('About', 'wp-biographia'),
			"wp_biographia_content_name" => "first-last-name",
			"wp_biographia_content_authorpage" => "on",
			"wp_biographia_content_image" => "on",
			"wp_biographia_content_bio" => "on",
			"wp_biographia_content_icons" => "",
			"wp_biographia_content_alt_icons" => "",
			"wp_biographia_content_icon_url" => "",
			"wp_biographia_content_email" => "on",
			"wp_biographia_content_web" => "on",
			"wp_biographia_content_twitter" => "on",
			"wp_biographia_content_facebook" => "on",
			"wp_biographia_content_linkedin" => "on",
			"wp_biographia_content_googleplus" => "on",
			"wp_biographia_content_delicious" => "",
			"wp_biographia_content_flickr" => "",
			"wp_biographia_content_picasa" => "",
			"wp_biograpia_content_vimeo" => "",
			"wp_biographia_content_youtube" => "",
			"wp_biographia_content_reddit" => "",
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
	global $wpdb;
	$content = "";
	$is_feed = false;
	
	extract (shortcode_atts (array (
		'mode' => 'raw',
		'author' => '',
		'prefix' => '',
		'name' => ''
     ), $atts));

	$override = array ();
	if (!empty ($prefix)) {
		$override['prefix'] = $prefix;
	}
	if (!empty ($name)) {
		switch ($name) {
			case 'account-name':
			case 'first-last-name':
			case 'nickname':
			case 'display-name':
			case 'none':
				$override['name'] = $name;
				break;
			default:
				break;
		}
	}

	if (!empty ($author)) {
		if ($author === "*") {
			$contributors = $wpdb->get_results ("SELECT ID, user_login from $wpdb->users ORDER BY user_login");
			$content = '<div class="wp-biographia-contributors">';
			foreach ($contributors as $user_obj) {
				if ($mode == 'raw') {
					$content .= wp_biographia_display ($is_feed, $user_obj->ID, $override);
				}

				elseif ($mode == 'configured') {
					$placeholder_content = "";
					$is_shortcode = true;

					$content .= wp_biographia_insert ($placeholder_content,
												 	$is_shortcode,
												 	$user_obj->ID,
													$override);
				}
			}
			$content .= '</div>';
		}
		
		else {
			$user_obj = get_user_by ('login', $author);
			if ($user_obj) {
				if ($mode == 'raw') {
					$content = wp_biographia_display ($is_feed, $user_obj->ID, $override);
				}

				elseif ($mode == 'configured') {
					$placeholder_content = "";
					$is_shortcode = true;

					$content = wp_biographia_insert ($placeholder_content,
												 	$is_shortcode,
												 	$user_obj->ID,
													$override);
				}
			}
		}
	}
	
	else {	
		if ($mode == 'raw') {
			$content = wp_biographia_display ($is_feed, NULL, $override);
		}
	
		elseif ($mode == 'configured') {
			$placeholder_content = "";
			$is_shortcode = true;
		
			$content = wp_biographia_insert ($placeholder_content,
											 $is_shortcode,
											 NULL,
											 $override);
		}
	}	

	return $content;
}

function wp_biographia_init() {
	$lang_dir = basename (dirname (__FILE__)) . DIRECTORY_SEPARATOR . 'lang';
	load_plugin_textdomain ('wp-biographia', false, $lang_dir);
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
 * 5) Add in checking for updating the configuration options after a plugin upgrade and load the i18n text domain
 * 6/7) Add in user profile extensions for excluding the Biography Box
 * 8/9) Save user profile extensions for exclusing the Biography Box
 */

add_action ('admin_menu','wp_biographia_add_options_subpanel');
add_action ('admin_print_scripts', 'wp_biographia_add_admin_scripts');
add_action ('admin_print_styles', 'wp_biographia_add_admin_styles');

// Move to using wp_enqueue_scripts rather than wp_print_styles; see
// http://wpdevel.wordpress.com/2011/12/12/use-wp_enqueue_scripts-not-wp_print_styles-to-enqueue-scripts-and-styles-for-the-frontend/
// add_action ('wp_print_styles', 'wp_biographia_style' );
add_action ('wp_enqueue_scripts', 'wp_biographia_style' );
add_action ('admin_init', 'wp_biographia_admin_init');
add_action ('init', 'wp_biographia_init');

add_action ('show_user_profile', 'wp_biographia_add_profile_extensions');
add_action ('edit_user_profile', 'wp_biographia_add_profile_extensions');
add_action ('personal_options_update', 'wp_biographia_save_profile_extensions');
add_action ('edit_user_profile_update', 'wp_biographia_save_profile_extensions');

/*
 * Define plugin specific core filter hooks
 *
 * 1) Sanitize/filter the author's profile contact info
 * 2) Add in post processing to add the Biography Box to the page content
 * 3) Add in post processing to add the Biography Box to archive pages using excerpts
 * 4) Add in plugin settings link
 */

add_filter ('user_contactmethods', 'wp_biographia_filter_contact');
add_filter ('the_content', 'wp_biographia_insert');
add_filter ('the_excerpt', 'wp_biographia_insert');
add_filter ('plugin_action_links_' . plugin_basename (__FILE__), 'wp_biographia_settings_link');

/*
 * Define plugin specific short-code hooks
 *
 * 1) [wp_biographia] short-code
 */

add_shortcode ('wp_biographia', 'wp_biographia_shortcode');


?>