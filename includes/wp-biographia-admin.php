<?php

if (!class_exists('WP_BiographiaAdmin')) {
	class WP_BiographiaAdmin extends WP_PluginBase_v1_1 {

		private static $instance;
		private static $admin_tab_names;

		const META_NONCE = 'wp-biographia-meta-nonce';
		
		/**
		 * Class constructor
		 */

		private function __construct() {
			self::$admin_tab_names = array (
				'display' => 'Display',
				'admin' => 'Admin',
				'exclude' => 'Exclusions',
				'style' => 'Style',
				'content' => 'Content',
				'design' => 'Design',
				'defaults' => 'Defaults',
				'colophon' => 'Colophon'
				);

			$this->hook ('admin_menu');
			$this->hook ('admin_print_scripts');
			$this->hook ('admin_print_styles');
			$this->hook ('admin_init');
			$this->hook ('show_user_profile', 'admin_add_profile_extensions');
			$this->hook ('edit_user_profile', 'admin_add_profile_extensions');
			$this->hook ('personal_options_update', 'admin_save_profile_extensions');
			$this->hook ('edit_user_profile_update', 'admin_save_profile_extensions');
			$this->hook (WP_Biographia::make_settings_link_hook(), 'admin_settings_link');
			$this->hook ('user_register', 'admin_user_register');
			$this->hook ('add_meta_boxes', 'admin_add_meta_boxes');
			$this->hook ('save_post', 'admin_save_meta_boxes');
			$this->hook ('before_delete_post', 'admin_before_delete_post');
			$this->hook('wp_ajax_wp_biographia_admin_preview');
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
		 * "admin_menu" action hook; called after the basic admin panel menu structure is in
		 * place.
		 */

		function admin_menu () {
			if (function_exists ('add_options_page')) {
				$page_title = __('WP Biographia', 'wp-biographia');
				$menu_title = __('WP Biographia', 'wp-biographia');
				$capability = 'manage_options';
				$menu_slug = __FILE__;
				add_options_page ($page_title, $menu_title, $capability, $menu_slug,
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
					strstr ($_GET['page'], "wp-biographia")) {
				wp_enqueue_script ('postbox');
				wp_enqueue_script ('dashboard');
				wp_enqueue_script ('farbtastic');
				
				$src = WPBIOGRAPHIA_URL . 'js/wp-biographia-admin';
				$src = WP_Biographia::make_js_path($src);
				$handle = 'wp-biographia-admin-script';
				wp_enqueue_script($handle, $src);
				
				$tab = $this->admin_validate_tab();
				if ($tab === 'design') {
					$action = 'wp_biographia_admin_preview';
					$nonce = 'wp-biographia-admin-preview-nonce';
					$ajax_args = array(
						'action' => $action,
						'nonce' => wp_create_nonce($nonce)
						);
					$object_name = 'WPBiographiaAdminPreview';
					wp_localize_script($handle, $object_name, $ajax_args);
				}
			}

			elseif ($pagenow == 'post.php' || $pagenow == 'post-new.php') {
				$post_override = WP_Biographia::get_option ('wp_biographia_admin_post_overrides');

				// Only enqueue the admin edit JS if post overrides are enabled
				if (isset ($post_override) && !empty ($post_override) && $post_override == 'on') {
					$src = WPBIOGRAPHIA_URL . 'js/wp-biographia-edit';
					$src = WP_Biographia::make_js_path($src);
					$handle = 'wp-biographia-edit-script';
					wp_enqueue_script($handle, $src);
				}
			}
		}

		function wp_ajax_wp_biographia_admin_preview() {
			check_ajax_referer('wp-biographia-admin-preview-nonce', 'nonce');

			$design = $this->admin_option('design');
			$wrap = $this->admin_option('wrap');

			$options = WP_Biographia::get_option();
			$saved_design = $options['wp_biographia_design_type'];
			$saved_wrap = $options['wp_biographia_design_wrap'];

			$options['wp_biographia_design_type'] = $design;
			$options['wp_biographia_design_wrap'] = ($wrap === 'true' ? 'on' : '');
			update_option (WP_Biographia::OPTIONS, $options);

			global $current_user;
			get_currentuserinfo();
			$mode = 'raw';

			require_once(WPBIOGRAPHIA_PATH . '/includes/wp-biographia-box.php');
			wpb_the_biography_box($mode, $current_user->user_login);
			
			$options['wp_biographia_design_type'] = $saved_design;
			$options['wp_biographia_design_wrap'] = $saved_wrap;

			update_option (WP_Biographia::OPTIONS, $options);
			
			die();
		}

		/**
		 * "admin_print_styles" action hook; called to enqueue admin specific CSS.
		 */

		function admin_print_styles () {
			global $pagenow;

			if ($pagenow == 'options-general.php' &&
					isset ($_GET['page']) &&
					strstr ($_GET['page'], "wp-biographia")) {
				wp_enqueue_style ('dashboard');
				wp_enqueue_style ('global');
				wp_enqueue_style ('wp-admin');
				wp_enqueue_style ('farbtastic');
				
				$src = WPBIOGRAPHIA_URL . 'css/wp-biographia-admin';
				$src = WP_Biographia::make_css_path($src);
				wp_enqueue_style ('wp-biographia-admin', $src);	
				
				$tab = $this->admin_validate_tab();
				if ($tab === 'design') {
					$src = WPBIOGRAPHIA_URL . 'css/wp-biographia-admin-preview';
					$src = WP_Biographia::make_css_path($src);
					$handle = 'wp-biographia-admin-preview';
					wp_enqueue_style($handle, $src);
					
					require_once(WPBIOGRAPHIA_PATH . '/includes/wp-biographia-box.php');
					WP_BiographiaBox::enqueue_box_style();
				}
			}

			elseif ($pagenow == 'post.php' || $pagenow == 'post-new.php') {
				$post_override = WP_Biographia::get_option ('wp_biographia_admin_post_overrides');
				if (isset ($post_override) && !empty ($post_override) && $post_override == 'on') {
					// Only enqueue the admin edit JS if post overrides are enabled

					$src = WPBIOGRAPHIA_URL . 'css/wp-biographia-edit';
					$src = WP_Biographia::make_css_path($src);
					$handle = 'wp-biographia-edt';
					wp_enqueue_style($handle, $src);
				}
			}
		}

		/**
		 * "admin_init" action hook; called after the admin panel is initialised.
		 */

		function admin_init () {
			$this->admin_upgrade ();

			$skip_tour = $this->admin_is_pointer_set ();

			if (isset ($_GET['wp_biographia_restart_tour'])) {
				if (check_admin_referer ('wp-biographia-restart-tour')) {
					$this->admin_clear_pointer ();
					$skip_tour = false;
				}
			}

			if (!$skip_tour) {
				require (WPBIOGRAPHIA_PATH . 'includes/wp-biographia-pointers.php');
			}

			global $pagenow;

			if ($pagenow == 'profile.php' ||
					$pagenow == 'user-edit.php' ||
					($pagenow == 'options-general.php' &&
						isset ($_GET['page']) &&
						strstr ($_GET['page'], "wp-biographia"))) {
				$this->hook ('admin_notices');
			}
		}

		/**
		 * "admin_notices" action hook; called to display a message near the top of admin
		 * pages.
		 */

		function admin_notices () {
			global $pagenow;
			global $current_user;
			$user_id = NULL;
			$notices = array ();

			if ($pagenow == 'profile.php') {
				$user_id = $current_user->ID;
				$invalid = $this->admin_validate_contacts ($user_id);
				if (!empty ($invalid)) {
					$notices[] = sprintf (__('There is a problem with %d of your contact links!', 'wp-biographia'), count ($invalid));
					$notice = $this->admin_create_notice ($invalid, $user_id);
					$notices = array_merge ($notices, $notice);
				}
			}

			elseif ($pagenow == 'user-edit.php') {
				if (isset ($_GET['user_id']) && !empty ($_GET['user_id'])) {
					$user_id = $_GET['user_id'];
				}

				else {
					$user_id = $current_user->ID;
				}
				$invalid = $this->admin_validate_contacts ($user_id);
				if (!empty ($invalid)) {
					$notices[] = sprintf (__('There is a problem with %d of this user\'s contact links!', 'wp-biographia'), count ($invalid));
					$notice = $this->admin_create_notice ($invalid, $user_id);
					$notices = array_merge ($notices, $notice);
				}
			}

			elseif ($pagenow == 'options-general.php' && isset ($_GET['page']) && strstr ($_GET['page'], 'wp-biographia')) {
				$user_id = $current_user->ID;
				$invalid = $this->admin_validate_contacts ($user_id);
				if (!empty ($invalid)) {
					$notices[] = sprintf (__('There is a problem with %d of your contact links; you probably want to <a href="%s">edit your profile</a> to fix this', 'wp-biographia'), count ($invalid), admin_url ('profile.php'));
				}
			}

			if (!empty ($notices)) {
				echo '<div class="error">' . PHP_EOL;
				echo '<p>' . implode ('<br />', $notices) . '</p>';
				echo '</div>' . PHP_EOL;
			}
		}

		/**
		 * Called from the "admin_notice" action hook handler; formats a message if one
		 * of the contact links URLs is incorrect/invalid.
		 */

		function admin_create_notice ($contacts, $user_id) {
			$user = get_userdata ($user_id);
			$notices = array ();
			foreach ($contacts as $key => $data) {
				$url = sprintf ($data['url'], $user->user_login);
				$notices[] = sprintf (__('The %s URL doesn\'t look right; it should look something like %s', 'wp-biographia'),
					$data['contactmethod'], $url);
			}

			return $notices;
		}

		/**
		 * Called from the "admin_notice" action hook handler; validates each contact link
		 * URL.
		 */

		function admin_validate_contacts ($user_id) {
			$invalid = array ();
			foreach (WP_Biographia::defaults () as $key => $data) {
				if (isset ($data['url']) && !empty ($data['url'])) {
					$url = get_the_author_meta ($data['field'], $user_id);
					if (isset ($url) && !empty ($url)) {
						$valid = filter_var ($url, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED);
						if (!$valid) {
							$invalid[$key] = $data;
						}
					}
				}
			}
			return $invalid;
		}

		/**
		 * "show_user_profile" and "edit_user_profile" action hooks; called to add fields to
		 * the admin user profile screen.
		 */

		function admin_add_profile_extensions ($user) {
			$content = array ();
			$hide_suppress_settings = false;
			$option = WP_Biographia::get_option ('wp_biographia_admin_hide_profiles');
			if (!empty ($option)) {
				$hidden_profiles = explode (',', $option);
				foreach ($user->roles as $role) {
					if (in_array ($role, $hidden_profiles)) {
						$hide_suppress_settings = true;
						break;
					}
				}	// end-foreach;
			}

			if (!$hide_suppress_settings) {
				$bio_excerpt = get_user_meta ($user->ID, 'wp_biographia_short_bio', true);
				if (!isset ($bio_excerpt) || empty ($bio_excerpt)) {
					$description = get_user_meta ($user->ID, 'description', true);
					if (isset ($description) && !empty ($description)) {
						$bio_excerpt = $description;
					}
					else {
						$bio_excerpt = '';
					}
				}

				$content[] = '<h3>' . __('Biography Options', 'wp-biographia') . '</h3>';
				$content[] = '<table class="form-table">';
				$content[] = '<tbody>';

				$content[] = '<tr>';
				$content[] = '<th>';
				$content[] = '<label for="wp_biographia_short_bio">' . __('Biographical Excerpt', 'wp-biographia') . '</label>';
				$content[] = '</th>';
				$content[] = '<td>';
				$content[] = '<textarea name="wp_biographia_short_bio" id="description" rows="5" cols="30">' . $bio_excerpt . '</textarea><br>';
				$content[] = '<span class="description">' . __('Share an excerpt of your biography which can be used by the WP Biographia shortcode, template tags, sidebar widget and configured to be used in place of the standard biography for differing template types.', 'wp-biographia') . '</span>';
				$content[] = '</td>';
				$content[] = '</tr>';

				$content[] = '<tr>';
				$content[] = '<th>';
				$content[] = '<label for="wp_biographia_suppress_posts">' . __('Hide The Biography Box On Posts', 'wp-biographia') . '</label>';
				$content[] = '</th>';
				$content[] = '<td>';
				$content[] = '<input type="checkbox" name="wp_biographia_suppress_posts" id="wp-biographia-suppress-posts" ' . checked (get_user_meta ($user->ID, 'wp_biographia_suppress_posts', true), 'on', false) . ' ' . disabled (current_user_can ('manage_options'), false, false) . ' />&nbsp;' . __('Don\'t show the Biography Box on your posts', 'wp-biographia');
				$content[] = '</td>';
				$content[] = '</tr>';
				$content[] = '<tr>';
				$content[] = '<th>';
				$content[] = '<label for="wp_biographia_suppress_pages">' . __('Hide The Biography Box On Pages', 'wp-biographia') . '</label>';
				$content[] = '</th>';
				$content[] = '<td>';
				$content[] = '<input type="checkbox" name="wp_biographia_suppress_pages" id="wp-biographia-suppress-pages" ' . checked (get_user_meta ($user->ID, 'wp_biographia_suppress_pages', true), 'on', false) . ' ' . disabled (current_user_can ('manage_options'), false, false) . '/>&nbsp;' . __('Don\'t show the Biography Box on your pages', 'wp-biographia');
				$content[] = '</td>';
				$content[] = '</tr>';
			}

			$content[] = '</tbody>';
			$content[] = '</table>';

			echo implode (PHP_EOL, $content);
		}

		/**
		 * "personal_options_update" and "edit_user_profile_update" action hook; called to
		 * save the plugin's extensions to the user profile.
		 */

		function admin_save_profile_extensions ($user_id) {
			update_user_meta ($user_id, 'wp_biographia_short_bio',
				$this->admin_option ('wp_biographia_short_bio'));

			$hide = false;
			$option = WP_Biographia::get_option ('wp_biographia_admin_hide_profiles');
			$user = get_userdata ($user_id);
			if (!empty ($option)) {
				$hidden_profiles = explode (',', $option);
				foreach ($user->roles as $role) {
					if (in_array ($role, $hidden_profiles)) {
						$hide = true;
						break;
					}
				}	// end-foreach;
			}

			if (!$hide) {
				update_user_meta ($user_id, 'wp_biographia_suppress_posts',
					$this->admin_option ('wp_biographia_suppress_posts'));
				update_user_meta ($user_id, 'wp_biographia_suppress_pages',
					$this->admin_option ('wp_biographia_suppress_pages'));
			}
		}

		/**
		 * "user_register" action hook; called immediately after a new user is registered and
		 * added to the database. If the user's role is in the list of excluded new user roles
		 * then set the 'wp_biographia_suppress_posts' and 'wp_biographia_suppress_pages' options
		 * in the user's metadata.
		 */

		function admin_user_register ($user_id) {
			$do_not_suppress = true;
			$option = WP_Biographia::get_option ('wp_biographia_admin_new_users');
			$user = get_userdata ($user_id);

			if (!empty ($option)) {
				$new_user_roles = explode (',', $option);
				foreach ($user->roles as $role) {
					if (in_array ($role, $new_user_roles)) {
						$do_not_suppress = false;
						break;
					}
				}	// end-foreach;
			}

			if (!$do_not_suppress) {
				update_user_meta ($user_id, 'wp_biographia_suppress_posts', 'on');
				update_user_meta ($user_id, 'wp_biographia_suppress_pages', 'on');
			}
		}

		/**
		 * "plugin_action_links_'plugin-name'" action hook; called to add a link to the plugin's
		 * settings/options panel.
		 */

		function admin_settings_link($links) {
			$settings_link = '<a href="' . $this->admin_get_options_url () . '">'
				. __('Settings', 'wp-biographia')
				. '</a>';
			array_unshift ($links, $settings_link);
			return $links;
		}

		/**
		 * Checks for the presence of a settings/options key and if not present, adds the
		 * key and its associated value.
		 *
		 * @param array settings Array containing the current set of settings/options
		 * @param string key Settings/options key; specified without the 'wp_biographia_' prefix
		 * @param stirng key Settings/options value for key
		 */

		function admin_upgrade_option (&$settings, $key, $value) {
			$kn = 'wp_biographia_' . $key;
			if (!isset ($settings[$kn])) {
				$settings[$kn] = $value;
			}
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

			$settings = WP_Biographia::get_option ();

			/*
			 * Bale out early if there's no need to check for the need to upgrade the configuration
			 * settings ...
			 */

			if (is_array ($settings) &&
					isset ($settings['wp_biographia_version']) &&
					$settings['wp_biographia_version'] == WP_Biographia::VERSION) {
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
				 *		wp_biographia_installed
				 *		wp_biographia_version = "01"
				 *		wp_biographia_alert_bg
				 *		wp_biographia_display_front
				 *		wp_biographia_display_archives
				 *		wp_biographia_display_posts
				 *		wp_biographia_display_pages
				 *		wp_biographia_display_feed
				 *		wp_biographia_alert_border
				 *		wp_biographia_content_prefix
				 *		wp_biographia_content_name
				 *		wp_biographia_content_image
				 *		wp_biographia_content_bio
				 *		wp_biographia_content_web
				 *		wp_biographia_content_twitter
				 *		wp_biographia_content_facebook
				 *		wp_biographia_content_linkedin
				 *		wp_biographia_content_googleplus
				 *		wp_biographia_content_posts
				 *
				 * v2.0 added configuration settings ...
				 *		wp_biographia_content_email = "on"
				 *		wp_biographia_content_image_size = "100"
				 *		wp_biographia_style_border (was wp_biographia_alert_border) = "top"
				 *		wp_biographia_style_bg (was wp_biographia_alert_bg) = "#FFEAA8"
				 *		wp_biographia_display_location = "bottom"
				 *		wp_biographia_page_exclusions (no default value)
				 *		wp_biographia_post_exclusions (no default value)
				 * v2.0 removed configuration settings
				 *		wp_biographia_alert_border (replaced by wp_biographia_style_border)
				 *		wp_biographia_alert_bg (replaced by wp_biographia_style_bg)
				 * v2.0 changed default configuration settings ...
				 *		wp_biographia_version = "20"
				 *
		         * v2.1 added configuration settings ...
		         *		wp_biographia_beta_enabled = ""
		         *		wp_biographia_suppress_posts = "" (user profile extension)
		         *		wp_biographia_suppress_pages = "" (user profile extension)
				 * v2.1 changed default configuration settings ...
				 *		wp_biographia_version = "21"
				 *
				 * v2.1.1 changed default configuration settings ...
				 *		wp_biographia_version = "211"
				 *
				 * v2.2 added configuration settings ...
				 *		wp_biographia_content_delicious = ""
				 *		wp_biographia_content_flickr = ""
				 *		wp_biographia_content_picasa = ""
				 *		wp_biographia_content_vimeo = ""
				 *		wp_biographia_content_youtube = ""
				 *		wp_biographia_content_reddit = ""
				 * v2.2 changed default configuration settings ...
				 *		wp_biographia_version = "22"
				 *
				 * v2.2.1 changed default configuration settings ...
				 * Note: v2.2.1 was a private beta and never formally released.
				 *		wp_biographia_version = "221"
				 *
				 * v2.3 changed default configuration settings ...
				 *		wp_biographia_version = "23"
				 *
				 * v2.4 added configuration settings ...
				 *		wp_biographia_content_authorpage = "on"
				 *		wp_biographia_content_icons = ""
				 *		wp_biographia_content_alt_icons = ""
				 *		wp_biographia_content_icon_url = ""
				 * v2.4 changed default configuration settings ...
				 *		wp_biographia_version = "24"
				 *
				 * v2.4.1 changed default configuration settings ...
				 * 		wp_biographia_version = "241"
				 * v2.4.2 changed default configuration settings ...
				 * 		wp_biographia_version = "242"
				 *
				 * v2.4.3 changed default configuration settings ...
				 *		wp_biographia_version = "243"
				 * v2.4.4 changed default configuration settings ...
				 *		wp_biographia_version = "244"
				 *
				 * v3.0 added configuration settings ...
				 *		wp_biographia_content_link_target = "_self"
				 *		wp_biographia_content_link_nofollow = ""
				 * v3.0 changed default configuration settings ...
				 *		wp_biographia_version = "30"
				 * v3.0 removed configuration settings
				 *		wp_biographia_beta_enabled
				 *		wp_biograpia_content_vimeo
				 *
				 * v3.0.1 changed default configuration settings ...
				 *		wp_biographia_version = "301"

				 * v3.1 changed default configuration settings ...
				 *		wp_biographia_version = "310"
				 * v3.1 added configuration settings ...
				 *		wp_biographia_admin_new_users = ""
				 * 		wp_biographia_admin_hide_profiles = ""
				 *		wp_biographia_category_exclusions = ""
				 *		wp_biographia_post_exclusions = ""
				 *		wp_biographia_global_post_exclusions = ""
				 *		wp_biographia_page_exclusions = ""
				 *		wp_biographia_admin_content_priority = "10"
				 *		wp_biographia_admin_excerpt_priority = "10"
				 *
				 * v3.2 changed default configuration settings ...
				 *		wp_biographia_version = "320"
				 * v3.2 added configuration settings ...
				 *		wp_biographia_display_front_posts = ""
				 *		wp_biographia_display_archives_posts = ""
				 *		wp_biographia_display_author_archives_posts = ""
				 *		wp_biographia_display_category_archives_posts = ""
				 *		wp_biographia_display_date_archives_posts = ""
				 *		wp_biographia_display_tag_archives_posts = ""
				 *		wp_biographia_sync_content_wpautop = ""
				 *		wp_biographia_sync_excerpt_wpautop = ""
				 * v3.2 removed configuration settings ...
				 *		wp_biographia_display_archives (replaced by wp_biographia_display_archive_posts)
				 *		wp_biographia_display_front (replaces by wp_biographia_display_front_posts)
				 *
				 * v3.2.1 changed default configuration settings ...
				 *		wp_biographia_version = "321"
				 *
				 * v3.3 changed default configuration settings ...
				 *		wp_biographia_version = "330"
				 * v3.3 added configuration settings ...
				 *		wp_biographia_admin_post_overrides = ""
				 *		wp_biographia_admin_links = array ()
				 *		wp_biographia_display_front_bio_posts = "full"
				 *		wp_biographia_display_archives_bio_posts = "full"
				 *		wp_biographia_display_author_archives_bio_posts = "full"
				 *		wp_biographia_display_category_archives_bio_posts = "full"
				 *		wp_biographia_display_date_archives_bio_posts = "full"
				 *		wp_biographia_display_tag_archives_bio_posts = "full"
				 *		wp_biographia_display_bio_posts = "full"
				 *		wp_biographia_display_bio_pages = "full"
				 *		wp_biographia_display_bio_feed = "full"
				 *		wp_biographia_admin_lock_to_loop = ""
				 *		wp_biographia_style_border_color = "#000000"
				 *
				 * v3.3.2 changed configuration settings ...
				 *		wp_biographia_version = "332"
				 * v3.3.2 added configuration settings ...
				 *		wp_biographia_display_type = 'both'
				 *		wp_biographia_design_type = 'classic'
				 *		wp_biographia_design_wrap = ''
				 */

				switch ($current_plugin_version) {
					case '00':
						$this->admin_upgrade_option ($settings, 'installed', 'on');
						$this->admin_upgrade_option ($settings, 'style_bg', '#FFFFFF');
						$this->admin_upgrade_option ($settings, 'style_border', 'top');
						$this->admin_upgrade_option ($settings, 'display_front', '');
						$this->admin_upgrade_option ($settings, 'display_archives', '');
						$this->admin_upgrade_option ($settings, 'display_posts', '');
						$this->admin_upgrade_option ($settings, 'display_pages', '');
						$this->admin_upgrade_option ($settings, 'display_feed', '');
						$this->admin_upgrade_option ($settings, 'content_prefix', 'About');
						$this->admin_upgrade_option ($settings, 'content_name', 'none');
						$this->admin_upgrade_option ($settings, 'content_image', '');
						$this->admin_upgrade_option ($settings, 'content_bio', '');
						$this->admin_upgrade_option ($settings, 'content_web', '');
						$this->admin_upgrade_option ($settings, 'content_twitter', '');
						$this->admin_upgrade_option ($settings, 'content_facebook', '');
						$this->admin_upgrade_option ($settings, 'content_linkedin', '');
						$this->admin_upgrade_option ($settings, 'content_googleplus', '');
						$this->admin_upgrade_option ($settings, 'content_posts', 'none');

					case '01':
						$this->admin_upgrade_option ($settings, 'content_email', '');
						$this->admin_upgrade_option ($settings, 'content_image_size', '100');

						if (isset ($settings['wp_biographia_alert_border'])) {
							$this->admin_upgrade_option ($settings, 'style_border',
							 						$settings['wp_biographia_alert_border']);
							unset ($settings['wp_biographia_alert_border']);
						}

						if (isset ($settings['wp_biographia_alert_bg'])) {
							$this->admin_upgrade_option ($settings, 'style_bg',
							 							$settings['wp_biographia_alert_bg']);
							unset ($settings['wp_biographia_alert_bg']);
						}

						$this->admin_upgrade_option ($settings, 'display_location', 'bottom');

					case '20':
						$users = WP_Biographia::get_users ();
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

					case '21':
					case '211':
					case '22':
						$this->admin_upgrade_option ($settings, 'content_delicious', '');
						$this->admin_upgrade_option ($settings, 'content_flickr', '');
						$this->admin_upgrade_option ($settings, 'content_picasa', '');
						$this->admin_upgrade_option ($settings, 'content_vimeo', '');
						$this->admin_upgrade_option ($settings, 'content_youtube', '');
						$this->admin_upgrade_option ($settings, 'content_reddit', '');

					case '221':
					case '23':
					case '24':
						$this->admin_upgrade_option ($settings, 'content_authorpage', 'on');
						$this->admin_upgrade_option ($settings, 'content_icons', '');
						$this->admin_upgrade_option ($settings, 'content_alt_icons', '');
						$this->admin_upgrade_option ($settings, 'content_icon_url', '');

					case '241':
					case '242':
					case '243':
					case '244':
					case '30':
						if (isset ($settings['wp_biographia_beta_enabled'])) {
							unset ($settings['wp_biographia_beta_enabled']);
						}
						$this->admin_upgrade_option ($settings, 'content_link_target', '_self');
						$this->admin_upgrade_option ($settings, 'content_link_nofollow', '');
						if (isset ($settings['wp_biograpia_content_vimeo'])) {
							$this->admin_upgrade_option ($settings, 'content_vimeo', '');
							unset ($settings['wp_biograpia_content_vimeo']);
						}

					case '301':
					case '310':
						$this->admin_upgrade_option ($settings, 'category_exclusions', '');
						$this->admin_upgrade_option ($settings, 'admin_new_users', '');
						$this->admin_upgrade_option ($settings, 'admin_hide_profiles', '');
						$this->admin_upgrade_option ($settings, 'post_exclusions', '');
						$this->admin_upgrade_option ($settings, 'global_post_exclusions', '');
						$this->admin_upgrade_option ($settings, 'page_exclusions', '');
						$this->admin_upgrade_option ($settings, 'admin_content_priority',
						 	WP_Biographia::PRIORITY);
						$this->admin_upgrade_option ($settings, 'admin_excerpt_priority',
							WP_Biographia::PRIORITY);

					case '320':
						if (isset ($settings['wp_biographia_display_front'])) {
							$this->admin_upgrade_option ($settings, 'display_front_posts',
					 							$settings['wp_biographia_display_front']);
							unset ($settings['wp_biographia_display_front']);
						}
						if (isset ($settings['wp_biographia_display_archives'])) {
							$option = $settings['wp_biographia_display_archives'];
							$this->admin_upgrade_option ($settings, 'display_archives_posts', $option);
							unset ($settings['wp_biographia_display_archives']);
							$this->admin_upgrade_option ($settings, 'display_author_archives_posts', $option);
							$this->admin_upgrade_option ($settings, 'display_category_archives_posts', $option);
							$this->admin_upgrade_option ($settings, 'display_date_archives_posts', $option);
							$this->admin_upgrade_option ($settings, 'display_tag_archives_posts', $option);
						}
						$this->admin_upgrade_option ($settings, 'sync_content_wpautop', '');
						$this->admin_upgrade_option ($settings, 'sync_excerpt_wpautop', '');

					case '321':
					case '330b1':
					case '330b2':
					case '330b3':
					case '330b4':
					case '330b5':
					case '330':
						$this->admin_upgrade_option ($settings, 'admin_post_overrides', '');

						$admin_links = array ();
						foreach (WP_Biographia::defaults () as $key => $data) {
							if (isset ($data['contactmethod']) && !empty ($data['contactmethod'])) {
								$admin_links[$key] = 'on';
							}
						}	// end-foreach (...)

						$this->admin_upgrade_option ($settings, 'admin_links', $admin_links);
						$this->admin_upgrade_option ($settings, 'display_front_bio_posts', 'full');
						$this->admin_upgrade_option ($settings, 'display_archives_bio_posts', 'full');
						$this->admin_upgrade_option ($settings, 'display_author_archives_bio_posts', 'full');
						$this->admin_upgrade_option ($settings, 'display_category_archives_bio_posts', 'full');
						$this->admin_upgrade_option ($settings, 'display_date_archives_bio_posts', 'full');
						$this->admin_upgrade_option ($settings, 'display_tag_archives_bio_posts', 'full');
						$this->admin_upgrade_option ($settings, 'display_bio_posts', 'full');
						$this->admin_upgrade_option ($settings, 'display_bio_pages', 'full');
						$this->admin_upgrade_option ($settings, 'display_bio_feed', 'full');
						$this->admin_upgrade_option ($settings, 'admin_lock_to_loop', '');
						$this->admin_upgrade_option ($settings, 'style_border_color', '#000000');

					case '331':
					case '332':
						$this->admin_upgrade_option ($settings, 'display_type', 'both');
						$this->admin_upgrade_option($settings, 'design_type', 'classic');
						$this->admin_upgrade_option($settings, 'design_wrap', '');

						$fields = array(0 => 'ID');
						$search = new WP_User_Query(array('fields' => $fields));
						$users = $search->get_results();
						$info = WP_Biographia::supported_contact_info();
						foreach ($users as $user) {
							$meta = get_user_meta($user->ID);

							foreach ($info as $key => $fields) {
								$method = $fields['field'];
								$pos = strpos($method, 'wpb_');
								if ($pos === 0) {
									$old_method = substr($method, $pos + strlen('wpb_'));
									$old_value = $meta[$old_method];
									if (delete_user_meta($user->ID, $old_method)) {
										add_user_meta($user->ID, $method, $old_value[0], true);
									}
								}
							}
						}

						$settings['wp_biographia_version'] = WP_Biographia::VERSION;
						$upgrade_settings = true;

					default:
						break;
				}	// end-switch

				if ($upgrade_settings) {
					$this->admin_clear_pointer ();
					update_option (WP_Biographia::OPTIONS, $settings);
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
			$bio_settings = array ();
			$role_settings = array ();
			$profile_settings = array ();
			$priority_settings = array ();
			$exclusion_settings = array ();
			$suppression_settings = array ();
			$category_settings = array ();
			$style_settings = array ();
			$content_settings = array ();
			$design_settings = array();
			$defaults_settings = array ();
			$colophon_content = array ();
			$config_settings = array ();
			$config_users = array ();

			$args = array (
				'public' => true,
				'_builtin' => false
			);
			$pts = get_post_types ($args, 'objects');

			$image_size = "";
			$avatars_enabled = (get_option ('show_avatars') == 1 ? true : false);
			$icons_enabled = ($settings['wp_biographia_content_icons'] == 'on' ? true : false);
			$alt_icons = ($settings['wp_biographia_content_alt_icons'] == 'on' ? true : false);

			$tab = $this->admin_validate_tab ();
			if ($tab === 'design') {
				require_once(WPBIOGRAPHIA_PATH . '/includes/wp-biographia-box.php');
			}

			// TODO: This function is getting out of hand; need to split the per tab content
			//       formatting into individual functions ...

			switch ($tab) {
				case 'admin':
					/****************************************************************************
				 	 * Admin tab content - 1) Automatically Exclude New Users By Role
				 	 */

					$role_settings[] = '<p><em>' . __('New User Settings allow you to configure globally whether a newly created user should have the Biography Box displayed under their posts or not. You can then control the display of the Biography Box on a per-user basis in the Exclusions tab.','wp-biographia') . '</em></p>';

					$editable_roles = get_editable_roles ();
					$roles_enabled = array ();
					$roles_excluded = array ();
					$role_list = explode (',', $settings['wp_biographia_admin_new_users']);

					foreach ($editable_roles as $role => $role_info) {
						if (in_array ($role, $role_list)) {
							$roles_excluded[$role] = $role_info['name'];
						}

						else {
							$roles_enabled[$role] = $role_info['name'];
						}
					}	// end-foreach (...)

					$role_settings[] = '<p><strong>' . __('Automatically Exclude New Users By Role', 'wp-biographia') . '</strong><br />';
					$role_settings[] = '<span class="wp-biographia-user-roles">';
					$role_settings[] = '<strong>' . __('Enabled Roles', 'wp-biographia') . '</strong><br />';
					$role_settings[] = '<select multiple id="wp-biographia-enabled-user-roles" name="wp-biographia-enabled-user-roles[]">';

					foreach ($roles_enabled as $role_name => $role_display) {
						$role_settings[] = '<option value="' . $role_name . '">' . $role_display . '</option>';
					}	// end-foreach (...)

					$role_settings[] = '</select>';
					$role_settings[] = '<a href="#" id="wp-biographia-user-role-add">' . __('Add', 'wp-biographia') . ' &raquo;</a>';
					$role_settings[] = '</span>';
					$role_settings[] = '<span class="wp-biographia-user-roles">';
					$role_settings[] = '<strong>' . __('Excluded Roles', 'wp-biographia') . '</strong><br />';
					$role_settings[] = '<select multiple id="wp-biographia-excluded-user-roles" name="wp-biographia-excluded-user-roles[]">';

					foreach ($roles_excluded as $role_name => $role_display) {
						$role_settings[] = '<option value="' . $role_name . '">' . $role_display . '</option>';
					}	// end-foreach (...)

					$role_settings[] = '</select>';
					$role_settings[] = '<a href="#" id="wp-biographia-user-role-rem">&laquo; ' . __('Remove', 'wp-biographia') . '</a>';
					$role_settings[] = '</span>';
					$role_settings[] = '<br />';
					$role_settings[] = '<div style="clear: both";><small>' . __('Select the roles for which new users should be automatically excluded from displaying the Biography Box. This setting only affects the creation of new users; individual users may be enabled to display the Biography Box on a per-user basis in the Exclusions Tab.', 'wp-biographia') . '</small></div></p>';

					/****************************************************************************
				 	 * Admin tab content - 2.1) Hide User Profile Settings By Role
				 	 */

					$profile_settings[] = '<p><em>' . __('If you want to stop users having the ability to stop the Biography Box being displayed on their posts and pages, you can control this according to the user\'s role below. An Administrator can still control the display of the Biography Box on a per-user basis in the Exclusions tab.', 'wp-biographia') . '</em></p>';

					$profiles_visible = array ();
					$profiles_hidden = array ();
					$profile_list = explode (',', $settings['wp_biographia_admin_hide_profiles']);

					foreach ($editable_roles as $role => $role_info) {
						if (in_array ($role, $profile_list)) {
							$profiles_hidden[$role] = $role_info['name'];
						}

						else {
							$profiles_visible[$role] = $role_info['name'];
						}
					}	// end-foreach (...)

					$profile_settings[] = '<p><strong>' . __('Hide Biography Box Settings In User Profiles by Role', 'wp-biographia') . '</strong><br />';
					$profile_settings[] = '<span class="wp-biographia-user-profiles">';
					$profile_settings[] = '<strong>' . __('Visible In Profiles', 'wp-biographia') . '</strong><br />';
					$profile_settings[] = '<select multiple id="wp-biographia-visible-profiles" name="wp-biographia-visible-profiles[]">';

					foreach ($profiles_visible as $role_name => $role_display) {
						$profile_settings[] = '<option value="' . $role_name . '">' . $role_display . '</option>';
					}	// end-foreach (...)

					$profile_settings[] = '</select>';
					$profile_settings[] = '<a href="#" id="wp-biographia-user-profile-add">' . __('Add', 'wp-biographia') . ' &raquo;</a>';
					$profile_settings[] = '</span>';
					$profile_settings[] = '<span class="wp-biographia-user-profiles">';
					$profile_settings[] = '<strong>' . __('Hidden In Profiles', 'wp-biographia') . '</strong><br />';
					$profile_settings[] = '<select multiple id="wp-biographia-hidden-profiles" name="wp-biographia-hidden-profiles[]">';

					foreach ($profiles_hidden as $role_name => $role_display) {
						$profile_settings[] = '<option value="' . $role_name . '">' . $role_display . '</option>';
					}	// end-foreach (...)

					$profile_settings[] = '</select>';
					$profile_settings[] = '<a href="#" id="wp-biographia-user-profile-rem">&laquo; ' . __('Remove', 'wp-biographia') . '</a>';
					$profile_settings[] = '</span>';
					$profile_settings[] = '<br />';
					$profile_settings[] = '<div style="clear: both";><small>' . __('Select the roles for users who should have the Biography Box hidden or visible in their user profile.', 'wp-biographia') . '</small></div></p>';

					/****************************************************************************
				 	 * Admin tab content - 2.2) Enable/Disable Contact Links
				 	 */

					$profile_settings[] = '<p><em>' . __('If you want to remove contact links from a user\'s profile you can do so below. Disabling a contact link removes it from the user\'s profile, from the Content tab and from the link being displayed in the Biography Box.', 'wp-biographia') . '</em></p>';

					foreach (WP_Biographia::defaults () as $key => $data) {
						if (isset ($data['contactmethod']) && !empty ($data['contactmethod'])) {
							$name = 'wp_biographia_admin_enable_' . $key;
							$id = 'wp-biographia-admin-enable-' . $key;
							$text = sprintf (__('Enable support for %s', 'wp-biographia'), $data['contactmethod']);
							if (isset ($settings['wp_biographia_admin_links'][$key]) && !empty ($settings['wp_biographia_admin_links'][$key])) {
								$checked = $settings['wp_biographia_admin_links'][$key];
							}
							else {
								$checked = false;
							}
							$profile_settings[] = '<p><input type="checkbox" name="' . $name . '" id="' . $id . '" ' . checked ($checked, 'on', false) . ' />
								<small>' . $text . '</small></p>';
						}
					}	// end-foreach (...)

					/****************************************************************************
				 	 * Admin tab content - 3) Set Post Content And Excerpt Priority
				 	 */

					$priority_settings[] = '<p><em>' . __('WP Biographia uses the WordPress <code>the_content</code> and <code>the_excerpt</code> filters to add the Biography Box to the start or the end of posts and excerpts. If another theme or plugin also adds content to the posts or excerpts, the Biography Box may not be displayed in the order you want. To prevent this happening, you can adjust the priority that WP Biographia uses when queuing the filters. A lower priority will cause the plugin\'s filters to fire earlier. A higher priority will cause the plugin\'s filters to fire later.', 'wp-biographia') . '</em></p>';

					$priority_settings[] = '<p><strong>' . __("Content Filter Priority", 'wp-biographia') . '</strong><br />
							<input type="text" name="wp_biographia_content_priority" id="wp_biographia_content_priority" value="' . $settings['wp_biographia_admin_content_priority'] . '" /><br />
							<small>' . __('Enter the priority to be used to display the Biography Box for the full content for posts, pages and custom post types, e.g. 10.', 'wp-biographia') . '</small></p>';

					$priority_settings[] = '<p><strong>' . __("Excerpt Filter Priority", 'wp-biographia') . '</strong><br />
						<input type="text" name="wp_biographia_excerpt_priority" id="wp_biographia_excerpt_priority" value="' . $settings['wp_biographia_admin_excerpt_priority'] . '" /><br />
						<small>' . __('Enter the priority to be used to display the Biography Box for the excerpt for posts, pages and custom post types, e.g. 10', 'wp-biographia') . '</small></p>';


					$priority_settings[] = '<div class="wp-biographia-warning">';
					$priority_settings[] = '<p>'
						. sprintf (__('A default WordPress install runs an automatic paragraph formatter (<a href="%s" target="_blank"><code>wpautop</code></a>) via the <code>the_content</code> and <code>the_excerpt</code> at the default filter priority of 10. See the WordPress Codex post on <a href="%s" target="_blank">How WordPress Processes Post Content</a> for more information on why this happens.', 'wp-biographia'), 'http://codex.wordpress.org/Function_Reference/wpautop', 'http://codex.wordpress.org/How_WordPress_Processes_Post_Content')
						. '</p>';
					$priority_settings[] = '<p>'
						. __('Lowering either the Content Filter Priority or the Excerpt Filter Priority to be a value below the default of 10, may result in the Biography Box being formatted incorrectly. This is because <code>wpautop</code> is now running after the Biography Box has been added to a post or an excerpt and is now changing the Biography Box output. To prevent this happening, WP Biographia can synchronise and lower the priority of <code>wpautop</code> being run via the_content or the_excerpt on your behalf to ensure it is run before the Biography Box is produced.', 'wp-biographia')
						. '</p>';
					$priority_settings[] = '<p>'
						. __('If you set the Content Filter Priority or the Excerpt Filter Priority to a value of 3 or lower, you may see unexpected formatting issues in your posts and pages caused by <code>wpautop</code> being run too early.', 'wp-biographia')
						. '</p>';
					$priority_settings[] = '</div>';

					$priority_settings[] = '<p><strong>' . __("Synchronise Automatic Paragraph Formatting For Content", 'wp-biographia') . '</strong><br /> 
							<input type="checkbox" name="wp_biographia_sync_content_wpautop" ' . checked ($settings['wp_biographia_sync_content_wpautop'], 'on', false) . ' />
							<small>' . __('Ensure Automatic Paragraph Formatting runs before producing the Biography Box for the full content on posts, pages and custom post types.', 'wp-biographia') . '</small></p>';
					$priority_settings[] = '<p><strong>' . __("Synchronise Automatic Paragraph Formatting For Excerpts", 'wp-biographia') . '</strong><br /> 
							<input type="checkbox" name="wp_biographia_sync_excerpt_wpautop" ' . checked ($settings['wp_biographia_sync_excerpt_wpautop'], 'on', false) . ' />
							<small>' . __('Ensure Automatic Paragraph Formatting runs before producing the Biography Box for the excerpt on posts, pages and custom post types.', 'wp-biographia') . '</small></p>';

					$priority_settings[] = '<div class="wp-biographia-warning">';
					$priority_settings[] = '<p>'
						. __('Some plugins and themes use the <code>the_content</code> or <code>the_excerpt</code> filters or secondary query loops to show content in the sidebar or in the footer. If you\'re seeing the Biography Box as part of a widget\'s output, in the footer or elsewhere, locking the plugin to operate within the context of the main WordPress Loop may stop this happening, depending on the specific set of plugins and theme being used.', 'wp-biographia')
						. '</p>';
					$priority_settings[] = '</div>';

					$priority_settings[] = '<p><strong>' . __('Lock Display Of The Biography Box To The Main Loop', 'wp-biographia') . '</strong><br />
						<input type="checkbox" name="wp_biographia_admin_lock_to_loop" ' . checked($settings['wp_biographia_admin_lock_to_loop'], 'on', false) . ' />
						<small>' . __('Restrict the plugin to operating on the post content or post excerpt only when in the main WordPress Loop', 'wp-biographia') . '</small></p>';

					/****************************************************************************
				 	 * Admin tab content - 4) Biography Box Settings
				 	 */

					$bio_settings[] = '<p><em>' . __('WP Biographia can allow limited guest post support; allowing the biography text and elements of the Biography Box to be over-ridden on a per post, custom post and page basis.', 'wp-biographia') . '</em></p>';

					$bio_settings[] = '<p><strong>' . __("Enable Post Specific Overrides", 'wp-biographia') . '</strong><br /> 
							<input type="checkbox" name="wp_biographia_admin_post_overrides" ' . checked ($settings['wp_biographia_admin_post_overrides'], 'on', false) . ' />
							<small>' . __('Allow users to override the biography and title elements of the Biography Box and to suppress the display of the user\'s avatar and contact links on a per post, per page or per custom post basis.', 'wp-biographia') . '</small></p>';

					/****************************************************************************
				 	 * End of Admin tab content
				 	 */
					break;

				case 'exclude':
					/****************************************************************************
				 	 * Exclusions tab content - 1) Exclusion Settings
				 	 */

					$exclusion_settings[] = '<p><em>' . __('If you want to stop the Biography Box being displayed on a single post, page or custom post type, you can do this here.', 'wp-biographia') . '</em></p>';

					$exclusion_settings[] = '<p><strong>' . __("Exclude From Single Posts (via Post ID)", 'wp-biographia') . '</strong><br />
							<input type="text" name="wp_biographia_post_exclusions" id="wp_biographia_post_exclusions" class="wp-biographia-exclusions-input" value="' . $settings['wp_biographia_post_exclusions'] . '" /><br />
							<small>' . __('Hides the Biography Box when a post is displayed using the Single Post Template. Enter the Post IDs to hide, comma separated with no spaces, e.g. 54,33,55', 'wp-biographia') . '</small></p>';

					$exclusion_settings[] = '<p><strong>' . __("Globally Exclude From Posts (via Post ID)", 'wp-biographia') . '</strong><br />
						<input type="text" name="wp_biographia_global_post_exclusions" id="wp_biographia_global_post_exclusions" class="wp-biographia-exclusions-input" value="' . $settings['wp_biographia_global_post_exclusions'] . '" /><br />
						<small>' . __('Hides the Biography Box whenever a post is displayed; singly, on archive pages or on the front page. Enter the Post IDs to globally hide, comma separated with no spaces, e.g. 54,33,55.', 'wp-biographia') . '</small></p>';

					foreach ($pts as $pt) {
						$key = 'wp_biographia_' . $pt->name . '_exclusions';
						$value = ($this->check_option ($settings, $key) ? $settings[$key] : '');
						$exclusion_settings[] = '<p><strong>' . sprintf (__('Exclude From Single %1$s (via %2$s ID)', 'wp-biographia'), $pt->labels->name, $pt->labels->singular_name) . '</strong><br />
							<input type="text" name="wp_biographia_' . $pt->name .'_exclusions" id="wp_biographia_'. $pt->name .'_exclusions" class="wp-biographia-exclusions-input" value="' . $value . '" /><br />
							<small>' . sprintf (__('Hides the Biography Box whenever a %1$s is displayed using the Single %1$s Template. Enter the %1$s IDs to hide, comma separated with no spaces, e.g. 54,33,55.', 'wp-biographia'), $pt->labels->singular_name) . '</small></p>';

						$key = 'wp_biographia_global_' . $pt->name . '_exclusions';
						$value = ($this->check_option ($settings, $key) ? $settings[$key] : '');
						$exclusion_settings[] = '<p><strong>' . sprintf (__('Globally Exclude From %1$s (via %2$s ID).', 'wp-biographia'), $pt->labels->name, $pt->labels->singular_name) . '</strong><br />
							<input type="text" name="wp_biographia_global_' . $pt->name . '_exclusions" id="wp_biographia_global_' . $pt->name . '_exclusions" class="wp-biographia-exclusions-input" value="' . $value . '" /><br />
							<small>' . sprintf (__('Hides the Biography Box whenever a %1$s is displayed; singly, on archives pages or on the front page. Enter the %1$s IDs to globally hide, comma separated with no spaces, e.g. 54,33,55.', 'wp-biographia'), $pt->labels->singular_name)  . '</small></p>';
					}

					$exclusion_settings[] = '<p><strong>' . __("Exclude Pages (via Page ID)", 'wp-biographia') . '</strong><br />
						<input type="text" name="wp_biographia_page_exclusions" id="wp_biographia_page_exclusions" class="wp-biographia-exclusions-input" value="' . $settings['wp_biographia_page_exclusions'] . '" /><br />
						<small>' . __('Hides the Biography Box when a page is displayed using the Page Template. Enter the Page IDs to hide, comma separated with no spaces, e.g. 54,33,55.', 'wp-biographia') . '</small></p>';

					/****************************************************************************
				 	 * Exclusions tab content - 2) User Suppression Settings
				 	 */

					$suppression_settings[] = '<p><em>' . __('If you want to stop the Biography Box being displayed on a single post or custom post type on a per-user basis, you can do this here.', 'wp-biographia') . '</em></p>';

					$users = WP_Biographia::get_users ();

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
					}	// end-foreach (...)

					$suppression_settings[] = '<p><strong>' . __('Hide The Biography Box On Posts For Specific Users', 'wp-biographia') . '</strong><br />';
					$suppression_settings[] = '<span class="wp-biographia-users">';
					$suppression_settings[] = '<strong>' . __('Enabled Users', 'wp-biographia') . '</strong><br />';
					$suppression_settings[] = '<select multiple id="wp-biographia-enabled-post-users" name="wp-biographia-enabled-post-users[]">';

					foreach ($post_enabled as $user_id => $user_login) {
						$suppression_settings[] = '<option value="' . $user_id . '">' . $user_login . '</option>';
					}	// end-foreach (...)

					$suppression_settings[] = '</select>';
					$suppression_settings[] = '<a href="#" id="wp-biographia-user-post-add">' . __('Add', 'wp-biographia') . ' &raquo;</a>';
					$suppression_settings[] = '</span>';
					$suppression_settings[] = '<span class="wp-biographia-users">';
					$suppression_settings[] = '<strong>' . __('Hidden Users', 'wp-biographia') . '</strong><br />';
					$suppression_settings[] = '<select multiple id="wp-biographia-suppressed-post-users" name="wp-biographia-suppressed-post-users[]">';

					foreach ($post_suppressed as $user_id => $user_login) {
						$suppression_settings[] = '<option value="' . $user_id . '">' . $user_login . '</option>';
					}	// end-foreach (...)

					$suppression_settings[] = '</select>';
					$suppression_settings[] = '<a href="#" id="wp-biographia-user-post-rem">&laquo; ' . __('Remove', 'wp-biographia') . '</a>';
					$suppression_settings[] = '</span>';
					$suppression_settings[] = '<br />';
					$suppression_settings[] = '<div style="clear: both";><small>' . __('Select the users who should not display the Biography Box on their authored posts. Selecting a user for hiding of the Biography Box affects all posts and custom post types by that user, on single post display, on archive pages and on the front page. This setting over-rides the individual user profile settings, providing the user has permission to edit their profile.', 'wp-biographia') . '</small></div></p>';

					$suppression_settings[] = '<p><strong>' . __('Hide The Biography Box On Pages For Specific Users', 'wp-biographia') . '</strong><br />';
					$suppression_settings[] = '<span class="wp-biographia-users">';
					$suppression_settings[] = '<strong>' . __('Enabled Users', 'wp-biographia') . '</strong><br />';
					$suppression_settings[] = '<select multiple id="wp-biographia-enabled-page-users" name="wp-biographia-enabled-page-users[]">';

					foreach ($page_enabled as $user_id => $user_login) {
						$suppression_settings[] = '<option value="' . $user_id . '">' . $user_login . '</option>';
					}	// end-foreach (...)

					$suppression_settings[] = '</select>';
					$suppression_settings[] = '<a href="#" id="wp-biographia-user-page-add">' . __('Add', 'wp-biographia') . ' &raquo;</a>';
					$suppression_settings[] = '</span>';
					$suppression_settings[] = '<span class="wp-biographia-users">';
					$suppression_settings[] = '<strong>' . __('Hidden Users', 'wp-biographia') . '</strong><br />';
					$suppression_settings[] = '<select multiple id="wp-biographia-suppressed-page-users" name="wp-biographia-suppressed-page-users[]">';

					foreach ($page_suppressed as $user_id => $user_login) {
						$suppression_settings[] = '<option value="' . $user_id . '">' . $user_login . '</option>';
					}	// end-foreach (...)

					$suppression_settings[] = '</select>';
					$suppression_settings[] = '<a href="#" id="wp-biographia-user-page-rem">&laquo; ' . __('Remove', 'wp-biographia') . '</a>
					</span>';
					$suppression_settings[] = '<br />';
					$suppression_settings[] = '<div style="clear: both";><small>' . __('Select the users who should not display the Biography Box on their authored pages. This setting over-rides the individual user profile settings, providing the user has permission to edit their profile.', 'wp-biographia') . '</small></div></p>';

					/****************************************************************************
				 	 * Exclusions tab content - 3) Category Suppression Settings
				 	 */

					$category_settings[] = '<p><em>' . __('If you want to stop the Biography Box being displayed on a single post or custom post type by Category, you can do this here.', 'wp-biographia') . '</em></p>';

					$categories = WP_Biographia::get_categories ();

					$categories_enabled = array ();
					$categories_excluded = array ();
					$cat_excluded = explode (',', $settings['wp_biographia_category_exclusions']);

					foreach ($categories as $cat) {
						if (in_array ($cat->cat_ID, $cat_excluded)) {
							$categories_excluded[$cat->cat_ID] = $cat->name;
						}

						else {
							$categories_enabled[$cat->cat_ID] = $cat->name;
						}
					}	// end-foreach (...)

					$category_settings[] = '<p><strong>' . __('Exclude By Category On Posts', 'wp-biographia') . '</strong><br />';
					$category_settings[] = '<span class="wp-biographia-categories">';
					$category_settings[] = '<strong>' . __('Enabled Categories', 'wp-biographia') . '</strong><br />';
					$category_settings[] = '<select multiple id="wp-biographia-enabled-categories" name="wp-biographia-enabled-categories[]">';

					foreach ($categories_enabled as $cat_id => $cat_name) {
						$category_settings[] = '<option value="' . $cat_id . '">' . $cat_name . '</option>';
					}	// end-foreach (...)

					$category_settings[] = '</select>';
					$category_settings[] = '<a href="#" id="wp-biographia-category-add">' . __('Add', 'wp-biographia') . ' &raquo;</a>';
					$category_settings[] = '</span>';
					$category_settings[] = '<span class="wp-biographia-categories">';
					$category_settings[] = '<strong>' . __('Excluded Categories', 'wp-biographia') . '</strong><br />';
					$category_settings[] = '<select multiple id="wp-biographia-excluded-categories" name="wp-biographia-excluded-categories[]">';

					foreach ($categories_excluded as $cat_id => $cat_name) {
						$category_settings[] = '<option value="' . $cat_id . '">' . $cat_name . '</option>';
					}	// end-foreach (...)

					$category_settings[] = '</select>';
					$category_settings[] = '<a href="#" id="wp-biographia-category-rem">&laquo; ' . __('Remove', 'wp-biographia') . '</a>';
					$category_settings[] = '</span>';
					$category_settings[] = '<br />';
					$category_settings[] = '<div style="clear: both";><small>' . __('Select the post categories that should not display the Biography Box. Selecting a category for exclusion of the Biography Box affects all posts of that category, on single post display, on archive pages and on the front page.', 'wp-biographia') . '</small></div></p>';

					/****************************************************************************
			 	 	 * End of Exclusions tab content
			 	 	 */
					break;

				case 'style':
					/****************************************************************************
				 	 * Style settings tab content
				 	 */

					$style_settings[] = '<p><em>' . __('This tab contains broad level settings to control how the Biography Box is styled; its background colour and border. The Biography Box is fully style-able but this needs knowledge of how to write CSS.', 'wp-biographia') . '</em></p>';

					$style_settings[] = '<p><strong>' . __("Box Background Color", 'wp-biographia') . '</strong><br /> 
								<input type="text" name="wp_biographia_style_bg" id="wp-biographia-background-color" value="' . $settings['wp_biographia_style_bg'] . '" />
								<a class="hide-if-no-js" href="#" id="wp-biographia-pick-background-color">' . __('Select a Color', 'wp-biographia') . '</a>
								<div id="wp-biographia-background-color-picker" style="z-index: 100; background:#eee; border:1px solid #ccc; position:absolute; display:none;"></div>
								<small>' . __('By default, the background color of the Biography Box is a yellowish tone.', 'wp-biographia') . '</small></p>';
					$style_settings[] = '<p><strong>' . __("Box Border", 'wp-biographia') . '</strong><br /> 
				                <select name="wp_biographia_style_border">
				                  <option value="top" ' .selected ($settings['wp_biographia_style_border'], 'top', false) . '>' . __('Thick Top Border', 'wp-biographia') . '</option>
				                  <option value="around" ' .selected ($settings['wp_biographia_style_border'], 'around', false) . '>' . __('Thin Surrounding Border', 'wp-biographia') . '</option>
				                  <option value="none" ' .selected ($settings['wp_biographia_style_border'], 'none', false) . '>' . __('No Border', 'wp-biographia') . '</option>
				                </select><br /><small>' . __('By default, a thick black line is displayed above the Biography Box.', 'wp-biographia') . '</small></p>';
					$style_settings[] = '<p><strong>' . __("Box Border Color", 'wp-biographia') . '</strong><br /> 
								<input type="text" name="wp_biographia_style_border_color" id="wp-biographia-border-color" value="' . $settings['wp_biographia_style_border_color'] . '" />
								<a class="hide-if-no-js" href="#" id="wp-biographia-pick-border-color">' . __('Select a Color', 'wp-biographia') . '</a>
								<div id="wp-biographia-border-color-picker" style="z-index: 100; background:#eee; border:1px solid #ccc; position:absolute; display:none;"></div>
								<small>' . __('By default, the border color of the Biography Box is black.', 'wp-biographia') . '</small></p>';

					/****************************************************************************
			 	 	* End of Style tab content
			 	 	*/
					break;

				case 'content':
					/****************************************************************************
				 	 * Content settings tab content
				 	 */

					$content_settings[] = '<p><em>' . __('This tab contains settings that control what information is and is not displayed within the Biography Box.', 'wp-biographia') . '</em></p>';

					$content_settings[] = '<p><strong>' . __("Biography Prefix", 'wp-biographia') . '</strong><br />
						<input type="text" name="wp_biographia_content_prefix" id="wp-biographia-content-name" size="40" value="'
						. $settings["wp_biographia_content_prefix"]
						. '" /><br />
						<small>' . __('Prefix text to be prepended to the user\'s name', 'wp-biographia') . '</small></p>';

					$content_settings[] = '<p><strong>' . __("User's Name", 'wp-biographia') . '</strong><br />
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
						<small>' . __('How you want to see the user\'s name displayed (if at all)', 'wp-biographia') . '</small></p>';

					$content_settings[] = '<p><strong>' . __('User\'s Name Link', 'wp-biographia') . '</strong><br/>
						<input type="checkbox" name="wp_biographia_content_authorpage" '
						.checked ($settings['wp_biographia_content_authorpage'], 'on', false)
						. '/>
						<small>' . __('Make user\'s name link to <em>More Posts By This User</em>', 'wp_biographia') . '</small></p>';

					if (!$avatars_enabled) {
						$content_settings[] = '<div class="wp-biographia-warning">'
							. sprintf (__('It looks like Avatars are not currently enabled; this means that the user\'s image won\'t be able to be displayed. If you want this to happen then go to <a href="%s">Settings &rsaquo; Discussions</a> and set Avatar Display to Show Avatars.', 'wp-biographia'), admin_url('options-discussion.php')) . '</div>';
					}

					$content_settings[] = '<p><strong>' . __("User's Image", 'wp-biographia') . '</strong><br />
						<input type="checkbox" name="wp_biographia_content_image" '
						. checked ($settings['wp_biographia_content_image'], 'on', false)
						. disabled ($avatars_enabled, false, false)
						. '/>
						<small>' . __('Display the user\'s image?', 'wp-biographia') . '</small></p>';

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
					$content_settings[] = '<p><strong>' . __("Show User's Biography", 'wp-biographia') . '</strong><br />
						<input type="checkbox" name="wp_biographia_content_bio" '
						. checked ($settings['wp_biographia_content_bio'], 'on', false)
						. '/>
							<small>' . __('Display the user\'s biography?', 'wp-biographia') . '</small></p>';

					$content_settings[] = '<p><strong>' . __("Show Contact Links As Icons", 'wp-biographia') . '</strong><br />
						<input type="checkbox" name="wp_biographia_content_icons" id="wp-biographia-content-icons" '
						. checked ($settings['wp_biographia_content_icons'], 'on', false)
						. '/>
						<small>' . __('Show the user\'s contact links as icons?', 'wp-biographia') . '</small></p>';

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

					$content_settings[] = '<p><strong>' . __("Opening Contact Links", 'wp-biographia') . '</strong><br />
			        	<select name="wp_biographia_content_link_target">
			        	<option value="_blank" ' .selected ($settings['wp_biographia_content_link_target'], '_blank', false) . '>' . __('Open contact links in a new window or tab', 'wp-biographia') . '</option>
				        <option value="_self" ' .selected ($settings['wp_biographia_content_link_target'], '_self', false) . '>' . __('Open contact links in the same frame', 'wp-biographia') . '</option>
				        <option value="_parent" ' .selected ($settings['wp_biographia_content_link_target'], '_parent', false) . '>' . __('Open contact links in the parent frame', 'wp-biographia') . '</option>
						<option value="_top" ' .selected ($settings['wp_biographia_content_link_target'], '_top', false) . '>' . __('Open contact links in the full body of the window', 'wp-biographia') . '</option>
				        </select><br /><small>' . __('Select where to open contact links.', 'wp-biographia') . '</small></p>';

					$content_settings[] = '<p><strong>' . __("Don't Follow Contact Links", 'wp-biographia') . '</strong><br />
					<input type="checkbox" name="wp_biographia_content_link_nofollow" '
					. checked ($settings['wp_biographia_content_link_nofollow'], 'on', false)
					. '/>
					<small>' . __('Add <em>rel="nofollow"</em> to contact links?', 'wp-biographia') . '</small></p>';

					$content_settings[] = '<p><strong>' . __("Show User's Email Address", 'wp-biographia') . '</strong><br />
						<input type="checkbox" name="wp_biographia_content_email" '
						. checked ($settings['wp_biographia_content_email'], 'on', false)
						. '/>
						<small>' . __('Display the user\'s email address?', 'wp-biographia') . '</small></p>';

					$content_settings[] = '<p><strong>' . __("Show User's Website Link", 'wp-biographia') . '</strong><br />
						<input type="checkbox" name="wp_biographia_content_web" '
						. checked ($settings['wp_biographia_content_web'], 'on', false)
						. '/>
						<small>' . __('Display the user\'s website details?', 'wp-biographia') . '</small></p>';

					$links = $settings['wp_biographia_admin_links'];
					foreach (WP_Biographia::defaults () as $key => $data) {
						if (isset ($data['contactmethod']) && !empty ($data['contactmethod']) &&
								isset ($links[$key]) && $links[$key] == 'on') {
							$name = 'wp_biographia_content_' . $key;
							$id = 'wp-biographia-content-' . $key;
							$title = sprintf (__('Show User\'s %s Link', 'wp-biographia'), $data['contactmethod']);
							$descr = sprintf (__('Display the user\'s %s details?', 'wp-biographia'), $data['contactmethod']);
							$checked = (isset ($settings[$name]) ? $settings[$name] : '');

							$content_settings[] = '<p><strong>' . $title . '</strong><br />
								<input type="checkbox" name="' . $name . '" id="' . $id . '"'
								. checked ($checked, 'on', false)
								. '/>
								<small>' . $descr . '</small></p>';
						}
					}	// end-foreach ($this->defaults () ... )

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
						<small>' . __('How you want to display and format the <em>More Posts By This User</em> link', 'wp-biographia') . '</small></p>';

					/****************************************************************************
			 	 	 * End of Content tab content
			 	 	 */
					break;

				case 'design':
					/****************************************************************************
				 	 * Design settings tab content
				 	 */

					$design_settings[] = '<p><strong>' . __('Design Type', 'wp_biographia') . '</strong><br />
					<input type="radio" name="wp_biographia_design_type" id="wp-biographia-design-type" value="classic" '
					. checked ($settings['wp_biographia_design_type'], 'classic', false)
					. ' />&nbsp;<small>' . __('Classic design type', 'wp-biographia') . '</small><br />
					<input type="radio" name="wp_biographia_design_type" id="wp-biographia-design-type" value="responsive" '
					. checked ($settings['wp_biographia_design_type'], 'responsive', false)
					. ' />&nbsp;<small>' . __('Responsive design type', 'wp-biographia') . '</small><br />
					<input type="radio" name="wp_biographia_design_type" id="wp-biographia-design-type" value="custom" '
					. checked ($settings['wp_biographia_design_type'], 'custom', false)
					. ' />&nbsp;<small>' . __('Custom design type', 'wp-biographia') . '</small><br />';

					$design_settings[] = '<p><strong>' . __("Wrap Biography Text", 'wp-biographia') . '</strong><br />
						<input type="checkbox" name="wp_biographia_design_wrap" '
						. checked ($settings['wp_biographia_design_wrap'], 'on', false)
						. '/>
						<small>' . __('Wrap the biography text around the user\'s avatar?', 'wp-biographia') . '</small></p>';
						
					$design_settings[] = '<div id="wp-biographia-design-classic"></div>';
					$design_settings[] = '<div id="wp-biographia-design-responsive"></div>';
					$design_settings[] = '<div id="wp-biographia-design-custom"></div>';
					
					$design_settings[] = '<div id="wp-biographia-design-preview">';
					global $current_user;
					get_currentuserinfo();
					$mode = 'raw';
					$design_settings[] = wpb_get_biography_box($mode, $current_user->user_login);
					$design_settings[] = '</div>';

					/****************************************************************************
			 	 	 * End of Design tab content
			 	 	 */
					break;

				case 'defaults':
					/****************************************************************************
			 	 	 * Defaults settings tab content
			 	 	 */

					$defaults_settings[] = '<p><em>' . __('<strong>Here Be Dragons</strong>. Please <strong>read</strong> the warning below before doing anything with this tab. The options in this tab with reset WP Biographia to a just installed state, clearing any configuration settings you may have made.', 'wp-biographia') . '</em></p>';

					$defaults_settings[] = '<p><strong>' . __('Reset WP Biographia To Defaults', 'wp-biographia') . '</strong><br />
						<input type="checkbox" name="wp_biographia_reset_defaults" />
						<small>' . __('Reset all WP Biographia settings and options to their default values.', 'wp-biographia') . '</small></p>';
					$defaults_settings[] = '<p>';
					$defaults_settings[] = sprintf (__('<strong>WARNING!</strong> Checking <strong><em>%s</em></strong> and clicking on <strong><em>%s</em></strong> will erase <strong><em>all</em></strong> the current WP Biographia settings and options and will restore WP Biographia to a <em>just installed</em> state. This is the equivalent to deactivating, uninstalling and reinstalling the plugin. Only proceed if this is what you intend to do. This action is final and irreversable.', 'wp-biographia'), __('Reset WP Biographia To Defaults', 'wp-biographia'), __('Save Changes', 'wp-biographia'));
					$defaults_settingsp[] = '</p>';

					/****************************************************************************
				 	 * End of Defaults tab content
				 	 */
					break;

				case 'colophon':
					/****************************************************************************
				 	 * Colophon tab content - 1) Colophon Display
				 	 */

					$colophon_content[] = '<p><em>' . __('"When it comes to software, I much prefer free software, because I have very seldom seen a program that has worked well enough for my needs and having sources available can be a life-saver"</em>&nbsp;&hellip;&nbsp;Linus Torvalds', 'wp-biographia') . '</p><p>';
					$colophon_content[] = __('For the inner nerd in you, the latest version of WP Biographia was written using <a href="http://macromates.com/">TextMate</a> on a MacBook Pro running OS X 10.7.2 Lion and tested on the same machine running <a href="http://mamp.info/en/index.html">MAMP</a> (Mac/Apache/MySQL/PHP) before being let loose on the author\'s <a href="http://www.vicchi.org/">blog</a>.', 'wp-biographia');
					$colophon_content[] = '</p><p>';
					$colophon_content[] = __('The official home for WP Biographia is on <a href="http://www.vicchi.org/codeage/wp-biographia/">Gary\'s Codeage</a>; it\'s also available from the official <a href="http://wordpress.org/extend/plugins/wp-biographia/">WordPress plugins repository</a>. If you\'re interested in what lies under the hood, the code is also on <a href="https://github.com/vicchi/wp-biographia">GitHub</a> to download, fork and otherwise hack around.', 'wp-biographia');
					$colophon_content[] = '</p><p>';
					$colophon_content[] = __('WP Biographia is named after the etymology of the modern English word <em>biography</em>. The word first appeared in the 1680s, probably from the latin <em>biographia</em> which itself derived from the Greek <em>bio</em>, meaning "life" and <em>graphia</em>, meaning "record" or "account" which derived from <em>graphein</em>, "to write".', 'wp-biographia');
					$colophon_content[] = '</p><p><small>Dictionary.com, "biography," in <em>Online Etymology Dictionary</em>. Source location: Douglas Harper, Historian. <a href="http://dictionary.reference.com/browse/biography">http://dictionary.reference.com/browse/biography</a>. Available: <a href="http://dictionary.reference.com">http://dictionary.reference.com</a>. Accessed: July 27, 2011.</small></p>';

					/****************************************************************************
				 	 * Colophon tab content - 2) Plugin Configuration Settings
				 	 */

					$config_settings[] = '<p>';
					$config_settings[] = __('For those times when you need help and support with this plugin, one of the first things you\'ll probably be asked for is the plugin\'s current configuration. If this happens, just <em>copy-and-paste</em> the dump of the <em>WP Biographia Settings and Options</em> below into any support forum post or email.', 'wp-biographia');
					$config_settings[] = '</p>';

					$config_settings[] = '<pre>';
					$config_settings[] = print_r ($settings, true);
					$config_settings[] = '</pre>';

					$users = WP_Biographia::get_users ();
					$debug_users = array ();

					foreach ($users as $user) {
						$debug_users[$user->ID] = array (
							'ID' => $user->ID,
							'user_login' =>$user->user_login,
							'wp_biographia_suppress_posts' => get_user_meta (
								$user->ID,
								'wp_biographia_suppress_pages',
								true),
							'wp_biographia_suppress_pages' => get_user_meta (
								$user->ID,
								'wp_biographia_suppress_pages',
								true)
							);
					}

					/****************************************************************************
				 	 * Colophon tab content - 3) User Configuration Settings
				 	 */

					$config_users[] = '<p>';
					$config_users[] = __('Almost all of WP Biographia\'s Settings and Options are maintained in the database in a single entry. But there\'s also some settings added to each user\'s account; you\'ll find these below.', 'wp-biographia');
					$config_users[] = '</p>';

					$config_users[] = '<pre>';
					$config_users[] = print_r ($debug_users, true);
					$config_users[] = '</pre>';

					/****************************************************************************
				 	 * End of Colophon tab content
				 	 */
					break;

				case 'display':
				default:
					/****************************************************************************
			 	 	 * Display settings tab content
			 	 	 */

					$archives_enabled = ($settings['wp_biographia_display_archives_posts'] == 'on' ? true : false);

					$display_settings[] = '<p><em>' . __('This tab contains broad level settings to control how the Biography Box is displayed and where. You can configure more specific display settings in the Exclusions tab and what is actually displayed in the Biography Box in the Content tab.', 'wp-biographia') . '</em></p>';

					$display_settings[] = '<p><strong>' . __("Display On Front Page", 'wp-biographia') . '</strong><br /> 
						<input type="checkbox" name="wp_biographia_display_front_posts" ' . checked ($settings['wp_biographia_display_front_posts'], 'on', false) . ' id="wp-biographia-display-front-posts" />
						<small>' . __('Displays the Biography Box for each post on the front page.', 'wp-biographia') . '</small></p>';

					$display_settings[] = '<div id="wp-biographia-front-bio-wrapper"';
					if ($settings['wp_biographia_display_front_posts'] != 'on') {
						$display_settings[] = ' style="display:none;"';
					}
					$display_settings[] = '>';

					$full_id = 'wp-biographia-display-front-bio-full';
					$excerpt_id = 'wp-biographia-display-front-bio-excerpt';

					$display_settings[] = '<p><strong>' . __("Front Page Biography Text", 'wp-biographia') . '</strong><br />
						<input type="radio" name="wp_biographia_display_front_bio_posts" id="' . $full_id . '" value="full" '
						. checked ($settings['wp_biographia_display_front_bio_posts'], 'full', false)
						.' />&nbsp;<small>' . __('Display the full text of the user\'s biography', 'wp-biographia') . '</small><br />
						<input type="radio" name="wp_biographia_display_front_bio_posts" id="' . $excerpt_id . '" value="excerpt" '
						. checked ($settings['wp_biographia_display_front_bio_posts'], 'excerpt', false)
						. ' />&nbsp;<small>' . __('Display the excerpt of the user\'s biography', 'wp-biographia') . '</small></p>';
					$display_settings[] = '</div>';

					$display_settings[] = '<p><strong>' . __("Display On Individual Posts", 'wp-biographia') . '</strong><br /> 
							<input type="checkbox" name="wp_biographia_display_posts" ' . checked ($settings['wp_biographia_display_posts'], 'on', false) . ' id="wp-biographia-display-posts" />
							<small>' . __('Displays the Biography Box for individual posts.', 'wp-biographia') . '</small></p>';

					$display_settings[] = '<div id="wp-biographia-posts-bio-wrapper"';
					if ($settings['wp_biographia_display_posts'] != 'on') {
						$display_settings[] = ' style="display:none;"';
					}
					$display_settings[] = '>';

					$full_id = 'wp-biographia-display-posts-bio-full';
					$excerpt_id = 'wp-biographia-display-posts-bio-excerpt';

					$display_settings[] = '<p><strong>' . __("Individual Posts Biography Text", 'wp-biographia') . '</strong><br />
						<input type="radio" name="wp_biographia_display_bio_posts" id="' . $full_id . '" value="full" '
						. checked ($settings['wp_biographia_display_bio_posts'], 'full', false)
						.' />&nbsp;<small>' . __('Display the full text of the user\'s biography', 'wp-biographia') . '</small><br />
						<input type="radio" name="wp_biographia_display_bio_posts" id="' . $excerpt_id . '" value="excerpt" '
						. checked ($settings['wp_biographia_display_bio_posts'], 'excerpt', false)
						. ' />&nbsp;<small>' . __('Display the excerpt of the user\'s biography', 'wp-biographia') . '</small></p>';
					$display_settings[] = '</div>';

					$display_settings[] = '<p><strong>' . __("Display On All Post Archives", 'wp-biographia') . '</strong><br /> 
							<input type="checkbox" name="wp_biographia_display_archives_posts" ' . checked ($settings['wp_biographia_display_archives_posts'], 'on', false) . ' id="wp-biographia-display-archives-posts" />
							<small>' . __('Displays the Biography Box for each post on <strong>all types</strong> of Archive page (Author, Category, Date and Tag)', 'wp-biographia') . '</small></p>';

					$display_settings[] = '<div id="wp-biographia-archives-bio-wrapper"';
					if (!$archives_enabled) {
						$display_settings[] = ' style="display:none;"';
					}
					$display_settings[] = '>';

					$full_id = 'wp-biographia-display-archives-bio-full';
					$excerpt_id = 'wp-biographia-display-archives-bio-excerpt';

					$display_settings[] = '<p><strong>' . __("All Post Archives Biography Text", 'wp-biographia') . '</strong><br />
						<input type="radio" name="wp_biographia_display_archives_bio_posts" id="' . $full_id . '" value="full" '
						. checked ($settings['wp_biographia_display_archives_bio_posts'], 'full', false)
						.' />&nbsp;<small>' . __('Display the full text of the user\'s biography', 'wp-biographia') . '</small><br />
						<input type="radio" name="wp_biographia_display_archives_bio_posts" id="' . $excerpt_id . '" value="excerpt" '
						. checked ($settings['wp_biographia_display_archives_bio_posts'], 'excerpt', false)
						. ' />&nbsp;<small>' . __('Display the excerpt of the user\'s biography', 'wp-biographia') . '</small></p>';
					$display_settings[] = '</div>';

					$display_settings[] = '<div id="wp-biographia-archive-posts-container"';
					if ($archives_enabled) {
						$display_settings[] = ' style="display:none"';
					}
					$display_settings[] = '>';

					$display_settings[] = '<p><strong>' . __("Display On Author Archives", 'wp-biographia') . '</strong><br /> 
							<input type="checkbox" name="wp_biographia_display_author_archives_posts" ' . checked ($settings['wp_biographia_display_author_archives_posts'], 'on', false) . ' id="wp-biographia-display-author-archives-posts" />
							<small>' . __('Displays the Biography Box for each post on Author Archive pages.', 'wp-biographia') . '</small></p>';

					$display_settings[] = '<div id="wp-biographia-author-bio-wrapper"';
					if ($settings['wp_biographia_display_author_archives_posts'] != 'on') {
						$display_settings[] = ' style="display:none;"';
					}
					$display_settings[] = '>';

					$full_id = 'wp-biographia-display-author-archives-bio-full';
					$excerpt_id = 'wp-biographia-display-author-archives-bio-excerpt';

					$display_settings[] = '<p><strong>' . __("Author Archive Posts Biography Text", 'wp-biographia') . '</strong><br />
						<input type="radio" name="wp_biographia_display_author_archives_bio_posts" id="' . $full_id . '" value="full" '
						. checked ($settings['wp_biographia_display_author_archives_bio_posts'], 'full', false)
						.' />&nbsp;<small>' . __('Display the full text of the user\'s biography', 'wp-biographia') . '</small><br />
						<input type="radio" name="wp_biographia_display_author_archives_bio_posts" id="' . $excerpt_id . '" value="excerpt" '
						. checked ($settings['wp_biographia_display_author_archives_bio_posts'], 'excerpt', false)
						. ' />&nbsp;<small>' . __('Display the excerpt of the user\'s biography', 'wp-biographia') . '</small></p>';
					$display_settings[] = '</div>';

					$display_settings[] = '<p><strong>' . __("Display On Category Archives", 'wp-biographia') . '</strong><br /> 
							<input type="checkbox" name="wp_biographia_display_category_archives_posts" ' . checked ($settings['wp_biographia_display_category_archives_posts'], 'on', false) . ' id="wp-biographia-display-category-archives-posts" />
							<small>' . __('Displays the Biography Box for each post on Category Archive pages.', 'wp-biographia') . '</small></p>';

					$display_settings[] = '<div id="wp-biographia-category-bio-wrapper"';
					if ($settings['wp_biographia_display_category_archives_posts'] != 'on') {
						$display_settings[] = ' style="display:none;"';
					}
					$display_settings[] = '>';

					$full_id = 'wp-biographia-display-category-archives-bio-full';
					$excerpt_id = 'wp-biographia-display-category-archives-bio-excerpt';

					$display_settings[] = '<p><strong>' . __("Category Archive Posts Biography Text", 'wp-biographia') . '</strong><br />
						<input type="radio" name="wp_biographia_display_category_archives_bio_posts" id="' . $full_id . '" value="full" '
						. checked ($settings['wp_biographia_display_category_archives_bio_posts'], 'full', false)
						.' />&nbsp;<small>' . __('Display the full text of the user\'s biography', 'wp-biographia') . '</small><br />
						<input type="radio" name="wp_biographia_display_category_archives_bio_posts" id="' . $excerpt_id . '" value="excerpt" '
						. checked ($settings['wp_biographia_display_category_archives_bio_posts'], 'excerpt', false)
						. ' />&nbsp;<small>' . __('Display the excerpt of the user\'s biography', 'wp-biographia') . '</small></p>';
					$display_settings[] = '</div>';

					$display_settings[] = '<p><strong>' . __("Display On Date Archives", 'wp-biographia') . '</strong><br /> 
							<input type="checkbox" name="wp_biographia_display_date_archives_posts" ' . checked ($settings['wp_biographia_display_date_archives_posts'], 'on', false) . ' id="wp-biographia-display-date-archives-posts" />
							<small>' . __('Displays the Biography Box for each post on Date Archive pages.', 'wp-biographia') . '</small></p>';

					$display_settings[] = '<div id="wp-biographia-date-bio-wrapper"';
					if ($settings['wp_biographia_display_date_archives_posts'] != 'on') {
						$display_settings[] = ' style="display:none;"';
					}
					$display_settings[] = '>';

					$full_id = 'wp-biographia-display-date-archives-bio-full';
					$excerpt_id = 'wp-biographia-display-date-archives-bio-excerpt';

					$display_settings[] = '<p><strong>' . __("Date Archive Posts Biography Text", 'wp-biographia') . '</strong><br />
						<input type="radio" name="wp_biographia_display_date_archives_bio_posts" id="' . $full_id . '" value="full" '
						. checked ($settings['wp_biographia_display_date_archives_bio_posts'], 'full', false)
						.' />&nbsp;<small>' . __('Display the full text of the user\'s biography', 'wp-biographia') . '</small><br />
						<input type="radio" name="wp_biographia_display_date_archives_bio_posts" id="' . $excerpt_id . '" value="excerpt" '
						. checked ($settings['wp_biographia_display_date_archives_bio_posts'], 'excerpt', false)
						. ' />&nbsp;<small>' . __('Display the excerpt of the user\'s biography', 'wp-biographia') . '</small></p>';
					$display_settings[] = '</div>';

					$display_settings[] = '<p><strong>' . __("Display On Tag Archives", 'wp-biographia') . '</strong><br /> 
							<input type="checkbox" name="wp_biographia_display_tag_archives_posts" ' . checked ($settings['wp_biographia_display_tag_archives_posts'], 'on', false) . ' id="wp-biographia-display-tag-archives-posts" />
							<small>' . __('Displays the Biography Box for each post on Tag Archive pages.', 'wp-biographia') . '</small></p>';

					$display_settings[] = '<div id="wp-biographia-tag-bio-wrapper"';
					if ($settings['wp_biographia_display_tag_archives_posts'] != 'on') {
						$display_settings[] = ' style="display:none;"';
					}
					$display_settings[] = '>';

					$full_id = 'wp-biographia-display-tag-archives-bio-full';
					$excerpt_id = 'wp-biographia-display-tag-archives-bio-excerpt';

					$display_settings[] = '<p><strong>' . __("Tag Archive Posts Biography Text", 'wp-biographia') . '</strong><br />
						<input type="radio" name="wp_biographia_display_tag_archives_bio_posts" id="' . $full_id . '" value="full" '
						. checked ($settings['wp_biographia_display_tag_archives_bio_posts'], 'full', false)
						.' />&nbsp;<small>' . __('Display the full text of the user\'s biography', 'wp-biographia') . '</small><br />
						<input type="radio" name="wp_biographia_display_tag_archives_bio_posts" id="' . $excerpt_id . '" value="excerpt" '
						. checked ($settings['wp_biographia_display_tag_archives_bio_posts'], 'excerpt', false)
						. ' />&nbsp;<small>' . __('Display the excerpt of the user\'s biography', 'wp-biographia') . '</small></p>';
					$display_settings[] = '</div>';

					$display_settings[] = '</div>';

					$display_settings[] = '<p><strong>' . __("Display On Individual Pages", 'wp-biographia') . '</strong><br /> 
							<input type="checkbox" name="wp_biographia_display_pages" ' . checked ($settings['wp_biographia_display_pages'], 'on', false) . ' id="wp-biographia-display-pages" />
							<small>' . __('Displays the Biography Box for individual pages.', 'wp-biographia') . '</small></p>';

					$display_settings[] = '<div id="wp-biographia-pages-bio-wrapper"';
					if ($settings['wp_biographia_display_pages'] != 'on') {
						$display_settings[] = ' style="display:none;"';
					}
					$display_settings[] = '>';

					$full_id = 'wp-biographia-display-pages-bio-full';
					$excerpt_id = 'wp-biographia-display-pages-bio-excerpt';

					$display_settings[] = '<p><strong>' . __("Individual Pages Biography Text", 'wp-biographia') . '</strong><br />
						<input type="radio" name="wp_biographia_display_bio_pages" id="' . $full_id . '" value="full" '
						. checked ($settings['wp_biographia_display_bio_pages'], 'full', false)
						.' />&nbsp;<small>' . __('Display the full text of the user\'s biography', 'wp-biographia') . '</small><br />
						<input type="radio" name="wp_biographia_display_bio_pages" id="' . $excerpt_id . '" value="excerpt" '
						. checked ($settings['wp_biographia_display_bio_pages'], 'excerpt', false)
						. ' />&nbsp;<small>' . __('Display the excerpt of the user\'s biography', 'wp-biographia') . '</small></p>';
					$display_settings[] = '</div>';

					foreach ($pts as $pt) {
						$name = 'wp_biographia_display_' . $pt->name;
						$id = 'wp-biographia-custom-display-' . $pt->name;
						$value = ($this->check_option ($settings, $name) ? $settings[$name] : '');

						$display_settings[] = '<p><strong>' . sprintf (__('Display On Individual %s', 'wp-biographia'), $pt->labels->name) . '</strong><br /> 
								<input type="checkbox" name="' . $name . '" ' . checked ($value, 'on', false) . ' id="' . $id . '" />
								<small>' . sprintf (__('Displays the Biography Box on individual instances of custom post type %s.', 'wp-biographia'), $pt->labels->name) . '</small></p>';

						$id = 'wp-biographia-custom-' . $pt->name . '-bio-wrapper';
						$display_settings[] = '<div id="' . $id . '"';
						if ($value != 'on') {
							$display_settings[] = ' style="display:none;"';
						}
						$display_settings[] = '>';

						$name = 'wp_biographia_display_bio_' . $pt->name;
						$full_id = 'wp-biographia-display-' . $pt->name . '-bio-full';
						$excerpt_id = 'wp-biographia-display-' . $pt->name . '-bio-excerpt';
						$value = ($this->check_option ($settings, $name) ? $settings[$name] : 'full');

						$display_settings[] = '<p><strong>' . sprintf (__("Individual %s Biography Text", 'wp-biographia'), $pt->labels->name) . '</strong><br />
							<input type="radio" name="' . $name . '" id="' . $full_id . '" value="full" '
							. checked ($value, 'full', false)
							.' />&nbsp;<small>' . __('Display the full text of the user\'s biography', 'wp-biographia') . '</small><br />
							<input type="radio" name="' . $name . '" id="' . $excerpt_id . '" value="excerpt" '
							. checked ($value, 'excerpt', false)
							. ' />&nbsp;<small>' . __('Display the excerpt of the user\'s biography', 'wp-biographia') . '</small></p>';
						$display_settings[] = '</div>';

						$name = 'wp_biographia_display_archives_' . $pt->name;
						$id = 'wp-biographia-custom-display-archives-' . $pt->name;
						$value = ($this->check_option ($settings, $name) ? $settings[$name] : '');

						$display_settings[] = '<p><strong>' . sprintf (__('Display On %s Archives', 'wp-biographia'), $pt->labels->singular_name) . '</strong><br /> 
								<input type="checkbox" name="' . $name . '" ' . checked ($value, 'on', false) . ' id="' . $id . '" />
								<small>' . sprintf (__('Displays the Biography Box on Archive pages for custom post type %s.', 'wp-biographia'), $pt->labels->name) . '</small></p>';	

						$id = 'wp-biographia-custom-archives-' . $pt->name . '-bio-wrapper';
						$display_settings[] = '<div id="' . $id . '"';
						if ($value != 'on') {
							$display_settings[] = ' style="display:none;"';
						}
						$display_settings[] = '>';

						$name = 'wp_biographia_display_archives_bio_' . $pt->name;
						$full_id = 'wp-biographia-display-' . $pt->name . '-archives-bio-full';
						$excerpt_id = 'wp-biographia-display-' . $pt->name . '-archives-bio-excerpt';
						$value = ($this->check_option ($settings, $name) ? $settings[$name] : 'full');

						$display_settings[] = '<p><strong>' . sprintf (__("%s Archives Biography Text", 'wp-biographia'), $pt->labels->name) . '</strong><br />
							<input type="radio" name="' . $name . '" id="' . $full_id . '" value="full" '
							. checked ($value, 'full', false)
							.' />&nbsp;<small>' . __('Display the full text of the user\'s biography', 'wp-biographia') . '</small><br />
							<input type="radio" name="' . $name . '" id="' . $excerpt_id . '" value="excerpt" '
							. checked ($value, 'excerpt', false)
							. ' />&nbsp;<small>' . __('Display the excerpt of the user\'s biography', 'wp-biographia') . '</small></p>';
						$display_settings[] = '</div>';

					}	// end-foreach (...)

					$display_settings[] = '<p><strong>' . __("Display In RSS Feeds", 'wp-biographia') . '</strong><br />
							<input type="checkbox" name="wp_biographia_display_feed" ' . checked ($settings['wp_biographia_display_feed'], 'on', false) . ' id="wp-biographia-display-feed" />
							<small>' . __('Displays the Biography Box in feeds for each entry.', 'wp-biographia') . '</small></p>';

					$display_settings[] = '<div id="wp-biographia-feed-bio-wrapper"';
					if ($settings['wp_biographia_display_feed'] != 'on') {
						$display_settings[] = ' style="display:none;"';
					}
					$display_settings[] = '>';

					$full_id = 'wp-biographia-display-feed-bio-full';
					$excerpt_id = 'wp-biographia-display-feed-bio-excerpt';

					$display_settings[] = '<p><strong>' . __("RSS Feeds Biography Text", 'wp-biographia') . '</strong><br />
						<input type="radio" name="wp_biographia_display_bio_feed" id="' . $full_id . '" value="full" '
						. checked ($settings['wp_biographia_display_bio_feed'], 'full', false)
						.' />&nbsp;<small>' . __('Display the full text of the user\'s biography', 'wp-biographia') . '</small><br />
						<input type="radio" name="wp_biographia_display_bio_feed" id="' . $excerpt_id . '" value="excerpt" '
						. checked ($settings['wp_biographia_display_bio_feed'], 'excerpt', false)
						. ' />&nbsp;<small>' . __('Display the excerpt of the user\'s biography', 'wp-biographia') . '</small></p>';
					$display_settings[] = '</div>';

					$settings['wp_biographia_display_location'] = (
						isset($settings['wp_biographia_display_location'])) ?
						$settings['wp_biographia_display_location'] : 'bottom';

					// Add Display Location: Top/Bottom
					$display_settings[] = '<p><strong>' . __("Display Location", 'wp-biographia') . '</strong><br />
					<input type="radio" name="wp_biographia_display_location" id="wp-biographia-content-name" value="top" '
					. checked ($settings['wp_biographia_display_location'], 'top', false)
					.' />&nbsp;<small>' . __('Display the Biography Box before the post or page content', 'wp-biographia') . '</small><br />
					<input type="radio" name="wp_biographia_display_location" id="wp-biographia-content-name" value="bottom" '
					. checked ($settings['wp_biographia_display_location'], 'bottom', false)
					. ' />&nbsp;<small>' . __('Display the Biography Box after the post or page content', 'wp-biographia') . '</small><br />';

					$display_settings[] = '<p><strong>' . __('Display Type', 'wp_biographia') . '</strong><br />
					<input type="radio" name="wp_biographia_display_type" id="wp-biographia-display-type" value="content" '
					. checked ($settings['wp_biographia_display_type'], 'content', false)
					. ' />&nbsp;<small>' . __('Display the Biography Box only on the post or page content', 'wp-biographia') . '</small><br />
					<input type="radio" name="wp_biographia_display_type" id="wp-biographia-display-type" value="excerpt" '
					. checked ($settings['wp_biographia_display_type'], 'excerpt', false)
					. ' />&nbsp;<small>' . __('Display the Biography Box only on the post or page excerpt', 'wp-biographia') . '</small><br />
					<input type="radio" name="wp_biographia_display_type" id="wp-biographia-display-type" value="both" '
					. checked ($settings['wp_biographia_display_type'], 'both', false)
					. ' />&nbsp;<small>' . __('Display the Biography Box on both the post or page content and excerpt', 'wp-biographia') . '</small><br />';

					/*************************************************************************
				 	 * End of Display tab content
				 	 */
					break;
			}	// end-switch ($tab)


			/********************************************************************************
		 	 * Put it all together ...
			 * TODO: Yes, I know it's another switch statement immediately following the previous
			 *       one.
		 	 */

			if (function_exists ('wp_nonce_field')) {
				$wrapped_content[] = wp_nonce_field (
					'wp-biographia-update-options',
					'_wpnonce',
					true,
					false);
			}

			$tab = $this->admin_validate_tab ();
			switch ($tab) {
				case 'admin':
					$wrapped_content[] = $this->admin_postbox ('wp-biographia-user-settings',
						__('New User Settings', 'wp-biographia'),
						implode ('', $role_settings));
					$wrapped_content[] = $this->admin_postbox ('wp-biographia-profile-settings',
						__('User Profile Settings', 'wp-biographia'),
						implode ('', $profile_settings));
					$wrapped_content[] = $this->admin_postbox ('wp-biographia-priority-settings',
						__('Content And Excerpt Settings', 'wp-biographia'),
						implode ('', $priority_settings));
					$wrapped_content[] = $this->admin_postbox ('wp-biographia-biography-settings',
						__('Biography Box Override Settings', 'wp-biographia'),
						implode ('', $bio_settings));
					break;

				case 'exclude':
					$wrapped_content[] = $this->admin_postbox ('wp-biographia-exclusion-settings',
						__('Post, Page And Custom Post Type Exclusion Settings', 'wp-biographia'),
						implode ('', $exclusion_settings));
					$wrapped_content[] = $this->admin_postbox ('wp-biographia-category-settings',
						__('Category Exclusion Settings', 'wp-biographia'),
						implode ('', $category_settings));
					$wrapped_content[] = $this->admin_postbox (
						'wp-biographia-supression-settings',
						__('User Hiding Settings', 'wp-biographia'),
						implode ('', $suppression_settings));
					break;

				case 'style':
					$wrapped_content[] = $this->admin_postbox ('wp-biographia-style-settings',
						__('Style Settings', 'wp-biographia'),
						implode ('', $style_settings));
					break;

				case 'content':
					$wrapped_content[] = $this->admin_postbox ('wp-biographia-content-settings',
						__('Content Settings', 'wp-biographia'),
						implode ('', $content_settings));
					break;
					
				case 'design':
					$wrapped_content[] = $this->admin_postbox('wp-biographia-design-settings',
						__('Design Settings', 'wp-biographia'),
						implode('', $design_settings));
					break;

				case 'defaults':
					$wrapped_content[] = $this->admin_postbox ('wp-biographia-default-settings',
						__('Reset WP Biographia'),
						implode ('', $defaults_settings));
					break;

				case 'colophon':
					$wrapped_content[] = $this->admin_postbox ('wp-biographia-colophon',
						__('Colophon', 'wp-biographia'),
						implode ('', $colophon_content));
					$wrapped_content[] = $this->admin_postbox ('wp-biographia-config-settings',
						__('Plugin Configuration Settings', 'wp-biographia'),
						implode ('', $config_settings));
					$wrapped_content[] = $this->admin_postbox ('wp-biographia-config-users',
						__('User Configuration Settings', 'wp-biographia'),
						implode ('', $config_users));
					break;

				case 'display':
				default:
					$wrapped_content[] = $this->admin_postbox ('wp-biographia-display-settings',
						__('Display Settings', 'wp-biographia'),
						implode ('', $display_settings));
					break;
			}	// end-switch ($tab)

			$this->admin_wrap ($tab,
				sprintf (__('WP Biographia %s - Settings And Options',
					'wp-biographia'), WP_Biographia::DISPLAY_VERSION),
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
		 * Adds/updates a set of key/value pairs to a list of user profiles.
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
			$settings = WP_Biographia::get_option ();

			if (!empty ($_POST['wp_biographia_option_submitted'])) {
				if (strstr ($_GET['page'], "wp-biographia") &&
				 		check_admin_referer ('wp-biographia-update-options')) {
					$tab = $this->admin_validate_tab ();
					$args = array (
						'public' => true,
						'_builtin' => false
					);
					$pts = get_post_types ($args, 'objects');
					$update_options = true;
					$reset_options = false;
					$update_msg = self::$admin_tab_names[$tab];
					$action_msg = __('Updated', 'wp-biographia');

					switch ($tab) {
						case 'admin':
							$roles = $this->admin_option ('wp-biographia-excluded-user-roles');
							if (!empty ($roles)) {
								$settings['wp_biographia_admin_new_users'] = implode (
									',', $roles);
							}
							else {
								$settings['wp_biographia_admin_new_users'] = '';
							}

							$profiles = $this->admin_option ('wp-biographia-hidden-profiles');
							if (!empty ($profiles)) {
								$settings['wp_biographia_admin_hide_profiles'] = implode (
									',', $profiles);
							}
							else {
								$settings['wp_biographia_admin_hide_profiles'] = '';
							}

							$links = $settings['wp_biographia_admin_links'];
							foreach (WP_Biographia::defaults () as $key => $data) {
								if (isset ($data['contactmethod']) && !empty ($data['contactmethod'])) {
									$setting_key = 'wp_biographia_admin_enable_' . $key;
									$setting_value = $this->admin_option ($setting_key);
									$links[$key] = $setting_value;
								}
							}	// end-foreach ($this->defaults () ... )
							$settings['wp_biographia_admin_links'] = $links;

							$value = $this->admin_option ('wp_biographia_content_priority');
							if (is_numeric ($value)) {
								$settings['wp_biographia_admin_content_priority'] = $value;
							}
							$value = $this->admin_option ('wp_biographia_excerpt_priority');
							if (is_numeric ($value)) {
								$settings['wp_biographia_admin_excerpt_priority'] = $value;
							}

							$settings['wp_biographia_sync_content_wpautop'] = $this->admin_option ('wp_biographia_sync_content_wpautop');
							$settings['wp_biographia_sync_excerpt_wpautop'] = $this->admin_option ('wp_biographia_sync_excerpt_wpautop');
							$settings['wp_biographia_admin_post_overrides'] = $this->admin_option ('wp_biographia_admin_post_overrides');
							$settings['wp_biographia_admin_lock_to_loop'] = $this->admin_option ('wp_biographia_admin_lock_to_loop');
							break;

						case 'exclude':
							foreach ($pts as $pt) {
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

							$settings['wp_biographia_page_exclusions'] =
								$this->admin_option ('wp_biographia_page_exclusions');

							// Category exclusions

							$categories = $this->admin_option (
								'wp-biographia-excluded-categories');
							if (!empty ($categories)) {
								$settings['wp_biographia_category_exclusions'] = implode (
									',', $categories);
							}
							else {
								$settings['wp_biographia_category_exclusions'] = '';
							}

							// Per user suppression of the Biography Box on posts and on pages

							$enabled_post_users = $this->admin_option ('wp-biographia-enabled-post-users');
							$suppressed_post_users = $this->admin_option ('wp-biographia-suppressed-post-users');
							$enabled_page_users = $this->admin_option ('wp-biographia-enabled-page-users');
							$suppressed_page_users = $this->admin_option ('wp-biographia-suppressed-page-users');

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
							break;

						case 'style':
							$color = preg_replace ('/[^0-9a-fA-F]/', '', $_POST['wp_biographia_style_bg']);

							if ((strlen ($color) == 6 || strlen ($color) == 3) &&
			 					isset($_POST['wp_biographia_style_bg'])) {
									$settings['wp_biographia_style_bg'] =
										$_POST['wp_biographia_style_bg'];
							}

							$settings['wp_biographia_style_border'] = 
								$this->admin_option ('wp_biographia_style_border');

							$field = 'wp_biographia_style_border_color';
							$color = preg_replace ('/[^0-9a-fA-F]/', '', $_POST[$field]);
							if ((strlen ($color) == 6 || strlen ($color) == 3) && isset ($_POST[$field])) {
									$settings[$field] = $_POST[$field];
							}
							break;

						case 'content':
							$settings['wp_biographia_content_prefix'] = 
								$this->admin_option ('wp_biographia_content_prefix');

							$settings['wp_biographia_content_name'] = 
								$this->admin_option ('wp_biographia_content_name');

							$settings['wp_biographia_content_authorpage'] =
								$this->admin_option ('wp_biographia_content_authorpage');

							$settings['wp_biographia_content_image'] = 
								$this->admin_option ('wp_biographia_content_image');

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

							$settings['wp_biographia_content_link_target'] =
								$this->admin_option ('wp_biographia_content_link_target');

							$settings['wp_biographia_content_link_nofollow'] =
								$this->admin_option ('wp_biographia_content_link_nofollow');

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
							break;

						case 'design':
							$settings['wp_biographia_design_type'] = 
								$this->admin_option ('wp_biographia_design_type');
							$settings['wp_biographia_design_wrap'] = 
								$this->admin_option ('wp_biographia_design_wrap');
							break;
							
						case 'defaults':
							$update_options = false;
							if (isset ($_POST['wp_biographia_reset_defaults']) &&
									$_POST['wp_biographia_reset_defaults'] === 'on') {
								$reset_options = true;
								$this->admin_reset_plugin ();
								$update_msg = __('All', 'wp-biographia');
								$action_msg = __('Reset To Default Values', 'wp-biographia');
							}
							break;

						case 'display':
							$settings['wp_biographia_display_front_posts'] =
								$this->admin_option ('wp_biographia_display_front_posts');
							$settings['wp_biographia_display_front_bio_posts'] = 
								$this->admin_option ('wp_biographia_display_front_bio_posts');

							$settings['wp_biographia_display_posts'] =
								$this->admin_option ('wp_biographia_display_posts');
							$settings['wp_biographia_display_bio_posts'] = 
								$this->admin_option ('wp_biographia_display_bio_posts');

							$settings['wp_biographia_display_archives_posts'] =
								$this->admin_option ('wp_biographia_display_archives_posts');
							$settings['wp_biographia_display_archives_bio_posts'] = 
								$this->admin_option ('wp_biographia_display_archives_bio_posts');

							$settings['wp_biographia_display_author_archives_posts'] =
								$this->admin_option ('wp_biographia_display_author_archives_posts');
							$settings['wp_biographia_display_author_archives_bio_posts'] = 
								$this->admin_option ('wp_biographia_display_author_archives_bio_posts');

							$settings['wp_biographia_display_category_archives_posts'] =
								$this->admin_option ('wp_biographia_display_category_archives_posts');
							$settings['wp_biographia_display_category_archives_bio_posts'] = 
								$this->admin_option ('wp_biographia_display_category_archives_bio_posts');

							$settings['wp_biographia_display_date_archives_posts'] =
								$this->admin_option ('wp_biographia_display_date_archives_posts');
							$settings['wp_biographia_display_date_archives_bio_posts'] = 
								$this->admin_option ('wp_biographia_display_date_archives_bio_posts');

							$settings['wp_biographia_display_tag_archives_posts'] =
								$this->admin_option ('wp_biographia_display_tag_archives_posts');
							$settings['wp_biographia_display_tag_archives_bio_posts'] = 
								$this->admin_option ('wp_biographia_display_tag_archives_bio_posts');

							$settings['wp_biographia_display_pages'] =
								$this->admin_option ('wp_biographia_display_pages');
							$settings['wp_biographia_display_bio_pages'] = 
								$this->admin_option ('wp_biographia_display_bio_pages');

							foreach ($pts as $pt) {
								$name = 'wp_biographia_display_' . $pt->name;
								$settings[$name] = $this->admin_option ($name);

								$name = 'wp_biographia_display_bio_' . $pt->name;
								$settings[$name] = $this->admin_option ($name);

								$name = 'wp_biographia_display_archives_' . $pt->name;
								$settings[$name] = $this->admin_option ($name);

								$name = 'wp_biographia_display_archives_bio_' . $pt->name;
								$settings[$name] = $this->admin_option ($name);
							}	// end-foreach (...)

							$settings['wp_biographia_display_feed'] =
								$this->admin_option ('wp_biographia_display_feed');
							$settings['wp_biographia_display_bio_feed'] =
								$this->admin_option ('wp_biographia_display_bio_feed');

							$settings['wp_biographia_display_location'] =
								$this->admin_option ('wp_biographia_display_location');
							$settings['wp_biographia_display_type'] = 
								$this->admin_option ('wp_biographia_display_type');
							break;

						case 'colophon':
						default:
							$update_options = false;
							break;
					}	// end-switch ($tab)

					if ($update_options) {
						update_option (WP_Biographia::OPTIONS, $settings);
					}

					if ($update_options || $reset_options) {
						echo "<div id=\"updatemessage\" class=\"updated fade\"><p>";
						echo sprintf (__('%s Settings And Options %s', 'wp-biographia'),
							$update_msg, $action_msg);
						echo "</p></div>\n";
						echo "<script 	type=\"text/javascript\">setTimeout(function(){jQuery('#updatemessage').hide('slow');}, 3000);</script>";	

					}
				}
			}

			$settings = WP_Biographia::get_option ();
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
		 * @param string tab Settings/options tab context name
		 * @param string title Title for the plugin's admin settings/options page.
		 * @param string content HTML content for the plugin's admin settings/options page.
		 * @return string Wrapped HTML content
		 */

		function admin_wrap ($tab, $title, $content) {
			$action = $this->admin_get_options_url ($tab);
		?>
		    <div class="wrap">
		        <h2><?php echo $title; ?></h2>
				<?php
				echo $this->admin_tabs ($tab);

				?>
		        <form method="post" action="<?php echo $action; ?>">
		            <div class="postbox-container wp-biographia-postbox-settings">
		                <div class="metabox-holder">	
		                    <div class="meta-box-sortables">
		                    <?php
		                        echo $content;
								echo $this->admin_submit ($tab);
		                    ?>
		                    <br /><br />
		                    </div>
		                  </div>
		                </div>
		                <div class="postbox-container wp-biographia-postbox-sidebar">
		                  <div class="metabox-holder">	
		                    <div class="meta-box-sortables">
		                    <?php
								echo $this->admin_help_and_support ();
								echo $this->admin_acknowledgements ();
		                    ?>
		                    </div>
		                </div>
		            </div>
					<?php wp_nonce_field ('closedpostboxes', 'closedpostboxesnonce', false); ?>
					<?php wp_nonce_field ('meta-box-order', 'meta-box-order-nonce', false); ?>
		        </form>
		    </div>
		<?php
		}

		/**
		 * Emit a tab specific submit button for saving the plugin's settings/options.
		 *
		 * @param string tab Settings/options tab context name
		 * @return string Submit button HTML
		 */

		function admin_submit ($tab) {
			$content = array ();

			switch ($tab) {
				case 'admin':
				case 'display':
				case 'exclude':
				case 'style':
				case 'content':
				case 'design':
				case 'defaults':
		        	$content[] = '<p class="submit">';
					$content[] = '<input type="submit" name="wp_biographia_option_submitted" class="button-primary" value="';
					$content[] = sprintf (__('Save %s Settings', 'wp-biographia'),
					 	self::$admin_tab_names[$tab]);
					$content[] = '" />';
					$content[] = '</p>';
					return implode ('', $content);
					break;

				case 'colophon':
				default:
					break;
			}	// end-switch ($tab)
		}

		/**
		 * Emits the plugin's help/support side-box for the plugin's admin settings/options page.
		 */

		function admin_help_and_support () {
			$email_address = antispambot ("gary@vicchi.org");
			$restart_url = $this->admin_get_options_url ('display');
			$restart_url .= '&wp_biographia_restart_tour';
			$restart_url = wp_nonce_url ($restart_url, 'wp-biographia-restart-tour');

			$content = array ();

			$content[] = '<p>';
			$content[] =  __('For help and support with WP Biographia, here\'s what you can do:', 'wp-biographia');
			$content[] = '<ul><li>';
			$content[] = __('Ask a question on the <a href="http://wordpress.org/support/plugin/wp-biographia">WordPress support forum</a>; this is by far the best way so that other users can follow the conversation.', 'wp-biographia');
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
			$content[] = __('WP Biographia is free; no premium or light version, no ads. If you\'d like to support this plugin <a href="http://www.vicchi.org/codeage/donate/">here\'s how</a>.', 'wp-biographia');
			$content[] = '</li></ul></p>';
			$content[] = sprintf (__('<p>Find out what\'s new and get an overview of WP Biographia; <a href="%s">restart the plugin tour</a>.</p>', 'wp-biographia'), $restart_url);

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
			$content[] = __('The fact that you\'re reading this wouldn\'t have been possible without the help, bug fixing, beta testing, gentle prodding and overall general warmth and continued support of <a href="https://twitter.com/#!/wp_smith">Travis Smith</a> and <a href="https://twitter.com/#!/webendev">Bruce Munson</a>. Travis and Bruce ... you\'re awesome. Thank you.', 'wp-biographia');
			$content[] = '</p><p>';
			$content[] = __('WP Biographia has supported translation and internationalisation for a while now. Thanks go out to <a href="https://twitter.com/#!/KazancExpert">Hakan Er</a> for the Turkish translation and to <a href="http://wordpress.org/support/profile/kubitomakita">Jakub Mikita</a> for the Polish translation. If you\'d like to see WP Biographia translated into your language and want to help with the process, then please drop me an <a href="mailto:%s">email</a>.', 'wp-biographia');
			$content[] = '</p><p>';
			$content[] = __('The v1.x and v2.x releases of WP Biographia were inspired and based on <a href="http://www.jonbishop.com">Jon Bishop\'s</a> <a href="http://wordpress.org/extend/plugins/wp-about-author/">WP About Author</a> plugin. WP Biographia has come a long way since v1.0, including a total rewrite in v3.0, but thanks and kudos must go to Jon for writing a well structured, working WordPress plugin released under a software license that enables other plugins such as this one to be written or derived in the first place.', 'wp-biographia');
			$content[] = '</p>';

			return $this->admin_postbox ('wp-biographia-acknowledgements',
				__('Acknowledgements', 'wp-biographia'),
				implode ('', $content));
		}

		/**
		 * Emit a WordPress standard set of tab headers as part of saving the plugin's
		 * settings/options.
		 *
		 * @param string current Currently selected settings/options tab context name
		 * @return string Tab headers HTML
		 */

		function admin_tabs ($current='display') {
			$content = array ();

			$content[] = '<div id="icon-tools" class="icon32"><br /></div>';
			$content[] = '<h2 class="nav-tab-wrapper">';

			foreach (self::$admin_tab_names as $tab => $name) {
				$class = ($tab == $current) ? ' nav-tab-active' : '';
				$content[] = sprintf ('<a class="nav-tab%s" id="wp-biographia-tab-%s" href="%s">%s</a>',
								$class,
								$tab,
								$this->admin_get_options_url ($tab),
								$name);
			}	// end-foreach (...)

			$content[] = '</h2>';

			return implode ('', $content);
		}

		/**
		 * Check and validate the tab parameter passed as part of the settings/options URL.
		 */

		function admin_validate_tab () {
			$tab = filter_input (INPUT_GET, 'tab', FILTER_SANITIZE_STRING);
			if ($tab !== FALSE && $tab !== null) {
				if (array_key_exists ($tab, self::$admin_tab_names)) {
					return $tab;
				}
			}

			$tab = 'display';
			return $tab;
		}

		/**
		 * Reset the plugin's settings/options back to the default values.
		 */

		function admin_reset_plugin () {
			$filter = false;
			$defaults = WP_Biographia::defaults ($filter);
			$fields = array (0 => 'ID');
			$search = new WP_User_Query (array ('fields' => $fields));
			$users = $search->get_results ();

			delete_option (WP_Biographia::OPTIONS);

			foreach ($users as $user) {
				update_user_meta ($user->ID, 'wp_biographia_suppress_posts', '');
				update_user_meta ($user->ID, 'wp_biographia_suppress_pages', '');
			}	// end-foreach (users)

			$this->add_settings ();
		}

		/**
		 * "add_meta_boxes" action hook; adds a meta box to hide the Biography Box for a page,
		 * and to hide the Biography Box on posts and custom post types to the admin edit
		 * screens.
		 */

		function admin_add_meta_boxes () {
			$user = wp_get_current_user ();
			$hide = false;
			$option = WP_Biographia::get_option ('wp_biographia_admin_hide_profiles');
			if (!empty ($option)) {
				$hidden_profiles = explode (',', $option);
				foreach ($user->roles as $role) {
					if (in_array ($role, $hidden_profiles)) {
						$hide = true;
						break;
					}
				}	// end-foreach;
			}

			if ($hide) {
				return;
			}

			$hide_page = (get_user_meta ($user->ID, 'wp_biographia_suppress_pages', true) == 'on');
			$hide_post = (get_user_meta ($user->ID, 'wp_biographia_suppress_posts', true) == 'on');

			$pts = get_post_types (array (), 'objects');

			foreach ($pts as $pt) {
				if ($pt->name == 'page' && $hide_page) {
					continue;
				}

				elseif ($pt->name == 'post' && $hide_post) {
					continue;
				}

				$id = sprintf ('wp-biographia-%s-meta', $pt->name);
				$title = sprintf (__('Biography Box %s Options', 'wp-biographia'), $pt->labels->singular_name);

				add_meta_box ($id, $title, array ($this, 'admin_display_meta_box'), $pt->name);
			}	// end-foreach
		}

		/**
		 * "add_meta_box" callback; adds a meta box to hide the Biography Box for a page,
		 * and to hide the Biography Box on posts and custom post types to the admin edit
		 * screens.
		 *
		 * @param object post WordPress post object
		 */

		function admin_display_meta_box ($post) {
			$content = array ();

			$pt = get_post_type ();
			$pto = get_post_type_object ($pt);

			$content[] = wp_nonce_field (basename (__FILE__), WP_BiographiaAdmin::META_NONCE);

			switch ($pt) {
				case 'page':
					$checked = false;
					$exclusions = WP_Biographia::get_option ('wp_biographia_page_exclusions');
					if (isset ($exclusions)) {
						$page_exclusions = explode (',', $exclusions);
						$checked = (in_array ($post->ID, $page_exclusions));

					}

					$content[] = '<p><strong>' . __("Hide The Biography Box On This Page", 'wp-biographia') . '</strong><br /> 
						<input type="checkbox" name="wp_biographia_admin_meta_page_hide" ' . checked ($checked, true, false) . ' />
						<small>' . __('Hides the Biography Box each time this page is displayed.', 'wp-biographia') . '</small></p>';
					break;

				default:
					$checked = false;
					$opt = 'wp_biographia_' . $pt . '_exclusions';
					$exclusions = WP_Biographia::get_option ($opt);
					if (isset ($exclusions)) {
						$post_exclusions = explode (',', $exclusions);
						$checked = (in_array ($post->ID, $post_exclusions));
					}

					$title = sprintf (__('Hide The Biography Box On Single %s', 'wp_biographia'), $pto->labels->name);
					$control = 'wp_biographia_admin_meta_single_hide';
					$text = sprintf (__('Hides the Biography Box each time this %1$s is displayed using the Single %2$s Template.', 'wp_biographia'),
						$pto->labels->singular_name, $pto->labels->singular_name);

					$content[] = '<p><strong>' . $title . '</strong><br /> 
						<input type="checkbox" name="' . $control . '" ' . checked ($checked, true, false) . ' />
						<small>' . $text . '</small></p>';

					$checked = false;
					$opt = 'wp_biographia_global_' . $pt . '_exclusions';
					$exclusions = WP_Biographia::get_option ($opt);
					if (isset ($exclusions)) {
						$post_exclusions = explode (',', $exclusions);
						$checked = (in_array ($post->ID, $post_exclusions));
					}

					$title = sprintf (__('Globally Hide The Biography Box On %s', 'wp_biographia'), $pto->labels->name);
					$control = 'wp_biographia_admin_meta_global_hide';
					$text = sprintf (__('Hides the Biography Box whenever this %s is displayed; singly, on archive pages or on the front page.', 'wp_biographia'),
						$pto->labels->singular_name);

					$content[] = '<p><strong>' . $title . '</strong><br /> 
						<input type="checkbox" name="' . $control . '" ' . checked ($checked, true, false) . ' />
						<small>' . $text . '</small></p>';
					break;
			}	// end-switch

			$allow_overrides = WP_Biographia::get_option ('wp_biographia_admin_post_overrides');
			if ($allow_overrides) {
				$title = sprintf (__('Override Biography Text For This %s', 'wp_biographia'), $pto->labels->singular_name);
				$control = 'wp_biographia_admin_meta_biography_override';
				$id = 'wp-biographia-admin-meta-biography-override';
				$key = '_wp_biographia_bio_override';
				$text = sprintf (__('Override the default biography whenever this %s is displayed.', 'wp_biographia'), $pto->labels->singular_name);
				$checked = get_post_meta ($post->ID, $key, true);

				$content[] = '<p><strong>' . $title . '</strong><br /> 
					<input type="checkbox" name="' . $control . '" id="' . $id . '" ' . checked ($checked, 'on', false) . ' />
					<small>' . $text . '</small></p>';

				$style = '';
				if ($checked !== 'on') {
					$style = 'style="display:none;"';
				}
				$content[] = '<div name="wp_biographia_admin_bio_override" id="wp-biographia-admin-bio-override" ' . $style . '>';

				$title = sprintf (__('%s Specific Biography Text', 'wp_biographia'), $pto->labels->singular_name);
				$control = 'wp_biographia_admin_meta_biography_text';
				$key = '_wp_biographia_bio_text';

				$bio_text = get_post_meta ($post->ID, $key, true);
				$profile_bio = get_the_author_meta ('description', $post->post_author);
				if (!isset ($bio_text) || empty ($bio_text)) {
					$bio_text = $profile_bio;
				}

				$content[] = '<p><strong>' . $title . '</strong><br />
					<textarea name="' . $control . '" id="wp-biographia-admin-meta-biography">' . $bio_text . '</textarea><br />
					<a class="button-secondary" name="wp_biographia_admin_reload_biography" id="wp-biographia-admin-reload-biography">' . __('Reload Default Profile Biography', 'wp_biographia') . '</a><br />';

				$content[] = '<textarea name="wp_biographia_admin_meta_profile_bio" id="wp-biographia-admin-meta-profile-bio" style="display:none;">' . $profile_bio . '</textarea>';
				$content[] = '</div>';

				$title = sprintf (__('Override Biography Title For This %s', 'wp_biographia'), $pto->labels->singular_name);
				$control = 'wp_biographia_admin_meta_title_override';
				$id = 'wp-biographia-admin-meta-title-override';
				$key = '_wp_biographia_title_override';
				$text = sprintf (__('Override the default title for the Biography Box whenever this %s is displayed.', 'wp_biographia'), $pto->labels->singular_name);
				$checked = get_post_meta ($post->ID, $key, true);

				$content[] = '<p><strong>' . $title . '</strong><br /> 
					<input type="checkbox" name="' . $control . '" id="' . $id . '" ' . checked ($checked, 'on', false) . ' />
					<small>' . $text . '</small></p>';

				$title = sprintf (__('%s Specific Biography Title', 'wp_biographia'), $pto->labels->singular_name);
				$control = 'wp_biographia_admin_meta_title';
				$id = 'wp-biographia-admin-meta-title';
				$key = '_wp_biographia_title_text';
				$title_text = get_post_meta ($post->ID, $key, true);
				$style = '';
				if ($checked !== 'on') {
					$style = 'style="display:none;"';
				}

				$content[] = '<div name="wp_biographia_admin_title_override" id="wp-biographia-admin-title-override" ' . $style . '>';
				$content[] = '<p><strong>' . $title . '</strong><br />
					<input type="text" name="' . $control . '" id="' . $id . '" value="' . $title_text . '" />';
				$content[] = '</div>';

				$title = sprintf (__('Suppress Avatar For This %s', 'wp_biographia'), $pto->labels->singular_name);
				$control = 'wp_biographia_admin_meta_avatar_suppress';
				$key = '_wp_biographia_suppress_avatar';
				$text = sprintf (__('Suppress the display of the Avatar in the Biography Box whenever this %s is displayed.', 'wp_biographia'), $pto->labels->singular_name);
				$checked = get_post_meta ($post->ID, $key, true);

				$content[] = '<p><strong>' . $title . '</strong><br /> 
					<input type="checkbox" name="' . $control . '" ' . checked ($checked, 'on', false) . ' />
					<small>' . $text . '</small></p>';

				$title = sprintf (__('Suppress Contact Links For This %s', 'wp_biographia'), $pto->labels->singular_name);
				$control = 'wp_biographia_admin_meta_links_suppress';
				$key = '_wp_biographia_suppress_links';
				$text = sprintf (__('Suppress the display of the contact links in the Biography Box whenever this %s is displayed.', 'wp_biographia'), $pto->labels->singular_name);
				$checked = get_post_meta ($post->ID, $key, true);

				$content[] = '<p><strong>' . $title . '</strong><br /> 
					<input type="checkbox" name="' . $control . '" ' . checked ($checked, 'on', false) . ' />
					<small>' . $text . '</small></p>';
			}

			if (!empty ($content)) {
				echo implode (PHP_EOL, $content);
			}
		}

		/**
		 * "save_post" action hook; save the post/page/custom post Biography Box hiding options
		 * (if shown)
		 *
		 * @param integer post_id Post ID for the current post
		 * @param object post WordPress post object
		 */

		function admin_save_meta_boxes ($post_id, $post) {
			// CODE HEALTH WARNING
			// the "save_post" hook is a misnomer; it's not called just on the saving of a
			// post, but during initial post creation, during autosave, during the creation of
			// a revision, in fact during anything that changes the disposition of the post.
			// Which is why there's a whole lot of checking and validation going on here before
			// we even look at the custom meta box options.

			if (defined ('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
				return $post_id; 
			}

			if ($parent_id = wp_is_post_revision ($post_id)) {
				return $post_id;
			}


			$post_type = get_post_type_object ($post->post_type);
			if (!current_user_can ($post_type->cap->edit_post, $post_id)) {
				return $post_id;
			}

			switch ($post->post_status) {
				case 'draft':
				case 'pending':
				case 'publish':
					break;

				default:
					return $post_id;
			}

			if (!isset ($_POST[WP_BiographiaAdmin::META_NONCE]) || !check_admin_referer (basename (__FILE__), WP_BiographiaAdmin::META_NONCE)) {
				return $post_id;
			}

			if ($post_type->name == 'page') {
				$stub = $post_type->name;
				$field = 'wp_biographia_admin_meta_page_hide';
				$value = $this->admin_option ($field);
				if (isset ($value) && $value) {
					$this->admin_set_exclusion ($stub, $post_id);
				}
				else {
					$this->admin_clear_exclusion ($stub, $post_id);
				}
			}

			else {
				$stub = $post_type->name;
				$field = 'wp_biographia_admin_meta_single_hide';
				$value = $this->admin_option ($field);
				if (isset ($value) && $value) {
					$this->admin_set_exclusion ($stub, $post_id);
				}
				else {
					$this->admin_clear_exclusion ($stub, $post_id);
				}

				$stub = 'global_' . $post_type->name;
				$field = 'wp_biographia_admin_meta_global_hide';
				$value = $this->admin_option ($field);
				if (isset ($value) && $value) {
					$this->admin_set_exclusion ($stub, $post_id);
				}
				else {
					$this->admin_clear_exclusion ($stub, $post_id);
				}
			}

			$field = 'wp_biographia_admin_meta_biography_override';
			$key = '_wp_biographia_bio_override';
			$this->admin_update_post_meta ($post_id, $field, $key);

			$field = 'wp_biographia_admin_meta_biography_text';
			$key = '_wp_biographia_bio_text';
			$this->admin_update_post_meta ($post_id, $field, $key);

			$field = 'wp_biographia_admin_meta_title_override';
			$key = '_wp_biographia_title_override';
			$this->admin_update_post_meta ($post_id, $field, $key);

			$field = 'wp_biographia_admin_meta_title';
			$key = '_wp_biographia_title_text';
			$this->admin_update_post_meta ($post_id, $field, $key);

			$field = 'wp_biographia_admin_meta_avatar_suppress';
			$key = '_wp_biographia_suppress_avatar';
			$this->admin_update_post_meta ($post_id, $field, $key);

			$field = 'wp_biographia_admin_meta_links_suppress';
			$key = '_wp_biographia_suppress_links';
			$this->admin_update_post_meta ($post_id, $field, $key);
		}

		/**
		 * Adds/updates a key/value pair to a post's metadata.
		 */

		function admin_update_post_meta ($post_id, $field, $key) {
			$new_value = $this->admin_option ($field);
			$meta_value = get_post_meta ($post_id, $key, true);

			if ($new_value && '' == $meta_value) {
				add_post_meta ($post_id, $key, $new_value, true);
			}

			elseif ($new_value && $new_value != $meta_value) {
				update_post_meta ($post_id, $key, $new_value);
			}

			elseif ('' == $new_value && $meta_value) {
				delete_post_meta ($post_id, $key, $meta_value);
			}
		}

		/**
		 * "before_delete_post" action hook; called just prior to a post being deleted.
		 *
		 * @param integer post_id Post ID for the current post
		 */

		function admin_before_delete_post ($post_id) {
			if ($parent_id = wp_is_post_revision ($post_id)) {
				return;
			}

			$post = get_post ($post_id);
			$stub = $post->post_type;
			$this->admin_clear_exclusion ($stub, $post_id);

			if ($post->post_type != 'page') {
				$stub = 'global_' . $post->post_type;
				$this->admin_clear_exclusion ($stub, $post_id);
			}
		}

		/**
		 * Helper function to get the current set of post/page/custom post type exclusions
		 *
		 * @param string $stub Stub settings/option name
		 */

		function admin_get_exclusions ($stub) {
			$option = 'wp_biographia_' . $stub . '_exclusions';
			$optval = WP_Biographia::get_option ($option);
			$excl = array ();
			if (!empty ($optval)) {
				$excl = explode (',', $optval);
			}

			return $excl;
		}

		/**
		 * Helper function to determine if the current post/page/custom post is excluded/hidden
		 *
		 * @param string $stub Stub settings/option name
		 * @param integer $post_id Post ID for the current post
		 */

		function admin_is_excluded ($stub, $post_id) {
			$excl = $this->admin_get_exclusions ($stub);
			if (isset ($optval)) {
				return (in_array ($post_id, $excl));
			}
			else
				return false;
		}

		/**
		 * Helper function to flag the current post/page/custom post as excluded/hidden
		 *
		 * @param string $stub Stub settings/option name
		 * @param integer $post_id Post ID for the current post
		 */

		function admin_set_exclusion ($stub, $post_id) {
			$excl = $this->admin_get_exclusions ($stub);
			if (!in_array ($post_id, $excl)) {
				$excl[] = strval ($post_id);
				sort ($excl);
			}
			$optval = implode (',', $excl);
			$option = 'wp_biographia_' . $stub . '_exclusions';
			WP_Biographia::set_option ($option, $optval);
		}

		/**
		 * Helper function to clear the current post/page/custom post as excluded/hidden
		 *
		 * @param string $stub Stub settings/option name
		 * @param integer $post_id Post ID for the current post
		 */

		function admin_clear_exclusion ($stub, $post_id) {
			$excl = $this->admin_get_exclusions ($stub);
			if (in_array ($post_id, $excl)) {
				if (($key = array_search (strval ($post_id), $excl)) !== false) {
					unset ($excl[$key]);
				}
			}
			$optval = implode (',', $excl);
			$option = 'wp_biographia_' . $stub . '_exclusions';
			WP_Biographia::set_option ($option, $optval);
		}

		/**
		 * Helper function to clear the plugin's tour pointer.
		 */

		function admin_clear_pointer () {
			$user_id = get_current_user_id ();
			$dismissed = explode (',', get_user_meta ($user_id, 'dismissed_wp_pointers', true));
			$key = array_search ('wp_biographia_pointer', $dismissed);
			if ($key !== false) {
				unset ($dismissed[$key]);
				update_user_meta ($user_id, 'dismissed_wp_pointers', implode (',', $dismissed));
			}
		}

		/**
		 * Helper function to get the status of the plugin's tour pointer.
		 */

		function admin_is_pointer_set () {
			$user_id = get_current_user_id ();
			$dismissed = explode (',', get_user_meta ($user_id, 'dismissed_wp_pointers', true));
			return in_array ('wp_biographia_pointer', $dismissed);
		}

		/**
		 * Helper function to get the plugin's Admin URL.
		 */

		function admin_get_options_url ($tab=NULL) {
			$url = array ();
			$url[] = admin_url ('options-general.php');
			$url[] = '?page=wp-biographia/includes/wp-biographia-admin.php';
			if (isset ($tab) && !empty ($tab)) {
				$url[] = '&tab=' . $tab;
			}

			return implode ('', $url);
		}


		
	}	// end-class (...)
}	// end-if (!class_exists(...))

WP_BiographiaAdmin::get_instance();

?>