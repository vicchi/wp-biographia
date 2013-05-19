<?

/**
 * Summary of WP Biographia configuration changes
 *-----------------------------------------------------------------------------
 * v1.0 configuration options
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
 *-----------------------------------------------------------------------------
 * v2.0
 *	Added configuration options
 *		wp_biographia_content_email = "on"
 *		wp_biographia_content_image_size = "100"
 *		wp_biographia_style_border (was wp_biographia_alert_border) = "top"
 *		wp_biographia_style_bg (was wp_biographia_alert_bg) = "#FFEAA8"
 *		wp_biographia_display_location = "bottom"
 *		wp_biographia_page_exclusions (no default value)
 *		wp_biographia_post_exclusions (no default value)
 *
 *	Removed configuration options
 *		wp_biographia_alert_border (replaced by wp_biographia_style_border)
 *		wp_biographia_alert_bg (replaced by wp_biographia_style_bg)
 *
 *	Changed default configuration options
 *		wp_biographia_version = "20"
 *
 *-----------------------------------------------------------------------------
 * v2.1
 *	Added configuration options
 *		wp_biographia_beta_enabled = ""
 *		wp_biographia_suppress_posts = "" (user profile extension)
 *		wp_biographia_suppress_pages = "" (user profile extension)
 *
 *	Changed default configuration options
 *		wp_biographia_version = "21"
 *
 *-----------------------------------------------------------------------------
 * v2.1.1
 *	Changed default configuration options
 *		wp_biographia_version = "211"
 *
 *-----------------------------------------------------------------------------
 * v2.2
 *	Added configuration options
 *		wp_biographia_content_delicious = ""
 *		wp_biographia_content_flickr = ""
 *		wp_biographia_content_picasa = ""
 *		wp_biographia_content_vimeo = ""
 *		wp_biographia_content_youtube = ""
 *		wp_biographia_content_reddit = ""
 *
 *	Changed default configuration options
 *		wp_biographia_version = "22"
 *
 *-----------------------------------------------------------------------------
 * v2.2.1 - Note: v2.2.1 was a private beta and never formally released.
 *	Changed default configuration options
 *		wp_biographia_version = "221"
 *
 *-----------------------------------------------------------------------------
 * v2.3
 *	Changed default configuration options
 *		wp_biographia_version = "23"
 *
 *-----------------------------------------------------------------------------
 * V2.4
 *	Added configuration options
 *		wp_biographia_content_authorpage = "on"
 *		wp_biographia_content_icons = ""
 *		wp_biographia_content_alt_icons = ""
 *		wp_biographia_content_icon_url = ""
 *
 *	Changed default configuration options
 *		wp_biographia_version = "24"
 *
 *-----------------------------------------------------------------------------
 * v2.4.1
 *	Changed default configuration options
 * 		wp_biographia_version = "241"
 *
 *-----------------------------------------------------------------------------
 * v2.4.2
 *	Changed default configuration options
 * 		wp_biographia_version = "242"
 *
 *-----------------------------------------------------------------------------
 * v2.4.3
 *	Changed default configuration options
 *		wp_biographia_version = "243"
 *
 *-----------------------------------------------------------------------------
 * v2.4.4
 *	Changed default configuration options
 *		wp_biographia_version = "244"
 *
 *-----------------------------------------------------------------------------
 * v3.0.0
 *	Added configuration options
 *		wp_biographia_content_link_target = "_self"
 *		wp_biographia_content_link_nofollow = ""
 *
 *	Changed default configuration options
 *		wp_biographia_version = "30"
 *
 *	Removed configuration options
 *		wp_biographia_beta_enabled
 *		wp_biograpia_content_vimeo
 *
 *-----------------------------------------------------------------------------
 * v3.0.1
 *	Changed default configuration options
 *		wp_biographia_version = "301"
 *
 *-----------------------------------------------------------------------------
 * v3.1.0
 *	Changed default configuration options
 *		wp_biographia_version = "310"
 *
 *	Added configuration options
 *		wp_biographia_admin_new_users = ""
 * 		wp_biographia_admin_hide_profiles = ""
 *		wp_biographia_category_exclusions = ""
 *		wp_biographia_post_exclusions = ""
 *		wp_biographia_global_post_exclusions = ""
 *		wp_biographia_page_exclusions = ""
 *		wp_biographia_admin_content_priority = "10"
 *		wp_biographia_admin_excerpt_priority = "10"
 *
 *-----------------------------------------------------------------------------
 * v3.2.0
 *	Changed default configuration options
 *		wp_biographia_version = "320"
 *
 *	Added configuration options
 *		wp_biographia_display_front_posts = ""
 *		wp_biographia_display_archives_posts = ""
 *		wp_biographia_display_author_archives_posts = ""
 *		wp_biographia_display_category_archives_posts = ""
 *		wp_biographia_display_date_archives_posts = ""
 *		wp_biographia_display_tag_archives_posts = ""
 *		wp_biographia_sync_content_wpautop = ""
 *		wp_biographia_sync_excerpt_wpautop = ""
 *
 *	Removed configuration options
 *		wp_biographia_display_archives (replaced by wp_biographia_display_archive_posts)
 *		wp_biographia_display_front (replaces by wp_biographia_display_front_posts)
 *
 *-----------------------------------------------------------------------------
 * v3.2.1
 *	Changed default configuration options
 *		wp_biographia_version = "321"
 *
 *-----------------------------------------------------------------------------
 * v3.3.0
 *	Changed default configuration options
 *		wp_biographia_version = "330"
 *
 *	Added configuration options
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
 *-----------------------------------------------------------------------------
 * v3.3.2
 *	Changed configuration options
 *		wp_biographia_version = "332"
 *
 *	Added configuration options
 *		wp_biographia_display_type = 'both'
 *		wp_biographia_design_type = 'classic'
 *		wp_biographia_design_wrap = ''
 *
 * Note: All configuration options have been changed to remove the 'wp_biographia_' prefix
 * which is unnecessary as all options are effectively namespaced within the options array
 */


if (!class_exists('WP_BiographiaUpgrade')) {
	class WP_BiographiaUpgrade extends WP_PluginBase_v1_1 {

		/**
		 * Called in response to the "admin_init" action hook; checks the current set of
		 * settings/options and upgrades them according to the new version of the plugin.
		 */

		public function upgrade () {
			$options = NULL;
			$upgrade_options = false;
			$current_plugin_version = NULL;

			/*
			 * Even if the plugin has only just been installed, the activation hook should
			 * have fired *before* the admin_init action so therefore we /should/ already
			 * have the plugin's configuration options defined in the database, but there's
			 * no harm in checking ... just to make sure ...
			 */

			$options = WP_Biographia::get_option ();

			/*
			 * Bale out early if there's no need to check for the need to upgrade the
			 * configuration options ...
			 */

			if (is_array($options)) {
				$key = null;
				if (isset($options['wp_biographia_version'])) {
					$key = 'wp_biographia_version';
				}
				else if (isset($options['version'])) {
					$key = 'version';
				}

				if ($key !== null && $options[$key] == WP_Biographia::VERSION) {
					return;
				}
			}

			if (!is_array ($options)) {
				/*
				 * Something odd is going on, so define the default set of config options ...
				 */
				$this->add_settings ();
			}

			else {
				/*
				 * Versions of WP Biographia prior to v2.1 had a bug where some configuration
				 * options that were created at initial installation of the plugin were not
				 * persisted after the configuration options were updated; one of these is
				 * 'wp_biographia_version'. In this case, the "special" 00 version captures
				 * and remedies this.
				 */

				$key = null;
				if (isset($options['wp_biographia_version'])) {
					$key = 'wp_biographia_version';
				}
				else if (isset($options['version'])) {
					$key = 'version';
				}
				if ($key !== null) {
					$current_plugin_version = $options[$key];
				}
				else {
					$current_plugin_version = '00';
				}
				
				switch ($current_plugin_version) {
					case '00':
						$this->upgrade_v00($options);

					case '01':
						$this->upgrade_v01($options);

					case '20':
						$this->upgrade_v20($options);

					case '21':
					case '211':
					case '22':
						$this->upgrade_v22($options);

					case '221':
					case '23':
					case '24':
						$this->upgrade_v24($options);

					case '241':
					case '242':
					case '243':
					case '244':
					case '30':
						$this->upgrade_v30($options);

					case '301':
					case '310':
						$this->upgrade_v310($options);

					case '320':
						$this->upgrade_v320($options);

					case '321':
					case '330b1':
					case '330b2':
					case '330b3':
					case '330b4':
					case '330b5':
					case '330':
						$this->upgrade_v330($options);

					case '331':
					case '332':
						$this->upgrade_v332($options);

						$options['version'] = WP_Biographia::VERSION;
						$upgrade_options = true;
						
					default:
						break;
				}	// end-switch

				if ($upgrade_options) {
					WP_BiographiaAdmin::admin_clear_pointer ();
					update_option (WP_Biographia::OPTIONS, $options);
				}
			}
		}

		private function upgrade_v00(&$options) {
			$this->upgrade_option_pre_v4 ($options, 'wp_biographia_installed', 'on');
			$this->upgrade_option_pre_v4 ($options, 'wp_biographia_style_bg', '#FFFFFF');
			$this->upgrade_option_pre_v4 ($options, 'wp_biographia_style_border', 'top');
			$this->upgrade_option_pre_v4 ($options, 'wp_biographia_display_front', '');
			$this->upgrade_option_pre_v4 ($options, 'wp_biographia_display_archives', '');
			$this->upgrade_option_pre_v4 ($options, 'wp_biographia_display_posts', '');
			$this->upgrade_option_pre_v4 ($options, 'wp_biographia_display_pages', '');
			$this->upgrade_option_pre_v4 ($options, 'wp_biographia_display_feed', '');
			$this->upgrade_option_pre_v4 ($options, 'wp_biographia_content_prefix', 'About');
			$this->upgrade_option_pre_v4 ($options, 'wp_biographia_content_name', 'none');
			$this->upgrade_option_pre_v4 ($options, 'wp_biographia_content_image', '');
			$this->upgrade_option_pre_v4 ($options, 'wp_biographia_content_bio', '');
			$this->upgrade_option_pre_v4 ($options, 'wp_biographia_content_web', '');
			$this->upgrade_option_pre_v4 ($options, 'wp_biographia_content_twitter', '');
			$this->upgrade_option_pre_v4 ($options, 'wp_biographia_content_facebook', '');
			$this->upgrade_option_pre_v4 ($options, 'wp_biographia_content_linkedin', '');
			$this->upgrade_option_pre_v4 ($options, 'wp_biographia_content_googleplus', '');
			$this->upgrade_option_pre_v4 ($options, 'wp_biographia_content_posts', 'none');
		}

		private function upgrade_v01(&$options) {
			$this->upgrade_option_pre_v4 ($options, 'content_email', '');
			$this->upgrade_option_pre_v4 ($options, 'content_image_size', '100');

			if (isset ($options['wp_biographia_alert_border'])) {
				$this->upgrade_option_pre_v4 ($options, 'style_border',
				 						$options['wp_biographia_alert_border']);
				unset ($options['wp_biographia_alert_border']);
			}

			if (isset ($options['wp_biographia_alert_bg'])) {
				$this->upgrade_option_pre_v4 ($options, 'style_bg',
				 							$options['wp_biographia_alert_bg']);
				unset ($options['wp_biographia_alert_bg']);
			}

			$this->upgrade_option_pre_v4 ($options, 'display_location', 'bottom');
		}

		private function upgrade_v20(&$options) {
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
		}

		private function upgrade_v22(&$options) {
			$this->upgrade_option_pre_v4 ($options, 'content_delicious', '');
			$this->upgrade_option_pre_v4 ($options, 'content_flickr', '');
			$this->upgrade_option_pre_v4 ($options, 'content_picasa', '');
			$this->upgrade_option_pre_v4 ($options, 'content_vimeo', '');
			$this->upgrade_option_pre_v4 ($options, 'content_youtube', '');
			$this->upgrade_option_pre_v4 ($options, 'content_reddit', '');
		}

		private function upgrade_v24(&$options) {
			$this->upgrade_option_pre_v4 ($options, 'content_authorpage', 'on');
			$this->upgrade_option_pre_v4 ($options, 'content_icons', '');
			$this->upgrade_option_pre_v4 ($options, 'content_alt_icons', '');
			$this->upgrade_option_pre_v4 ($options, 'content_icon_url', '');
		}

		private function upgrade_v30(&$options) {
			if (isset ($options['wp_biographia_beta_enabled'])) {
				unset ($options['wp_biographia_beta_enabled']);
			}
			$this->upgrade_option_pre_v4 ($options, 'content_link_target', '_self');
			$this->upgrade_option_pre_v4 ($options, 'content_link_nofollow', '');
			if (isset ($options['wp_biograpia_content_vimeo'])) {
				$this->upgrade_option_pre_v4 ($options, 'content_vimeo', '');
				unset ($options['wp_biograpia_content_vimeo']);
			}
		}

		private function upgrade_v310(&$options) {
			$this->upgrade_option_pre_v4 ($options, 'category_exclusions', '');
			$this->upgrade_option_pre_v4 ($options, 'admin_new_users', '');
			$this->upgrade_option_pre_v4 ($options, 'admin_hide_profiles', '');
			$this->upgrade_option_pre_v4 ($options, 'post_exclusions', '');
			$this->upgrade_option_pre_v4 ($options, 'global_post_exclusions', '');
			$this->upgrade_option_pre_v4 ($options, 'page_exclusions', '');
			$this->upgrade_option_pre_v4 ($options, 'admin_content_priority',
			 	WP_Biographia::PRIORITY);
			$this->upgrade_option_pre_v4 ($options, 'admin_excerpt_priority',
				WP_Biographia::PRIORITY);
			}

		private function upgrade_v320(&$options) {
			if (isset ($options['wp_biographia_display_front'])) {
				$this->upgrade_option_pre_v4 ($options, 'display_front_posts',
		 							$options['wp_biographia_display_front']);
				unset ($options['wp_biographia_display_front']);
			}
			if (isset ($options['wp_biographia_display_archives'])) {
				$option = $options['wp_biographia_display_archives'];
				$this->upgrade_option_pre_v4 ($options, 'display_archives_posts', $option);
				unset ($options['wp_biographia_display_archives']);
				$this->upgrade_option_pre_v4 ($options, 'display_author_archives_posts', $option);
				$this->upgrade_option_pre_v4 ($options, 'display_category_archives_posts', $option);
				$this->upgrade_option_pre_v4 ($options, 'display_date_archives_posts', $option);
				$this->upgrade_option_pre_v4 ($options, 'display_tag_archives_posts', $option);
			}
			$this->upgrade_option_pre_v4 ($options, 'sync_content_wpautop', '');
			$this->upgrade_option_pre_v4 ($options, 'sync_excerpt_wpautop', '');
		}

		private function upgrade_v330(&$options) {
			$this->upgrade_option_pre_v4 ($options, 'admin_post_overrides', '');

			$admin_links = array ();
			foreach (WP_Biographia::defaults () as $key => $data) {
				if (isset ($data['contactmethod']) && !empty ($data['contactmethod'])) {
					$admin_links[$key] = 'on';
				}
			}	// end-foreach (...)

			$this->upgrade_option_pre_v4 ($options, 'admin_links', $admin_links);
			$this->upgrade_option_pre_v4 ($options, 'display_front_bio_posts', 'full');
			$this->upgrade_option_pre_v4 ($options, 'display_archives_bio_posts', 'full');
			$this->upgrade_option_pre_v4 ($options, 'display_author_archives_bio_posts', 'full');
			$this->upgrade_option_pre_v4 ($options, 'display_category_archives_bio_posts', 'full');
			$this->upgrade_option_pre_v4 ($options, 'display_date_archives_bio_posts', 'full');
			$this->upgrade_option_pre_v4 ($options, 'display_tag_archives_bio_posts', 'full');
			$this->upgrade_option_pre_v4 ($options, 'display_bio_posts', 'full');
			$this->upgrade_option_pre_v4 ($options, 'display_bio_pages', 'full');
			$this->upgrade_option_pre_v4 ($options, 'display_bio_feed', 'full');
			$this->upgrade_option_pre_v4 ($options, 'admin_lock_to_loop', '');
			$this->upgrade_option_pre_v4 ($options, 'style_border_color', '#000000');
		}

		private function upgrade_v332(&$options) {
			$new_options = array();
			foreach ($options as $key => $value) {
				$pos = strpos($key, 'wp_biographia_');
				if ($pos === 0) {
					$new_key = substr($key, $pos + strlen('wp_biographia_'));
					$new_options[$new_key] = $value;
				}
			}
			$options = $new_options;

			$this->upgrade_option ($options, 'display_type', 'both');
			$this->upgrade_option($options, 'design_type', 'classic');
			$this->upgrade_option($options, 'design_wrap', '');

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
						if (array_key_exists($old_method, $meta)) {
							$old_value = $meta[$old_method];
							if (delete_user_meta($user->ID, $old_method)) {
								add_user_meta($user->ID, $method, $old_value[0], true);
							}
						}
					}
				}
			}
		}

		/**
		 * Checks for the presence of a settings/options key and if not present, adds the
		 * key and its associated value.
		 *
		 * @param array options Array containing the current set of settings/options
		 * @param string key Settings/options key; specified without the 'wp_biographia_'
		 * prefix
		 * @param string key Settings/options value for key
		 */

		private function upgrade_option (&$options, $key, $value) {
			if (!isset ($options[$key])) {
				$options[$key] = $value;
			}
		}

		private function upgrade_option_pre_v4(&$options, $key, $value) {
			$kn = 'wp_biographia_' . $key;
			if (!isset ($options[$kn])) {
				$options[$kn] = $value;
			}
		}
		
	}	// end-class(...)
}	// end-if (!class_exists(...))

?>