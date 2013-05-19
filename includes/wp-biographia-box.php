<?php

if (!class_exists ('WP_BiographiaFilterPriority')) {
	class WP_BiographiaFilterPriority {
		public $has_filter = false;
		public $original = WP_BiographiaBox::PRIORITY;
		public $new = WP_BiographiaBox::PRIORITY;
	}	// end-class WP_BiographiaFilterPriority
}	// end-if (!class_exists ('WP_BiographiaFilterPriority'))

if (!class_exists('WP_BiographiaBox')) {
	class WP_BiographiaBox extends WP_PluginBase_v1_1 {

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

		private $has_sla_plugin = false;
		private $has_cop_plugin = false;

		const PRIORITY = 10;
		const DISPLAY_STUB = 'display';
		const ARCHIVE_STUB = 'archive';
		const BIOGRAPHY_STUB = 'biography';
		const ARCHIVE_BIOGRAPHY_STUB = 'archive-biography';

		private function __construct() {
			$this->author_id = NULL;
			$this->override = NULL;

			$active_plugins = apply_filters('active_plugins', get_option('active_plugins'));
			
			$this->has_sla_plugin = in_array('simple-local-avatars/simple-local-avatars.php', $active_plugins);
			$this->has_cop_plugin = in_array('co-authors-plus/co-authors-plus.php', $active_plugins);
			
			$this->hook ('wp_enqueue_scripts', 'style');

			$this->icon_dir_url = WPBIOGRAPHIA_URL . 'images/';
			$this->content_autop = new WP_BiographiaFilterPriority;
			$this->excerpt_autop = new WP_BiographiaFilterPriority;


			$settings = WP_Biographia::get_option ();
			if (is_array ($settings) && isset ($settings['version'])) {
				$content_priority = $settings['admin_content_priority'];
				$excerpt_priority = $settings['admin_excerpt_priority'];
			}
			else {
				$content_priority = $excerpt_priority = self::PRIORITY;
			}

			$hook_to_loop = false;
			$display_type = null;

			if (isset ($settings['display_type']) && !empty ($settings['display_type'])) {
				$display_type = $settings['display_type'];
			}
			
			if ($display_type === 'content' || $display_type === 'both') {
				$this->hook ('the_content', 'insert', intval ($content_priority));
				if ($content_priority < self::PRIORITY) {
					if (isset ($settings['sync_content_wpautop']) &&
							($settings['sync_content_wpautop'] == 'on')) {
						$hook_to_loop = true;
					}
				}
			}

			if ($display_type === 'excerpt' || $display_type === 'both') {
				$this->hook ('the_excerpt', 'insert', intval($excerpt_priority));
				if ($excerpt_priority < self::PRIORITY) {
					if (isset ($settings['sync_excerpt_wpautop']) &&
							($settings['sync_excerpt_wpautop'] == 'on')) {
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
		
			if ($this->has_sla_plugin) {
				$this->hook ('simple_local_avatar');
			}
			$this->hook ('get_avatar', 'get_avatar', 10, 5);

			add_shortcode ('wp_biographia', array ($this, 'shortcode'));
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
		 * "loop_start" action hook; called before the start of the Loop.
		 */

		function loop_start () {
			$settings = WP_Biographia::get_option ();
			if (isset ($settings['sync_content_wpautop']) && ($settings['sync_content_wpautop'] == 'on')) {
				$priority = has_filter ('the_content', 'wpautop');
				if ($priority !== false) {
					$content_priority = $settings['admin_content_priority'];
					$this->content_autop->has_filter = true;
					$this->content_autop->original = $priority;
					$this->content_autop->new = --$content_priority;

					remove_filter ('the_content', 'wpautop', $this->content_autop->original);
					add_filter ('the_content', 'wpautop', $this->content_autop->new);
				}
			}
			if (isset ($settings['sync_excerpt_wpautop']) && ($settings['sync_excerpt_wpautop'] == 'on')) {
				$priority = has_filter ('the_excerpt', 'wpautop');
				if ($priority !== false) {
					$excerpt_priority = $settings['admin_content_priority'];
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
		 * "wp_enqueue_scripts" action hook; called to load the plugin's CSS for the
		 * Biography Box.
		 */

		function style () {
			WP_BiographiaBox::enqueue_box_style();
		}

		static public function enqueue_box_style() {
			$src = WPBIOGRAPHIA_URL . 'css/wp-biographia';
			$src = WP_Biographia::make_css_path($src);
			$handle = 'wp-biographia-bio';
			wp_enqueue_style ($handle, $src);
			
			$theme = wp_get_theme();
			$css = 'css/wp-biographia-' . $theme->get_stylesheet() . '.css';
			$path = WPBIOGRAPHIA_PATH . $css;
			$src = WPBIOGRAPHIA_URL . $css;

			if (is_file($path)) {
				$deps = array('wp-biographia-bio');
				$handle = 'wp-biographia-bio-' . $theme->get_stylesheet();
				wp_enqueue_style($handle, $src, $deps);
			}
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
			$option = WP_Biographia::get_option ('admin_lock_to_loop');
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

			$location = WP_Biographia::get_option ('display_location');
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
					$optval = WP_Biographia::get_option ($optname);
					if (!empty ($optval) && $optval === 'on') {
						$do_display = true;
						$bio_stub = self::BIOGRAPHY_STUB;
					}
					
					elseif (isset ($options[self::ARCHIVE_STUB])) {
						$optname = $options[self::ARCHIVE_STUB] . $post_type_name;
						$optval = WP_Biographia::get_option ($optname);
						if (!empty ($optval) && $optval === 'on') {
							$do_display = true;
							$bio_stub = self::ARCHIVE_BIOGRAPHY_STUB;
						}
					}

					if ($do_display || $this->is_shortcode) {
						if (isset ($bio_stub) && isset ($options[$bio_stub])) {
							$optname = $options[$bio_stub] . $post_type_name;
							$optval = WP_Biographia::get_option ($optname);
							if (!empty ($optval) && $optval === 'excerpt') {
								$this->override['type'] = $optval;
							}
						}

						$bio_content = $this->display ();

						// check exclusions
						$post_option = $post_type . '_exclusions';
						$global_option = 'global_' . $post_type . '_exclusions';
					
						if (WP_Biographia::get_option ($post_option) ||
								WP_Biographia::get_option ($global_option)) {
							$post_exclusions = $global_exclusions = array ();
						
							if (WP_Biographia::get_option ($post_option)) {
								$post_exclusions = explode (',',
															WP_Biographia::get_option ($post_option));
							}
							if (WP_Biographia::get_option ($global_option)) {
								$global_exclusions = explode (',',
															WP_Biographia::get_option ($global_option));
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
			$settings = WP_Biographia::get_option ();
			$excluded = false;
			$options = array ();

			if ((get_user_meta ($this->author_id,
						'wp_biographia_suppress_posts',
						true) == 'on') &&
					($post->post_type != 'page')) {
				return $content;
			}

			if (!is_page ()) {
				$categories = explode (',', $settings['category_exclusions']);
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
					$option = 'display_front_';
					if (!$excluded || $this->is_shortcode) {
						$options[self::DISPLAY_STUB] = 'display_front_';
						$options[self::BIOGRAPHY_STUB] = 'display_front_bio_';
						$new_content = $this->post_types_cycle ($options, $content, $pattern);
					}
					else {
						$new_content = $content;
					}
					break;

				case "archive":
					if (!$excluded || $this->is_shortcode) {
						if (is_post_type_archive ()) {
							$options[self::DISPLAY_STUB] = 'display_archives_';
							$options[self::BIOGRAPHY_STUB] = 'display_archives_bio_';
						}
						else {
							$options[self::DISPLAY_STUB] = 'display_archives_';
							$options[self::BIOGRAPHY_STUB] = 'display_archives_bio_';

							if (is_author ()) {
								$options[self::ARCHIVE_STUB] = 'display_author_archives_';
								$options[self::ARCHIVE_BIOGRAPHY_STUB] = 'display_author_archives_bio_';
							}
							else if (is_category ()) {
								$options[self::ARCHIVE_STUB] = 'display_category_archives_';
								$options[self::ARCHIVE_BIOGRAPHY_STUB] = 'display_category_archives_bio_';
							}
							else if (is_date ()) {
								$options[self::ARCHIVE_STUB] = 'display_date_archives_';
								$options[self::ARCHIVE_BIOGRAPHY_STUB] = 'display_date_archives_bio_';
							}
							else if (is_tag ()) {
								$options[self::ARCHIVE_STUB] = 'display_tag_archives_';
								$options[self::ARCHIVE_BIOGRAPHY_STUB] = 'display_tag_archives_bio_';
							}
						}
						
						$new_content = $this->post_types_cycle ($options, $content, $pattern);
					}
					else {
						$new_content = $content;
					}
					break;

				case "page":
					$option = WP_Biographia::get_option ('display_pages');
					if ((isset ($option) &&	$option &&
							get_user_meta ($this->author_id, 'wp_biographia_suppress_pages', true) !== 'on') ||
							($this->is_shortcode && get_user_meta ($this->author_id, 'wp_biographia_suppress_pages', true) !== 'on')) {
						$this->display_bio = true;
					}

					if (!$excluded && $this->display_bio) {
						if (WP_Biographia::get_option ('page_exclusions')) {
							$page_exclusions = explode (',', WP_Biographia::get_option ('page_exclusions'));
							$this->display_bio = (!in_array ($post->ID, $page_exclusions));
						}
					}

					if (!$excluded && $this->display_bio) {
						$option = WP_Biographia::get_option ('display_bio_pages');
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
						$options[self::DISPLAY_STUB] = 'display_';
						$options[self::BIOGRAPHY_STUB] = 'display_bio_';
						$new_content = $this->post_types_cycle ($options, $content, $pattern);
					}

					else {
						$new_content = $content;
					}
					break;
				
				case "feed":
					$option = WP_Biographia::get_option ('display_feed');
					if (isset ($option) && $option) {
						$this->display_bio = true;
					}

					else {
						$this->display_bio = $this->is_shortcode;
					}

					if (!$excluded && $this->display_bio) {
						$this->for_feed = true;
						$option = WP_Biographia::get_option ('display_bio_feed');
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

								$user_set = WP_Biographia::get_users ($current_role);
								if (!empty ($user_set)) {
									$users = array_merge ($users, $user_set);
								}
							}
						}	// end-foreach ($role ...)
					}
				
					// No role filtering needed, just grab 'em all ...
					else {
						$users = WP_Biographia::get_users ();
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
			$settings = WP_Biographia::get_option();
			$design = $settings['design_type'];
			
			$overrides = $this->get_overrides();
			$meta = $this->get_user_meta($overrides);
			$title = $this->make_wpb_title($design, $meta, $overrides);
			$border = $this->make_wpb_border($design);
			$avatar = $this->make_wpb_avatar($design, $meta, $overrides);
			$text = $this->make_wpb_text($meta, $overrides);
			$links = $this->make_wpb_links($design, $meta, $overrides, $title);

			/*<div class="wp-biographia-container-top" style="background-color: #FFEAA8; border-top: 4px solid #000000;">
				<div class="wp-biographia-pic" style="height:100px; width:100px;">
					<img alt="" src="http://1.gravatar.com/avatar/d300ed1dcec487e20c8600570abeffab?s=100&amp;d=http%3A%2F%2F1.gravatar.com%2Favatar%2Fad516503a11cd5ca435acc9bb6523536%3Fs%3D100&amp;r=G" class="wp-biographia-avatar avatar-100 photo" height="100" width="100">
				</div>
				<div class="wp-biographia-text">
					<h3>About <a href="http://localvicchi:8888/author/gary/" title="Gary Gale">Gary Gale</a></h3>
					<p>Self professed ”geek with a life”, <a href="http://www.vicchi.org">geo-blogger</a>, <a href="http://www.vicchi.org/speaking/">geo-talker</a> and <a href="http://twitter.com/#!/vicchi">geo-tweeter</a>, Gary works in London and Berlin as Director of <a href="http://here.com">Global Community Programs for Nokia’s HERE Maps</a>; he’s a co-founder of <a href="http://wherecamp.eu">WhereCamp EU</a>, the chair of <a href="http://www.w3gconf.com">w3gconf</a> and sits on the W3C POI Working Group and the UK Location User Group. A <a href="https://github.com/vicchi">contributor</a> to the <a href="http://mapstraction.com/">Mapstraction</a> mapping API, Gary speaks and presents at a wide range of conferences and events including <a href="http://where2conf.com/">Where 2.0</a>, State of the Map, <a href="http://www.agigeocommunity.com/">AGI GeoCommunity</a>, <a href="http://geoloco.tv/">Geo-Loco</a>, <a href="http://socialloco.net/">Social-Loco</a>, <a href="http://geomobldn.org/">GeoMob</a>, the <a href="http://geospatial.bcs.org/web/">BCS GeoSpatial SG</a> and <a href="http://news.thewherebusiness.com/lbs">LocBiz</a>. Writing as regularly as possible on location, place, maps and other facets of geography, Gary blogs at <a href="http://www.vicchi.org">www.vicchi.org</a> and tweets as <a href="http://twitter.com/#!/vicchi">@vicchi</a>.</p>
					<div class="wp-biographia-links">
						<small>
							<ul class="wp-biographia-list wp-biographia-list-text">
								<li><a href="mailto:gary@vicchi.org" target="_self" title="Send Gary Gale Mail" class="wp-biographia-link-text">Mail</a></li>
								 | 
								<li><a href="http://www.garygale.com/" target="_self" title="Gary Gale On The Web" class="wp-biographia-link-text">Web</a></li>
								 | 
								<li><a href="https://twitter.com/#!/vicchi" target="_self" title="Gary Gale On Twitter" class="wp-biographia-link-text">Twitter</a></li>
								 | 
								<li><a href="https://www.facebook.com/vicchi" target="_self" title="Gary Gale On Facebook" class="wp-biographia-link-text">Facebook</a></li>
								 | 
								<li><a href="http://uk.linkedin.com/in/garygale" target="_self" title="Gary Gale On LinkedIn" class="wp-biographia-link-text">LinkedIn</a></li>
								 | 
								<li><a href="https://plus.google.com/112341509012024280765?rel=author" target="_self" title="Gary Gale On Google+" class="wp-biographia-link-text">Google+</a></li>
								 | 
								<li><a href="http://localvicchi:8888/author/gary/" target="_self" title="More Posts By Gary Gale" class="wp-biographia-link-text">More Posts (360)</a></li>
							</ul>
						</small>
					</div>
				</div>
			</div>*/

			// CLASSIC LAYOUT
			
			/*<div class="%WPB-CONTAINER-CLASS%" style="%WPB-CONTAINER-STYLE%">
				<div class="%WPB-AVATAR-CLASS" style="%WPB-AVATAR-STYLE%">
					%WPB-AVATAR%
				</div>
				<div class="%WPB-TEXT-CLASS%">
					%WPB-TITLE%
					%WPB-BIO%
					<div class="%WPB-LINKS-CLASS%">
						<ul class="%WPB-LINKS-LIST-CLASS">
							%WPB-LINKS%
						</ul>
					</div>
				</div>
			</div>*/

			// RESPONSIVE LAYOUT
			/*<div class="%WPB-CONTAINER-CLASS%" style="%WPB-CONTAINER-STYLE%">
				<div class="%WPB-AVATAR-CLASS" style="%WPB-AVATAR-STYLE%">
					%WPB-AVATAR%
				</div>
				%WPB-TITLE%
				<div class="%WPB-TEXT-CLASS%">
					%WPB-BIO%
				</div>
				<div class="%WPB-LINKS-CLASS%">
					<ul class="%WPB-LINKS-LIST-CLASS">
						%WPB-LINKS%
					</ul>
				</div>
			</div>*/

			if ($design === 'classic') {
				$layout = 
				'<div class="%WPB-CONTAINER-CLASS%" style="%WPB-CONTAINER-STYLE%">' . PHP_EOL .
				'<div class="%WPB-AVATAR-CLASS%" style="%WPB-AVATAR-STYLE%">' . PHP_EOL .
				'%WPB-AVATAR%' . PHP_EOL .
				'</div>' . PHP_EOL .
				'<div class="%WPB-TEXT-CLASS%">' . PHP_EOL .
				'%WPB-TITLE%' . PHP_EOL .
				'%WPB-BIO%' . PHP_EOL .
				'<div class="%WPB-LINKS-CLASS%">' . PHP_EOL .
				'<ul class="%WPB-LINKS-LIST-CLASS%">' . PHP_EOL .
				'%WPB-LINKS%' . PHP_EOL .
				'</ul>' . PHP_EOL .
				'</div>' . PHP_EOL .
				'</div>' . PHP_EOL .
				'</div>'. PHP_EOL;
			}

			else if ($design === 'responsive') {
				$layout = 
				'<div class="%WPB-CONTAINER-CLASS%" style="%WPB-CONTAINER-STYLE%">' . PHP_EOL .
				'<div class="%WPB-AVATAR-CLASS%" style="%WPB-AVATAR-STYLE%">' . PHP_EOL .
				'%WPB-AVATAR%' . PHP_EOL .
				'</div>' . PHP_EOL .
				'<div class="%WPB-TITLE-CLASS%">' . PHP_EOL .
				'%WPB-TITLE%' . PHP_EOL .
				'</div>' . PHP_EOL . 
				'<div class="%WPB-TEXT-CLASS%">' . PHP_EOL .
				'%WPB-BIO%' . PHP_EOL .
				'</div>' . PHP_EOL .
				'<div class="%WPB-LINKS-CLASS%">' . PHP_EOL .
				'<ul class="%WPB-LINKS-LIST-CLASS%">' . PHP_EOL .
				'%WPB-LINKS%' . PHP_EOL .
				'</ul>' . PHP_EOL .
				'</div>' . PHP_EOL .
				'</div>' . PHP_EOL;
			}

			else {
				
			}

			$search = array();
			$replace = array();

			$search[] = '%WPB-CONTAINER-CLASS%';
			$replace[] = $border['class'];
			
			$search[] = '%WPB-CONTAINER-STYLE%';
			$replace[] = $border['style'];
			
			$search[] = '%WPB-TITLE-CLASS%';
			$replace[] = $title['class'];
			
			$search[] = '%WPB-TITLE%';
			if ($title['enabled']) {
				$replace[] = implode('', $title['content']);
			}
			else {
				$replace[] = '';
			}
			
			$search[] = '%WPB-AVATAR-CLASS%';
			$search[] = '%WPB-AVATAR-STYLE%';
			$search[] = '%WPB-AVATAR%';
			if ($avatar['enabled']) {
				$replace[] = $avatar['class'];
				$replace[] = $avatar['style'];
				$replace[] = $avatar['avatar'];
			}
			else {
				$replace[] = 'wp-biographia-pic-hidden';
				$replace[] = 'display: none;';
				$replace[] = '';
			}
			
			$search[] = '%WPB-TEXT-CLASS%';
			$replace[] = $avatar['bio-class'];
			
			$search[] = '%WPB-BIO%';
			if ($text['enabled']) {
				$replace[] = $text['content'];
			}
			else {
				$replace[] = '';
			}
			
			$search[] = '%WPB-LINKS-CLASS%';
			$search[] = '%WPB-LINKS-LIST-CLASS%';
			$search[] = '%WPB-LINKS%';
			if ($links['enabled']) {
				$replace[] = $links['links-class'];
				$replace[] = $links['links-list-class'];
				$replace[] = implode(PHP_EOL, $links['content']);
			}
			else {
				$replace[] = 'wp-biographia-links-hidden';
				$replace[] = 'wp-biographia-list-hidden';
				$replace[] = '';
			}

			$biography_box = array();
			$biography_box[] = '<!-- WP Biographia ' . WP_Biographia::DISPLAY_VERSION . ' -->';
			$biography_box[] = '<!-- LAYOUT: ' . $design . ' -->';
			$biography_box[] = str_replace($search, $replace, $layout);
			$biography_box[] = '<!-- WP Biographia ' . WP_Biographia::DISPLAY_VERSION . ' -->';
			
			return implode(PHP_EOL, $biography_box);
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
	
		static function check_option (&$settings, $key) {
			return (isset ($settings[$key]) && !empty ($settings[$key]));
		}


		// REWORK STARTS HERE

		private function get_overrides() {
			global $post;
			$options = WP_Biographia::get_option();
			$overrides = array();
			
			$overrides['enabled'] = ($options['admin_post_overrides'] === 'on');
			if ($overrides['enabled']) {
				$overrides['bio_override'] = (get_post_meta($post->ID, '_wp_biographia_bio_override', true) === 'on');
				$overrides['bio_text'] = get_post_meta ($post->ID, '_wp_biographia_bio_text', true);
				$overrides['title_override'] = (get_post_meta ($post->ID, '_wp_biographia_title_override', true) === 'on');
				$overrides['title_text'] = get_post_meta ($post->ID, '_wp_biographia_title_text', true);
				$overrides['suppress_avatar'] = (get_post_meta ($post->ID, '_wp_biographia_suppress_avatar', true) === 'on');
				$overrides['suppress_links'] = (get_post_meta ($post->ID, '_wp_biographia_suppress_links', true) === 'on');
			}
			
			return $overrides;
		}

		
		private function get_user_meta($overrides) {
			global $post;
			$options = WP_Biographia::get_option();
			$meta = array();

			if (!$this->author_id || $this->author_id === 0) {
				$meta['id'] = $post->post_author;
			}
			else {
				$meta['id'] = $this->author_id;
			}
			
			foreach(WP_Biographia::defaults() as $key => $info) {
				if ($key !== 'first-last-name') {
					$meta[$key] = get_the_author_meta($info['field'], $meta['id']);
				}
				else {
					$meta[$key] = get_the_author_meta('first_name', $meta['id']) .
						' ' .
						get_the_author_meta('last_name', $meta['id']);
				}
			}
			
			if ($overrides['enabled'] && $overrides['bio_override']) {
				$meta['bio'] = $overrides['bio_text'];
			}
			
			elseif (!empty($this->override) && !empty($this->override['type']) && $this->override['type'] === 'excerpt') {
				$excerpt = get_user_meta($meta['id'], 'wp_biographia_short_bio', true);
				if (!empty($excerpt)) {
					$meta['bio'] = $excerpt;
				}
			}

			$meta['posts'] = (int)count_user_posts ($meta['id']);
			$meta['posts_url'] = get_author_posts_url ($meta['id']);

			$meta['pic_size'] =
				 (isset ($options['content_image_size'])) ?
					$options['content_image_size'] : '100';

			$this->sentry = true;
			$meta['pic'] = get_avatar($meta['email'], $meta['pic_size']);
			$this->sentry = false;
			
			return $meta;
		}
		
		private function make_wpb_title($design, $meta, $overrides) {
			$text = array();
			$title = array('enabled' => false, 'name' => '', 'content' => array());
			$options = WP_Biographia::get_option();
			
			$title['class'] = 'wp-biographia-' . $design . '-title';
			if (!empty($options['design_wrap']) && $options['design_wrap'] === 'on') {
				$title['class'] .= ' wp-biographia-' . $design . '-title-wrap';
			}
			if ($overrides['enabled'] && $overrides['title_override']) {
				$text[] = $overrides['title_text'];
			}

			else if (!empty($options['content_prefix']) || !empty($options['content_name'])) {
				$prefix = '';
				
				if (!empty($this->override) && !empty($this->override['prefix'])) {
					$prefix = $this->override['prefix'];
				}
				
				elseif (!empty($options['content_prefix'])) {
					$prefix = $options['content_prefix'];
				}
				
				if (!empty($prefix)) {
					$text[] = $prefix . ' ';
				}
				
				$name_type = '';
				if (!empty($this->override) && !empty($this->override['name'])) {
					$name_type = $this->override['name'];
				}
				elseif (!empty($options['content_name'])) {
					$name_type = $options['content_name'];
				}
				
				if (!empty($name_type) && $name_type !== 'none') {
					switch ($name_type) {
						case 'first-last-name':
						case 'account-name':
						case 'nickname':
							$title['name'] = $meta[$name_type];
							break;
						default:
							$title['name'] = $meta['display-name'];
							break;
					}
					
					if (!empty($options['content_authorpage']) && ($options['content_authorpage'] === 'on')) {
						$fmt = '<a href="%s" title="%s">%s</a>';
						$text[] = sprintf($fmt, $meta['posts_url'], $title['name'], $title['name']);
					}
					else {
						$text[] = $title['name'];
					}
				}
				
				$title['enabled'] = true;
				$title['content'][] = '<h3>';
				$title['content'][] = apply_filters('wp_biographia_content_title', implode('', $text));
				$title['content'][] = '</h3>';
			}
			
			return $title;
		}
		
		private function make_wpb_border($design) {
			$options = WP_Biographia::get_option();
			$type = $options['style_border'];
			$color = $options['style_border_color'];
			$background = $options['style_bg'];
			$class = 'wp-biographia-' . $design . '-container-' . $type;
			$style = 'background-color: ' . $background . ';';
			
			switch ($type) {
				case 'top':
					$style .= ' border-top: 4px solid ' . $color . ';';
					break;
				case 'around':
					$style .= ' border: 1px solid ' . $color . ';';
					break;
				case 'none':
				default:
					break;
			}
			
			return array('class' => $class, 'style' => $style);
		}
		
		private function make_wpb_avatar($design, $meta, $overrides) {
			$options = WP_Biographia::get_option();
			$avatar = array('enabled' => false, 'class' => '', 'avatar' => '');
			
			$enabled = (!empty($options['content_image']) && ($options['content_image'] === 'on'));
			if ($enabled && $overrides['enabled'] && $overrides['suppress_avatar']) {
				$enabled = false;
			}
			
			$avatar['enabled'] = $enabled;
			if ($enabled) {
				$avatar['bio-class'] = 'wp-biographia-' . $design . '-text';
				$avatar['class'] = 'wp-biographia-' . $design . '-avatar';

				if (!empty($options['design_wrap']) && $options['design_wrap'] === 'on') {
					$avatar['bio-class'] .= ' wp-biographia-' . $design . '-text-wrap';
					$avatar['class'] .= ' wp-biographia-' . $design . '-avatar-wrap';
				}

				$fmt = 'height: %dpx; width: %dpx;';
				$avatar['style'] = sprintf($fmt, $meta['pic_size'], $meta['pic_size']);
				$avatar['avatar'] = $meta['pic'];
			}
			else {
				$avatar['bio-class'] = 'wp-biographia-text-no-pic';
			}
			
			return $avatar;
		}
		
		private function make_wpb_text($meta, $overrides) {
			$options = WP_Biographia::get_option();
			$text[] = array('enabled' => false, 'content' => '');
			
			if (!empty($options['content_bio']) || ($overrides['enabled'] && $overrides['bio_override'])) {
				$text['enabled'] = true;
				$text['content'] = '<p>' . $meta['bio'] . '</p>';
			}
			
			return $text;
		}
		
		private function make_wpb_links($design, $meta, $overrides, $title) {
			$options = WP_Biographia::get_option();
			$links = array('enabled' => false, 'icons' => false, 'url' => '', 'content' => array());
			
			if (!$overrides['enabled'] || !$overrides['suppress_links']) {
				if (!$this->for_feed) {
					$links['icons'] = (!empty($options['content_icons']) && ($options['content_icons'] === 'on')) ? 'icon' : 'text';
				}
				
				if ($links['icons'] && (!empty($options['content_alt_icons']) && $options['content_alt_icons'] === 'on' && !empty($options['content_icon_url']))) {
					$links['url'] = $options['content_icon_url'];
				}

				$link_items = $this->link_items ();
				if ($this->for_feed) {
					$item_stub = '<a href="%s" %s title="%s" class="%s">%s</a>';
				}
				else {
					$item_stub = ($links['icons'] == "icon") ? '<li><a href="%s" %s title="%s" class="%s"><img src="%s" class="%s" /></a></li>' : '<li><a href="%s" %s title="%s" class="%s">%s</a></li>';
				}
				$title_name_stub = __('%1$s On %2$s', 'wp-biographia');
				$title_noname_stub = __('On %s', 'wp-biographia');

				$link_meta = 'target="' . $options['content_link_target']. '"';
				if (!empty($options['content_link_nofollow']) &&
						($options['content_link_nofollow'] == 'on')) {
					$link_meta .= ' rel="nofollow"';
				}

				// Deal with the email link first as a special case ...
				if ((!empty ($options['content_email']) && ($options['content_email'] == 'on')) && (!empty ($meta['email']))) {
					if (!empty ($title['name'])) {
						$link_title = sprintf (__('Send %s Mail', 'wp-biographia'), $title['name']);
					}

					else {
						$link_title = __('Send Mail', 'wp-biographia');
					}

					$link_text = __('Mail', 'wp-biographia');

					$link_body = ($links['icons'] == "icon") ? $this->icon_dir_url . 'mail.png' : $link_text;
					$links['content'][] = $this->link_item ($links['icons'], $item_stub, 'mailto:' . antispambot ($meta['email']), $link_meta, $link_title, $link_body);
				}

				// Now deal with the other links that follow the same format and can be "templatised" ...

				$supported_links = $this->supported_link_items ();
				$config_links = $options['admin_links'];
				foreach ($link_items as $link_key => $link_attrs) {
					$display_link = false;
					if (array_key_exists ($link_key, $supported_links)) {
						$option_name = 'content_' . $link_key;
						if ($link_key == 'web') {
							$display_link = (!empty ($options[$option_name]) && ($options[$option_name] == 'on') && (!empty ($meta[$link_key])));
						}

						else {
							$display_link = (isset ($config_links[$link_key]) && $config_links[$link_key] == 'on' && !empty ($options[$option_name]) && ($options[$option_name] == 'on') && (!empty ($meta[$link_key])));
						}
					}

					else {
						$display_link = (isset ($config_links[$link_key]) && $config_links[$link_key] == 'on' && !empty ($meta[$link_key]));
					}

					if ($display_link) {
						if (!empty ($title['name'])) {
							$link_title = sprintf ($title_name_stub, $title['name'], $link_attrs['link_title']);
						}

						else {
							$link_title = sprintf ($title_noname_stub, $link_attrs['link_title']);
						}

						$link_body = ($links['icons'] == "icon") ? $link_attrs['link_icon'] : $link_attrs['link_text'];

						$links['content'][] = $this->link_item ($links['icons'], $item_stub, $meta[$link_key], $link_meta, $link_title, $link_body);
					}
				}	// end-foreach (...)

				// Finally, deal with the "More Posts" link
				if (!empty ($options['content_posts']) && ($options['content_posts'] != 'none') && ($meta['posts'] > 0)) {
					if (!empty ($title['name'])) {
						$link_title = sprintf (__('More Posts By %s', 'wp-biographia'), $title['name']);
					}

					else {
						$link_title = __('More Posts', 'wp-biographia');
					}

					switch ($options['content_posts']) {
						case 'extended':
							$link_text = __('More Posts', 'wp-biographia') . ' ('
								. $meta['posts']
								. ')';
							break;

						default:
							$link_text = __('More Posts', 'wp-biographia');
							break;
					}

					$link_body = ($links['icons'] == "icon") ? $this->icon_dir_url . 'wordpress.png' : $link_text;
					$links['content'][] = $this->link_item ($links['icons'], $item_stub, $meta['posts_url'], $link_meta, $link_title, $link_body);
				}

				$item_glue = ($links['icons'] == 'icon') ? "" : " | ";
				$list_class = "wp-biographia-list-" . $links['icons'];
			}

			if (!empty ($links['content'])) {
				$links['enabled'] = true;
				$links['links-class'] = 'wp-biographia-' . $design . '-links';
				$links['links-list-class'] = 'wp-biographia-' . $design . '-list wp-biographia-' . $design . '-list-' . $links['icons'];

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
				 	$prefix . implode ($item_glue, $links['content']) . $postfix,
					$links, $params);
			}
			
			return $links;
		}


	}	// end-class (...)
}	// end-if (!class_exists(...))

WP_BiographiaBox::get_instance();

?>