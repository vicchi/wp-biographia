<?php
/*
Plugin Name: WP Biographia
Plugin URI: http://www.vicchi.org/codeage/wp-biographia/
Description: Add and display a customizable author biography for individual posts, in RSS feeds, on pages, in archives and on each entry on the landing page and much more.
Version: 3.0
Author: Gary Gale & Travis Smith
Author URI: http://www.garygale.com/
License: GPL2
Text Domain: wp-biographia
*/

define ('WPBIOGRAPHIA_PATH', plugin_dir_path (__FILE__));

require_once (WPBIOGRAPHIA_PATH . '/wp-plugin-base/wp-plugin-base.php');

class WP_Biographia extends WP_PluginBase { 

	static $instance;
	public $author_id;
	public $override;
	public $display_bio = false;
	public $for_feed = false;
	public $is_shortcode = false;
	public $icon_dir_url = '';
	
	const OPTIONS = 'wp_biographia_settings';
	const VERSION = '30';
	const PLUGIN_URL = '';
	const PLUGIN_PATH = '';
	
	/**
	 * Class constructor
	 */
	
	function __construct() { 
		self::$instance = $this;
		define ('PLUGIN_URL', plugin_dir_url (__FILE__));
		define ('PLUGIN_PATH', plugin_dir_path (__FILE__));
		$this->author_id = get_the_author_meta ('ID');
		$this->override = NULL;
		$this->hook ('plugins_loaded');
		$this->icon_dir_url = PLUGIN_URL . 'images/';
	}
	
	/**
	 * "plugins_loaded" action hook; called after all active plugins and pluggable functions
	 * are loaded.
	 *
	 * Adds front-end display actions, shortcode support and admin actions.
	 */
	
	function plugins_loaded () {
		register_activation_hook (__FILE__, array ($this, 'add_settings'));

		$this->hook ('wp_enqueue_scripts', 'style');
		$this->hook ('init');	
		$this->hook ('the_excerpt', 'insert');
		$this->hook ('the_content', 'insert');
		$this->hook ('user_contactmethods');
		
		add_shortcode ('wp_biographia', array ($this, 'shortcode'));

		if (is_admin ()) {
			$this->hook ('admin_menu');
			$this->hook ('admin_print_scripts');
			$this->hook ('admin_print_styles');
			$this->hook ('admin_init');
			$this->hook ('show_user_profile', 'admin_add_profile_extensions');
			$this->hook ('edit_user_profile', 'admin_add_profile_extensions');
			$this->hook ('personal_options_update', 'admin_save_profile_extensions');
			$this->hook ('edit_user_profile_update', 'admin_save_profile_extensions');
			$this->hook ('plugin_action_links_' . plugin_basename (__FILE__),
				'admin_settings_link');
		}
	}
	
	/**
	 * Queries the back-end database for WP Biographia settings and options.
	 *
	 * @param string $key Optional settings/options key name; if specified only the value
	 * for the key will be returned, if omitted all settings/options will be returned.
	 * @return mixed If $key is specified, a string containing the key's settings/option 
	 * value is returned. If $key is omitted, an array containing all settings/options will
	 * be returned.
	 */
	
	function get_option ($key='') {
		$options = get_option (self::OPTIONS);

		if(isset($options[$key])) {
			return $options[$key];
		}

		else {
			return $options;
		}
	}
	
	/**
	 * Adds/updates a settings/option key and value in the back-end database.
	 *
	 * @param string key Settings/option key to be created/updated.
	 * @param string value Value to be associated with the specified settings/option key
	 */
	
	function set_option ($key , $value) {
		$options = get_option (self::OPTIONS);
		$options[$key] = $value;
		update_option (self::OPTIONS , $options);
	}
	
	/**
	 * "init" action hook; called to initialise the plugin
	 */
	
	function init () {
		$lang_dir = basename (dirname (__FILE__)) . DIRECTORY_SEPARATOR . 'lang';
		load_plugin_textdomain ('wp-biographia', false, $lang_dir);
	}
	
	/**
	 * Wrapper function for the WP_User_Query class. Queries the back-end database and
	 * returns a list of users.
	 *
	 * @param string role Constrains the search to users of a specific role. Optional;
	 * if omitted all users will be returned.
	 * @param array args Array that specifies the fields to be returned. Optional; if
	 * omitted the ID and user_login fields will be returned.
	 * @return array Array containing the users that the search returned.
	 */
	 
	function get_users ($role='', $args=array (0 => 'ID', 1 => 'user_login')) {
		$wp_user_search = new WP_User_Query (array ('role' => $role, 'fields' => $args));
		$roles = $wp_user_search->get_results ();
		return $roles;
	}
	
	
	/**
	 * "user_contactmethods" filter hook; Sanitizes, filters and augments the author's
	 * profile contact information.
	 *
	 * @param array contactmethods Array containing the current set of contact methods.
	 * @return array Array containing the modified set of contact methods.
	 */
	
	function user_contactmethods ($contactmethods) {

		foreach ($this->defaults() as $key => $data) {
			if ($data['contactmethod']) {
				$contactmethods[$key] = $data['contactmethod'];
			}
		}	// end-foreach (...)

		return $contactmethods;
	}
	
	/**
	 * plugin activation / "activate_pluginname" action hook; called when the plugin is
	 * first activated.
	 *
	 * Defines and sets up the default settings and options for the plugin. The default set
	 * of options are configurable, at activation time, via the
	 * 'wp_biographia_default_settings' filter hook.
	 */
	
	function add_settings () {
		$settings = $this->get_option ();
		
		if (!is_array ($settings)) {
			$settings = apply_filters ('wp_biographia_default_settings' , 
				array (
					'wp_biographia_installed' => 'on',
					'wp_biographia_version' => self::VERSION,
					'wp_biographia_style_bg' => '#FFEAA8',
					'wp_biographia_style_border' => 'top',
					'wp_biographia_display_front' => 'on',
					'wp_biographia_display_archives' => 'on',
					'wp_biographia_display_posts' => 'on',
					'wp_biographia_display_pages' => 'on',
					'wp_biographia_display_feed' => '',
					'wp_biographia_content_prefix' => __('About', 'wp-biographia'),
					'wp_biographia_content_name' => 'first-last-name',
					'wp_biographia_content_authorpage' => 'on',
					'wp_biographia_content_image' => 'on',
					'wp_biographia_content_bio' => 'on',
					'wp_biographia_content_icons' => '',
					'wp_biographia_content_alt_icons' => '',
					'wp_biographia_content_icon_url' => '',
					'wp_biographia_content_email' => 'on',
					'wp_biographia_content_web' => 'on',
					'wp_biographia_content_twitter' => 'on',
					'wp_biographia_content_facebook' => 'on',
					'wp_biographia_content_linkedin' => 'on',
					'wp_biographia_content_googleplus' => 'on',
					'wp_biographia_content_delicious' => '',
					'wp_biographia_content_flickr' => '',
					'wp_biographia_content_picasa' => '',
					'wp_biograpia_content_vimeo' => '',
					'wp_biographia_content_youtube' => '',
					'wp_biographia_content_reddit' => '',
					'wp_biographia_content_posts' => 'extended',
					'wp_biographia_beta_enabled' => '',
				) 
			);
			update_option (self::OPTIONS, $settings);
		}

		if (!$settings['wp_biographia_display_feed'])
			$this->set_option ('wp_biographia_display_feed' , '');
	}
	
	/**
	 * Determines whether the current page is the last page
	 *
	 * @return boolean Returns true if the current page is the last page, otherwise returns
	 * false.
	 */
	
	function is_last_page () {
		global $page;
		global $numpages;
		global $multipage;
		
		if (is_single()) {
			return true;
		}

		elseif ($multipage) {
			return ($page == $numpages) ? true : false;
		}

		else {
			return true;
		}
	}
	
	/**
	 * Defines the default set of author's contact information. The default set of contact
	 * links are filterable via the 'wp_biographia_defaults' filter hook. Used by the
	 * display() and user_contactmethods() functions.
	 *
	 * @return array Array of default, filtered, contact information.
	 */

	function defaults () {
		$defaults = array (
			//option name => array (field => custom field , contactmethod => field name)
			'account-name' => array (
					'field' => 'user_login',
				),
			'first-last-name' => array (
					'field' => '',
				),
			'nickname' => array (
					'field' => 'nickname',
				),
			'display-name' => array (
					'field' => 'display_name',
				),
			'bio' => array (
					'field' => 'description',
				),
			'email' => array (
					'field' => 'email',
				),
			'website' => array (
					'field' => 'url',
				),
			'twitter' => array (
					'field' => 'twitter',
					'contactmethod' => __('Twitter', 'wp-biographia'),
				),
			'facebook' => array (
					'field' => 'facebook',
					'contactmethod' => __('Facebook', 'wp-biographia'),				
				),
			'linkedin' => array (
					'field' => 'linkedin',
					'contactmethod' => __('LinkedIn', 'wp-biographia'),				
				),
			'googleplus' => array (
					'field' => 'googleplus',
					'contactmethod' => __('Google+', 'wp-biographia'),					
				),
			'delicious' => array (
					'field' => 'delicious',
					'contactmethod' => __('Delicious', 'wp-biographia'),					
				),
			'flickr' => array (
					'field' => 'flickr',
					'contactmethod' => __('Flickr', 'wp-biographia'),					
				),
			'picasa' => array (
					'field' => 'picasa',
					'contactmethod' => __('Picasa', 'wp-biographia'),
				),
			'vimeo' => array (
					'field' => 'vimeo',
					'contactmethod' => __('Vimeo', 'wp-biographia'),
				),
			'youtube' => array (
					'field' => 'youtube',
					'contactmethod' => __('YouTube', 'wp-biographia'),
				),
			'reddit' => array (
					'field' => 'reddit',
					'contactmethod' => __('Reddit', 'wp-biographia'),
				),
			'reddit' => array (
					'field' => 'reddit',
					'contactmethod' => __('Reddit', 'wp-biographia'),
				),
			'yim' => array (
					'field' => 'yim',
					'contactmethod' => __('Yahoo IM', 'wp-biographia'),
				),
			'aim' => array (
					'field' => 'aim',
					'contactmethod' => __('AIM', 'wp-biographia'),
				),
			'msn' => array (
					'field' => 'msn',
					'contactmethod' => __('Windows Live Messenger', 'wp-biographia'),
				),
			'jabber' => array (
					'field' => 'jabber',
					'contactmethod' => __('Jabber / Google Talk', 'wp-biographia'),
				),
			
		);
		
		return  apply_filters ('wp_biographia_defaults' , $defaults);
	}
	
	/**
	 * Defines the default set of contact link items for the Biography Box. The default set
	 * of links are filterable via the 'wp_biographia_link_items filter hook.
	 *
	 * @return array Array of default, filtered, Biography Box link items.
	 */

	function link_items () {
		$link_items = array (
			"web" => array (
				"link_title" => __('The Web', 'wp-biographia'),
				"link_text" => __('Web', 'wp-biographia'),
				"link_icon" => $this->icon_dir_url . 'web.png'
				),
			"twitter" => array (
				"link_title" => __('Twitter', 'wp-biographia'),
				"link_text" => __('Twitter', 'wp-biographia'),
				"link_icon" => $this->icon_dir_url . 'twitter.png'
				),
			"facebook" => array (
				"link_title" => __('Facebook', 'wp-biographia'),
				"link_text" => __('Facebook', 'wp-biographia'),
				"link_icon" => $this->icon_dir_url . 'facebook.png'
				),
			"linkedin" => array (
				"link_title" => __('LinkedIn', 'wp-biographia'),
				"link_text" => __('LinkedIn', 'wp-biographia'),
				"link_icon" => $this->icon_dir_url . 'linkedin.png'
				),
			"googleplus" => array (
				"link_title" => __('Google+', 'wp-biographia'),
				"link_text" => __('Google+', 'wp-biographia'),
				"link_icon" => $this->icon_dir_url . 'google.png'
				),
			"delicious" => array (
				"link_title" => __('Delicous', 'wp-biographia'),
				"link_text" => __('Delicous', 'wp-biographia'),
				"link_icon" => $this->icon_dir_url . 'delicious.png'
				),
			"flickr" => array (
				"link_title" => __('Flickr', 'wp-biographia'),
				"link_text" => __('Flickr', 'wp-biographia'),
				"link_icon" => $this->icon_dir_url . 'flickr.png'
				),
			"picasa" => array (
				"link_title" => __('Picasa', 'wp-biographia'),
				"link_text" => __('Picasa', 'wp-biographia'),
				"link_icon" => $this->icon_dir_url . 'picasa.png'
				),
			"vimeo" => array (
				"link_title" => __('Vimeo', 'wp-biographia'),
				"link_text" => __('Vimeo', 'wp-biographia'),
				"link_icon" => $this->icon_dir_url . 'vimeo.png'
				),
			"youtube" => array (
				"link_title" => __('YouTube', 'wp-biographia'),
				"link_text" => __('YouTube', 'wp-biographia'),
				"link_icon" => $this->icon_dir_url . 'youtube.png'
				),
			"reddit" => array (
				"link_title" => __('Reddit', 'wp-biographia'),
				"link_text" => __('Reddit', 'wp-biographia'),
				"link_icon" => $this->icon_dir_url . 'reddit.png'
				),
		);
		
		return apply_filters ('wp_biographia_link_items', $link_items, $this->icon_dir_url);
	}

	/**
	 * "wp_enqueue_scripts" action hook; called to load the plugin's CSS for the
	 * Biography Box.
	 */

	function style () {
		wp_enqueue_style ('wp-biographia-bio', PLUGIN_URL . 'css/wp-biographia.css');	
	}
	
	/**
	 * "the_content" and "the_excerpt" action hook; adds the Biography Box to post or
	 * page content according to the current set of plugin settings/options. The
	 * Biography Box is filterable via the 'wp_biographia_pattern' and 'wp_biographia_pre'
	 * filters.
	 *
	 * @param string content String containing the post or page content or excerpt.
	 * @return string String containing the original post/page content/excerpt plus
	 * the Biography Box, providing the current set of settings/options permit this.
	 */

	function insert ($content) {
		$new_content = $content;
		
		if (!isset ($this->author_id)) {
			$this->author_id = get_the_author_meta ('ID');
		}
		
		if (!$this->is_shortcode) {
			$location = $this->get_option ('wp_biographia_display_location');
			if ((isset ($location)) && ($location == 'top')) {
				$pattern = apply_filters ('wp_biographia_pattern', '%2$s %1$s');
			}
			
			else {
				$pattern = apply_filters ('wp_biographia_pattern', '%1$s %2$s');
			}

			// allow short circuit
			if (($pattern == '') ||
					($pattern == '%1s') ||
					apply_filters ('wp_biographia_pre' , false)) {
				return $content;
			}
		}

		if (is_front_page ()) {
			$new_content = $this->insert_biographia ('frontpage', $content, $pattern);
		}

		elseif (is_archive ()) {
			$new_content = $this->insert_biographia ('archive', $content, $pattern);
		}
		
		elseif (is_page ()) {
			$new_content = $this->insert_biographia ('page', $content, $pattern);
		}

		elseif (is_single ()) {
			$new_content = $this->insert_biographia ('single', $content, $pattern);
		}

		elseif (is_feed ()) {
			$new_content = $this->insert_biographia ('feed', $content, $pattern);
		}

		return $new_content;
	}
	
	/**
	 * Cycles through all default and currently defined custom post types
	 *
	 * @param string content Source post content
	 * @param string pattern Pattern to be used for output
	 * @return string String containing the modified source post content
	 */

	function post_types_cycle ($content='', $pattern='') {
		global $post;
		$new_content = $content;
		$bio_content = $this->display();
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
				if ($this->get_option ('wp_biographia_display_' . $post_type_name) ||
				 		$this->is_shortcode) {
					// check exclusions
					$post_option = 'wp_biographia_' . $post_type . '_exclusions';
					$global_option = 'wp_biographia_global_' . $post_type . '_exclusions';
					
					if ($this->get_option ($post_option) ||
							$this->get_option ($global_option)) {
						$post_exclusions = $global_exclusions = array ();
						
						if ($this->get_option ($post_option)) {
							$post_exclusions = explode (',',
														$this->get_option ($post_option));
						}
						if ($this->get_option ($global_option)) {
							$global_exclusions = explode (',',
														$this->get_option ($global_option));
						}
						
						if (!in_array ($post->ID, $post_exclusions) &&
								!in_array ($post->ID, $global_exclusions) &&
							 	$this->is_last_page ()) {
							$new_content = sprintf ($pattern, $content, $bio_content);
							break;
						}

						else {
							$new_content = $content;
						}
					}

					else {
						if ($this->is_last_page ()) {
							$new_content = sprintf ($pattern, $content, $bio_content);
							break;
						}
					}
				}

				else {
					$new_content = $content;
				}
			}
		}	// end-foreach ()

		return $new_content;
	}
	
	/**
	 * Emits the Biography Box according to the current page content and settings/options.
	 *
	 * @param string context Current page context; frontpage|archive|page|single|feed
	 * @param string content Original post content
	 * @param string pattern Biography Box location formatting pattern
	 * @return string String containing the configured Biography Box or the original contents
	 * of the content parameter string if the current page context and/or settings/options
	 * require that no Biography Box is displayed.
	 */
	
	function insert_biographia ($context, $content, $pattern) {
		global $post;

		$settings = $this->get_option ();
		if (!$this->author_id || $this->author_id == 0) {
			$this->author_id = get_the_author_meta ('ID');
		}

		if ((get_user_meta ($this->author_id,
					'wp_biographia_suppress_posts',
					true) == 'on') &&
				($post->post_type != 'page')) {
			return $content;
		}
		
		switch ($context) {
			case "frontpage":
				if (($this->get_option ('wp_biographia_display_front') &&
						$this->get_option ('wp_biographia_display_front')) ||
					 	($this->is_shortcode)) {
					$new_content = $this->post_types_cycle ($content, $pattern);
				}
				break;

			case "archive":
				if ((($this->get_option ('wp_biographia_display_archives')) &&
				 		$this->get_option ('wp_biographia_display_archives')) ||
						($this->is_shortcode)) {
					$new_content = $this->post_types_cycle ($content, $pattern);
				}
				break;

			case "page":
				if (($this->get_option ('wp_biographia_display_pages') &&
						$this->get_option ('wp_biographia_display_pages') &&
						get_user_meta ($this->author_id, 'wp_biographia_suppress_pages', true) !== 'on') ||
						($this->is_shortcode && get_user_meta ($this->author_id, 'wp_biographia_suppress_pages', true) !== 'on')) {
					$this->display_bio = true;
				}

				if ($this->display_bio) {
					$page_exclusions = $this->get_option ('wp_biographia_page_exclusions');
					
					if ($this->get_option ('wp_biographia_page_exclusions')) {
						$page_exclusions = explode (',', $this->get_option ('wp_biographia_page_exclusions'));
						$this->display_bio = (!in_array ($post->ID, $page_exclusions));
					}
				}

				if ($this->display_bio) {
					$bio_content = $this->display ();
					$new_content = sprintf ($pattern, $content, $bio_content);
				}

				else {
					$new_content = $content;
				}
				break;

			case "single":
				// Cycle through Custom Post Types
				$new_content = $this->post_types_cycle ($content, $pattern);
				break;
				
			case "feed":
				if ($this->get_option ('wp_biographia_display_feed')) {
					$this->display_bio = true;
				}

				else {
					$this->display_bio = $this->is_shortcode;
				}

				if ($this->display_bio) {
					$this->is_feed = true;
					$bio_content = $this->display ();
					$new_content = sprintf ($pattern, $content, $bio_content);
				}
				break;

			default:
				$new_content = $content;
				break;
		}
		
		return $new_content;
	}
	
	/**
	 * Shortcode handler for the [wp_biographia] shortcode; expands the shortcode to the
	 * Biography Box according to the current set of plugin settings/options. The
	 * Biography Box is filterable via the 'wp_biographia_shortcode filter.
	 *
	 * @param array atts Array containing the optional shortcode attributes specified by
	 * the current instance of the shortcode.
	 * @return string String containing the Biography Box, providing that the current set
	 * of settings/options permit this.
	 */

	function shortcode ($atts) {
		global $wpdb;
		$content = "";
		$this->for_feed = false;
		
		extract (shortcode_atts (array (
			'mode' => 'raw',
			'author' => '',
			'prefix' => '',
			'name' => ''
		), $atts));

		$this->override = $content = array ();
		if (!empty ($prefix)) {
			$this->override['prefix'] = $prefix;
		}

		if (!empty ($name)) {
			switch ($name) {
				case 'account-name':
				case 'first-last-name':
				case 'nickname':
				case 'display-name':
				case 'none':
					$this->override['name'] = $name;
					break;
				default:
					break;
			}
		}

		if (!empty ($author)) {
			if ($author === "*") {
				$contributors = $this->get_users();
				$content[] = '<div class="wp-biographia-contributors">';
				foreach ($contributors as $user_obj) {
					if ($mode == 'raw') {
						$this->author_id = $user_obj->ID;
						$content[] = $this->display ();
					}

					elseif ($mode == 'configured') {
						$placeholder_content = "";
						$this->is_shortcode = true;
						$this->author_id = $user_obj->ID;

						$content[] = $this->insert ($placeholder_content);
					}
				}

				$content[] = '</div>';
			}
			
			else {
				$user_obj = get_user_by ('login', $author);
				if ($user_obj) {
					if ($mode == 'raw') {
						$this->author_id = $user_obj->ID;
						$content[] = $this->display ();
					}

					elseif ($mode == 'configured') {
						$placeholder_content = "";
						$this->is_shortcode = true;
						$this->author_id = $user_obj->ID;

						$content[] = $this->insert ($placeholder_content);
					}
				}
			}
		}
		
		else {	
			if ($mode == 'raw') {
				$content[] = $this->display ();
			}
		
			elseif ($mode == 'configured') {
				$placeholder_content = "";
				$this->is_shortcode = true;
				$this->author_id = $user_obj->ID;
			
				$content[] = $this->insert ($placeholder_content);
			}
		}	

		return apply_filters ('wp_biographia_shortcode', implode ('', $content), $content);
	}
	
	/**
	 * Emits the Biography Box according to current settings/options.
	 */

	function display () {
		$settings = $this->get_option ();
		
		if (!$this->author_id || $this->author_id == 0) {
			$this->author_id = get_the_author_meta ('ID');
		}

		$wp_biographia_content = $wp_biographia_links = $wp_biographia_author = $wp_biographia_biography = array();
			
		foreach ($this->defaults () as $key => $data) {
			if ($key != 'first-last-name') {
				$wp_biographia_author[$key] = get_the_author_meta ($data['field'], $this->author_id);
			}

			else {
				$wp_biographia_author[$key] = get_the_author_meta('first_name', $this->author_id) . ' ' . get_the_author_meta ('last_name', $this->author_id);
			}
		}
		
		$wp_biographia_author['posts'] = (int)count_user_posts ($this->author_id);
		$wp_biographia_author['posts_url'] = get_author_posts_url ($this->author_id);

		// Add Image Size Output
		$wp_biographia_author_pic_size =
			 (isset ($settings['wp_biographia_content_image_size'])) ?
				$this->get_option ('wp_biographia_content_image_size') : '100';

		$wp_biographia_author_pic = get_avatar ($wp_biographia_author['email'], $wp_biographia_author_pic_size);

		if (!empty ($settings['wp_biographia_content_prefix']) ||
			!empty ($settings['wp_biographia_content_name'])) {
			$wp_biographia_content[] = '<h3>';

			$name_prefix = "";
			if ((!empty ($this->override)) && (!empty ($this->override['prefix']))) {
				$name_prefix = $this->override['prefix'];
			}

			elseif (!empty ($settings['wp_biographia_content_prefix'])) {
				$name_prefix = $settings['wp_biographia_content_prefix'];
			}

			if (!empty ($name_prefix)) {
				$wp_biographia_content[] = $name_prefix . ' ';
			}

			$display_name = "";
			if ((!empty ($this->override)) && (!empty ($this->override['name']))) {
				$display_name = $this->override['name'];
			}

			elseif (!empty ($settings['wp_biographia_content_name'])) {
				$display_name = $settings['wp_biographia_content_name'];
			}

			if (!empty ($display_name) && $display_name != 'none') {
				switch ($display_name) {
					case 'first-last-name':
						$wp_biographia_formatted_name = $wp_biographia_author['first-last-name'];
						break;

					case 'account-name':
						$wp_biographia_formatted_name = $wp_biographia_author['account-name'];
						break;

					case 'nickname':
						$wp_biographia_formatted_name = $wp_biographia_author['nickname'];
						break;

					default:
						$wp_biographia_formatted_name = $wp_biographia_author['display-name'];
						break;
				}
				
				if (!empty ($settings['wp_biographia_content_authorpage']) && ($settings['wp_biographia_content_authorpage'] == 'on')) {
					$wp_biographia_content[] = '<a href="' . $wp_biographia_author['posts_url']	. '" title="' . $wp_biographia_formatted_name . '">' . $wp_biographia_formatted_name . '</a>';
				}

				else {
					$wp_biographia_content[] = $wp_biographia_formatted_name;
				}
			}

			$wp_biographia_content[] = '</h3>';
		}

		if (!empty ($settings['wp_biographia_content_bio'])) {
			$wp_biographia_content[] = "<p>" . $wp_biographia_author['bio'] . "</p>";
		}

		$display_icons = (!empty ($settings['wp_biographia_content_icons']) &&
			 ($settings['wp_biographia_content_icons'] == 'on')) ? 'icon' : 'text';

		if (($display_icons) && (!empty ($settings['wp_biographia_content_alt_icons']) && $settings['wp_biographia_content_alt_icons'] == 'on' && !empty ($settings['wp_biographia_content_icon_url']))) {
			$this->icon_dir_url = $settings['wp_biographia_content_icon_url'];
		}

		$link_items = $this->link_items ();
		$item_stub = ($display_icons == "icon") ? '<li><a href="%s" title="%s" class="%s"><img src="%s" class="%s" /></a></li>' : '<li><a href="%s" title="%s" class="%s">%s</a></li>';
		$title_name_stub = __('%1$s On %2$s', 'wp-biographia');
		$title_noname_stub = __('On %s', 'wp-biographia');
		
		// Deal with the email link first as a special case ...
		if ((!empty ($settings['wp_biographia_content_email']) && ($settings['wp_biographia_content_email'] == 'on')) && (!empty ($wp_biographia_author['email']))) {
			if (!empty ($wp_biographia_formatted_name)) {
				$link_title = sprintf (__('Send %s Mail', 'wp-biographia'), $wp_biographia_formatted_name);
			}

			else {
				$link_title = __('Send Mail', 'wp-biographia');
			}

			$link_text = __('Mail', 'wp-biographia');
			
			$link_body = ($display_icons == "icon") ? $this->icon_dir_url . 'mail.png' : $link_text;
			$wp_biographia_links[] = $this->link_item ($display_icons, $item_stub, 'mailto:' . antispambot ($wp_biographia_author['email']), $link_title, $link_body);
				
		}
		
		// Now deal with the other links that follow the same format and can be "templatised" ...
		foreach ($link_items as $link_key => $link_attrs) {
			$option_name = 'wp_biographia_content_' . $link_key;
			if (!empty ($settings[$option_name]) && ($settings[$option_name] == 'on') && (!empty ($wp_biographia_author[$link_key]) || ($link_key == 'web'))) {
				if (!empty ($wp_biographia_formatted_name)) {
					$link_title = sprintf ($title_name_stub, $wp_biographia_formatted_name, $link_attrs['link_title']);
				}

				else {
					$link_title = sprintf ($title_noname_stub, $link_attrs['link_title']);
				}

				$link_body = ($display_icons == "icon") ? $link_attrs['link_icon'] : $link_attrs['link_text'];
				$link_key = ($link_key != 'web') ? $link_key  : 'website';
				$wp_biographia_links[] = $this->link_item ($display_icons, $item_stub, $wp_biographia_author[$link_key], $link_title, $link_body);
			}
		}

		// Finally, deal with the "More Posts" link
		if (!empty ($settings['wp_biographia_content_posts']) && ($settings['wp_biographia_content_posts'] != 'none') && ($wp_biographia_author['posts'] > 0)) {
			if (!empty ($wp_biographia_formatted_name)) {
				$link_title = sprintf (__('More Posts By %s', 'wp-biographia'), $wp_biographia_formatted_name);
			}

			else {
				$link_title = __('More Posts', 'wp-biographia');
			}

			switch ($settings['wp_biographia_content_posts']) {
				case 'extended':
					$link_text = __('More Posts', 'wp-biographia') . ' ('
						. $wp_biographia_author['posts']
						. ')';
					break;

				default:
					$link_text = __('More Posts', 'wp-biographia');
					break;
			}
			
			$link_body = ($display_icons == "icon") ? $this->icon_dir_url . 'wordpress.png' : $link_text;
			$wp_biographia_links[] = $this->link_item ($display_icons, $item_stub, $wp_biographia_author['posts_url'], $link_title, $link_body);
		}
		
		$item_glue = ($display_icons == 'icon') ? "" : " | ";
		$list_class = "wp-biographia-list-" . $display_icons;

		if (!empty ($wp_biographia_links)) {
			$wp_biographia_content[] = apply_filters ('wp_biographia_links' , '<div class="wp-biographia-links">'
				. '<small><ul class="wp-biographia-list ' . $list_class . '">'
				. implode ($item_glue, $wp_biographia_links)
				. '</ul></small>'
				. '</div>' , $wp_biographia_links , $item_glue , $list_class);
		}
		
		if (!$this->for_feed) {
			$wp_biographia_biography[] = '<div class="wp-biographia-container-'
				. $settings['wp_biographia_style_border']
				. '" style="background-color:'
				. $settings['wp_biographia_style_bg']
				. ';">';

			if (!empty ($settings['wp_biographia_content_image']) &&
					 ($settings['wp_biographia_content_image'] == 'on')) {
				$wp_biographia_biography[] = '<div class="wp-biographia-pic" style="height:'
					. $wp_biographia_author_pic_size
					. 'px; width:'
					. $wp_biographia_author_pic_size
					. 'px;">'
					. $wp_biographia_author_pic
					. '</div>';
			}

			$wp_biographia_biography[] = apply_filters ('wp_biographia_feed' , '<div class="wp-biographia-text">'
				. implode ('', $wp_biographia_content)
				. '</div></div>' , $wp_biographia_content , $settings);
		}
		
		elseif (!empty ($settings['wp_biographia_content_image']) &&
					 ($settings['wp_biographia_content_image'] == 'on')) {
			$wp_biographia_biography[] = '<p>';
			$wp_biographia_biography[] = '<div style="float:left; text-align:left;>'.$wp_biographia_author_pic.'</div>';
			$wp_biographia_biography[] = $wp_biographia_content.'</p>';	
		}
		
		return apply_filters ('wp_biographia_biography' , implode ('', $wp_biographia_biography) , $wp_biographia_biography);
	}

	/**
	 * Produce and format a contact link item.
	 *
	 * @param string display_icons String containing the CSS class type; text|icon
	 * @param string format String containing a printf/sprintf format for output
	 * @param string link_key Link key string.
	 * @param string link_title Link title string.
	 * @param string link_body Link body string.
	 * @return string Formatted contact link item
	 */

	function link_item ($display_icons, $format, $link_key, $link_title, $link_body) {
		$item_class = "wp-biographia-item-" . $display_icons;
		$link_class = "wp-biographia-link-" . $display_icons;
		
		if ($display_icons == 'icon') {
			return sprintf ($format, $link_key, $link_title, $link_class, $link_body, $item_class);
		}
		
		else {
			return sprintf ($format, $link_key, $link_title, $link_class, $link_body);
		}
	}
	
	/**
	 * "admin_menu" action hook; called after the basic admin panel menu structure is in
	 * place.
	 */

	function admin_menu () {
		if (function_exists ('add_options_page')) {
			$page_title = __('WP Biographia', 'wp-biographia');
			$menu_title = __('WP Biographia', 'wp-biographia');
			add_options_page ($page_title, $menu_title, 'manage_options', __FILE__,
				array ($this, 'admin_display_settings'));
		}
	}
	
	/**
	 * "admin_print_scripts" action hook; called to enqueue admin specific scripts.
	 */

	function admin_print_scripts () {
		global $pagenow;

		if ($pagenow == 'options-general.php' &&
				isset ($_GET['page']) &&
				strstr ($_GET['page'],"wp-biographia")) {
			wp_enqueue_script ('postbox');
			wp_enqueue_script ('dashboard');
			wp_enqueue_script ('custom-background');
			wp_enqueue_script ('wp-biographia_admin-script', PLUGIN_URL . 'js/wp-biographia-admin.js');
		}
	}
	
	/**
	 * "admin_print_styles" action hook; called to enqueue admin specific CSS.
	 */

	function admin_print_styles () {
		global $pagenow;

		if ($pagenow == 'options-general.php' &&
				isset ($_GET['page']) &&
				strstr ($_GET['page'],"wp-biographia")) {
			wp_enqueue_style ('dashboard');
			wp_enqueue_style ('global');
			wp_enqueue_style ('wp-admin');
			wp_enqueue_style ('farbtastic');
			wp_enqueue_style ('wp-biographia-admin', PLUGIN_URL . 'css/wp-biographia-admin.css');	
		}
	}
	
	/**
	 * "admin_init" action hook; called after the admin panel is initialised.
	 */

	function admin_init () {
		$this->admin_upgrade ();
	}

	/**
	 * "show_user_profile" and "edit_user_profile"; called to add fields to the user profile.
	 */

	function admin_add_profile_extensions ($user) {
		?>
		<h3>Biography Box</h3>
		<table class="form-table">
			<tr>
				<th scope="row"><?php __('Suppress From Posts', 'wp-biographia')?></th>
				<td>
					<label for="wp_biographia_suppress_posts">
						<input type="checkbox" name="wp_biographia_suppress_posts" id="wp-biographia-suppress-posts" <?php checked (get_user_meta ($user->ID, 'wp_biographia_suppress_posts', true), 'on'); ?> <?php disabled (current_user_can ('manage_options'), false); ?> />&nbsp;<?php _e('Don\'t show the Biography Box on your posts', 'wp-biographia')?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php __('Suppress From Pages', 'wp-biographia')?></th>
				<td>
					<label for="wp_biographia_suppress_pages">
						<input type="checkbox" name="wp_biographia_suppress_pages" id="wp-biographia-suppress-pages" <?php checked (get_user_meta ($user->ID, 'wp_biographia_suppress_pages', true), 'on'); ?> <?php disabled (current_user_can ('manage_options'), false); ?> />&nbsp;<?php _e('Don\'t show the Biography Box on your pages', 'wp-biographia')?>
					</label>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * "personal_options_update" and "edit_user_profile_update" action hook; called to
	 * save the plugin's extensions to the user profile.
	 */

	function admin_save_profile_extensions ($user_id) {
		update_user_meta ($user_id, 'wp_biographia_suppress_posts',
			$this->admin_option ('wp_biographia_suppress_posts'));
		update_user_meta ($user_id, 'wp_biographia_suppress_pages',
			$this->admin_option ('wp_biographia_suppress_pages'));
	}
	
	/**
	 * "plugin_action_links_'plugin-name'" action hook; called to add a link to the plugin's
	 * settings/options panel.
	 */

	function admin_settings_link($links) {
		$settings_link = '<a href="options-general.php?page=wp-biographia/wp-biographia.php">'
			. __('Settings', 'wp-biographia')
			. '</a>';
		array_unshift ($links, $settings_link);
		return $links;
	}

	/**
	 * Called in response to the "admin_init" action hook; checks the current set of
	 * settings/options and upgrades them according to the new version of the plugin.
	 */
	
	function admin_upgrade () {
		$settings = NULL;
		$upgrade_settings = false;
		$current_plugin_version = NULL;

		/*
		 * Even if the plugin has only just been installed, the activation hook should have
		 * fired *before* the admin_init action so therefore we /should/ already have the
		 * plugin's configuration options defined in the database, but there's no harm in checking
		 * just to make sure ...
		 */

		$settings = $this->get_option ();

		/*
		 * Bale out early if there's no need to check for the need to upgrade the configuration
		 * settings ...
		 */

		if (is_array ($settings) &&
				isset ($settings['wp_biographia_version']) &&
				$settings['wp_biographia_version'] == self::VERSION) {
			return;
		}

		if (!is_array ($settings)) {
			/*
			 * Something odd is going on, so define the default set of config settings ...
			 */
			$this->add_settings ();
		}

		else {
			/*
			 * Versions of WP Biographia prior to v2.1 had a bug where some configuration
			 * settings that were created at initial installation of the plugin were not
			 * persisted after the configuration settings were updated; one of these is
			 * 'wp_biographia_version'. In this case, the "special" 00 version captures
			 * and remedies this.
			 */

			if (isset ($settings['wp_biographia_version'])) {
				$current_plugin_version = $settings['wp_biographia_version'];
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
			 * wp_biographia_content_vimeo = ""
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
			 * v2.4 added configuration settings ...
			 *
			 * wp_biographia_content_authorpage = "on"
			 * wp_biographia_content_icons = ""
			 * wp_biographia_content_alt_icons = ""
			 * wp_biographia_content_icon_url = ""
			 *
			 * v2.4 changed default configuration settings ...
			 *
			 * wp_biographia_version = "24"
			 *
			 * v2.4.1 changed default configuration settings ...
			 *
			 * wp_biographia_version = "241"
			 *
			 * v2.4.2 changed default configuration settings ...
			 *
			 * wp_biographia_version = "242"
			 *
			 * v2.4.3 changed default configuration settings ...
			 *
			 * wp_biographia_version = "243"
			 *
			 * v2.4.4 changed default configuration settings ...
			 *
			 * wp_biographia_version = "244"
			 */

			switch ($current_plugin_version) {
				case '00':
					if (!isset ($settings['wp_biographia_installed'])) {
						$settings['wp_biographia_installed'] = "on";
					}
					if (!isset ($settings['wp_biographia_style_bg'])) {
						$settings['wp_biographia_style_bg'] = "#FFEAA8";
					}
					if (!isset ($settings['wp_biographia_style_border'])) {
						$settings['wp_biographia_style_border'] = "top";
					}
					if (!isset ($settings['wp_biographia_display_front'])) {
						$settings['wp_biographia_display_front'] = "";
					}
					if (!isset ($settings['wp_biographia_display_archives'])) {
						$settings['wp_biographia_display_archives'] = "";
					}
					if (!isset ($settings['wp_biographia_display_posts'])) {
						$settings['wp_biographia_display_posts'] = "";
					}
					if (!isset ($settings['wp_biographia_display_pages'])) {
						$settings['wp_biographia_display_pages'] = "";
					}
					if (!isset ($settings['wp_biographia_display_feed'])) {
						$settings['wp_biographia_display_feed'] = "";
					}
					if (!isset ($settings['wp_biographia_content_prefix'])) {
						$settings['wp_biographia_content_prefix'] = "About";
					}
					if (!isset ($settings['wp_biographia_content_name'])) {
						$settings['wp_biographia_content_name'] = "none";
					}
					if (!isset ($settings['wp_biographia_content_image'])) {
						$settings['wp_biographia_content_image'] = "";
					}
					if (!isset ($settings['wp_biographia_content_bio'])) {
						$settings['wp_biographia_content_bio'] = "";
					}
					if (!isset ($settings['wp_biographia_content_web'])) {
						$settings['wp_biographia_content_web'] = "";
					}
					if (!isset ($settings['wp_biographia_content_twitter'])) {
						$settings['wp_biographia_content_twitter'] = "";
					}
					if (!isset ($settings['wp_biographia_content_facebook'])) {
						$settings['wp_biographia_content_facebook'] = "";
					}
					if (!isset ($settings['wp_biographia_content_linkedin'])) {
						$settings['wp_biographia_content_linkedin'] = "";
					}
					if (!isset ($settings['wp_biographia_content_googleplus'])) {
						$settings['wp_biographia_content_googleplus'] = "";
					}
					if (!isset ($settings['wp_biographia_content_posts'])) {
						$settings['wp_biographia_content_posts'] = "none";
					}

				case '01':
					if (!isset ($settings['wp_biographia_content_email'])) {
						$settings["wp_biographia_content_email"] = "";
					}

					if (!isset ($settings['wp_biographia_content_image_size'])) {
						$settings["wp_biographia_content_image_size"] = "100";
					}

					if (isset ($settings['wp_biographia_alert_border'])) {
						if (!isset ($settings['wp_biographia_style_border'])) {
							$settings['wp_biographia_style_border'] = $settings['wp_biographia_alert_border'];
						}
						unset ($settings['wp_biographia_alert_border']);
					}

					if (isset ($settings['wp_biographia_alert_bg'])) {
						if (!isset ($settings['wp_biographia_style_bg'])) {
							$settings['wp_biographia_style_bg'] = $settings['wp_biographia_alert_bg'];
						}
						unset ($settings['wp_biographia_alert_bg']);
					}

					if (!isset ($settings['wp_biographia_display_location'])) {
						$settings["wp_biographia_display_location"] = "bottom";
					}

					$upgrade_settings = true;

				case '20':
	/*
	 *				if (!isset ($settings['wp_biographia_beta_enabled'])) {
	 *					$settings['wp_biographia_beta_enabled'] = "";
	 *				}
	 */

					$users = $this->get_users ();
					foreach ($users as $user) {
						if (!get_user_meta ($user->ID,
								'wp_biographia_suppress_posts',
								true)) {
							update_user_meta ($user->ID, 'wp_biographia_suppress_posts', '');
						}

						if (!get_user_meta ($user->ID,
							'wp_biographia_suppress_pages',
							true)) {
							update_user_meta ($user->ID, 'wp_biographia_suppress_pages', '');
						}
					}
					$upgrade_settings = true;

				case '21':
				case '211':
				case '22':
					if (!isset ($settings['wp_biographia_content_delicious'])) {
						$settings["wp_biographia_content_delicious"] = "";
					}
					if (!isset ($settings['wp_biographia_content_flickr'])) {
						$settings["wp_biographia_content_flickr"] = "";
					}
					if (!isset ($settings['wp_biographia_content_picasa'])) {
						$settings["wp_biographia_content_picasa"] = "";
					}
					if (!isset ($settings['wp_biographia_content_vimeo'])) {
						$settings["wp_biographia_content_vimeo"] = "";
					}
					if (!isset ($settings['wp_biographia_content_youtube'])) {
						$settings["wp_biographia_content_youtube"] = "";
					}
					if (!isset ($settings['wp_biographia_content_reddit'])) {
						$settings["wp_biographia_content_reddit"] = "";
					}

				case '221':
				case '23':
				case '24':
					if (!isset ($settings['wp_biographia_content_authorpage'])) {
						$settings["wp_biographia_content_authorpage"] = "on";
					}
					if (!isset ($settings['wp_biographia_content_icons'])) {
						$settings['wp_biographia_content_icons'] = "";
					}
					if (!isset ($settings['wp_biographia_content_alt_icons'])) {
						$settings['wp_biographia_content_alt_icons'] = "";
					}
					if (!isset ($settings['wp_biographia_content_icon_url'])) {
						$settings['wp_biographia_content_icon_url'] = "";
					}

				case '241':
				case '242':
				case '243':
				case '244':
				case '30':
					$settings['wp_biographia_version'] = self::VERSION;
					$upgrade_settings = true;

				default:
					break;
			}	// end-switch

			if ($upgrade_settings) {
				update_option (self::OPTIONS, $settings);
			}
		}
	}

	/**
	 * add_options_page() callback function; called to emit the plugin's settings/options
	 * page.
	 */
	
	function admin_display_settings () {
		$settings = $this->admin_save_settings ();

		$wrapped_content = array ();
		$display_settings = array ();
		$user_settings = array ();
		$style_settings = array ();
		$content_settings = array ();
	/*
	 *	$beta_settings = "";
	 */

		$image_size = "";
		$avatars_enabled = (get_option ('show_avatars') == 1 ? true : false);
		$icons_enabled = ($settings['wp_biographia_content_icons'] == 'on' ? true : false);
		$alt_icons = ($settings['wp_biographia_content_alt_icons'] == 'on' ? true : false);

	/*
	 *	$beta_enabled = ($settings['wp_biographia_beta_enabled'] == "on" ? true : false);
	 */

		/*
	 	 * Biography Box Display Settings
	 	 */

		$display_settings[] = '<p><strong>' . __("Display On Front Page", 'wp-biographia') . '</strong><br /> 
					<input type="checkbox" name="wp_biographia_display_front" ' . checked ($settings['wp_biographia_display_front'], 'on', false) . ' />
					<small>' . __('Displays the Biography Box for each post on the front page.', 'wp-biographia') . '</small></p>';


		$display_settings[] = '<p><strong>' . __("Display On Individual Posts", 'wp-biographia') . '</strong><br /> 
					<input type="checkbox" name="wp_biographia_display_posts" ' . checked ($settings['wp_biographia_display_posts'], 'on', false) . ' />
					<small>' . __('Displays the Biography Box for individual posts.', 'wp-biographia') . '</small></p>';

		// Archives -> Post Archives
		$display_settings[] = '<p><strong>' . __("Display In Post Archives", 'wp-biographia') . '</strong><br /> 
					<input type="checkbox" name="wp_biographia_display_archives" ' . checked ($settings['wp_biographia_display_archives'], 'on', false) . ' />
					<small>' . __('Displays the Biography Box for each post on archive pages.', 'wp-biographia') . '</small></p>';	

		// Add Post ID Exclusion
		$display_settings[] = '<p><strong>' . __("Exclude From Single Posts (via Post ID)", 'wp-biographia') . '</strong><br />
				<input type="text" name="wp_biographia_post_exclusions" id="wp_biographia_post_exclusions" value="' . $settings['wp_biographia_post_exclusions'] . '" /><br />
				<small>' . __('Suppresses the Biography Box when a post is displayed using the Single Post Template. Enter the Post IDs to suppress, comma separated with no spaces, e.g. 54,33,55', 'wp-biographia') . '</small></p>';

		$display_settings[] = '<p><strong>' . __("Globally Exclude From Posts (via Post ID)", 'wp-biographia') . '</strong><br />
			<input type="text" name="wp_biographia_global_post_exclusions" id="wp_biographia_global_post_exclusions" value="' . $settings['wp_biographia_global_post_exclusions'] . '" /><br />
			<small>' . __('Suppresses the Biography Box whenever a post is displayed; singly, on archive pages or on the front page. Enter the Post IDs to globally suppress, comma separated with no spaces, e.g. 54,33,55.', 'wp-biographia') . '</small></p>';

		$display_settings[] = '<p><strong>' . __("Display On Individual Pages", 'wp-biographia') . '</strong><br /> 
					<input type="checkbox" name="wp_biographia_display_pages" ' . checked ($settings['wp_biographia_display_pages'], 'on', false) . ' />
					<small>' . __('Displays the Biography Box for individual pages.', 'wp-biographia') . '</small></p>';

		// Add Page ID Exclusion
		$display_settings[] = '<p><strong>' . __("Exclude Pages (via Page ID)", 'wp-biographia') . '</strong><br />
			<input type="text" name="wp_biographia_page_exclusions" id="wp_biographia_page_exclusions" value="' . $settings['wp_biographia_page_exclusions'] . '" /><br />
			<small>' . __('Suppresses the Biography Box when a page is displayed using the Page Template. Enter the Page IDs to suppress, comma separated with no spaces, e.g. 54,33,55.', 'wp-biographia') . '</small></p>';

		// Add Custom Post Types for Single & Archives
		//'wp_biographia_display_archives_'.$pt->name

		$args = array (
			'public' => true,
			'_builtin' => false
		);

		$pts = get_post_types ($args, 'objects');

		foreach ($pts as $pt) {
			$display_settings[] = '<p><strong>' . sprintf (__('Display On Individual %s', 'wp-biographia'), $pt->labels->name) . '</strong><br /> 
						<input type="checkbox" name="wp_biographia_display_' . $pt->name . '" ' . checked ($settings['wp_biographia_display_' . $pt->name], 'on', false) . ' />
						<small>' . sprintf (__('Displays the Biography Box on individual instances of custom post type %s.', 'wp-biographia'), $pt->labels->name) . '</small></p>';

			$display_settings[] = '<p><strong>' . sprintf (__('Display In %s Archives', 'wp-biographia'), $pt->labels->singular_name) . '</strong><br /> 
						<input type="checkbox" name="wp_biographia_display_archives_' . $pt->name . '" ' . checked ($settings['wp_biographia_display_archives_'.$pt->name], 'on', false) . ' />
						<small>' . sprintf (__('Displays the Biography Box on archive pages for custom post type %s.', 'wp-biographia'), $pt->labels->name) . '</small></p>';	

			$display_settings[] = '<p><strong>' . sprintf (__('Exclude From Single %1$s (via %2$s ID)', 'wp-biographia'), $pt->labels->name, $pt->labels->singular_name) . '</strong><br />
				<input type="text" name="wp_biographia_' . $pt->name .'_exclusions" id="wp_biographia_'. $pt->name .'_exclusions" value="' . $settings['wp_biographia_' . $pt->name . '_exclusions'] . '" /><br />
				<small>' . sprintf (__('Suppresses the Biography Box whenever a %1$s is displayed; singly, on archive pages or on the front page. Enter the %2$s IDs to globally suppress, comma separated with no spaces, e.g. 54,33,55.', 'wp-biographia'), $pt->labels->singular_name, $pt->labels->singular_name) . '</small></p>';

			$display_settings[] = '<p><strong>' . sprintf (__('Globally Exclude From %1$s (via %2$s ID).', 'wp-biographia'), $pt->labels->name, $pt->labels->singular_name) . '</strong><br />
				<input type="text" name="wp_biographia_global_' . $pt->name . '_exclusions" id="wp_biographia_global_' . $pt->name . '_exclusions" value="' . $settings['wp_biographia_global_' . $pt->name . '_exclusions'] . '" /><br />
				<small>' . sprintf (__('Suppresses the Biography Box whenever a %1$s is displayed. Enter the %2$s IDs to globally suppress, comma separated with no spaces, e.g. 54,33,55.', 'wp-biographia'), $pt->labels->singular_name, $pt->labels->singular_name)  . '</small></p>';
		}

		$settings['wp_biographia_display_location'] = (
			 isset($settings['wp_biographia_display_location'])) ?
			 $settings['wp_biographia_display_location'] : 'bottom';

		// Add Display Location: Top/Bottom
		$display_settings[] = '<p><strong>' . __("Display Location", 'wp-biographia') . '</strong><br />
			<input type="radio" name="wp_biographia_display_location" id="wp-biographia-content-name" value="top" '
			. checked ($settings['wp_biographia_display_location'], 'top', false)
			.' />&nbsp;' . __('Display the Biography Box before the post or page content', 'wp-biographia') . '<br />
			<input type="radio" name="wp_biographia_display_location" id="wp-biographia-content-name" value="bottom" '
			. checked ($settings['wp_biographia_display_location'], 'bottom', false)
			. ' />&nbsp;' . __('Display the Biography Box after the post or page content', 'wp-biographia') . '<br />';

		$display_settings[] = '<p><strong>' . __("Display In RSS Feeds", 'wp-biographia') . '</strong><br />
					<input type="checkbox" name="wp_biographia_display_feed" ' . checked ($settings['wp_biographia_display_feed'], 'on', false) . ' />
					<small>' . __('Displays the Biography Box in feeds for each entry.', 'wp-biographia') . '</small></p>';

		/*
		 * Biography Box User Settings
		 */

		// Add per user suppression of the Biography Box on posts and on pages

		$users = $this->get_users ();

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

		$user_settings[] = '<p><strong>' . __('Per User Suppression Of The Biography Box On Posts', 'wp-biographia') . '</strong><br />';
		$user_settings[] = '<span class="wp-biographia-users">';
		$user_settings[] = '<strong>' . __('Enabled Users', 'wp-biographia') . '</strong><br />';
		$user_settings[] = '<select multiple id="wp-biographia-enabled-post-users" name="wp-biographia-enabled-post-users[]">';

		foreach ($post_enabled as $user_id => $user_login) {
			$user_settings[] = '<option value="' . $user_id . '">' . $user_login . '</option>';
		}

		$user_settings[] = '</select>';
		$user_settings[] = '<a href="#" id="wp-biographia-user-post-add">' . __('Add', 'wp-biographia') . ' &raquo;</a>';
		$user_settings[] = '</span>';
		$user_settings[] = '<span class="wp-biographia-users">';
		$user_settings[] = '<strong>' . __('Suppressed Users', 'wp-biographia') . '</strong><br />';
		$user_settings[] = '<select multiple id="wp-biographia-suppressed-post-users" name="wp-biographia-suppressed-post-users[]">';

		foreach ($post_suppressed as $user_id => $user_login) {
			$user_settings[] = '<option value="' . $user_id . '">' . $user_login . '</option>';
		}

		$user_settings[] = '</select>';
		$user_settings[] = '<a href="#" id="wp-biographia-user-post-rem">&laquo; ' . __('Remove', 'wp-biographia') . '</a>';
		$user_settings[] = '</span>';
		$user_settings[] = '<br />';
		$user_settings[] = '<div style="clear: both";><small>' . __('Select the users who should not display the Biography Box on their authored posts. Selecting a user for suppression of the Biography Box affects all posts and custom post types by that user, on single post display, on archive pages and on the front page. This setting over-rides the individual user profile settings, providing the user has permission to edit their profile.', 'wp-biographia') . '</small></div></p>';

		$user_settings[] = '<p><strong>' . __('Per User Suppression Of The Biography Box On Pages', 'wp-biographia') . '</strong><br />';
		$user_settings[] = '<span class="wp-biographia-users">';
		$user_settings[] = '<strong>' . __('Enabled Users', 'wp-biographia') . '</strong><br />';
		$user_settings[] = '<select multiple id="wp-biographia-enabled-page-users" name="wp-biographia-enabled-page-users[]">';

		foreach ($page_enabled as $user_id => $user_login) {
			$user_settings[] = '<option value="' . $user_id . '">' . $user_login . '</option>';
		}

		$user_settings[] = '</select>';
		$user_settings[] = '<a href="#" id="wp-biographia-user-page-add">' . __('Add', 'wp-biographia') . ' &raquo;</a>';
		$user_settings[] = '</span>';
		$user_settings[] = '<span class="wp-biographia-users">';
		$user_settings[] = '<strong>' . __('Suppressed Users', 'wp-biographia') . '</strong><br />';
		$user_settings[] = '<select multiple id="wp-biographia-suppressed-page-users" name="wp-biographia-suppressed-page-users[]">';

		foreach ($page_suppressed as $user_id => $user_login) {
			$user_settings[] = '<option value="' . $user_id . '">' . $user_login . '</option>';
		}

		$user_settings[] = '</select>';
		$user_settings[] = '<a href="#" id="wp-biographia-user-page-rem">&laquo; ' . __('Remove', 'wp-biographia') . '</a>
	</span>';
		$user_settings[] = '<br />';
		$user_settings[] = '<div style="clear: both";><small>' . __('Select the users who should not display the Biography Box on their authored pages. This setting over-rides the individual user profile settings, providing the user has permission to edit their profile.', 'wp-biographia') . '</small></div></p>';

		/*
		 * Biography Box Style Settings
		 */

		$style_settings[] = '<p><strong>' . __("Box Background Color", 'wp-biographia') . '</strong><br /> 
					<input type="text" name="wp_biographia_style_bg" id="background-color" value="' . $settings['wp_biographia_style_bg'] . '" />
					<a class="hide-if-no-js" href="#" id="pickcolor">' . __('Select a Color', 'wp-biographia') . '</a>
					<div id="colorPickerDiv" style="z-index: 100; background:#eee; border:1px solid #ccc; position:absolute; display:none;"></div>
					<small>' . __('By default, the background color of the Biography Box is a yellowish tone.', 'wp-biographia') . '</small></p>';
		$style_settings[] = '<p><strong>' . __("Box Border", 'wp-biographia') . '</strong><br /> 
	                <select name="wp_biographia_style_border">
	                  <option value="top" ' .selected($settings['wp_biographia_style_border'], 'top', false) . '>' . __('Thick Top Border', 'wp-biographia') . '</option>
	                  <option value="around" ' .selected($settings['wp_biographia_style_border'], 'around', false) . '>' . __('Thin Surrounding Border', 'wp-biographia') . '</option>
	                  <option value="none" ' .selected($settings['wp_biographia_style_border'], 'none', false) . '>' . __('No Border', 'wp-biographia') . '</option>
	                </select><br /><small>' . __('By default, a thick black line is displayed above the Biography Box.', 'wp-biographia') . '</small></p>';

		/*
		 * Biography Box Content Settings
		 */

		$content_settings[] = '<p><strong>' . __("Biography Prefix", 'wp-biographia') . '</strong><br />
			<input type="text" name="wp_biographia_content_prefix" id="wp-biographia-content-name" value="'
			. $settings["wp_biographia_content_prefix"]
			. '" /><br />
			<small>' . __('Prefix text to be prepended to the author\'s name', 'wp-biographia') . '</small></p>';

		$content_settings[] = '<p><strong>' . __("Author's Name", 'wp-biographia') . '</strong><br />
			<input type="radio" name="wp_biographia_content_name" id="wp-biographia-content-name" value="first-last-name" '
			. checked ($settings['wp_biographia_content_name'], 'first-last-name', false)
			.' />&nbsp;' . __('First/Last Name', 'wp-biographia') . '<br />
			<input type="radio" name="wp_biographia_content_name" id="wp-biographia-content-name" value="account-name" '
			. checked ($settings['wp_biographia_content_name'], 'account-name', false)
			. ' />&nbsp;' . __('Account Name', 'wp-biographia') . '<br />
			<input type="radio" name="wp_biographia_content_name" id="wp-biographia-content-name" value="nickname" '
			. checked ($settings['wp_biographia_content_name'], 'nickname', false)
			. ' />&nbsp;' . __('Nickname', 'wp-biographia') . '<br />
			<input type="radio" name="wp_biographia_content_name" id="wp-biographia-content-name" value="display-name" '
			. checked ($settings['wp_biographia_content_name'], 'display-name', false)
			. ' />&nbsp;' . __('Display Name', 'wp-biographia') . '<br />
			<input type="radio" name="wp_biographia_content_name" id="wp-biographia-content-name" value="none" '
			. checked ($settings['wp_biographia_content_name'], 'none', false)
			. ' />&nbsp;' . __('Don\'t Show The Name', 'wp-biographia') . '<br />
			<small>' . __('How you want to see the author\'s name displayed (if at all)', 'wp-biographia') . '</small></p>';

		$content_settings[] = '<p><strong>' . __('Author\'s Name Link', 'wp-biographia') . '</strong><br/>
			<input type="checkbox" name="wp_biographia_content_authorpage" '
			.checked ($settings['wp_biographia_content_authorpage'], 'on', false)
			. '/>
			<small>' . __('Make author\'s name link to <em>More Posts By This Author</em>', 'wp_biographia') . '</small></p>';

		if (!$avatars_enabled) {
			$content_settings[] = '<div class="wp-biographia-warning">'
				. sprintf (__('It looks like Avatars are not currently enabled; this means that the author\'s image won\'t be able to be displayed. If you want this to happen then go to <a href="%s">Settings &rsaquo; Discussions</a> and set Avatar Display to Show Avatars.', 'wp-biographia'), admin_url('options-discussion.php')) . '</div>';
		}

		$content_settings[] = '<p><strong>' . __("Author's Image", 'wp-biographia') . '</strong><br />
			<input type="checkbox" name="wp_biographia_content_image" '
			. checked ($settings['wp_biographia_content_image'], 'on', false)
			. disabled ($avatars_enabled, false, false)
			. '/>
			<small>' . __('Display the author\'s image?', 'wp-biographia') . '</small></p>';

		if (!isset ($settings['wp_biographia_content_image_size']) ||
				$settings['wp_biographia_content_image_size'] === '' ||
				$settings['wp_biographia_content_image_size'] === 0) {
			$image_size = '100';
		}

		else {
			$image_size = $settings['wp_biographia_content_image_size'];
		}

		$content_settings[] = '<p><strong>' . __("Image Size", 'wp-biographia') . '</strong><br />
			<input type="text" name="wp_biographia_content_image_size" id="wp_biographia_content_image_size" value="'. $image_size .'"'
			. disabled ($avatars_enabled, false, false)
			. '/><br />'
			. '<small>' . __('Enter image size, e.g. 32 for a 32x32 image, 70 for a 70x70 image, etc. Defaults to a 100x100 size image.', 'wp-biographia') . '</small></p>';
		$content_settings[] = '<p><strong>' . __("Show Author's Biography", 'wp-biographia') . '</strong><br />
			<input type="checkbox" name="wp_biographia_content_bio" '
			. checked ($settings['wp_biographia_content_bio'], 'on', false)
			. '/>
			<small>' . __('Display the author\'s biography?', 'wp-biographia') . '</small></p>';

		$content_settings[] = '<p><strong>' . __("Show Contact Links As Icons", 'wp-biographia') . '</strong><br />
			<input type="checkbox" name="wp_biographia_content_icons" id="wp-biographia-content-icons" '
			. checked ($settings['wp_biographia_content_icons'], 'on', false)
			. '/>
			<small>' . __('Show the author\'s contact links as icons?', 'wp-biographia') . '</small></p>';

		$content_settings[] = '<div id="wp-biographia-icon-container"';
		if (!$icons_enabled) {
			$content_settings[] = ' style="display:none"';
		}
		$content_settings[] = '><p><strong>' . __("Use Alternate Icon Set", 'wp-biographia') . '</strong><br />
			<input type="checkbox" name="wp_biographia_content_alt_icons" id="wp-biographia-content-alt-icons" '
			. checked ($settings['wp_biographia_content_alt_icons'], 'on', false)
			. '/>
			<small>' . __('Use an alternative icon set for contact links?', 'wp-biographia') . '</small></p>'
			. '<p><strong>' . __("Alternate Icon Set URL", 'wp-biographia') . '</strong><br />
			<input type="text" name="wp_biographia_content_icon_url" id="wp-biographia-content-icon-url" value="'
			. $settings["wp_biographia_content_icon_url"]
			. '" '
			. disabled ($alt_icons, false, false)
			. '/><br />
			<small>' . __('Enter the URL where the alternate contact links icon set is located', 'wp-biographia') . '</small></p></div>';

		$content_settings[] = '<p><strong>' . __("Show Author's Email Address", 'wp-biographia') . '</strong><br />
			<input type="checkbox" name="wp_biographia_content_email" '
			. checked ($settings['wp_biographia_content_email'], 'on', false)
			. '/>
			<small>' . __('Display the author\'s email address?', 'wp-biographia') . '</small></p>';

		$content_settings[] = '<p><strong>' . __("Show Author's Website Link", 'wp-biographia') . '</strong><br />
			<input type="checkbox" name="wp_biographia_content_web" '
			. checked ($settings['wp_biographia_content_web'], 'on', false)
			. '/>
			<small>' . __('Display the author\'s website details?', 'wp-biographia') . '</small></p>';

		$content_settings[] = '<p><strong>' . __("Show Author's Twitter Link", 'wp-biographia') . '</strong><br />
			<input type="checkbox" name="wp_biographia_content_twitter" '
			. checked ($settings['wp_biographia_content_twitter'], 'on', false)
			. '/>
			<small>' . __('Display the author\'s Twitter details?', 'wp-biographia') . '</small></p>';

		$content_settings[] = '<p><strong>' . __("Show Author's Facebook Link", 'wp-biographia') . '</strong><br />
			<input type="checkbox" name="wp_biographia_content_facebook" '
			. checked ($settings['wp_biographia_content_facebook'], 'on', false)
			. '/>
			<small>' . __('Display the author\'s Facebook details?', 'wp-biographia') . '</small></p>';

		$content_settings[] = '<p><strong>' . __("Show Author's LinkedIn Link", 'wp-biographia') . '</strong><br />
			<input type="checkbox" name="wp_biographia_content_linkedin" '
			. checked ($settings['wp_biographia_content_linkedin'], 'on', false)
			. '/>
			<small>' . __('Display the author\'s LinkedIn details?', 'wp-biographia') . '</small></p>';

		$content_settings[] = '<p><strong>' . __("Show Author's Google+ Link", 'wp-biographia') . '</strong><br />
			<input type="checkbox" name="wp_biographia_content_googleplus" '
			. checked ($settings['wp_biographia_content_googleplus'], 'on', false)
			. '/>
			<small>' . __('Display the author\'s Google+ details?', 'wp-biographia') . '</small></p>';

		$content_settings[] = '<p><strong>' . __("Show Author's Delicious Link", 'wp-biographia') . '</strong><br />
			<input type="checkbox" name="wp_biographia_content_delicious" '
			. checked ($settings['wp_biographia_content_delicious'], 'on', false)
			. '/>
			<small>' . __('Display the author\'s Delicious details?', 'wp-biographia') . '</small></p>';

		$content_settings[] = '<p><strong>' . __("Show Author's Flickr Link", 'wp-biographia') . '</strong><br />
			<input type="checkbox" name="wp_biographia_content_flickr" '
			. checked ($settings['wp_biographia_content_flickr'], 'on', false)
			. '/>
			<small>' . __('Display the author\'s Flickr details?', 'wp-biographia') . '</small></p>';

		$content_settings[] = '<p><strong>' . __("Show Author's Picasa Link", 'wp-biographia') . '</strong><br />
			<input type="checkbox" name="wp_biographia_content_picasa" '
			. checked ($settings['wp_biographia_content_picasa'], 'on', false)
			. '/>
			<small>' . __('Display the author\'s Picasa details?', 'wp-biographia') . '</small></p>';

		$content_settings[] = '<p><strong>' . __("Show Author's Vimeo Link", 'wp-biographia') . '</strong><br />
			<input type="checkbox" name="wp_biographia_content_vimeo" '
			. checked ($settings['wp_biographia_content_vimeo'], 'on', false)
			. '/>
			<small>' . __('Display the author\'s Vimeo details?' , 'wp-biographia') . '</small></p>';

		$content_settings[] = '<p><strong>' . __("Show Author's YouTube Link", 'wp-biographia') . '</strong><br />
			<input type="checkbox" name="wp_biographia_content_youtube" '
			. checked ($settings['wp_biographia_content_youtube'], 'on', false)
			. '/>
			<small>' . __('Display the author\'s YouTube details?', 'wp-biographia') . '</small></p>';

		$content_settings[] = '<p><strong>' . __("Show Author's Reddit Link", 'wp-biographia') . '</strong><br />
			<input type="checkbox" name="wp_biographia_content_reddit" '
			. checked ($settings['wp_biographia_content_reddit'], 'on', false)
			. '/>
			<small>' . __('Display the author\'s Reddit details?', 'wp-biographia') . '</small></p>';


		$content_settings[] = '<p><strong>' . __("Show More Posts Link", 'wp-biographia') . '</strong><br />
			<input type="radio" name="wp_biographia_content_posts" id="wp-biographia-content-posts" value="basic" '
			. checked ($settings['wp_biographia_content_posts'], 'basic', false)
			. ' />&nbsp;' . __('Basic More Posts Link', 'wp-biographia') . '<br />
			<input type="radio" name="wp_biographia_content_posts" id="wp-biographia-content-posts" value="extended" '
			. checked ($settings['wp_biographia_content_posts'], 'extended', false)
			. ' />&nbsp;' . __('Extended More Posts Link', 'wp-biographia') . '<br />
			<input type="radio" name="wp_biographia_content_posts" id="wp-biographia-content-posts" value="none" '
			. checked ($settings['wp_biographia_content_posts'], 'none', false)
			. ' />&nbsp;' . __('Don\'t Show The More Posts Link', 'wp-biographia') . '<br />
			<small>' . __('How you want to display and format the <em>More Posts By This Author</em> link', 'wp-biographia') . '</small></p>';

		/*
		 * Biography Box Experimental Settings
		 */

	/*
		$beta_settings[] = '<div class="wp-biographia-warning">'
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

		$beta_settings[] = '<p><strong>' . __("Enable Experimental Features") . '</strong><br />
			<input type="checkbox" name="wp_biographia_beta_enabled" '
			. checked ($settings['wp_biographia_beta_enabled'], 'on', false)
			. '/>
			<small>Enable setting and use of WP Biographia experimental features</small></p>';
	*/

		if (function_exists ('wp_nonce_field')) {
			$wrapped_content[] = wp_nonce_field (
				'wp-biographia-update-options',
				'_wpnonce',
				true,
				false);
		}

		$wrapped_content[] = $this->admin_postbox ('wp-biographia-display-settings',
			__('Biography Box Display Settings', 'wp-biographia'),
			implode ('', $display_settings));

		$wrapped_content[] = $this->admin_postbox ('wp-biographia-user-settings',
			__('Biography Box Per User Settings', 'wp-biographia'),
			implode ('', $user_settings));

		$wrapped_content[] = $this->admin_postbox ('wp-biographia-style-settings',
			__('Biography Box Style Settings', 'wp-biographia'),
			implode ('', $style_settings));

		$wrapped_content[] = $this->admin_postbox ('wp-biographia-settings-content',
			__('Biography Box Content Settings', 'wp-biographia'),
			implode ('', $content_settings));

	/*
	 *	$wrapped_content[] = wp_biographia_postbox ('wp-biographia-settings-beta', 'Biography Box Experimental Settings', implode ('', $beta_settings));
	 */	

		$this->admin_wrap (__('WP Biographia Settings And Options', 'wp-biographia'),
			implode ('', $wrapped_content));
	}

	/**
	 * Extracts a specific settings/option field from the $_POST array.
	 *
	 * @param string field Field name.
	 * @return string Contents of the field parameter if present, else an empty string.
	 */

	function admin_option ($field) {
		return (isset ($_POST[$field]) ? $_POST[$field] : "");
	}

	/**
	 * Adds/updates a set of key/value pairs to a list of author profiles.
	 *
	 * @param array user_array Array of user profiles.
	 * @param string meta_key Key for the user_meta option to be updated/added.
	 * @param string meta_value Value for the user_meta option to be updated/added.
	 */
	
	function admin_meta_option ($user_array, $meta_key, $meta_value) {
		if ($user_array) {
			foreach ($user_array as $id) {
				update_user_meta ($id, $meta_key, $meta_value);
			}
		}
	}

	/**
	 * Verifies and saves the plugin's settings/options to the back-end database.
	 */
	
	function admin_save_settings () {
		$settings = $this->get_option ();

		if (!empty ($_POST['wp_biographia_option_submitted'])) {
			if (strstr ($_GET['page'], "wp-biographia") &&
			 		check_admin_referer ('wp-biographia-update-options')) {

				/*
				 * Biography Box Display Settings
				 */

				$settings['wp_biographia_display_front'] =
					$this->admin_option ('wp_biographia_display_front');

				$settings['wp_biographia_display_archives'] =
					$this->admin_option ('wp_biographia_display_archives');

				$settings['wp_biographia_display_posts'] =
					$this->admin_option ('wp_biographia_display_posts');

				// Add Custom Post Types for Archives & Single
				$args = array (
					'public' => true,
					'_builtin' => false
				);

				$pts = get_post_types ($args, 'objects');
				foreach ($pts as $pt) {
					$settings['wp_biographia_display_archives_' . $pt->name] =
						$this->admin_option ('wp_biographia_display_archives_' . $pt->name);


					$settings['wp_biographia_display_' . $pt->name] =
						$this->admin_option ('wp_biographia_display_' . $pt->name);

					$settings['wp_biographia_' . $pt->name . '_exclusions'] =
						$this->admin_option ('wp_biographia_' . $pt->name . '_exclusions');

					$settings['wp_biographia_global_' . $pt->name . '_exclusions'] =
						$this->admin_option ('wp_biographia_global_' . $pt->name . '_exclusions');
				}

				// Post exclusions 
				$settings['wp_biographia_post_exclusions'] =
					$this->admin_option ('wp_biographia_post_exclusions');

				$settings['wp_biographia_global_post_exclusions'] =
					$this->admin_option ('wp_biographia_global_post_exclusions');

				$settings['wp_biographia_display_pages'] =
						$this->admin_option ('wp_biographia_display_pages');

				// Page exclusions 
				$settings['wp_biographia_page_exclusions'] =
					$this->admin_option ('wp_biographia_page_exclusions');

				// Per user suppression of the Biography Box on posts and on pages

				$enabled_post_users = $_POST['wp-biographia-enabled-post-users'];
				$suppressed_post_users = $_POST['wp-biographia-suppressed-post-users'];
				$enabled_page_users = $_POST['wp-biographia-enabled-page-users'];
				$suppressed_page_users = $_POST['wp-biographia-suppressed-page-users'];

				$this->admin_meta_option ($enabled_post_users,
											'wp_biographia_suppress_posts',
											'');
				$this->admin_meta_option ($suppressed_post_users,
											'wp_biographia_suppress_posts',
											'on');
				$this->admin_meta_option ($enabled_page_users,
											'wp_biographia_suppress_pages',
											'');
				$this->admin_meta_option ($suppressed_page_users,
											'wp_biographia_suppress_pages',
											'on');

				// Add my additions: location-top/bottom
				$settings['wp_biographia_display_location'] =
					$this->admin_option ('wp_biographia_display_location');

				$settings['wp_biographia_display_feed'] =
					$this->admin_option ('wp_biographia_display_feed');

				/*
				 * Biography Box Style Settings
				 */

				$color = preg_replace ('/[^0-9a-fA-F]/', '', $_POST['wp_biographia_style_bg']);

				if ((strlen ($color) == 6 || strlen ($color) == 3) &&
	 				isset($_POST['wp_biographia_style_bg'])) {
						$settings['wp_biographia_style_bg'] = $_POST['wp_biographia_style_bg'];
				}

				$settings['wp_biographia_style_border'] = 
					$this->admin_option ('wp_biographia_style_border');

				/*
				 * Biography Box Content Settings
				 */
				$settings['wp_biographia_content_prefix'] = 
					$this->admin_option ('wp_biographia_content_prefix');

				$settings['wp_biographia_content_name'] = 
					$this->admin_option ('wp_biographia_content_name');

				$settings['wp_biographia_content_authorpage'] =
					$this->admin_option ('wp_biographia_content_authorpage');

				$settings['wp_biographia_content_image'] = 
					$this->admin_option ('wp_biographia_content_image');

				// Add Image Size
				$settings['wp_biographia_content_image_size'] = 
					$this->admin_option ('wp_biographia_content_image_size');

				$settings['wp_biographia_content_bio'] = 
					$this->admin_option ('wp_biographia_content_bio');

				$settings['wp_biographia_content_icons'] = 
					$this->admin_option ('wp_biographia_content_icons');

				$settings['wp_biographia_content_alt_icons'] = 
					$this->admin_option ('wp_biographia_content_alt_icons');

				$settings['wp_biographia_content_icon_url'] =
					$this->admin_option ('wp_biographia_content_icon_url');

				$settings['wp_biographia_content_email'] = 
					$this->admin_option ('wp_biographia_content_email');

				$settings['wp_biographia_content_web'] = 
					$this->admin_option ('wp_biographia_content_web');

				$settings['wp_biographia_content_twitter'] = 
					$this->admin_option ('wp_biographia_content_twitter');

				$settings['wp_biographia_content_facebook'] = 
					$this->admin_option ('wp_biographia_content_facebook');

				$settings['wp_biographia_content_linkedin'] = 
					$this->admin_option ('wp_biographia_content_linkedin');

				$settings['wp_biographia_content_googleplus'] = 
					$this->admin_option ('wp_biographia_content_googleplus');

				$settings['wp_biographia_content_delicious'] =
					$this->admin_option ('wp_biographia_content_delicious');

				$settings['wp_biographia_content_flickr'] =
					$this->admin_option ('wp_biographia_content_flickr');

				$settings['wp_biographia_content_picasa'] =
					$this->admin_option ('wp_biographia_content_picasa');

				$settings['wp_biographia_content_vimeo'] =
					$this->admin_option ('wp_biographia_content_vimeo');

				$settings['wp_biographia_content_youtube'] =
					$this->admin_option ('wp_biographia_content_youtube');

				$settings['wp_biographia_content_reddit'] =
					$this->admin_option ('wp_biographia_content_reddit');

				$settings['wp_biographia_content_posts'] = 
					$this->admin_option ('wp_biographia_content_posts');

				/*
				 * Biography Box Beta/Experimental Settings
				 */

				/*
				$settings['wp_biographia_beta_enabled'] = 
					$this->admin_option ('wp_biographia_beta_enabled');
				*/

				echo "<div id=\"updatemessage\" class=\"updated fade\"><p>";
				_e('WP Biographia Settings And Options Updated.', 'wp-biographia');
				echo "</p></div>\n";
				echo "<script type=\"text/javascript\">setTimeout(function(){jQuery('#updatemessage').hide('slow');}, 3000);</script>";	

				update_option (self::OPTIONS, $settings);
			}
		}

		$settings = $this->get_option ();

		return $settings;
	}

	/**
	 * Creates a postbox entry for the plugin's admin settings/options page.
	 *
	 * @param string id CSS id for this postbox
	 * @param string title Title string for this postbox
	 * @param string content HTML content for this postbox
	 * @return string Wrapped postbox content.
	 */
	
	function admin_postbox ($id, $title, $content) {
		$handle_title = __('Click to toggle', 'wp-biographia');
		$wrapper = array ();
		
		$wrapper[] = '<div id="' . $id . '" class="postbox">';
		$wrapper[] = '<div class="handlediv" title="'
			. $handle_title
			. '"><br /></div>';
		$wrapper[] = '<h3 class="hndle"><span>' . $title . '</span></h3>';
		$wrapper[] = '<div class="inside">' . $content . '</div></div>';

		return implode ('', $wrapper);
	}	

	/**
	 * Wrap up all the constituent components of the plugin's admin settings/options page.
	 *
	 * @param string title Title for the plugin's admin settings/options page.
	 * @param string content HTML content for the plugin's admin settings/options page.
	 * @return string Wrapped HTML content
	 */

	function admin_wrap ($title, $content) {
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
	                        <input type="submit" name="wp_biographia_option_submitted" class="button-primary" value="<?php _e('Save Changes', 'wp-biographia')?>" /> 
	                    </p> 
	                    <br /><br />
	                    </div>
	                  </div>
	                </div>
	                <div class="postbox-container wp-biographia-postbox-sidebar">
	                  <div class="metabox-holder">	
	                    <div class="meta-box-sortables">
	                    <?php
							echo $this->admin_help_and_support ();
							echo $this->admin_colophon ();
							echo $this->admin_acknowledgements ();
	                    ?>
	                    </div>
	                </div>
	            </div>
	        </form>
	    </div>
	<?php
	}
	
	/**
	 * Emits the plugin's colophon side-box for the plugin's admin settings/options page.
	 */
	
	function admin_colophon () {
		$content = array ();
		
		$content[] = '<p><em>"When it comes to software, I much prefer free software, because I have very seldom seen a program that has worked well enough for my needs and having sources available can be a life-saver"</em>&nbsp;&hellip;&nbsp;Linus Torvalds</p><p>';
		$content[] = __('For the inner nerd in you, the latest version of WP Biographia was written using <a href="http://macromates.com/">TextMate</a> on a MacBook Pro running OS X 10.7.2 Lion and tested on the same machine running <a href="http://mamp.info/en/index.html">MAMP</a> (Mac/Apache/MySQL/PHP) before being let loose on the author\'s <a href="http://www.vicchi.org/">blog</a>.', 'wp-biographia');
		$content[] = '</p><p>';
		$content[] = __('The official home for WP Biographia is on <a href="http://www.vicchi.org/codeage/wp-biographia/">Gary\'s Codeage</a>; it\'s also available from the official <a href="http://wordpress.org/extend/plugins/wp-biographia/">WordPress plugins repository</a>. If you\'re interested in what lies under the hood, the code is also on <a href="https://github.com/vicchi/wp-biographia">GitHub</a> to download, fork and otherwise hack around.', 'wp-biographia');
		$content[] = '</p><p>';
		$content[] = __('WP Biographia is named after the etymology of the modern English word <em>biography</em>. The word first appeared in the 1680s, probably from the latin <em>biographia</em> which itself derived from the Greek <em>bio</em>, meaning "life" and <em>graphia</em>, meaning "record" or "account" which derived from <em>graphein</em>, "to write".', 'wp-biographia');
		$content[] = '</p><p><small>Dictionary.com, "biography," in <em>Online Etymology Dictionary</em>. Source location: Douglas Harper, Historian. <a href="http://dictionary.reference.com/browse/biography">http://dictionary.reference.com/browse/biography</a>. Available: <a href="http://dictionary.reference.com">http://dictionary.reference.com</a>. Accessed: July 27, 2011.</small></p>';

		return $this->admin_postbox (
			'wp-biographia-colophon', __('Colophon', 'wp-biographia'),
			implode ('', $content));
	}

	/**
	 * Emits the plugin's help/support side-box for the plugin's admin settings/options page.
	 */

	function admin_help_and_support () {
		$email_address = antispambot ("gary@vicchi.org");
		$content = array ();

		$content[] = '<p>';
		$content[] =  __('For help and support with WP Biographia, here\'s what you can do:', 'wp-biographia');
		$content[] = '<ul><li>';
		$content[] = __('Ask a question on the <a href="http://wordpress.org/tags/wp-biographia?forum_id=10">WordPress support forum</a>; this is by far the best way so that other users can follow the conversation.', 'wp-biographia');
		$content[] = '</li><li>';
		$content[] = __('Ask me a question on Twitter; I\'m <a href="http://twitter.com/vicchi">@vicchi</a>.', 'wp-biographia');
		$content[] = '</li><li>';
		$content[] = sprintf (__('Drop me an <a href="mailto:%s">email </a>instead.', 'wp-biographia'), $email_address);
		$content[] = '</li></ul></p><p>';
		$content[] = __('But help and support is a two way street; here\'s what you might want to do:', 'wp-biographia');
		$content[] = '<ul><li>';
		$content[] = sprintf (__('If you like this plugin and use it on your WordPress site, or if you write about it online, <a href="http://www.vicchi.org/codeage/wp-biographia/">link to the plugin</a> and drop me an <a href="mailto:%s">email</a> telling me about this.', 'wp-biographia'), $email_address);
		$content[] = '</li><li>';
		$content[] = __('Rate the plugin on the <a href="http://wordpress.org/extend/plugins/wp-biographia/">WordPress plugin repository</a>.', 'wp-biographia');
		$content[] = '</li><li>';
		$content[] = __('WP Biographia is both free as in speech and free as in beer. No donations are required; <a href="http://www.vicchi.org/codeage/donate/">here\'s why</a>.', 'wp-biographia');
		$content[] = '</li></ul></p>';

		return $this->admin_postbox ('wp-biographia-support',
			__('Help &amp; Support', 'wp-biographia'),
			implode ('', $content));
	}

	/**
	 * Emits the plugin's acknowledgements side-box for the plugin's admin settings/options
	 * page.
	 */

	function admin_acknowledgements () {
		$email_address = antispambot ("gary@vicchi.org");
		$content = array ();

		$content[] = '<p>';
		$content[] = __('WP Biographia is inspired by and based on <a href="http://www.jonbishop.com">Jon Bishop\'s</a> <a href="http://wordpress.org/extend/plugins/wp-about-author/">WP About Author</a> plugin. Thanks and kudos must go to Jon for writing a well structured, working WordPress plugin released under a software license that enables other plugins such as this one to be written or derived in the first place. Jon\'s written other <a href="http://profiles.wordpress.org/users/JonBishop/">WordPress plugins</a> as well; you should take a look.', 'wp-biographia');
		$content[] = '</p>';

		$content[] = '<p>';
		$content[] = __('WP Biographia is now internationalised. Turkish language translation and support is thanks to <a href="https://twitter.com/#!/KazancExpert">Hakan Er</a>.', 'wp-biographia');
		$content[] = '</p>';

		$content[] = '<p>';
		$content[] = sprintf (__('If you\'d like to see WP Biographia translated into your language and want to help with the process, then please drop me an <a href="mailto:%s">email</a>.', 'wp-biographia'), $email_address);
		$content[] = '</p>';

		return $this->admin_postbox ('wo-biographia-acknowledgements',
			__('Acknowledgements', 'wp-biographia'),
			implode ('', $content));
	}

}

$__wp_biographia_instance = new WP_Biographia;

?>