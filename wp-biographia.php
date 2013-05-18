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
//error_reporting(E_STRICT);

require_once (WPBIOGRAPHIA_PATH . 'includes/wp-plugin-base/wp-plugin-base.php');
require_once (WPBIOGRAPHIA_PATH . 'includes/wp-biographia-widget.php');
require_once (WPBIOGRAPHIA_PATH . 'includes/wp-biographia-tags.php');

if (!class_exists ('WP_Biographia')) {
	class WP_Biographia extends WP_PluginBase_v1_1 { 

		private static $instance;
		private $is_sla_plugin_active = false;
	
		const OPTIONS = 'wp_biographia_settings';
		const VERSION = '332';
		const DISPLAY_VERSION = 'v3.3.2';
	
		/**
		 * Class constructor
		 */
	
		private function __construct () { 
			register_activation_hook (__FILE__, array ($this, 'add_settings'));
			$this->hook ('plugins_loaded');
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
				require_once(WPBIOGRAPHIA_PATH . '/includes/wp-biographia-box.php');
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
	
		static function get_option () {
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
	
		static function set_option ($key , $value) {
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
	 
		static function get_users ($role='', $args=array (0 => 'ID', 1 => 'user_login')) {
			$wp_user_search = new WP_User_Query (array ('role' => $role, 'fields' => $args));
			$roles = $wp_user_search->get_results ();
			return $roles;
		}
	
		/**
		 * Returns the currently defined set of post categories.
		 *
		 * @return array Array containing the categories.
		 */
	
		static function get_categories () {
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
						'wp_biographia_display_type' => 'both',
						'wp_biographia_design_type' => 'classic',
						'wp_biographia_design_wrap' => ''
					) 
				);
				update_option (self::OPTIONS, $settings);
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
		 * Helper function to create the plugin's settings link hook.
		 * Note: Whilst this is only called from wp-biographia-admin.php it needs to be
		 * defined here as the link hook is based on the main plugin's file name and
		 * not that of an included file, possibly in a sub-directory.
		 */
		
		static function make_settings_link_hook() {
			return 'plugin_action_links_' . plugin_basename(__FILE__);
		}

		/**
		 * Helper function to determine if debugging is enabled in WordPress and/or
		 * the plugin.
		 */
		
		static function is_debug() {
			return ((defined('WP_DEBUG') && WP_DEBUG == true) ||
					(defined('WPBIOGRAPHIA_DEBUG') && WPBIOGRAPHIA_DEBUG == true));
		}

		/**
		 * Helper function to make a style filename load debug or minimized CSS depending
		 * on the setting of WP_DEBUG and/or WPBIOGRAPHIA_DEBUG.
		 */
		
		static function make_css_path($stub) {
			if (WP_Biographia::is_debug()) {
				return $stub . '.css';
			}
			
			return $stub . '.min.css';
		}

		/**
		 * Helper function to make a script filename load debug or minimized JS depending
		 * on the setting of WP_DEBUG and/or WPBIOGRAPHIA_DEBUG.
		 */
		
		static function make_js_path($stub) {
			if (WP_Biographia::is_debug()) {
				return $stub . '.js';
			}
			
			return $stub . '.min.js';
		}

	}	// end-class WP_Biographia
}	// end-if (!class_exists ('WP_Biographia'))

WP_Biographia::get_instance ();

?>
