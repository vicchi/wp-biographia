<?php

if (!defined('WPBIOGRAPHIA_INCLUDE_SENTRY')) {
	die ('The way is shut. It was made by those who are dead, and the dead keep it. The way is shut.');
}

if (!class_exists ('WP_BiographiaWidget')) {
	class WP_BiographiaWidget extends WP_Widget {
	
		static $name_options;
	
		private $widget_sem = false;
		private $wrap_bio = false;
	
		function __construct () {
			self::$name_options = array (
				'first-last-name' => array (
					'field' => '',
					'text' => __('Show Name As First/Last Name')
				),
				'account-name' => array (
					'field' => 'user_login',
					'text' => __('Show Name As Account Name')
				),
				'nickname' => array (
					'field' => 'nickname',
					'text' => __('Show Name As Nickname')
				),
				'display-name' => array (
					'field' => 'display_name',
					'text' => __('Show Name As Display Name')
				),
				'none' => array (
					'field' => '',
					'text' => __('Don\'t Show Name')
				)
			);

			$widget_ops = array (
				'description' => __('Add the Biography Box to your sidebar')
			);
			parent::WP_Widget ('WP_BiographiaWidget', __('WP Biographia'), $widget_ops);
		
			add_filter ('get_avatar', array ($this, 'change_avatar_css'));
		}
	
		function form ($instance) {
			$text_stub = '<p><label for="%s">%s</label><input type="text" id="%s" name="%s" value="%s" class="widefat" /></p>';
			$check_stub = '<p><input type="checkbox" id="%s" name="%s" %s />&nbsp;<label for="%s">%s</label></p>';
			$radio_stub = '<input type="radio" id="%s" name="%s" value="%s" %s />&nbsp;<label for="%s">%s</label><br />';
			$content = array ();
		
			$instance = wp_parse_args (
				(array)$instance,
				array (
					'show_title' => 'on',
					'single_title' => __('Written By'),
					'multi_title' => __('Contributors'),
					'name_prefix' => __('About'),
					'name_options' => 'display-name',
					'show_avatar' => 'on',
					'avatar_size' => 100,
					'show_bio' => 'on',
					'short_bio' => '',
					'wrap_bio' => '',
					'show_about_link' => 'on'
				)
			);

			$content[] = sprintf ($check_stub,
				$this->get_field_id ('show_title'),
				$this->get_field_name ('show_title'),
				checked ($instance['show_title'], 'on', false),
				$this->get_field_id ('show_title'),
				__('Show Widget Title')
				);

			$content[] = sprintf ($text_stub,
				$this->get_field_id ('single_title'),
				__('Single User Title:'),
				$this->get_field_id ('single_title'),
				$this->get_field_name ('single_title'),
				esc_attr ($instance['single_title'])
				);

			$content[] = sprintf ($text_stub,
				$this->get_field_id ('multi_title'),
				__('Multiple User Title:'),
				$this->get_field_id ('multi_title'),
				$this->get_field_name ('multi_title'),
				esc_attr ($instance['multi_title'])
				);

			$content[] = sprintf ($text_stub,
				$this->get_field_id ('name_prefix'),
				__('Name Prefix:'),
				$this->get_field_id ('name_prefix'),
				$this->get_field_name ('name_prefix'),
				esc_attr ($instance['name_prefix'])
				);
			
			$content[] = '<p>';
			foreach (self::$name_options as $key => $data) {
				$content[] = sprintf ($radio_stub,
					$this->get_field_id ('name_options'),
					$this->get_field_name ('name_options'),
					$key,
					checked ($instance['name_options'], $key, false),
					$this->get_field_id ('name_options'),
					$data['text']
				);
			}
		
			/*foreach (self::$name_options as $option => $descr) {
				$content[] = sprintf ($radio_stub,
					$this->get_field_id ('name_options'),
					$this->get_field_name ('name_options'),
					$option,
					checked ($instance['name_options'], $option, false),
					$this->get_field_id ('name_options'),
					$descr
				);
			}*/
			$content[] = '</p>';
		
			$content[] = sprintf ($check_stub,
				$this->get_field_id ('show_avatar'),
				$this->get_field_name ('show_avatar'),
				checked ($instance['show_avatar'], 'on', false),
				$this->get_field_id ('show_avatar'),
				__('Show User\'s Avatar')
				);

			$content[] = sprintf ($text_stub,
				$this->get_field_id ('avatar_size'),
				__('User\'s Avatar Size:'),
				$this->get_field_id ('avatar_size'),
				$this->get_field_name ('avatar_size'),
				esc_attr ($instance['avatar_size'])
				);

			$content[] = sprintf ($check_stub,
				$this->get_field_id ('show_bio'),
				$this->get_field_name ('show_bio'),
				checked ($instance['show_bio'], 'on', false),
				$this->get_field_id ('show_bio'),
				__('Show User\'s Biography')
				);

			$content[] = sprintf ($check_stub,
				$this->get_field_id ('short_bio'),
				$this->get_field_name ('short_bio'),
				checked ($instance['short_bio'], 'on', false),
				$this->get_field_id ('short_bio'),
				__('Use User\'s Short Biography')
				);

			$content[] = sprintf ($check_stub,
				$this->get_field_id ('wrap_bio'),
				$this->get_field_name ('wrap_bio'),
				checked ($instance['wrap_bio'], 'on', false),
				$this->get_field_id ('wrap_bio'),
				__('Wrap Biography Around Avatar')
				);

			$content[] = sprintf ($check_stub,
				$this->get_field_id ('show_about_link'),
				$this->get_field_name ('show_about_link'),
				checked ($instance['show_about_link'], 'on', false),
				$this->get_field_id ('show_about_link'),
				__('Show "About" Link In User\'s Name')
				);

			echo (implode ('', $content));
		}
	
		function update ($new_instance, $old_instance) {
			$instance = $old_instance;

			$instance['show_title'] = $this->update_arg ($new_instance, 'show_title');
			$instance['single_title'] = $this->update_arg ($new_instance, 'single_title');
			$instance['multi_title'] = $this->update_arg ($new_instance, 'multi_title');
			$instance['name_prefix'] = $this->update_arg ($new_instance, 'name_prefix');
			$instance['name_options'] = $this->update_arg ($new_instance, 'name_options');
			$instance['show_avatar'] = $this->update_arg ($new_instance, 'show_avatar');
			$instance['avatar_size'] = $this->update_arg ($new_instance, 'avatar_size');
			$instance['show_bio'] = $this->update_arg ($new_instance, 'show_bio');
			$instance['short_bio'] = $this->update_arg ($new_instance, 'short_bio');
			$instance['wrap_bio'] = $this->update_arg ($new_instance, 'wrap_bio');
			$instance['show_about_link'] = $this->update_arg ($new_instance, 'show_about_link');

			return $instance;
		}
	
		function update_arg (&$src, $key) {
			if (isset ($src[$key]) && !empty ($src[$key])) {
				return strip_tags ($src[$key]);
			}
			return '';
		}
	
		function widget ($args, $instance) {
			global $wp_query;
			global $post;

			extract ($args, EXTR_SKIP);
		
			if (!is_main_query ()) {
				wp_reset_query ();
			}
	
			if ($wp_query->post_count > 0) {
				$content = array ();
				$users = array ();
			
				$content[] = $before_widget;
			
				while (have_posts ()) : the_post ();
					$user = $post->post_author;
					if (!in_array ($user, $users)) {
						$users[] = $user;
					}
				endwhile;

				$title = '';
				if (count ($users) == 1 && $instance['show_title']) {
					if ($instance['single_title']) {
						$title = $instance['single_title'];
					}
				}
			
				elseif ($instance['show_title']) {
					if ($instance['multi_title']) {
						$title = $instance['multi_title'];
					}
				
				}

				if (!empty ($title)) {
					$content[] = $before_title . $title . $after_title;
				}
			
				foreach ($users as $user) {
					$widget_bio = $this->display ($user, $args, $instance);
					$content = array_merge ($content, $widget_bio);
				}
			
				$content[] = $after_widget;
				echo implode ('', $content);
			}
		}
	
		function display ($user_id, $args, $instance) {
			extract ($args, EXTR_SKIP);

			$author = $content = array ();
		
			foreach (self::$name_options as $key => $data) {
				if ($key != 'first-last-name'  && $key != 'none') {
					$author[$key] = get_the_author_meta ($data['field'], $user_id);
				}
				elseif (!empty ($key)  && $key != 'none') {
					$author[$key] = get_the_author_meta ('first_name', $user_id) . ' ' . get_the_author_meta ('last_name', $user_id);
				}
			}	// end-foreach

			$author['posts'] = (int) count_user_posts ($user_id);
			$author['posts_url']= get_author_posts_url ($user_id);
			$author['avatar_size'] = (isset ($instance['avatar_size']) ? $instance['avatar_size'] : '100');
			$author['bio'] = get_the_author_meta ('description', $user_id);
			$author['short_bio'] = get_the_author_meta ('wp_biographia_short_bio', $user_id);
			$author['email'] = get_the_author_meta ('email', $user_id);
		
			if ($instance['name_options'] != 'none') {
				$content[] = $before_title;
				if (!empty($instance['name_prefix'])) {
					$content[] = $instance['name_prefix'] . ' ';
				}
				if ($instance['show_about_link']) {
					$content[] = sprintf ('<a href="%s">%s</a>', $author['posts_url'], $author[$instance['name_options']]);
				}
				else {
					$content[] = $author[$instance['name_options']];
				}
				$content[] = $after_title;
			}
		
			if ($instance['show_avatar'] || $instance['show_bio']) {
				$content[] = '<div class="wp-biographia-widget textwidget">';

				if ($instance['show_avatar']) {
					$this->widget_sem = true;
					$this->wrap_bio = $instance['wrap_bio'];
					$author_pic = get_avatar ($author['email'], $author['avatar_size']);
					$this->widget_sem = false;
					$content[] = $author_pic;
				}

				if ($instance['show_bio']) {
					if ($instance['short_bio'] && !empty ($author['short_bio'])) {
						$content[] = '<p>' . $author['short_bio'] . '</p>';
					}
					elseif (!empty ($author['bio'])) {
						$content[] = '<p>' . $author['bio'] . '</p>';
					}
				}

				$content[] = '</div>';
			}
		
			return $content;
		}
	
		function change_avatar_css ($class) {
			if ($this->widget_sem) {
				$css = 'class=\'wp-biographia-avatar wp-biographia-avatar-' . ($this->wrap_bio ? 'wrap' : 'nowrap');
				$class = str_replace ("class='avatar", $css, $class);
			}
			return $class;
		}
	
	}	// end-class WP_BiographiaWidget
}	// end-if (!class_exists ('WP_BiographiaWidget'))

?>