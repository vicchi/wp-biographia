<?php

if (!defined('WPBIOGRAPHIA_INCLUDE_SENTRY')) {
	die ('The way is shut. It was made by those who are dead, and the dead keep it. The way is shut.');
}

if (!class_exists ('WP_BiographiaPointers')) {
	class WP_BiographiaPointers extends WP_PluginBase {
	
		private static $instance;
		
		private function __construct () {
			$this->hook ('admin_enqueue_scripts');
		}

		public static function get_instance () {
			if (!isset (self::$instance)) {
				$c = __CLASS__;
				self::$instance = new $c ();
			}
			
			return self::$instance;
		}
		
		function admin_enqueue_scripts () {
			$dismissed = explode (',', get_user_meta (wp_get_current_user ()->ID, 'dismissed_wp_pointers', true));
			$do_tour = !in_array ('wp_biographia_pointer', $dismissed);
			if ($do_tour) {
				wp_enqueue_style ('wp-pointer');
			
				wp_enqueue_script ('jquery-ui');
				wp_enqueue_script ('wp-pointer');
				wp_enqueue_script ('utils');
			
				$this->hook ('admin_print_footer_scripts');
				$this->hook ('admin_head');
			}
		}

		function admin_head () {
			?>
	<style type="text/css" media="screen">
		#pointer-primary {
			margin: 0 5px 0 0;
		}
		</style>
			<?php
		}

		function admin_print_footer_scripts () {
			global $pagenow;
			global $current_user;
		
			$tour = array (
				'display' => array (
					'content' => '<h3>' . __('The Display Tab', 'wp-biographia') . '</h3>'
						. '<p><strong>' . __('First Steps', 'wp-biographia') . '</strong></p>'
						. '<p>' . sprintf (__('Before you do anything else; go to your <a href="%s">user profile</a> and ensure your name, biography and contact links are set. Then go to <a href="%s">Settings / Discussion</a> and ensure <em>Show Avatars</em> is checked.', 'wp-biographia'), admin_url ('profile.php'), admin_url ('options-discussion.php')) . '</p>'
						. '<p><strong>' . __('Display Settings', 'wp-biographia') . '</strong></p>'
						. '<p>' . __('Here you\'ll find broad level settings to control how the Biography Box is displayed and where. You can configure more specific display settings in the Exclusions tab and what is actually displayed in the Biography Box in the Content tab.', 'wp-biographia') . '</p>',
					'button2' => __('Next', 'wp-biographia'),
					'function' => 'window.location="' . $this->get_tab_url ('admin') . '"'
					),
				'admin' => array (
					'content' => '<h3>' . __('The Admin Tab', 'wp-biographia') . '</h3>'
					. '<p><strong>' . __('New User Settings', 'wp-biographia') . '</strong></p>'
					. '<p>' . __('Here you can configure globally whether a newly created user should have the Biography Box displayed under their posts or not. You can then control the display of the Biography Box on a per-user basis in the Exclusions tab.', 'wp-biographia') . '</p>'
					. '<p><strong>' . __('User Profile Settings', 'wp-biographia') . '</strong></p>'
					. '<p>' . __('If you want to stop the Biography Box being displayed on posts and pages according to the user\'s role you can do this here. An Administrator can still control the display of the Biography Box on a per-user basis in the 
					Exclusions tab.', 'wp-biographia') . '</p>',
					'button2' => __('Next', 'wp-biographia'),
					'function' => 'window.location="' . $this->get_tab_url ('exclude') . '"'
					),
				'exclude' => array (
					'content' => '<h3>' . __('The Exclusions Tab', 'wp-biographia') . '</h3>'
					. '<p><strong>' . __('Post, Page and Custom Post Type Exclusions', 'wp-biographia') . '</strong></p>'
					. '<p>' . __('If you want to stop the Biography Box being displayed on a single post, page or custom post type, you can do this here.', 'wp-biographia') . '</p>'
					. '<p><strong>' . __('Category Exclusions', 'wp-biographia') . '</strong></p>'
					. '<p>' . __('If you want to stop the Biography Box being displayed on a single post or custom post type by Category, you can do this here.', 'wp-biographia') . '</p>'
					. '<p><strong>' . __('User Hiding', 'wp-biographia') . '</strong></p>'
					. '<p>' . __('If you want to stop the Biography Box being displayed on a single post, page or custom post type on a per-user basis, you can do this here.', 'wp-biographia') . '</p>',				'button2' => __('Next', 'wp-biographia'),
					'function' => 'window.location="' . $this->get_tab_url ('style') . '"'
					),
				'style' => array (
					'content' => '<h3>' . __('The Style Tab', 'wp-biographia') . '</h3>'
					. '<p><strong>' . __('Controlling The Look Of the Biography Box', 'wp-biographia') . '</strong></p>'
					. '<p>' . __('This tab contains broad level settings to control how the Biography Box is styled; its background colour and border. If you want more specific control over how the Biography Box looks, you can do this with custom CSS.', 'wp-biographia') . '</p>', 
					'button2' => __('Next', 'wp-biographia'),
					'function' => 'window.location="' . $this->get_tab_url ('content') . '"'
					),
				'content' => array (
					'content' => '<h3>' . __('The Content Tab', 'wp-biographia') . '</h3>'
					. '<p><strong>' . __('Controlling The Biography Box Content', 'wp-biographia') . '</strong></p>'
					. '<p>' . __('This tab contains settings that control what information is and is not displayed within the Biography Box. Here you can add your name, your avatar, your contact links, a <em>more posts</em> link and control whether links are shown as text or as icons.', 'wp-biographia'),
					'button2' => __('Next', 'wp-biographia'),
					'function' => 'window.location="' . $this->get_tab_url ('defaults') . '"'
					),
				'defaults' => array (
					'content' => '<h3>' . __('The Defaults Tab', 'wp-biographia') . '</h3>'
					. '<p><strong>' . __('Reset WP Biographia', 'wp-biographia') . '</strong></p>'
					. '<p>' . __('Reset the plugin back to a just installed state, clearing any configuration settings you may have made. This is the equivalent to deactivating, uninstalling and reinstalling the plugin. <strong><em>Use with care</em></strong>.', 'wp-biographia') . '</p>',
					'button2' => __('Next', 'wp-biographia'),
					'function' => 'window.location="' . $this->get_tab_url ('colophon') . '"'
					),
				'colophon' => array (
					'content' => '<h3>' . __('The Colophon Tab', 'wp-biographia') . '</h3>'
					. '<p><strong>' . __('About WP Biographia', 'wp-biographia') . '</p></strong>'
					. '<p>' . __('This tab contains the details on how this plugin was written and why it\'s called WP Biographia. You can also find a helpful display of the plugin\'s configuration settings which you can use when asking for support on the <a href="http://wordpress.org/tags/wp-biographia?forum_id=10">WordPress forums</a>.', 'wp-biographia') . '</p>'
					. '<p>' . __('This is the end of the tour. To see this again you can click on the "<em>restart the plugin tour</em>" link, found on the <em>Help &amp; Support</em> side box on any of the plugin\'s admin tabs.', 'wp-biographia') . '</p>'
					)
				);
			
			$tab = '';
			if (isset ($_GET['tab'])) {
				$tab = $_GET['tab'];
			}

			$sub_page = '';
			if (isset ($_GET['page'])) {
				$sub_page = $_GET['page'];
			}
		
			$restart_tour = false;
			if (isset ($_GET['wp_biographia_restart_tour'])) {
				if (check_admin_referer ('wp-biographia-restart-tour')) {
					$restart_tour = true;
				}
			}
		
			$function = '';
			$button2 = '';
			$options = array ();
			$show_pointer = false;
		
			if ($restart_tour || ('options-general.php' != $pagenow || !array_key_exists ($tab, $tour))) {
				$show_pointer = true;
				$file_error = true;
				$id = '#menu-settings';
				$content = '<h3>' . sprintf (__('What\'s New In WP Biographia %s?', 'wp-biographia'), WP_Biographia::DISPLAY_VERSION) . '</h3>';

				$whatsnew_file = WPBIOGRAPHIA_PATH . 'help/whatsnew-' . WP_Biographia::VERSION . '.html';
				if (file_exists ($whatsnew_file)) {
					$whatsnew = file_get_contents ($whatsnew_file);
					if (isset ($whatsnew) && !empty ($whatsnew)) {
						$file_error = false;
						$content .= $whatsnew;
					}
				}
				
				if ($file_error) {
					$content .= '<p>' . sprintf (__('Something seems to be wrong with your WP Biographia installation; the file %s could not be found', 'wp-biographia'), $whatsnew_file) . '</p>';
				}

				$content .= '<p>' . __('Want to know more? Look in the plugin\'s <code>readme.txt</code> or just click the <em>Find Out More</em> button below.', 'wp-biographia' ) . '</p>';

				$options = array (
					'content' => $content,
					'position' => array ('edge' => 'left', 'align' => 'center')
					);
				$button2 = __( "Find Out More", 'wp-biographia' );
				$function = 'document.location="' . $this->get_tab_url ('display') . '";';
			}
		
			else {
				if ($tab != '' && in_array ($tab, array_keys ($tour))) {
					$show_pointer = true;
					$id = "#wp-biographia-tab-$tab";
					$options = array (
						'content' => $tour[$tab]['content'],
						'position' => array ('edge' => 'top', 'align' => 'left')
						);
					$button2 = false;
					$function = '';
					if (isset ($tour[$tab]['button2'])) {
						$button2 = $tour[$tab]['button2'];
					}
					if (isset ($tour[$tab]['function'])) {
						$function = $tour[$tab]['function'];
					}
				}
			}
		
			if ($show_pointer) {
				$this->make_pointer_script ($id, $options, __('Close', 'wp-biographia'), $button2, $function);
			}
		}
	
		function make_pointer_script ($id, $options, $button1, $button2=false, $function='') {
			?>
			<script type="text/javascript">
				(function ($) {
					var wp_biographia_tour_opts = <?php echo json_encode ($options); ?>, setup;
				
					wp_biographia_tour_opts = $.extend (wp_biographia_tour_opts, {
						buttons: function (event, t) {
							button = jQuery ('<a id="pointer-close" class="button-secondary">' + '<?php echo $button1; ?>' + '</a>');
							button.bind ('click.pointer', function () {
								t.element.pointer ('close');
							});
							return button;
						},
						close: function () {
							$.post (ajaxurl, {
								pointer: 'wp_biographia_pointer',
								action: 'dismiss-wp-pointer'
							});
						}
					});
				
					setup = function () {
						$('<?php echo $id; ?>').pointer(wp_biographia_tour_opts).pointer('open');
						<?php if ($button2) { ?>
							jQuery ('#pointer-close').after ('<a id="pointer-primary" class="button-primary">' + '<?php echo $button2; ?>' + '</a>');
							jQuery ('#pointer-primary').click (function () {
								<?php echo $function; ?>
							});
							jQuery ('#pointer-close').click (function () {
								$.post (ajaxurl, {
									pointer: 'wp_biographia_pointer',
									action: 'dismiss-wp-pointer'
								});
							})
						<?php } ?>
					};
				
					if (wp_biographia_tour_opts.position && wp_biographia_tour_opts.position.defer_loading) {
						$(window).bind('load.wp-pointers', setup);
					}
					else {
						setup ();
					}
				}) (jQuery);
			</script>
			<?php
		}
	
		function get_tab_url ($tab) {
			$url = admin_url ('options-general.php');
			$url .= '?page=wp-biographia/wp-biographia.php&tab=' . $tab;
		
			return $url;
		}
	}	// end-class WP_BiographiaPointers
}	// end-if (!class_exists ('WP_BiographiaPointers'))

WP_BiographiaPointers::get_instance ();

?>