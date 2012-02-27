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
	public $wp_biographia_settings = array();
	public $display_bio = false;
	public $for_feed = false;
	public $is_shortcode = false;
	public $icon_dir_url = '';
	
	const OPTIONS = 'wp_biographia_settings';
	const WPBIOGRAPHIA_VERSION = '24';
	const WPBIOGRAPHIAURL_URL = '';
	const WPBIOGRAPHIAURL_PATH = '';
	
	function __construct() { 
		self::$instance = $this;
		define ('WPBIOGRAPHIAURL_URL', plugin_dir_url (__FILE__));
		define ('WPBIOGRAPHIAURL_PATH', plugin_dir_path (__FILE__));
		$this->author_id = get_the_author_meta ('ID');
		$this->override = NULL;
		$this->hook ('plugins_loaded');
		$this->icon_dir_url = WPBIOGRAPHIAURL_URL . 'images/';
	}
	
	function plugins_loaded () {

		register_activation_hook (__FILE__, array ($this, 'add_settings'));

		$this->hook ('wp_enqueue_scripts', 'style');
		$this->hook ('init');	
		$this->hook ('the_excerpt', 'insert');
		$this->hook ('the_content', 'insert');
		$this->hook ('user_contactmethods');
		
		add_shortcode ('wp_biographia', array ($this, 'shortcode'));

		if (is_admin ()) {
			//require_once( WPBIOGRAPHIAURL_PATH . "includes/wp-biographia-admin.php" );
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
	
	function get_option( $key = '' ) {
		$options = get_option( self::OPTIONS );
		if( isset( $options[$key] ) ) return $options[$key];
		else return $options;
	}
	
	function set_option( $key , $value ) {
		$options = get_option( self::OPTIONS );
		$options[$key] = $value;
		update_option( self::OPTIONS , $options );
	}
	
	function init() {
		$lang_dir = basename( dirname (__FILE__) ) . DIRECTORY_SEPARATOR . 'lang';
		load_plugin_textdomain( 'wp-biographia', false, $lang_dir );
	}
	
	/*
	 * Filterable defaults (future use?)
	 * Used in display() and user_contactmethods()
	 *
	 * @author Travis Smith
	 * @return array output
	 */
	function wps_get_users( $role = '' , $args = array( 0 => 'ID' , 1 => 'user_login') ) {
		$wp_user_search = new WP_User_Query( array( 'role' => $role, 'fields' => $args ) );
		$roles = $wp_user_search->get_results();
		return $roles;
	}
	
	/*
	 * Sanitize/filter the author's profile contact info, via the user_contactmethods filter hook
	 */
	function user_contactmethods( $contactmethods ) {

		foreach(  $this->defaults() as $key => $data ) {
			if ( $data['contactmethod'] )
				$contactmethods[$key] = $data['contactmethod'];
		}

		return $contactmethods;
	}
	
	/*
	 * Define and set up the default settings and options for formatting the Biography Box
	 */
	function add_settings() {
		$this->wp_biographia_settings = $this->get_option();
		
		if( ! is_array( $this->wp_biographia_settings ) ) {
			$this->wp_biographia_settings = apply_filters( 'wp_biographia_default_settings' , 
				array (
					'wp_biographia_installed' => 'on',
					'wp_biographia_version' => WPBIOGRAPHIA_VERSION,
					'wp_biographia_style_bg' => '#FFEAA8',
					'wp_biographia_style_border' => 'top',
					'wp_biographia_display_front' => 'on',
					'wp_biographia_display_archives' => 'on',
					'wp_biographia_display_posts' => 'on',
					'wp_biographia_display_pages' => 'on',
					'wp_biographia_display_feed' => '',
					'wp_biographia_content_prefix' => __( 'About', 'wp-biographia' ),
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
			update_option ( 'wp_biographia_settings', $this->wp_biographia_settings );
		}

		if ( ! $this->wp_biographia_settings['wp_biographia_display_feed'] )
			$this->set_option( 'wp_biographia_display_feed' , '' );
	}
	
	/*
	 * Determines whether current page is the last page
	 *
	 * @return boolean
	 */
	function is_last_page() {
		global $page;
		global $numpages;
		global $multipage;
		
		if ( is_single() ) return true;
		elseif ( $multipage ) {
			return ( $page == $numpages ) ? true : false;
		}
		else return true;
	}
	
	/*
	 * Filterable defaults (future use?)
	 * Used in display() and user_contactmethods()
	 *
	 * @author Travis Smith
	 * @return array output
	 */
	function defaults() {
		$defaults = array(
			//option name => array ( field => custom field , contactmethod => field name)
			'account-name' => array( 
					'field' => 'user_login',
				),
			'first-last-name' => array( 
					'field' => '',
				),
			'nickname' => array( 
					'field' => 'nickname',
				),
			'display-name' => array( 
					'field' => 'display_name',
				),
			'bio' => array( 
					'field' => 'description',
				),
			'email' => array( 
					'field' => 'email',
				),
			'website' => array( 
					'field' => 'url',
				),
			'twitter' => array( 
					'field' => 'twitter',
					'contactmethod' => __( 'Twitter', 'wp-biographia' ),
				),
			'facebook' => array( 
					'field' => 'facebook',
					'contactmethod' => __( 'Facebook', 'wp-biographia' ),				
				),
			'linkedin' => array( 
					'field' => 'linkedin',
					'contactmethod' => __( 'LinkedIn', 'wp-biographia' ),				
				),
			'googleplus' => array( 
					'field' => 'googleplus',
					'contactmethod' => __( 'Google+', 'wp-biographia' ),					
				),
			'delicious' => array( 
					'field' => 'delicious',
					'contactmethod' => __( 'Delicious', 'wp-biographia' ),					
				),
			'flickr' => array( 
					'field' => 'flickr',
					'contactmethod' => __( 'Flickr', 'wp-biographia' ),					
				),
			'picasa' => array( 
					'field' => 'picasa',
					'contactmethod' => __( 'Picasa', 'wp-biographia' ),
				),
			'vimeo' => array( 
					'field' => 'vimeo',
					'contactmethod' => __( 'Vimeo', 'wp-biographia' ),
				),
			'youtube' => array( 
					'field' => 'youtube',
					'contactmethod' => __( 'YouTube', 'wp-biographia' ),
				),
			'reddit' => array( 
					'field' => 'reddit',
					'contactmethod' => __( 'Reddit', 'wp-biographia' ),
				),
			'reddit' => array( 
					'field' => 'reddit',
					'contactmethod' => __( 'Reddit', 'wp-biographia' ),
				),
			'yim' => array( 
					'field' => 'yim',
					'contactmethod' => __( 'Yahoo IM', 'wp-biographia' ),
				),
			'aim' => array( 
					'field' => 'aim',
					'contactmethod' => __( 'AIM', 'wp-biographia' ),
				),
			'msn' => array( 
					'field' => 'msn',
					'contactmethod' => __( 'Windows Live Messenger', 'wp-biographia' ),
				),
			'jabber' => array( 
					'field' => 'jabber',
					'contactmethod' => __( 'Jabber / Google Talk', 'wp-biographia' ),
				),
			
		);
		
		return  apply_filters( '$wp_biographia_defaults' , $defaults );
	}
	
	/*
	 * Items to be linked
	 *
	 * @return array filterable link items
	 */
	function link_items() {
		$link_items = array (
			"web" => array (
				"link_title" => __( 'The Web', 'wp-biographia' ),
				"link_text" => __( 'Web', 'wp-biographia' ),
				"link_icon" => $this->icon_dir_url . 'web.png'
				),
			"twitter" => array (
				"link_title" => __( 'Twitter', 'wp-biographia' ),
				"link_text" => __( 'Twitter', 'wp-biographia' ),
				"link_icon" => $this->icon_dir_url . 'twitter.png'
				),
			"facebook" => array (
				"link_title" => __( 'Facebook', 'wp-biographia' ),
				"link_text" => __( 'Facebook', 'wp-biographia' ),
				"link_icon" => $this->icon_dir_url . 'facebook.png'
				),
			"linkedin" => array (
				"link_title" => __( 'LinkedIn', 'wp-biographia' ),
				"link_text" => __( 'LinkedIn', 'wp-biographia' ),
				"link_icon" => $this->icon_dir_url . 'linkedin.png'
				),
			"googleplus" => array (
				"link_title" => __( 'Google+', 'wp-biographia' ),
				"link_text" => __( 'Google+', 'wp-biographia' ),
				"link_icon" => $this->icon_dir_url . 'google.png'
				),
			"delicious" => array (
				"link_title" => __( 'Delicous', 'wp-biographia' ),
				"link_text" => __( 'Delicous', 'wp-biographia' ),
				"link_icon" => $this->icon_dir_url . 'delicious.png'
				),
			"flickr" => array (
				"link_title" => __( 'Flickr', 'wp-biographia' ),
				"link_text" => __( 'Flickr', 'wp-biographia' ),
				"link_icon" => $this->icon_dir_url . 'flickr.png'
				),
			"picasa" => array (
				"link_title" => __( 'Picasa', 'wp-biographia' ),
				"link_text" => __( 'Picasa', 'wp-biographia' ),
				"link_icon" => $this->icon_dir_url . 'picasa.png'
				),
			"vimeo" => array (
				"link_title" => __( 'Vimeo', 'wp-biographia' ),
				"link_text" => __( 'Vimeo', 'wp-biographia' ),
				"link_icon" => $this->icon_dir_url . 'vimeo.png'
				),
			"youtube" => array (
				"link_title" => __( 'YouTube', 'wp-biographia' ),
				"link_text" => __( 'YouTube', 'wp-biographia' ),
				"link_icon" => $this->icon_dir_url . 'youtube.png'
				),
			"reddit" => array (
				"link_title" => __( 'Reddit', 'wp-biographia' ),
				"link_text" => __( 'Reddit', 'wp-biographia' ),
				"link_icon" => $this->icon_dir_url . 'reddit.png'
				),
		);
		
		return apply_filters( '$wp_biographia_links_items' , $link_items , $this->icon_dir_url );
	}

	/*
	 * Add/enqueue the Biography Box CSS for the generated page, via the wp_print_styles action hook
	 */
	function style() {
		wp_enqueue_style ('wp-biographia-bio', WPBIOGRAPHIAURL_URL . 'css/wp-biographia.css');	
	}
	
	/*
	 * Controls how the bio box is outputed based on page context
	 *
	 * @author Travis Smith
	 * @param string content
	 * @return HTML output
	 */
	function insert( $content ) {
		$new_content = $content;

		if ( ! isset( $this->author_id ) )
			$this->author_id = get_the_author_meta ('ID');

		if ( ! $this->is_shortcode ) {
			$location = $this->get_option( 'wp_biographia_display_location' );
			if ( ( isset( $location ) ) && ( $location == 'top' ) )
				$pattern = apply_filters( 'wp_biographia_pattern', '%2$s %1$s' );
			else
				$pattern = apply_filters( 'wp_biographia_pattern', '%1$s %2$s' );
		
			// allow short circuit
			if ( ( $pattern == '' ) || ( $pattern == '%1s' ) || apply_filters( 'wp_biographia_pre' , false ) )
				return $content;
		}

		if ( is_front_page() )
			$new_content = $this->insert_biographia( 'frontpage', $content, $pattern );
		
		elseif ( is_archive() )
			$new_content = $this->insert_biographia( 'archive', $content, $pattern );
		
		elseif ( is_page() )
			$new_content = $this->insert_biographia( 'page', $content, $pattern );
		
		elseif ( is_single() )
			$new_content = $this->insert_biographia( 'single', $content, $pattern );
		
		elseif ( is_feed() )
			$new_content = $this->insert_biographia( 'feed', $content, $pattern );

		return $new_content;
	}
	
	/*
	 * Cycles through all the post types
	 *
	 * @author Travis Smith
	 * @param string content
	 * @param string pattern for output
	 * @return new HTML content
	 */
	function post_types_cycle( $content = '' , $pattern = '') {
		global $post;
		$new_content = $content;
		$bio_content = $this->display();
		$post_types = get_post_types( array(), 'objects' );
		foreach(  $post_types as $post_type => $post_data ) {
			if ( ( $post_data->_builtin ) && ( $post_type != 'post' ) )
				continue;
			
			if ( $post_type == 'post' ) {
				$post_type_name = 'posts';
			}
			else {
				$post_type_name = $post_type;
			}

			if ( $post->post_type == $post_type ) {
				
				if ( $this->get_option( 'wp_biographia_display_' . $post_type_name ) || $is_shortcode ) {
					// check exclusions
					$post_option = 'wp_biographia_' . $post_type . '_exclusions';
					$global_option = 'wp_biographia_global_' . $post_type . '_exclusions';
					
					if ( $this->get_option( $post_option ) || $this->get_option( $global_option ) ) {
						$post_exclusions = $global_exclusions = array ();
						
						if ( $this->get_option( $post_option ) )
							$post_exclusions = explode ( ',', $this->get_option( $post_option ) );
							
						if ( $this->get_option( $global_option ) )
							$global_exclusions = explode ( ',', $this->get_option( $global_option ) );
						
						if ( ! in_array( $post->ID, $post_exclusions ) && ! in_array( $post->ID, $global_exclusions ) && $this->is_last_page() ) {
								$new_content = sprintf( $pattern, $content, $bio_content );
								break;
						}
						else {
							$new_content = $content;
						}
					}
					else {
						if ( $this->is_last_page() )
							$new_content = sprintf( $pattern, $content, $bio_content);
							break;
					}
				}
				else
					$new_content = $content;
			}
		}	// end-foreach( )
		return $new_content;
	}
	
	/*
	 * Outputs the bio box based on page context
	 *
	 * @author Travis Smith
	 */
	function insert_biographia( $context, $content, $pattern ) {
		global $post;

		$wp_biographia_settings = $this->get_option();
		if ( ! $this->author_id || $this->author_id == 0 )
			$this->author_id = get_the_author_meta( 'ID' );
			
		if ( ( get_user_meta( $this->author_id, 'wp_biographia_suppress_posts', true ) == 'on' ) && ( $post->post_type != 'page' ) ) return $content;
		
		switch( $context ) {
			case "frontpage":
				if ( ( $this->get_option( 'wp_biographia_display_front' ) && $this->get_option( 'wp_biographia_display_front' ) ) || ( $this->is_shortcode ) )
					$new_content = $this->post_types_cycle( $content, $pattern );
				break;
			case "archive":
				if ( ( ( $this->get_option( 'wp_biographia_display_archives' ) ) && $this->get_option( 'wp_biographia_display_archives' ) ) || ( $this->is_shortcode ) )
					$new_content = $this->post_types_cycle( $content, $pattern );
				break;
			case "page":
				if ( ( $this->get_option( 'wp_biographia_display_pages' ) &&	$this->get_option( 'wp_biographia_display_pages' ) && get_user_meta( $this->author_id, 'wp_biographia_suppress_pages', true ) !== 'on' ) || ( $is_shortcode && get_user_meta ($this->author_id, 'wp_biographia_suppress_pages', true ) !== 'on' ) )
					$this->display_bio = true;
				
				if ( $this->display_bio ) {
					
					$page_exclusions = $this->get_option( 'wp_biographia_page_exclusions' );
					
					if ( $this->get_option( 'wp_biographia_page_exclusions' ) ) {
						$page_exclusions = explode (',', $this->get_option( 'wp_biographia_page_exclusions' ) );
						print_r( $page_exclusions );
						$this->display_bio = ! in_array( $post->ID, $page_exclusions );
					}
				}

				if ( $this->display_bio ) {
					$bio_content = $this->display();
					$new_content = sprintf( $pattern, $content, $bio_content );
				}
				else
					$new_content = $content;
				break;
			case "single":
				// Cycle through Custom Post Types
				$new_content = $this->post_types_cycle( $content, $pattern );
				
				break;
			case "feed":
				if ( $this->get_option( 'wp_biographia_display_feed' ) )
					$this->display_bio = true;
				else
					$this->display_bio = $this->is_shortcode;
				
				if ( $this->display_bio ) {
					$this->is_feed = true;
					$bio_content = $this->display();
					$new_content = sprintf( $pattern, $content, $bio_content );
				}
				break;
			default:
				$new_content = $content;
				break;
		}
		
		return $new_content;
	}
	
	/*
	 * Display the biography box when the [wp_biographia] short-code is detected
	 */
	function shortcode( $atts ) {
		global $wpdb;
		$content = "";
		$is_feed = false;
		
		extract( shortcode_atts( array (
			'mode' => 'raw',
			'author' => '',
			'prefix' => '',
			'name' => ''
		 ), $atts ) );

		$this->override = $content = array();
		if ( ! empty( $prefix ) ) {
			$this->override['prefix'] = $prefix;
		}
		if ( ! empty( $name ) ) {
			switch ( $name ) {
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

		if ( ! empty( $author ) ) {
			if ( $author === "*" ) {
			
				//$contributors = $wpdb->get_results("SELECT ID, user_login from $wpdb->users ORDER BY user_login");
				$contributors = $this->wps_get_users();
				$content[] = '<div class="wp-biographia-contributors">';
				foreach( $contributors as $user_obj) {
					if ($mode == 'raw') {
						$content[] = $this->display ($is_feed, $user_obj->ID, $this->override);
					}

					elseif ($mode == 'configured') {
						$placeholder_content = "";
						$is_shortcode = true;

						$content[] = $this->insert ($placeholder_content,
														$is_shortcode,
														$user_obj->ID,
														$this->override);
					}
				}
				$content[] = '</div>';
			}
			
			else {
				$user_obj = get_user_by ('login', $author);
				if ($user_obj) {
					if ($mode == 'raw') {
						$content[] = $this->display ($is_feed, $user_obj->ID, $this->override);
					}

					elseif ($mode == 'configured') {
						$placeholder_content = "";
						$is_shortcode = true;

						$content[] = $this->insert ($placeholder_content,
														$is_shortcode,
														$user_obj->ID,
														$this->override);
					}
				}
			}
		}
		
		else {	
			if ($mode == 'raw') {
				$content[] = $this->display ($is_feed, NULL, $this->override);
			}
		
			elseif ($mode == 'configured') {
				$placeholder_content = "";
				$is_shortcode = true;
			
				$content[] = $this->insert ($placeholder_content,
												 $is_shortcode,
												 NULL,
												 $this->override);
			}
		}	

		return apply_filters( 'wp_biographia_sc' , implode( ' ' , $content ) , $content);
	}
	
	/*
	 * Produce and format the Biography Box according to the currently defined options
	 */
	function display() {
		$wp_biographia_settings = $this->get_option();
		
		if ( ! $this->author_id || $this->author_id == 0 )
			$this->author_id = get_the_author_meta( 'ID' );
		$wp_biographia_content = $wp_biographia_links = $wp_biographia_author = $wp_biographia_biography = array();
			
		foreach(  $this->defaults() as $key => $data ) {
			if ( $key != 'first-last-name')
				$wp_biographia_author[$key] = get_the_author_meta( $data['field'], $this->author_id );
			else
				$wp_biographia_author[$key] = get_the_author_meta('first_name', $this->author_id ) . ' ' . get_the_author_meta ('last_name', $this->author_id );
		}
		
		$wp_biographia_author['posts'] = (int)count_user_posts( $this->author_id );
		$wp_biographia_author['posts_url'] = get_author_posts_url( $this->author_id );

		// Add Image Size Output
		$wp_biographia_author_pic_size =
			( isset( $wp_biographia_settings['wp_biographia_content_image_size'] ) ) ?
				$this->get_option( 'wp_biographia_content_image_size' ) : '100';
		$wp_biographia_author_pic = get_avatar ( $wp_biographia_author['email'], $wp_biographia_author_pic_size );

		if ( ! empty( $wp_biographia_settings['wp_biographia_content_prefix']) ||
			!empty ($wp_biographia_settings['wp_biographia_content_name'])) {
			$wp_biographia_content[] = '<h3>';

			$name_prefix = "";
			if ( ( ! empty( $this->override ) ) && ( ! empty( $this->override['prefix'] ) ) )
				$name_prefix = $this->override['prefix'];
			elseif ( ! empty( $wp_biographia_settings['wp_biographia_content_prefix'] ) )
				$name_prefix = $wp_biographia_settings['wp_biographia_content_prefix'];
			
			if ( ! empty( $name_prefix ) )
				$wp_biographia_content[] = $name_prefix . ' ';

			$display_name = "";
			if ( ( ! empty( $this->override ) ) && ( ! empty( $this->override['name'] ) ) )
				$display_name = $this->override['name'];
			elseif ( ! empty( $wp_biographia_settings['wp_biographia_content_name'] ) )
				$display_name = $wp_biographia_settings['wp_biographia_content_name'];

			if ( ! empty( $display_name ) && $display_name != 'none' ) {
				switch ( $display_name ) {
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
				
				if ( ! empty( $wp_biographia_settings['wp_biographia_content_authorpage'] ) && ( $wp_biographia_settings['wp_biographia_content_authorpage'] == 'on' ) )
						$wp_biographia_content[] = '<a href="' . $wp_biographia_author['posts_url']	. '" title="' . $wp_biographia_formatted_name . '">' . $wp_biographia_formatted_name . '</a>';
				else
					$wp_biographia_content[] = $wp_biographia_formatted_name;	
			}
			$wp_biographia_content[] = '</h3>';
		}

		if ( ! empty( $wp_biographia_settings['wp_biographia_content_bio'] ) ) {
			$wp_biographia_content[] = "<p>" . $wp_biographia_author['bio'] . "</p>";
		}

		$display_icons = ( ! empty( $wp_biographia_settings['wp_biographia_content_icons'] ) &&
			( $wp_biographia_settings['wp_biographia_content_icons'] == 'on' ) ) ? 'icon' : 'text';

		if ( ( $display_icons ) && ( ! empty( $wp_biographia_settings['wp_biographia_content_alt_icons'] ) && $wp_biographia_settings['wp_biographia_content_alt_icons'] == 'on' && ! empty( $wp_biographia_settings['wp_biographia_content_icon_url'] ) ) )
				$this->icon_dir_url = $wp_biographia_settings['wp_biographia_content_icon_url'];
				
		$link_items = $this->link_items();
		$item_stub = ( $display_icons == "icon" ) ? '<li><a href="%s" title="%s" class="%s"><img src="%s" class="%s" /></a></li>' : '<li><a href="%s" title="%s" class="%s">%s</a></li>';
		$title_name_stub = __( '%1$s On %2$s', 'wp-biographia' );
		$title_noname_stub = __( 'On %s', 'wp-biographia' );
		
		// Deal with the email link first as a special case ...
		if ( ( ! empty( $wp_biographia_settings['wp_biographia_content_email'] ) && ( $wp_biographia_settings['wp_biographia_content_email'] == 'on' ) ) && ( ! empty( $wp_biographia_author['email'] ) ) ) {
			if ( ! empty( $wp_biographia_formatted_name ) )
				$link_title = sprintf (__( 'Send %s Mail', 'wp-biographia' ), $wp_biographia_formatted_name);
			else
				$link_title = __( 'Send Mail', 'wp-biographia' );

			$link_text = __( 'Mail', 'wp-biographia' );
			
			$link_body = ( $display_icons == "icon" ) ? $this->icon_dir_url . 'mail.png' : $link_text;
			$wp_biographia_links[] = $this->link_item( $display_icons, $item_stub, 'mailto:' . antispambot( $wp_biographia_author['email'] ), $link_title, $link_body );
				
		}
		
		// Now deal with the other links that follow the same format and can be "templatised" ...
		foreach( $link_items as $link_key => $link_attrs ) {
			$option_name = 'wp_biographia_content_' . $link_key;
			if ( ! empty( $wp_biographia_settings[$option_name] ) && ( $wp_biographia_settings[$option_name] == 'on' ) && ( ! empty( $wp_biographia_author[$link_key] ) || ( $link_key == 'web' ) ) ) {

				if ( ! empty( $wp_biographia_formatted_name ) )
					$link_title = sprintf( $title_name_stub, $wp_biographia_formatted_name, $link_attrs['link_title']);
				else
					$link_title = sprintf( $title_noname_stub, $link_attrs['link_title']);
				
				$link_body = ( $display_icons == "icon" ) ? $link_attrs['link_icon'] : $link_attrs['link_text'];
				$link_key = ( $link_key != 'web' ) ? $link_key  : 'website';
				$wp_biographia_links[] = $this->link_item( $display_icons, $item_stub, $wp_biographia_author[$link_key], $link_title, $link_body );

			}
		}

		// Finally, deal with the "More Posts" link
		if ( ! empty( $wp_biographia_settings['wp_biographia_content_posts'] ) && ( $wp_biographia_settings['wp_biographia_content_posts'] != 'none' ) && ( $wp_biographia_author['posts'] > 0 ) ) {
			if ( ! empty( $wp_biographia_formatted_name ) )
				$link_title = sprintf (__( 'More Posts By %s', 'wp-biographia' ), $wp_biographia_formatted_name);
			else
				$link_title = __( 'More Posts', 'wp-biographia' );

			switch( $wp_biographia_settings['wp_biographia_content_posts'] ) {
				case 'extended':
					$link_text = __( 'More Posts', 'wp-biographia' ) . ' ('
						. $wp_biographia_author['posts']
						. ')';
					break;
				default:
					$link_text = __( 'More Posts', 'wp-biographia' );
					break;
			}
			
			$link_body = ( $display_icons == "icon" ) ? $this->icon_dir_url . 'wordpress.png' : $link_text;
			$wp_biographia_links[] = $this->link_item( $display_icons, $item_stub, $wp_biographia_author['posts_url'], $link_title, $link_body );
			
		}
		
		$item_glue = ( $display_icons == 'icon' ) ? "" : " | ";
		$list_class = "wp-biographia-list-" . $display_icons;
		if ( ! empty( $wp_biographia_links ) ) {
			$wp_biographia_content[] = apply_filters( 'wp_biographia_links' , '<div class="wp-biographia-links">'
				. '<small><ul class="wp-biographia-list ' . $list_class . '">'
				. implode ( $item_glue, $wp_biographia_links )
				. '</ul></small>'
				. '</div>' , $wp_biographia_links , $item_glue , $list_class );
		}
		
		if ( ! $this->for_feed ) {
			$wp_biographia_biography[] = '<div class="wp-biographia-container-'
				. $wp_biographia_settings['wp_biographia_style_border']
				. '" style="background-color:'
				. $wp_biographia_settings['wp_biographia_style_bg']
				. ';">';

			if ( ! empty( $wp_biographia_settings['wp_biographia_content_image'] ) &&
					( $wp_biographia_settings['wp_biographia_content_image'] == 'on' ) ) {
				$wp_biographia_biography[] = '<div class="wp-biographia-pic" style="height:'
					. $wp_biographia_author_pic_size
					. 'px; width:'
					. $wp_biographia_author_pic_size
					. 'px;">'
					. $wp_biographia_author_pic
					. '</div>';
			}

			$wp_biographia_biography[] = apply_filters( 'wp_biographia_feed' , '<div class="wp-biographia-text">'
				. implode ( '', $wp_biographia_content )
				. '</div></div>' , $wp_biographia_content , $wp_biographia_settings );
		}
		
		elseif ( ! empty( $wp_biographia_settings['wp_biographia_content_image'] ) &&
					( $wp_biographia_settings['wp_biographia_content_image'] == 'on' ) ) {
						$wp_biographia_biography[] = '<p>';
						$wp_biographia_biography[] = '<div style="float:left; text-align:left;>'.$wp_biographia_author_pic.'</div>';
						$wp_biographia_biography[] = $wp_biographia_content.'</p>';	
		}
		
		return apply_filters( 'wp_biographia_biography' , implode ( '', $wp_biographia_biography ) , $wp_biographia_biography );
	}
	
	/*
	 * Produce and format the Biography Box according to the currently defined options
	 *
	 * @author Travis Smith
	 * @param string icon/text
	 * @param string pattern for output
	 * @param string url
	 * @param string link title
	 * @param string link body
	 * @return HTML output
	 */
	function link_item( $display_icons, $pattern, $link_key, $link_title, $link_body ) {
		$item_class = "wp-biographia-item-" . $display_icons;
		$link_class = "wp-biographia-link-" . $display_icons;
		
		if ( $display_icons == 'icon' )
			return sprintf( $pattern, $link_key, $link_title, $link_class, $link_body, $item_class );
		else
			return sprintf( $pattern, $link_key, $link_title, $link_class, $link_body );
	}
	
	function admin_menu () {
		if (function_exists ('add_options_page')) {
			$page_title = __('WP Biographia', 'wp-biographia');
			$menu_title = __('WP Biographia', 'wp-biographia');
			add_options_page ($page_title, $menu_title, 'manage_options', __FILE__,
				'wp_biographia_general_settings');
		}
	}
	
	function admin_print_scripts () {
		global $pagenow;

		if ($pagenow == 'options-general.php' &&
				isset ($_GET['page']) &&
				strstr ($_GET['page'],"wp-biographia")) {
			wp_enqueue_script ('postbox');
			wp_enqueue_script ('dashboard');
			wp_enqueue_script ('custom-background');
			wp_enqueue_script ('wp-biographia_admin-script', WPBIOGRAPHIAURL_URL . 'js/wp-biographia-admin.js');
		}
	}
	
	function admin_print_styles () {
		global $pagenow;

		if ($pagenow == 'options-general.php' &&
				isset ($_GET['page']) &&
				strstr ($_GET['page'],"wp-biographia")) {
			wp_enqueue_style ('dashboard');
			wp_enqueue_style ('global');
			wp_enqueue_style ('wp-admin');
			wp_enqueue_style ('farbtastic');
			wp_enqueue_style ('wp-biographia-admin', WPBIOGRAPHIAURL_URL . 'css/wp-biographia-admin.css');	
		}
	}
	
	function admin_init () {
		/*
		 * Check, and if needed, upgrade the plugin's configuration settings ...
		 */

		wp_biographia_upgrade ();
	}

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

	function admin_save_profile_extensions ($user_id) {
		update_user_meta ($user_id, 'wp_biographia_suppress_posts',
			wp_biographia_option ('wp_biographia_suppress_posts'));
		update_user_meta ($user_id, 'wp_biographia_suppress_pages',
			wp_biographia_option ('wp_biographia_suppress_pages'));
	}
	
	function admin_settings_link($links) {
		$settings_link = '<a href="options-general.php?page=wp-biographia-new.php">'
			. __('Settings', 'wp-biographia')
			. '</a>';
		array_unshift ($links, $settings_link);
		return $links;
	}
	
}

//define ('WPBIOGRAPHIA_VERSION', '24');
//define ('WPBIOGRAPHIA_BASENAME', plugin_basename (__FILE__) );
//define ('WPBIOGRAPHIAURL_URL', plugin_dir_url(__FILE__));
//define ('WPBIOGRAPHIAURL_PATH', plugin_dir_path(__FILE__));

$__wp_biographia_instance = new WP_Biographia;

//require_once( WPBIOGRAPHIAURL_PATH . "includes/wp-biographia-admin.php" );

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

//add_action ('admin_menu','wp_biographia_add_options_subpanel');
//add_action ('admin_print_scripts', 'wp_biographia_add_admin_scripts');
//add_action ('admin_print_styles', 'wp_biographia_add_admin_styles');

//add_action ('admin_init', 'wp_biographia_admin_init');
//add_action ('show_user_profile', 'wp_biographia_add_profile_extensions');
//add_action ('edit_user_profile', 'wp_biographia_add_profile_extensions');
//add_action ('personal_options_update', 'wp_biographia_save_profile_extensions');
//add_action ('edit_user_profile_update', 'wp_biographia_save_profile_extensions');
//add_filter ('plugin_action_links_' . plugin_basename (__FILE__), 'wp_biographia_settings_link');

?>