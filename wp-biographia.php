<?php
/*
Plugin Name: WP Biographia
Plugin URI: http://www.vicchi.org/codeage/wp-biographia/
Description: Add and display a customizable author biography for individual posts, in RSS feeds, on pages, in archives and on each entry on the landing page and much more.
Version: 3.3.2
Author: Gary Gale & Travis Smith
Author URI: http://www.garygale.com/
License: GPL2
Text Domain: wp-biographia
*/

/*
A quick and dirty aide memoire for me to stop having to work this out each time I modify
the plugin ...

hook 'the_excerpt' calls $this->insert ()
hook 'the_content' calls $this->insert ()
add_shortcode () calls $this->shortcode ()

$this->insert calls $this->insert_biographia ()

$this->insert_biographia ()
	for posts and custom post-types
		checks per-user suppression via 'wp_biographia_suppress_posts'
	if not a page
		checks per-category suppression via 'wp_biographia_category_exclusions'
	for frontpage
		checks display via 'wp_biographia_display_front_posts'
		calls $this->post_types_cycle ()
	for archive
		checks display via 'wp_biographia_display_archives_posts'
		calls $this->post_types_cycle ()
	for page
		checks display via 'wp_biographia_display_pages'
		checks per-user suppression via 'wp_biographia_page_exclusions'
		calls $this->display ()
	for single
		calls $this->post_types_cycle ()
	for feed
		checks display via 'wp_biographia_display_feed'
		
$this->post_types_cycle ()
	calls $this->display ()
	for-each post type
		checks display via 'wp_biographia_display_"post-type-name"'
		checks exclusions via 'wp_biographia_"post-type"_exclusions'
		checks exclusions via 'wp_biographia_global_"post-type"_exclusions'
		emits Biography Box
	end-for-each

$this->shortcode ()
	if author attribute is not empty
		if author attribute is *, for-each user
			if mode attribute is 'raw', call $this->display ()
			if mode attribute is 'configured', call $this->insert ()
		else
			if mode attribute is 'raw', call $this->display ()
			if mode attribute is 'configured', call $this->insert ()
		end-for-each
	else
		if mode attribute is 'raw', call $this->display ()
		if mode attribute is 'configured', call $this->insert ()

$this->display
	formats the Biography Box according to the settings defined in Style and Content tabs
*/

define ('WPBIOGRAPHIA_PATH', plugin_dir_path (__FILE__));
define ('WPBIOGRAPHIA_URL', plugin_dir_url (__FILE__));
define ('WPBIOGRAPHIA_INCLUDE_SENTRY', true);
//define ('WPBIOGRAPHIA_DEBUG', true);

require_once (WPBIOGRAPHIA_PATH . 'includes/wp-plugin-base/wp-plugin-base.php');
require_once (WPBIOGRAPHIA_PATH . 'includes/wp-biographia-widget.php');
require_once (WPBIOGRAPHIA_PATH . 'includes/wp-biographia-tags.php');

if (!class_exists ('WP_BiographiaFilterPriority')) {
	class WP_BiographiaFilterPriority {
		public $has_filter = false;
		public $original = WP_Biographia::PRIORITY;
		public $new = WP_Biographia::PRIORITY;
	}	// end-class WP_BiographiaFilterPriority
}	// end-if (!class_exists ('WP_BiographiaFilterPriority'))

if (!class_exists ('WP_Biographia')) {
	class WP_Biographia extends WP_PluginBase_v1_1 { 

		private static $instance;
		public $author_id;
		public $override;
		public $display_bio = false;
		public $for_feed = false;
		public $is_shortcode = false;
		public $icon_dir_url = '';
	
		private $has_hacked_content_autop_prio = false;
		private $original_content_autop_prio = 10;
		private $hacked_content_autop_prio = 10;
	
		private $content_autop;
		private $excerpt_autop;
		private $sentry = false;
		private $is_sla_plugin_active = false;
	
		const OPTIONS = 'wp_biographia_settings';
		const VERSION = '332';
		const DISPLAY_VERSION = 'v3.3.2';
		const PRIORITY = 10;
		const DISPLAY_STUB = 'display';
		const ARCHIVE_STUB = 'archive';
		const BIOGRAPHY_STUB = 'biography';
		const ARCHIVE_BIOGRAPHY_STUB = 'archive-biography';
	
		/**
		 * Class constructor
		 */
	
		private function __construct () { 
			$this->author_id = NULL;
			$this->override = NULL;
		
			$this->is_sla_plugin_active = in_array (
					'simple-local-avatars/simple-local-avatars.php',
					apply_filters ('active_plugins', get_option ('active_plugins' )));

			register_activation_hook (__FILE__, array ($this, 'add_settings'));

			$this->hook ('plugins_loaded');
			$this->icon_dir_url = WPBIOGRAPHIA_URL . 'images/';
			$this->content_autop = new WP_BiographiaFilterPriority;
			$this->excerpt_autop = new WP_BiographiaFilterPriority;
		}
	
		/**
		 * Class singleton factory helper
		 */
		
		public static function get_instance () {
			if (!isset (self::$instance)) {
				$c = __CLASS__;
				self::$instance = new $c ();
			}

			return self::$instance;
		}
	
		/**
		 * "plugins_loaded" action hook; called after all active plugins and pluggable functions
		 * are loaded.
		 *
		 * Adds front-end display actions, shortcode support and admin actions.
		 */
	
		function plugins_loaded () {
			//register_activation_hook (__FILE__, array ($this, 'add_settings'));

			$settings = $this->get_option ();
			if (is_array ($settings) && isset ($settings['wp_biographia_version'])) {
				$content_priority = $settings['wp_biographia_admin_content_priority'];
				$excerpt_priority = $settings['wp_biographia_admin_excerpt_priority'];
			}
			else {
				$content_priority = $excerpt_priority = self::PRIORITY;
			}
		
			$this->hook ('wp_enqueue_scripts', 'style');
			$this->hook ('init');
			$this->hook ('widgets_init');
			$this->hook ('user_contactmethods');

			if (is_admin ()) {
				require_once(WPBIOGRAPHIA_PATH . '/includes/wp-biographia-admin.php');
				
				/*$this->hook ('admin_menu');
				$this->hook ('admin_print_scripts');
				$this->hook ('admin_print_styles');
				$this->hook ('admin_init');
				$this->hook ('show_user_profile', 'admin_add_profile_extensions');
				$this->hook ('edit_user_profile', 'admin_add_profile_extensions');
				$this->hook ('personal_options_update', 'admin_save_profile_extensions');
				$this->hook ('edit_user_profile_update', 'admin_save_profile_extensions');
				$this->hook ('plugin_action_links_' . plugin_basename (__FILE__),
					'admin_settings_link');
				$this->hook ('user_register', 'admin_user_register');
				$this->hook ('add_meta_boxes', 'admin_add_meta_boxes');
				$this->hook ('save_post', 'admin_save_meta_boxes');
				$this->hook ('before_delete_post', 'admin_before_delete_post');*/
			}
			else {
				$hook_to_loop = false;
				$display_type = null;

				if (isset ($settings['wp_biographia_display_type']) && !empty ($settings['wp_biographia_display_type'])) {
					$display_type = $settings['wp_biographia_display_type'];
				}
				
				if ($display_type === 'content' || $display_type === 'both') {
					$this->hook ('the_content', 'insert', intval ($content_priority));
					if ($content_priority < self::PRIORITY) {
						if (isset ($settings['wp_biographia_sync_content_wpautop']) &&
								($settings['wp_biographia_sync_content_wpautop'] == 'on')) {
							$hook_to_loop = true;
						}
					}
				}

				if ($display_type === 'excerpt' || $display_type === 'both') {
					$this->hook ('the_excerpt', 'insert', intval($excerpt_priority));
					if ($excerpt_priority < self::PRIORITY) {
						if (isset ($settings['wp_biographia_sync_excerpt_wpautop']) &&
								($settings['wp_biographia_sync_excerpt_wpautop'] == 'on')) {
							$hook_to_loop = true;
						}
					}
				}

				if ($hook_to_loop) {
					$this->hook ('loop_start');
					$this->hook ('loop_end');
				}

				// If the Simple Local Avatars plugin is installed and active, hook into that
				// plugin's 'simple_local_avatar' filter to fix up the Avatar's IMG tag's CSS,
				// if not already fixed up by the 'get_avatar' filter.
			
				if ($this->is_sla_plugin_active) {
					$this->hook ('simple_local_avatar');
				}
				$this->hook ('get_avatar', 'get_avatar', 10, 5);

				add_shortcode ('wp_biographia', array ($this, 'shortcode'));
			}
		}
	
		/**
		 * "loop_start" action hook; called before the start of the Loop.
		 */

		function loop_start () {
			$settings = $this->get_option ();
			if (isset ($settings['wp_biographia_sync_content_wpautop']) && ($settings['wp_biographia_sync_content_wpautop'] == 'on')) {
				$priority = has_filter ('the_content', 'wpautop');
				if ($priority !== false) {
					$content_priority = $this->get_option ('wp_biographia_admin_content_priority');
					$this->content_autop->has_filter = true;
					$this->content_autop->original = $priority;
					$this->content_autop->new = --$content_priority;

					remove_filter ('the_content', 'wpautop', $this->content_autop->original);
					add_filter ('the_content', 'wpautop', $this->content_autop->new);
				}
			}
			if (isset ($settings['wp_biographia_sync_excerpt_wpautop']) && ($settings['wp_biographia_sync_excerpt_wpautop'] == 'on')) {
				$priority = has_filter ('the_excerpt', 'wpautop');
				if ($priority !== false) {
					$excerpt_priority = $this->get_option ('wp_biographia_admin_content_priority');
					$this->excerpt_autop->has_filter = true;
					$this->excerpt_autop->original = $priority;
					$this->excerpt_autop->new = --$excerpt_priority;

					remove_filter ('the_excerpt', 'wpautop', $this->excerpt_autop->original);
					add_filter ('the_excerpt', 'wpautop', $this->excerpt_autop->new);
				}
			}
		}
	
		/**
		 * "loop_end" action hook; called after the end of the Loop.
		 */

		function loop_end () {
			if ($this->content_autop->has_filter) {
				remove_filter ('the_content', 'wpautop', $this->content_autop->new);
				add_filter ('the_content', 'wpautop', $this->content_autop->original);
			}
			if ($this->excerpt_autop->has_filter) {
				remove_filter ('the_excerpt', 'wpautop', $this->excerpt_autop->new);
				add_filter ('the_excerpt', 'wpautop', $this->excerpt_autop->original);
			}
		}
	
		/**
		 * Queries the back-end database for WP Biographia settings and options.
		 *
		 * @param string $key Optional settings/options key name; if specified only the value
		 * for the key will be returned, if the key exists, if omitted all settings/options
		 * will be returned.
		 * @return mixed If $key is specified, a string containing the key's settings/option 
		 * value is returned, if the key exists, else an empty string is returned. If $key is
		 * omitted, an array containing all settings/options will be returned.
		 */
	
		function get_option () {
			$num_args = func_num_args ();
			$options = get_option (self::OPTIONS);

			if ($num_args > 0) {
				$args = func_get_args ();
				$key = $args[0];
				$value = "";
				if (isset ($options[$key])) {
					$value = $options[$key];
				}
				return $value;
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
		 * "widgets_init" action hook; called to initialise the plugin's widget(s)
		 */
	
		function widgets_init () {
			return register_widget ('WP_BiographiaWidget');
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
		 * Returns the currently defined set of post categories.
		 *
		 * @return array Array containing the categories.
		 */
	
		function get_categories () {
			$args = array (
				'type' => 'post',
				'orderby' => 'name',
				'order' => 'asc',
				'hide_empty' => '0'
				);
		
			return get_categories ($args);
		}
	
		/**
		 * "user_contactmethods" filter hook; Sanitizes, filters and augments the user's
		 * profile contact information.
		 *
		 * @param array contactmethods Array containing the current set of contact methods.
		 * @return array Array containing the modified set of contact methods.
		 */
	
		function user_contactmethods ($contactmethods) {
			$links = $this->get_option ('wp_biographia_admin_links');
			foreach ($this->defaults () as $key => $data) {
				if (isset ($data['contactmethod']) && !empty ($data['contactmethod'])) {
					if (isset ($links[$key]) && $links[$key] == 'on') {
						$contactmethods[$data['field']] = $data['contactmethod'];
					}
					else {
						unset ($contactmethods[$data['field']]);
					}
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
				$admin_links = array ();
				foreach ($this->defaults () as $key => $data) {
					if (isset ($data['contactmethod']) && !empty ($data['contactmethod'])) {
						$admin_links[$key] = 'on';
					}
				}	// end-foreach (...)

				$settings = apply_filters ('wp_biographia_default_settings' , 
					//option name => option value
					array (
						'wp_biographia_installed' => 'on',
						'wp_biographia_version' => self::VERSION,
						'wp_biographia_style_bg' => '#FFEAA8',
						'wp_biographia_style_border' => 'top',
						'wp_biographia_display_front_posts' => 'on',
						'wp_biographia_display_archives_posts' => 'on',
						'wp_biographia_display_author_archives_posts' => 'on',
						'wp_biographia_display_category_archives_posts' => 'on',
						'wp_biographia_display_date_archives_posts' => 'on',
						'wp_biographia_display_tag_archives_posts' => 'on',
						'wp_biographia_display_posts' => 'on',
						'wp_biographia_display_pages' => 'on',
						'wp_biographia_display_feed' => '',
						'wp_biographia_display_location' => 'bottom',
						'wp_biographia_content_prefix' => __('About', 'wp-biographia'),
						'wp_biographia_content_name' => 'first-last-name',
						'wp_biographia_content_authorpage' => 'on',
						'wp_biographia_content_image' => 'on',
						'wp_biographia_content_image_size' => '100',
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
						'wp_biographia_content_vimeo' => '',
						'wp_biographia_content_youtube' => '',
						'wp_biographia_content_reddit' => '',
						'wp_biographia_content_posts' => 'extended',
						'wp_biographia_content_link_target' => '_self',
						'wp_biographia_content_link_nofollow' => '',
						'wp_biographia_admin_new_users' => '',
						'wp_biographia_admin_hide_profiles' => '',
						'wp_biographia_category_exclusions' => '',
						'wp_biographia_post_exclusions' => '',
						'wp_biographia_global_post_exclusions' => '',
						'wp_biographia_page_exclusions' => '',
						'wp_biographia_admin_content_priority' => self::PRIORITY,
						'wp_biographia_admin_excerpt_priority' => self::PRIORITY,
						'wp_biographia_sync_content_wpautop' => '',
						'wp_biographia_sync_excerpt_wpautop' => '',
						'wp_biographia_admin_post_overrides' => '',
						'wp_biographia_admin_links' => $admin_links,
						'wp_biographia_display_front_bio_posts' => 'full',
						'wp_biographia_display_archives_bio_posts' => 'full',
						'wp_biographia_display_author_archives_bio_posts' => 'full',
						'wp_biographia_display_category_archives_bio_posts' => 'full',
						'wp_biographia_display_date_archives_bio_posts' => 'full',
						'wp_biographia_display_tag_archives_bio_posts' => 'full',
						'wp_biographia_display_bio_posts' => 'full',
						'wp_biographia_display_bio_pages' => 'full',
						'wp_biographia_display_bio_feed' => 'full',
						'wp_biographia_admin_lock_to_loop' => '',
						'wp_biographia_style_border_color' => '#000000',
						'wp_biographia_display_type' => 'both'
					) 
				);
				update_option (self::OPTIONS, $settings);
			}
		}
	
		/**
		 * "get_avatar" filter hook; filters and augments the return from get_avatar().
		 *
		 * @param string avatar String containing the IMG tag returned by get_avatar().
		 * @return string String containing the (modified) avatar IMG tag
		 */

		function get_avatar ($avatar, $id_or_email, $size, $default, $alt) {
			if ($this->sentry) {
				if ($this->sentry) {
					$avatar = $this->fixup_avatar_css ($avatar);
				}
			}
			return $avatar;
		}

		/**
		 * "simple_local_avatar" filter hook; filters and augments the return from get_avatar().
		 *
		 * @param string avatar String containing the IMG tag returned by get_avatar().
		 * @return string String containing the (modified) avatar IMG tag
		 */

		function simple_local_avatar ($avatar) {
			if ($this->sentry) {
				$avatar = $this->fixup_avatar_css ($avatar);
			}
			return $avatar;
		}
	
		/**
		 * Called from the "get_avatar" or "simple_local_avatar" filter hooks; fixes up the
		 * IMG tag returned by get_avatar() to use WP Biographia's avatar image placement CSS.
	     *
		 * @param string avatar String containing the IMG tag returned by get_avatar().
		 * @return string String containing the (modified) avatar IMG tag
		 */

		function fixup_avatar_css ($avatar) {
			$pos = strpos ($avatar, 'wp-biographia-avatar');
			if ($pos === false) {
				$pos = strpos ($avatar, "class='avatar ");
				if ($pos !== false) {
					$avatar = str_replace ("class='avatar ", "class='wp-biographia-avatar ", $avatar, $count);
				}
			}
		
			return $avatar;
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

			if ($multipage) {
				return ($page == $numpages) ? true : false;
			}

			else {
				return true;
			}
		}
	
		/**
		 * Defines the default set of user's contact methods that the plugin natively
		 * supports. 
		 *
		 * @return array Array of contact methods.
		 */

		static function supported_contact_info () {
			$contacts = array (
				//option name => array (field => custom field , contactmethod => field name)
				'twitter' => array (
					'field' => 'wpb_twitter',
					'contactmethod' => __('Twitter', 'wp-biographia'),
					'url' => 'http://twitter.com/%s'
				),
				'facebook' => array (
					'field' => 'wpb_facebook',
					'contactmethod' => __('Facebook', 'wp-biographia'),
					'url' => 'http://www.facebook.com/%s'
				),
				'linkedin' => array (
					'field' => 'wpb_linkedin',
					'contactmethod' => __('LinkedIn', 'wp-biographia'),
					'url' => 'http://www.linkedin.com/in/%s'
				),
				'googleplus' => array (
					'field' => 'wpb_googleplus',
					'contactmethod' => __('Google+', 'wp-biographia'),
					'url' => 'http://plus.google.com/%s'
				),
				'delicious' => array (
					'field' => 'wpb_delicious',
					'contactmethod' => __('Delicious', 'wp-biographia'),
					'url' => 'http://www.delicious.com/%s'
				),
				'flickr' => array (
					'field' => 'wpb_flickr',
					'contactmethod' => __('Flickr', 'wp-biographia'),
					'url' => 'http://www.flickr.com/photos/%s'
				),
				'picasa' => array (
					'field' => 'wpb_picasa',
					'contactmethod' => __('Picasa', 'wp-biographia'),
					'url' => 'http://picasaweb.google.com/%s'
				),
				'vimeo' => array (
					'field' => 'wpb_vimeo',
					'contactmethod' => __('Vimeo', 'wp-biographia'),
					'url' => 'http://vimeo.com/%s'
				),
				'youtube' => array (
					'field' => 'wpb_youtube',
					'contactmethod' => __('YouTube', 'wp-biographia'),
					'url' => 'http://www.youtube.com/user/%s'
				),
				'reddit' => array (
					'field' => 'wpb_reddit',
					'contactmethod' => __('Reddit', 'wp-biographia'),
					'url' => 'http://www.reddit.com/user/%s'
				),
				'yim' => array (
					'field' => 'yim',
					'contactmethod' => __('Yahoo IM', 'wp-biographia'),
					'url' => 'http://profiles.yahoo.com/%s'
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
				)
			);

			return $contacts;
		}
	
		/**
		 * Defines the default set of user's contact information. The default set of contact
		 * links are filterable via the 'wp_biographia_contact_info' filter hook. Used by the
		 * display() and user_contactmethods() functions.
		 *
		 * @param boolean filter Controls whether the 'wp_biographia_contact_info' filter should
		 * be applied.
		 * @return array Array of default, filtered, contact information.
		 */

		static function defaults ($filter=true) {
			$non_contact_defaults = array (
				//option name => array (field => custom field , contactmethod => field name)
				'account-name' => array (
					'field' => 'user_login'
				),
				'first-last-name' => array (
					'field' => ''
				),
				'nickname' => array (
					'field' => 'nickname'
				),
				'display-name' => array (
					'field' => 'display_name'
				),
				'bio' => array (
					'field' => 'description'
				),
				'email' => array (
					'field' => 'email'
				),
				'web' => array (
					'field' => 'url'
				)
			);
		
			$supported_contact_info = WP_Biographia::supported_contact_info ();
			if ($filter) {
				$filtered_contact_info = apply_filters ('wp_biographia_contact_info',
				 										$supported_contact_info);

				return array_merge ($non_contact_defaults, $filtered_contact_info);
			}
		
			else {
				return array_merge ($non_contact_defaults, $supported_contact_info);
			}
		}
	
		/**
		 * Defines the default set of contact link items for the Biography Box that the plugin
		 * natively supports.
		 *
		 * @return array Array of default Biography Box link items.
		 */

		function supported_link_items () {
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
					)
			);
		
			return $link_items;
		}

		/**
		 * Defines the default set of contact link items for the Biography Box. The default set
		 * of links are filterable via the 'wp_biographia_link_items' filter hook.
		 *
		 * @return array Array of default, filtered, Biography Box link items.
		 */

		function link_items () {
			$supported_link_items = $this->supported_link_items ();

			return apply_filters ('wp_biographia_link_items',
									$supported_link_items,
									$this->icon_dir_url);
		}

		/**
		 * "wp_enqueue_scripts" action hook; called to load the plugin's CSS for the
		 * Biography Box.
		 */

		function style () {
			if ((defined('WP_DEBUG') && WP_DEBUG == true) || (defined('WPBIOGRAPHIA_DEBUG') && WPBIOGRAPHIA_DEBUG == true)) {
				$css_url = 'css/wp-biographia.css';
			}
			
			else {
				$css_url = 'css/wp-biographia.min.css';
			}
			wp_enqueue_style ('wp-biographia-bio', WPBIOGRAPHIA_URL . $css_url);
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
			$option = $this->get_option ('wp_biographia_admin_lock_to_loop');
			if ($option === 'on') {
				if (!in_the_loop () || !is_main_query ()) {
					return $content;
				}
			}

			global $post;
			$new_content = $content;
		
			if (!$this->is_shortcode) {
				$this->author_id = $post->post_author;
			}

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

			if (is_front_page ()) {
				$new_content = $this->insert_biographia ('front', $content, $pattern);
			}

			elseif (is_archive () || is_post_type_archive ()) {
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
		 * @param array options Settings option stubs to determine whether the Biography Box is to be displayed
		 * @param string content Source post content
		 * @param string pattern Pattern to be used for output
		 * @return string String containing the modified source post content
		 */

		function post_types_cycle ($options, $content='', $pattern='') {
			global $post;
			$new_content = $content;
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
					$do_display = false;
					$bio_stub = NULL;
					
					$optname = $options[self::DISPLAY_STUB] . $post_type_name;
					$optval = $this->get_option ($optname);
					if (!empty ($optval) && $optval === 'on') {
						$do_display = true;
						$bio_stub = self::BIOGRAPHY_STUB;
					}
					
					elseif (isset ($options[self::ARCHIVE_STUB])) {
						$optname = $options[self::ARCHIVE_STUB] . $post_type_name;
						$optval = $this->get_option ($optname);
						if (!empty ($optval) && $optval === 'on') {
							$do_display = true;
							$bio_stub = self::ARCHIVE_BIOGRAPHY_STUB;
						}
					}

					if ($do_display || $this->is_shortcode) {
						if (isset ($bio_stub) && isset ($options[$bio_stub])) {
							$optname = $options[$bio_stub] . $post_type_name;
							$optval = $this->get_option ($optname);
							if (!empty ($optval) && $optval === 'excerpt') {
								$this->override['type'] = $optval;
							}
						}

						$bio_content = $this->display ();

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
		 * @param string context Current page context; front|archive|page|single|feed
		 * @param string content Original post content
		 * @param string pattern Biography Box location formatting pattern
		 * @return string String containing the configured Biography Box or the original contents
		 * of the content parameter string if the current page context and/or settings/options
		 * require that no Biography Box is displayed.
		 */
	
		function insert_biographia ($context, $content, $pattern) {
			global $post;
			$this->display_bio = false;
			$settings = $this->get_option ();
			$excluded = false;
			$options = array ();

			if ((get_user_meta ($this->author_id,
						'wp_biographia_suppress_posts',
						true) == 'on') &&
					($post->post_type != 'page')) {
				return $content;
			}

			if (!is_page ()) {
				$categories = explode (',', $settings['wp_biographia_category_exclusions']);
				if (!empty ($categories)) {
					foreach ($categories as $category) {
						if (in_category ($category, $post->ID)) {
							$excluded = true;
							break;
						}
					}	// end-foreach (...)
				}
			}

			switch ($context) {
				case "front":
					$option = 'wp_biographia_display_front_';
					if (!$excluded || $this->is_shortcode) {
						$options[self::DISPLAY_STUB] = 'wp_biographia_display_front_';
						$options[self::BIOGRAPHY_STUB] = 'wp_biographia_display_front_bio_';
						$new_content = $this->post_types_cycle ($options, $content, $pattern);
					}
					else {
						$new_content = $content;
					}
					break;

				case "archive":
					if (!$excluded || $this->is_shortcode) {
						if (is_post_type_archive ()) {
							$options[self::DISPLAY_STUB] = 'wp_biographia_display_archives_';
							$options[self::BIOGRAPHY_STUB] = 'wp_biographia_display_archives_bio_';
						}
						else {
							$options[self::DISPLAY_STUB] = 'wp_biographia_display_archives_';
							$options[self::BIOGRAPHY_STUB] = 'wp_biographia_display_archives_bio_';

							if (is_author ()) {
								$options[self::ARCHIVE_STUB] = 'wp_biographia_display_author_archives_';
								$options[self::ARCHIVE_BIOGRAPHY_STUB] = 'wp_biographia_display_author_archives_bio_';
							}
							else if (is_category ()) {
								$options[self::ARCHIVE_STUB] = 'wp_biographia_display_category_archives_';
								$options[self::ARCHIVE_BIOGRAPHY_STUB] = 'wp_biographia_display_category_archives_bio_';
							}
							else if (is_date ()) {
								$options[self::ARCHIVE_STUB] = 'wp_biographia_display_date_archives_';
								$options[self::ARCHIVE_BIOGRAPHY_STUB] = 'wp_biographia_display_date_archives_bio_';
							}
							else if (is_tag ()) {
								$options[self::ARCHIVE_STUB] = 'wp_biographia_display_tag_archives_';
								$options[self::ARCHIVE_BIOGRAPHY_STUB] = 'wp_biographia_display_tag_archives_bio_';
							}
						}
						
						$new_content = $this->post_types_cycle ($options, $content, $pattern);
					}
					else {
						$new_content = $content;
					}
					break;

				case "page":
					$option = $this->get_option ('wp_biographia_display_pages');
					if ((isset ($option) &&	$option &&
							get_user_meta ($this->author_id, 'wp_biographia_suppress_pages', true) !== 'on') ||
							($this->is_shortcode && get_user_meta ($this->author_id, 'wp_biographia_suppress_pages', true) !== 'on')) {
						$this->display_bio = true;
					}

					if (!$excluded && $this->display_bio) {
						if ($this->get_option ('wp_biographia_page_exclusions')) {
							$page_exclusions = explode (',', $this->get_option ('wp_biographia_page_exclusions'));
							$this->display_bio = (!in_array ($post->ID, $page_exclusions));
						}
					}

					if (!$excluded && $this->display_bio) {
						$option = $this->get_option ('wp_biographia_display_bio_pages');
						if (!empty ($option) && $option === 'excerpt') {
							$this->override['type'] = $option;
						}
						$bio_content = $this->display ();
						$new_content = sprintf ($pattern, $content, $bio_content);
					}

					else {
						$new_content = $content;
					}
					break;

				case "single":
					// Cycle through Custom Post Types
					if (!$excluded) {
						$options[self::DISPLAY_STUB] = 'wp_biographia_display_';
						$options[self::BIOGRAPHY_STUB] = 'wp_biographia_display_bio_';
						$new_content = $this->post_types_cycle ($options, $content, $pattern);
					}

					else {
						$new_content = $content;
					}
					break;
				
				case "feed":
					$option = $this->get_option ('wp_biographia_display_feed');
					if (isset ($option) && $option) {
						$this->display_bio = true;
					}

					else {
						$this->display_bio = $this->is_shortcode;
					}

					if (!$excluded && $this->display_bio) {
						$this->for_feed = true;
						$option = $this->get_option ('wp_biographia_display_bio_feed');
						if (!empty ($option) && $option === 'excerpt') {
							$this->override['type'] = $option;
						}
						$bio_content = $this->display ();
						$new_content = sprintf ($pattern, $content, $bio_content);
					}
					else {
						$new_content = $content;
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
		 * @param string content String containing the enclosed content when the shortcode is
		 * specified in the enclosing form. If the self-closing form is used, this parameter will
		 * default to null.
		 * @return string String containing the Biography Box, providing that the current set
		 * of settings/options permit this.
		 */
	
		function shortcode ($atts, $content=NULL) {
			$this->for_feed = false;
		
			extract (shortcode_atts (array (
				'mode' => 'raw',
				'user' => '',
				'author' => '',
				'prefix' => '',
				'name' => '',
				'role' => '',
				'type' => 'full',
				'order' => 'account-name'
			), $atts));

			// Handle legacy shortcode useage (before the introduction of the user attribute);
			// if the 'author' attribute is present but no 'user' attribute exists, treat the
			// 'author' attribute *as* the 'user' attribute.
		
			if (empty ($user) && !empty ($author)) {
				$user = $author;
			}
		
			$this->is_shortcode = true;
			$ret = $this->biography_box ($mode, $user, $prefix, $name, $role, $type, $order);
			$this->is_shortcode = false;
		
			$content = $ret['content'];
			$params = $ret['params'];
		
			return apply_filters ('wp_biographia_shortcode', implode ('', $content), $params);
		}
	
		/**
		 * Biography Box marshalling helper; called by the shortcode and template tags
		 * handlers.
		 */
		
		function biography_box ($mode='raw', $user=NULL, $prefix=NULL, $name=NULL, $role=NULL, $type='full', $order='account-name') {
			$this->override = array ();
			$content = array ();
		
			// Check and validate the Biography Box display mode (raw/configured)
			switch ($mode) {
				case 'raw':
				case 'configured':
					break;
				default:
					$mode = 'raw';
					break;
			}	// end-switch ($mode)
		
			if (isset ($prefix) && !empty ($prefix)) {
				$this->override['prefix'] = $prefix;
			}

			// Check and validate the biography text type, if present ...
			if (isset ($type) && !empty ($type)) {
				switch ($type) {
					case 'full':
					case 'excerpt':
						$this->override['type'] = $type;
						break;
					default:
						$type = 'full';
						break;
				}
			}	// end-switch ($type)
		
			// Check and validate the name display, if present ...
			if (isset ($name) && !empty ($name)) {
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
				}	// end-switch ($name)
			}
		
			// Check and validate the name (sort) order , if present ...
			if (isset ($order) && !empty ($order)) {
				switch ($order) {
					case 'account-name':
					case 'first-name':
					case 'last-name':
					case 'nickname':
					case 'display-name':
					case 'login-id':
						break;
					default:
						$order = 'account-name';
						break;
				}	// end-switch ($order)
			}
		
			// Setup the array of validated arguments to be passed to either the template tag
			// or shortcode filter
		
			$params = array ('mode' => $mode,
				'user' => $user,
				'author' => $user,
				'prefix' => $prefix,
				'name' => $name,
				'role' => $role,
				'type' => $type,
				'order' => $order);

			// Is this Biography Box for a specific user (or all users in wildcard mode) ... ?
			if (isset ($user) && !empty ($user)) {
				// Wildcard user ... ?
				if ($user === '*') {
					$users = $contribs = array ();
				
					// Do we need to filter the users by role ... ?
					if (isset ($role) && !empty ($role)) {
						global $wp_roles;
						
						$defined_roles = $wp_roles->get_names ();
						$valid_role = false;
						//$role = strtolower ($role);
						
						$supplied_roles = explode (',', $role);
						foreach ($supplied_roles as $current_role) {
							$valid_role = array_key_exists ($current_role, $defined_roles);
							if ($valid_role) {
								// CODE HEALTH WARNING
								// The WP back-end supports multiple roles per user but the
								// front-end (as of WP 3.4.2) doesn't. If this changes, or if
								// there's some clever plugin at work (note to self: test) then
								// this code may break in strange and unexpected ways ...

								$user_set = $this->get_users ($current_role);
								if (!empty ($user_set)) {
									$users = array_merge ($users, $user_set);
								}
							}
						}	// end-foreach ($role ...)
					}
				
					// No role filtering needed, just grab 'em all ...
					else {
						$users = $this->get_users ();
					}
				
					if (!empty ($users)) {
						$order_fields = array (
							// order attribute value => meta value
							'account-name' => 'user_login',
							'first-name' => 'first_name',
							'last-name' => 'last_name',
							'nickname' => 'nickname',
							'display-name' => 'display_name',
							'login-id' => 'ID'
						);

						foreach ($users as $uo) {
							if (isset ($order) && !empty ($order) && $order === 'login-id') {
								$contribs[$uo->ID] = $uo->ID;
							}

							else {
								$contribs[$uo->ID] = get_the_author_meta ($order_fields[$order], $uo->ID);
							}
						}	// end-foreach ($users as $uo)
						natcasesort ($contribs);
					}
				
					if (!empty ($contribs)) {
						$content[] = '<div class="wp-biographia-contributors">';
						foreach ($contribs as $uid => $uval) {
							$this->author_id = $uid;
							// 'raw mode' ...
							if ($mode === 'raw') {
								$content[] = $this->display ();
							}
						
							// 'configured' mode ...
							else {
								$placeholder = '';
								$content[] = $this->insert ($placeholder);
							}
						}	// end-foreach ($contribs ...)
						$content[] = '</div>';
					}
				}
			
				// Specific user ... ?
				else {
					$uo = get_user_by ('login', $user);
					if ($uo) {
						$this->author_id = $uo->ID;
					
						// 'raw' mode ...
						if ($mode === 'raw') {
							$content[] = $this->display ();
						}
					
						// 'configured' mode ...
						else {
							$placeholder = '';
							$content[] = $this->insert ($placeholder);
						}
					}
				}
			}
		
			// If there's no specific user or all users in wilcard mode ($user='*') then 
			// queue the first post, so we have the $post global properly populated so,
			// in turn, we can pluck out the user ID we need to display the Biography Box for ...

			elseif (have_posts ()) {
				the_post ();

				global $post;
				$this->author_id = $post->post_author;
			
				// 'raw' mode ...
				if ($mode === 'raw') {
					$content[] = $this->display ();
				}
			
				// 'configured' mode ...
				else {
					$placeholder = '';
					$content[] = $this->insert ($placeholder);
				}

				// Rewind/reset The Loop back to the beginning so if being called from a
				// template, The Loop can be run properly, in full ...
				rewind_posts ();
			}
		
			return array ('content' => $content, 'params' => $params);
		}
	
		/**
		 * Emits the Biography Box according to current settings/options.
		 */

		function display () {
			global $post;

			$settings = $this->get_option ();
			$post_bio_override = $post_title_override = $post_suppress_avatar = $post_suppress_links = false;
			$post_bio_text = $post_title_text = '';


			if (!$this->author_id || $this->author_id == 0) {
				$this->author_id = $post->post_author;
			}

			$content = $links = $author = $biography = array();
			
			foreach ($this->defaults () as $key => $data) {
				if ($key != 'first-last-name') {
					$author[$key] = get_the_author_meta ($data['field'], $this->author_id);
				}

				else {
					$author[$key] = get_the_author_meta('first_name', $this->author_id) . ' ' . get_the_author_meta ('last_name', $this->author_id);
				}
			}

			$post_override = ($settings['wp_biographia_admin_post_overrides'] == 'on');
			if ($post_override) {
				$post_bio_override = (get_post_meta ($post->ID, '_wp_biographia_bio_override', true) == 'on');
				$post_bio_text = get_post_meta ($post->ID, '_wp_biographia_bio_text', true);
				$post_title_override = (get_post_meta ($post->ID, '_wp_biographia_title_override', true) == 'on');
				$post_title_text = get_post_meta ($post->ID, '_wp_biographia_title_text', true);
				$post_suppress_avatar = (get_post_meta ($post->ID, '_wp_biographia_suppress_avatar', true) == 'on');
				$post_suppress_links = (get_post_meta ($post->ID, '_wp_biographia_suppress_links', true) == 'on');
			}

			if ($post_override && $post_bio_override) {
				$author['bio'] = $post_bio_text;
			}

			elseif (!empty ($this->override) && !empty ($this->override['type']) && $this->override['type'] == 'excerpt') {
				$excerpt = get_user_meta ($this->author_id, 'wp_biographia_short_bio', true);
				if (!empty ($excerpt)) {
					$author['bio'] = $excerpt;
				}
			}
			
			$author['posts'] = (int)count_user_posts ($this->author_id);
			$author['posts_url'] = get_author_posts_url ($this->author_id);

			// Add Image Size Output
			$author_pic_size =
				 (isset ($settings['wp_biographia_content_image_size'])) ?
					$this->get_option ('wp_biographia_content_image_size') : '100';

			$this->sentry = true;
			$author_pic = get_avatar ($author['email'], $author_pic_size);
			$this->sentry = false;
		
			if ($post_override && $post_title_override) {
				$content[] = '<h3>';
				$content[] = $post_title_text;
				$content[] = '</h3>';
			}

			elseif (!empty ($settings['wp_biographia_content_prefix']) ||
				!empty ($settings['wp_biographia_content_name'])) {
				$title = array ();
			
				$name_prefix = "";
				if ((!empty ($this->override)) && (!empty ($this->override['prefix']))) {
					$name_prefix = $this->override['prefix'];
				}

				elseif (!empty ($settings['wp_biographia_content_prefix'])) {
					$name_prefix = $settings['wp_biographia_content_prefix'];
				}

				if (!empty ($name_prefix)) {
					$title[] = $name_prefix . ' ';
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
							$formatted_name = $author['first-last-name'];
							break;

						case 'account-name':
							$formatted_name = $author['account-name'];
							break;

						case 'nickname':
							$formatted_name = $author['nickname'];
							break;

						default:
							$formatted_name = $author['display-name'];
							break;
					}
				
					if (!empty ($settings['wp_biographia_content_authorpage']) && ($settings['wp_biographia_content_authorpage'] == 'on')) {
						$title[] = '<a href="' . $author['posts_url']	. '" title="' . $formatted_name . '">' . $formatted_name . '</a>';
					}

					else {
						$title[] = $formatted_name;
					}
				}

				$content[] = '<h3>';
				$content[] = apply_filters ('wp_biographia_content_title', implode ('', $title), $name_prefix, $formatted_name);
				$content[] = '</h3>';
			}

			if (!empty ($settings['wp_biographia_content_bio']) || ($post_override && $post_bio_override)) {
				$content[] = "<p>" . $author['bio'] . "</p>";
			}

			if (!$post_override || !$post_suppress_links) {
				// If this Biography Box is for a feed, override/ignore the "display links as icons"
				// setting ...
				if ($this->for_feed) {
					$display_icons = false;
				}
		
				else {
					$display_icons = (!empty ($settings['wp_biographia_content_icons']) &&
					 	($settings['wp_biographia_content_icons'] == 'on')) ? 'icon' : 'text';
				}

				if (($display_icons) && (!empty ($settings['wp_biographia_content_alt_icons']) && $settings['wp_biographia_content_alt_icons'] == 'on' && !empty ($settings['wp_biographia_content_icon_url']))) {
					$this->icon_dir_url = $settings['wp_biographia_content_icon_url'];
				}

				$link_items = $this->link_items ();
				if ($this->for_feed) {
					$item_stub = '<a href="%s" %s title="%s" class="%s">%s</a>';
				}
				else {
					$item_stub = ($display_icons == "icon") ? '<li><a href="%s" %s title="%s" class="%s"><img src="%s" class="%s" /></a></li>' : '<li><a href="%s" %s title="%s" class="%s">%s</a></li>';
				}
				$title_name_stub = __('%1$s On %2$s', 'wp-biographia');
				$title_noname_stub = __('On %s', 'wp-biographia');
		
				$link_meta = 'target="' . $settings['wp_biographia_content_link_target']. '"';
				if (!empty($settings['wp_biographia_content_link_nofollow']) &&
				($settings['wp_biographia_content_link_nofollow'] == 'on')) {
					$link_meta .= ' rel="nofollow"';
				}
		
				// Deal with the email link first as a special case ...
				if ((!empty ($settings['wp_biographia_content_email']) && ($settings['wp_biographia_content_email'] == 'on')) && (!empty ($author['email']))) {
					if (!empty ($formatted_name)) {
						$link_title = sprintf (__('Send %s Mail', 'wp-biographia'), $formatted_name);
					}

					else {
						$link_title = __('Send Mail', 'wp-biographia');
					}

					$link_text = __('Mail', 'wp-biographia');
			
					$link_body = ($display_icons == "icon") ? $this->icon_dir_url . 'mail.png' : $link_text;
					$links[] = $this->link_item ($display_icons, $item_stub, 'mailto:' . antispambot ($author['email']), $link_meta, $link_title, $link_body);
				}
		
				// Now deal with the other links that follow the same format and can be "templatised" ...
	
				$supported_links = $this->supported_link_items ();
				$config_links = $settings['wp_biographia_admin_links'];
				foreach ($link_items as $link_key => $link_attrs) {
					$display_link = false;
					if (array_key_exists ($link_key, $supported_links)) {
						$option_name = 'wp_biographia_content_' . $link_key;
						if ($link_key == 'web') {
							$display_link = (!empty ($settings[$option_name]) && ($settings[$option_name] == 'on') && (!empty ($author[$link_key])));
						}
						
						else {
							$display_link = (isset ($config_links[$link_key]) && $config_links[$link_key] == 'on' && !empty ($settings[$option_name]) && ($settings[$option_name] == 'on') && (!empty ($author[$link_key])));
						}
					}

					else {
						$display_link = (isset ($config_links[$link_key]) && $config_links[$link_key] == 'on' && !empty ($author[$link_key]));
					}

					if ($display_link) {
						if (!empty ($formatted_name)) {
							$link_title = sprintf ($title_name_stub, $formatted_name, $link_attrs['link_title']);
						}

						else {
							$link_title = sprintf ($title_noname_stub, $link_attrs['link_title']);
						}

						$link_body = ($display_icons == "icon") ? $link_attrs['link_icon'] : $link_attrs['link_text'];

						$links[] = $this->link_item ($display_icons, $item_stub, $author[$link_key], $link_meta, $link_title, $link_body);
					}
				}	// end-foreach (...)

				// Finally, deal with the "More Posts" link
				if (!empty ($settings['wp_biographia_content_posts']) && ($settings['wp_biographia_content_posts'] != 'none') && ($author['posts'] > 0)) {
					if (!empty ($formatted_name)) {
						$link_title = sprintf (__('More Posts By %s', 'wp-biographia'), $formatted_name);
					}

					else {
						$link_title = __('More Posts', 'wp-biographia');
					}

					switch ($settings['wp_biographia_content_posts']) {
						case 'extended':
							$link_text = __('More Posts', 'wp-biographia') . ' ('
								. $author['posts']
								. ')';
							break;

						default:
							$link_text = __('More Posts', 'wp-biographia');
							break;
					}
			
					$link_body = ($display_icons == "icon") ? $this->icon_dir_url . 'wordpress.png' : $link_text;
					$links[] = $this->link_item ($display_icons, $item_stub, $author['posts_url'], $link_meta, $link_title, $link_body);
				}
		
				$item_glue = ($display_icons == 'icon') ? "" : " | ";
				$list_class = "wp-biographia-list-" . $display_icons;
			}

			if (!empty ($links)) {
				if ($this->for_feed) {
					$prefix = '<div class="wp-biographia-links"><small>';
					$postfix = '</small></div>';
				}
			
				else {
					$prefix = '<div class="wp-biographia-links"><small><ul class="wp-biographia-list ' . $list_class . '">';
					$postfix = '</ul></small></div>';
				}
			
				$params = array (
					'glue' => $item_glue,
					'class' => $list_class,
					'prefix' => $prefix,
					'postfix' => $postfix);
				
				$content[] = apply_filters ('wp_biographia_links' ,
				 	$prefix . implode ($item_glue, $links) . $postfix,
					$links, $params);
			}
		
			if (!$this->for_feed) {
				$border_type = $settings['wp_biographia_style_border'];
				$border_color = $settings['wp_biographia_style_border_color'];
				$bg_color = $settings['wp_biographia_style_bg'];
				$class = 'wp-biographia-container-' . $border_type;
				$style = 'background-color: ' . $bg_color . ';';
				
				switch ($border_type) {
					case 'top':
						$style .= ' border-top: 4px solid ' . $border_color . ';';
						break;
					case 'around':
						$style .= ' border: 1px solid ' . $border_color . ';';
						break;
					case 'none':
					default:
						break;
				}	// end-switch ($border_type)
				
				$biography[] = '<div class="' . $class . '" style="' . $style . '">';

				$display_avatar = (!empty ($settings['wp_biographia_content_image']) &&
						 ($settings['wp_biographia_content_image'] == 'on'));
				if ($display_avatar && $post_override && $post_suppress_avatar) {
					$display_avatar = false;
				}

				if ($display_avatar) {
					$biography[] = '<div class="wp-biographia-pic" style="height:'
						. $author_pic_size
						. 'px; width:'
						. $author_pic_size
						. 'px;">'
						. $author_pic
						. '</div>';
				}

				if ($display_avatar) {
					$class = 'wp-biographia-text';
				}
				else {
					$class = 'wp-biographia-text-no-pic';
				}
				$biography[] = '<div class="' . $class . '">'
					. implode ('', $content)
					. '</div></div>';
			}
		
			else {
				$display_avatar = (!empty ($settings['wp_biographia_content_image']) &&
						 ($settings['wp_biographia_content_image'] == 'on'));
				if ($display_avatar && $post_override && $post_suppress_avatar) {
					$display_avatar = false;
				}
			
				if ($display_avatar) {
					$biography[] = '<p>' . $author_pic . '</p>';
					$class = 'wp-biographia-text';
				}

				else {
					$class = 'wp-biographia-text-no-pic';
				}
				$biography[] = apply_filters ('wp_biographia_feed' , '<div class="' . $class . '">'
					. implode ('', $content)
					. '</div>' , $content , $settings);
			}

			$biography_box = array ();
			$biography_box[] = '<!-- WP Biographia ' . self::DISPLAY_VERSION . ' -->' . PHP_EOL;
			$biography_box[] = apply_filters ('wp_biographia_biography_box', implode ('', $biography), $biography);
			$biography_box[] = '<!-- WP Biographia ' . self::DISPLAY_VERSION . ' -->' . PHP_EOL;

			return (implode ('', $biography_box));
		}

		/**
		 * Produce and format a contact link item.
		 *
		 * @param string display_icons String containing the CSS class type; text|icon
		 * @param string format String containing a printf/sprintf format for output
		 * @param string link_key Link key string.
		 * @param string link_meta Link meta attributes (target/rel)
		 * @param string link_title Link title string.
		 * @param string link_body Link body string.
		 * @return string Formatted contact link item
		 */

		function link_item ($display_icons, $format, $link_key, $link_meta, $link_title, $link_body) {
			$item_class = "wp-biographia-item-" . $display_icons;
			$link_class = "wp-biographia-link-" . $display_icons;

			$params = array (
				'type' => $display_icons,
				'format' => $format,
				'url' => $link_key,
				'meta' => $link_meta,
				'title' => $link_title,
				'body' => $link_body,
				'link-class' => $link_class
			);

			if ($display_icons == 'icon') {
				$params['item-class'] = $item_class;
				
				return apply_filters ('wp_biographia_link_item', 
					sprintf ($format, $link_key, $link_meta, $link_title, $link_class, $link_body, $item_class),
					$params);
			}
		
			else {
				return apply_filters ('wp_biographia_link_item',
					sprintf ($format, $link_key, $link_meta, $link_title, $link_class, $link_body),
					$params);
			}
		}
	
		/**
		 * Helper function to check whether a settings/options value exists
		 *
		 * @param array $settings Current settings/options array
		 * @param string $key Name of setting to check
		 * @return boolean Returns true if the setting exists and is not empty
		 */
	
		function check_option (&$settings, $key) {
			return (isset ($settings[$key]) && !empty ($settings[$key]));
		}

		/**
		 * Helper function to create the plugin's settings link hook.
		 * Note: Whilst this is only called from wp-biographia-admin.php it needs to be
		 * defined here as the link hook is based on the main plugin's file name and
		 * not that of an included file, possibly in a sub-directory.
		 */
		
		static function make_settings_link_hook() {
			return 'plugin_action_links_' . plugin_basename(__FILE__);
		}
	
	}	// end-class WP_Biographia
}	// end-if (!class_exists ('WP_Biographia'))

WP_Biographia::get_instance ();

?>
