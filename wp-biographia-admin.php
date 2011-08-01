<?PHP
/*
 * Add in our CSS for the admin panel, via the admin_print_styles action hook
 */

function add_wp_biographia_admin_styles() {
	global $pagenow;

	if ($pagenow == 'options-general.php' &&
			isset ($_GET['page']) &&
			strstr ($_GET['page'],"wp-biographia")) {
		wp_enqueue_style ('dashboard');
		wp_enqueue_style ('global');
		wp_enqueue_style ('wp-admin');
		wp_enqueue_style ('farbtastic');
	}
}

/*
 * Add in our scripts for the admin panel, via the admin_print_scripts action hook
 */

function add_wp_biographia_admin_scripts() {
	global $pagenow;

	if ($pagenow == 'options-general.php' &&
			isset ($_GET['page']) &&
			strstr ($_GET['page'],"wp-biographia")) {
		wp_enqueue_script ('postbox');
		wp_enqueue_script ('dashboard');
		wp_enqueue_script ('custom-background');
	}
}

/*
 * Define the Colophon side box
 */

function wp_biographia_show_colophon() {
	$content = '<p><em>"When it comes to software, I much prefer free software, because I have very seldom seen a program that has worked well enough for my needs and having sources available can be a life-saver"</em>&nbsp;&hellip;&nbsp;Linus Torvalds</p>';

	$content .= '<p>WP Biographia is inspired by and based on <a href="http://www.jonbishop.com">Jon Bishop\'s</a> <a href="http://wordpress.org/extend/plugins/wp-about-author/">WP About Author</a> plugin. Thanks and kudos must go to Jon for writing a well structured, working WordPress plugin released under a software license that enables other plugins such as this one to be written or derived in the first place. Jon\'s written other <a href="http://profiles.wordpress.org/users/JonBishop/">WordPress plugins</a> as well; you should take a look.</p>';
	
	$content .= '<p>For the inner nerd in you, WP Biographia was written using <a href="http://macromates.com/">TextMate</a> on a MacBook Pro running OS X 10.6.8 and tested on the same machine running <a href="http://mamp.info/en/index.html">MAMP</a> (Mac/Apache/MySQL/PHP) before being let loose on the author\'s <a href="http://www.vicchi.org/">blog</a>.<p>';
	
	$content .= '<p>WP Biographia is named after the etymology of the modern English word <em>biography</em>. The word first appeared in the 1680s, probably from the latin <em>biographia</em> which itself derived from the Greek <em>bio</em>, meaning "life" and <em>graphia</em>, meaning "record" or "account" which derived from <em>graphein</em>, "to write".</p>';
	
	$content .= '<p><small>Dictionary.com, "biography," in <em>Online Etymology Dictionary</em>. Source location: Douglas Harper, Historian. <a href="http://dictionary.reference.com/browse/biography">http://dictionary.reference.com/browse/biography</a>. Available: <a href="http://dictionary.reference.com">http://dictionary.reference.com</a>. Accessed: July 27, 2011.</small></p>';

	return wp_biographia_postbox ('wp-biographia-colophon', 'Colophon', $content);
}

/*
 * Define the admin panel
 */

function wp_biographia_general_settings() {
	$wp_biographia_settings = wp_biographia_process_settings ();
	
	$wrapped_content = "";
	$general_content = "";	
	$box_content = "";
	$content_settings = "";
	
	if (function_exists ('wp_nonce_field')) {
		$general_content .= wp_nonce_field ('wp-biographia-update-options','_wpnonce',true,false);
	}
	
	/*
 	 * Biography Box Display Settings
 	 */
	
	$general_content .= '<p><strong>' . __("Display On Front Page") . '</strong><br /> 
				<input type="checkbox" name="wp_biographia_display_front" ' .checked($wp_biographia_settings['wp_biographia_display_front'], 'on', false) . ' />
				<small>Display a biography box on the front page at the end of each  post.</small></p>';			
	$general_content .= '<p><strong>' . __("Display In Archives") . '</strong><br /> 
				<input type="checkbox" name="wp_biographia_display_archives" ' .checked($wp_biographia_settings['wp_biographia_display_archives'], 'on', false) . ' />
				<small>Display a biography box on archive pages at the end of each post.</small></p>';	
	$general_content .= '<p><strong>' . __("Display On Individual Posts") . '</strong><br /> 
				<input type="checkbox" name="wp_biographia_display_posts" ' .checked($wp_biographia_settings['wp_biographia_display_posts'], 'on', false) . ' />
				<small>Display a biography box on individual posts at the end of the post.</small></p>';	
	$general_content .= '<p><strong>' . __("Display On Individual Pages") . '</strong><br /> 
				<input type="checkbox" name="wp_biographia_display_pages" ' .checked($wp_biographia_settings['wp_biographia_display_pages'], 'on', false) . ' />
				<small>Display a biography box on individual pages at the top of the entry.</small></p>';
        $general_content .= '<p><strong>' . __("Display In RSS Feeds") . '</strong><br />
				<input type="checkbox" name="wp_biographia_display_feed" ' .checked($wp_biographia_settings['wp_biographia_display_feed'], 'on', false) . ' />
				<small>Display a biography box in feeds at the top of each entry.</small></p>';
	$wrapped_content .= wp_biographia_postbox('wp-biographia-settings-general', 'Biography Box Display Settings', $general_content);

	/*
	 * Biography Box Style Settings
	 */
	
	$box_content .= '<p><strong>' . __("Box Background Color") . '</strong><br /> 
				<input type="text" name="wp_biographia_alert_bg" id="background-color" value="' . $wp_biographia_settings['wp_biographia_alert_bg'] . '" />
				<a class="hide-if-no-js" href="#" id="pickcolor">' . __('Select a Color') . '</a>
				<div id="colorPickerDiv" style="z-index: 100; background:#eee; border:1px solid #ccc; position:absolute; display:none;"></div>
				<small>By default, the background color of the box is a yellowish tone.</small></p>';
	$box_content .= '<p><strong>' . __("Box Border") . '</strong><br /> 
                <select name="wp_biographia_alert_border">
                  <option value="top" ' .selected($wp_biographia_settings['wp_biographia_alert_border'], 'top', false) . '>Thick Top Border</option>
                  <option value="around" ' .selected($wp_biographia_settings['wp_biographia_alert_border'], 'around', false) . '>Thin Surrounding Border</option>
                  <option value="none" ' .selected($wp_biographia_settings['wp_biographia_alert_border'], 'none', false) . '>No Border</option>
                </select><br /><small>By default, a thick black line is displayed above the author bio.</small></p>';
	$wrapped_content .= wp_biographia_postbox('wp-biographia-settings-alert', 'Biography Box Style Settings', $box_content);

	/*
	 * Biography Box Content Settings
	 */
	
	$content_settings .= '<p><strong>' . __("Biography Prefix") . '</strong><br />
		<input type="text" name="wp_biographia_content_prefix" id="background-color" value="'
		. $wp_biographia_settings["wp_biographia_content_prefix"]
		. '" /><br />
		<small>Prefix text to be prepended to the author\'s name</small></p>';

	$content_settings .= '<p><strong>' . __("Author's Name") . '</strong><br />
		<input type="radio" name="wp_biographia_content_name" id="background-color" value="first-last-name" '
		. checked ($wp_biographia_settings['wp_biographia_content_name'], 'first-last-name', false)
		.' />&nbsp;First/Last Name<br />
		<input type="radio" name="wp_biographia_content_name" id="background-color" value="account-name" '
		. checked ($wp_biographia_settings['wp_biographia_content_name'], 'account-name', false)
		. ' />&nbsp;Account Name<br />
		<input type="radio" name="wp_biographia_content_name" id="background-color" value="nickname" '
		. checked ($wp_biographia_settings['wp_biographia_content_name'], 'nickname', false)
		. ' />&nbsp;Nickname<br />
		<input type="radio" name="wp_biographia_content_name" id="background-color" value="display-name" '
		. checked ($wp_biographia_settings['wp_biographia_content_name'], 'display-name', false)
		. ' />&nbsp;Display Name<br />
		<input type="radio" name="wp_biographia_content_name" id="background-color" value="none" '
		. checked ($wp_biographia_settings['wp_biographia_content_name'], 'none', false)
		. ' />&nbsp;Don\'t Show The Name<br />
		<small>How you want to see the author\'s name displayed (if at all)</small></p>';
	
	$content_settings .= '<p><strong>' . __("Author's Image") . '</strong><br />
		<input type="checkbox" name="wp_biographia_content_image" '
		. checked ($wp_biographia_settings['wp_biographia_content_image'], 'on', false)
		. '/>
		<small>Display the author\'s image?</small></p>';

	$content_settings .= '<p><strong>' . __("Show Author's Biography") . '</strong><br />
		<input type="checkbox" name="wp_biographia_content_bio" '
		. checked ($wp_biographia_settings['wp_biographia_content_bio'], 'on', false)
		. '/>
		<small>Display the author\'s biography?</small></p>';

	$content_settings .= '<p><strong>' . __("Show Author's Website Link") . '</strong><br />
		<input type="checkbox" name="wp_biographia_content_web" '
		. checked ($wp_biographia_settings['wp_biographia_content_web'], 'on', false)
		. '/>
		<small>Display the author\'s website details?</small></p>';

	$content_settings .= '<p><strong>' . __("Show Author's Twitter Link") . '</strong><br />
		<input type="checkbox" name="wp_biographia_content_twitter" '
		. checked ($wp_biographia_settings['wp_biographia_content_twitter'], 'on', false)
		. '/>
		<small>Display the author\'s Twitter details?</small></p>';
	
	$content_settings .= '<p><strong>' . __("Show Author's Facebook Link") . '</strong><br />
		<input type="checkbox" name="wp_biographia_content_facebook" '
		. checked ($wp_biographia_settings['wp_biographia_content_facebook'], 'on', false)
		. '/>
		<small>Display the author\'s Facebook details?</small></p>';

	$content_settings .= '<p><strong>' . __("Show Author's LinkedIn Link") . '</strong><br />
		<input type="checkbox" name="wp_biographia_content_linkedin" '
		. checked ($wp_biographia_settings['wp_biographia_content_linkedin'], 'on', false)
		. '/>
		<small>Display the author\'s LinkedIn details?</small></p>';

	$content_settings .= '<p><strong>' . __("Show Author's Google+ Link") . '</strong><br />
		<input type="checkbox" name="wp_biographia_content_googleplus" '
		. checked ($wp_biographia_settings['wp_biographia_content_googleplus'], 'on', false)
		. '/>
		<small>Display the author\'s Google+ details?</small></p>';
	
	$content_settings .= '<p><strong>' . __("Show More Posts Link") . '</strong><br />
		<input type="radio" name="wp_biographia_content_posts" id="background-color" value="basic" '
		. checked ($wp_biographia_settings['wp_biographia_content_posts'], 'basic', false)
		. ' />&nbsp;Basic More Posts Link<br />
		<input type="radio" name="wp_biographia_content_posts" id="background-color" value="extended" '
		. checked ($wp_biographia_settings['wp_biographia_content_posts'], 'extended', false)
		. ' />&nbsp;Extended More Posts Link<br />
		<input type="radio" name="wp_biographia_content_posts" id="background-color" value="none" '
		. checked ($wp_biographia_settings['wp_biographia_content_posts'], 'none', false)
		. ' />&nbsp;Don\'t Show The More Posts Link<br />
		<small>How you want to display and format the <em>More Posts By This Author</em> link</small></p>';
	
	$wrapped_content .= wp_biographia_postbox('wp-biographia-settings-content', 'Biography Box Content Settings', $content_settings);
	
	wp_biographia_admin_wrap ('WP Biographia Settings And Options', $wrapped_content);
}

/*
 * Save the submitted admin options
 */

function wp_biographia_process_settings() {
	if (!empty ($_POST['wp_biographia_option_submitted'])) {
		$wp_biographia_settings = array();
		
		if (strstr ($_GET['page'], "wp-biographia") &&
		 		check_admin_referer ('wp-biographia-update-options')) {

			/*
			 * Biography Box Display Settings
			 */


			if (isset ($_POST['wp_biographia_display_front'])) {
				$wp_biographia_settings['wp_biographia_display_front'] =
					$_POST['wp_biographia_display_front'];
			}

			if (isset ($_POST['wp_biographia_display_archives'])) {
				$wp_biographia_settings['wp_biographia_display_archives'] = 
					$_POST['wp_biographia_display_archives'];
			}

			if (isset ($_POST['wp_biographia_display_posts'])) {
				$wp_biographia_settings['wp_biographia_display_posts'] =
					$_POST['wp_biographia_display_posts'];
			}

			if (isset ($_POST['wp_biographia_display_pages'])) {
				$wp_biographia_settings['wp_biographia_display_pages'] =
					$_POST['wp_biographia_display_pages'];
			}

			if (isset ($_POST['wp_biographia_display_feed'])) {
				$wp_biographia_settings['wp_biographia_display_feed'] =
					$_POST['wp_biographia_display_feed'];
			}

			/*
			 * Biography Box Style Settings
			 */

			$color = preg_replace('/[^0-9a-fA-F]/', '', $_POST['wp_biographia_alert_bg']);

			if ((strlen ($color) == 6 || strlen ($color) == 3) &&
 				isset($_POST['wp_biographia_alert_bg'])) {
				$wp_biographia_settings['wp_biographia_alert_bg']=$_POST['wp_biographia_alert_bg'];
			}

			if (isset ($_POST['wp_biographia_alert_border'])) {
				$wp_biographia_settings['wp_biographia_alert_border'] = 
					$_POST['wp_biographia_alert_border'];
			}

			/*
			 * Biography Box Content Settings
			 */

			if (isset ($_POST['wp_biographia_content_prefix'])) {
				$wp_biographia_settings['wp_biographia_content_prefix'] =
					$_POST['wp_biographia_content_prefix'];
			}

			if (isset ($_POST['wp_biographia_content_name'])) {
				$wp_biographia_settings['wp_biographia_content_name'] =
					$_POST['wp_biographia_content_name'];
			}

			if (isset ($_POST['wp_biographia_content_image'])) {
				$wp_biographia_settings['wp_biographia_content_image'] =
					$_POST['wp_biographia_content_image'];
			}

			if (isset ($_POST['wp_biographia_content_bio'])) {
				$wp_biographia_settings['wp_biographia_content_bio'] =
					$_POST['wp_biographia_content_bio'];
			}

			if (isset ($_POST['wp_biographia_content_web'])) {
				$wp_biographia_settings['wp_biographia_content_web'] =
					$_POST['wp_biographia_content_web'];
			}

			if (isset ($_POST['wp_biographia_content_twitter'])) {
				$wp_biographia_settings['wp_biographia_content_twitter'] =
					$_POST['wp_biographia_content_twitter'];
			}

			if (isset ($_POST['wp_biographia_content_facebook'])) {
				$wp_biographia_settings['wp_biographia_content_facebook'] =
					$_POST['wp_biographia_content_facebook'];
			}

			if (isset ($_POST['wp_biographia_content_linkedin'])) {
				$wp_biographia_settings['wp_biographia_content_linkedin'] =
					$_POST['wp_biographia_content_linkedin'];
			}

			if (isset ($_POST['wp_biographia_content_googleplus'])) {
				$wp_biographia_settings['wp_biographia_content_googleplus'] =
					$_POST['wp_biographia_content_googleplus'];
			}

			if (isset ($_POST['wp_biographia_content_posts'])) {
				$wp_biographia_settings['wp_biographia_content_posts'] =
					$_POST['wp_biographia_content_posts'];
			}
			
			echo "<div id=\"updatemessage\" class=\"updated fade\"><p>WP Biographia settings updated.</p></div>\n";
			echo "<script type=\"text/javascript\">setTimeout(function(){jQuery('#updatemessage').hide('slow');}, 3000);</script>";	
			
			update_option ('wp_biographia_settings', $wp_biographia_settings);
		}
	}
	
	$wp_biographia_settings = get_option ('wp_biographia_settings');
	
	return $wp_biographia_settings;
}

/*
 * Add in our admin panel, via the admin_menu action hook
 */

function add_wp_biographia_options_subpanel() {
	if (function_exists ('add_options_page')) {
		add_options_page ('WP Biographia', 'WP Biographia', 'manage_options', __FILE__,
			'wp_biographia_general_settings');
	}
}

/*
 * Add in a single admin panel sub-box
 */

function wp_biographia_postbox($id, $title, $content) {
	$postbox_wrap = "";
	$postbox_wrap .= '<div id="' . $id . '" class="postbox">';
	$postbox_wrap .= '<div class="handlediv" title="Click to toggle"><br /></div>';
	$postbox_wrap .= '<h3 class="hndle"><span>' . $title . '</span></h3>';
	$postbox_wrap .= '<div class="inside">' . $content . '</div>';
	$postbox_wrap .= '</div>';

	return $postbox_wrap;
}	

/*
 * Wrap up all the constituent components of our admin panel
 */

function wp_biographia_admin_wrap($title, $content) {
?>
    <div class="wrap">
        <h2><?php echo $title; ?></h2>
        <form method="post" action="">
            <div class="postbox-container" style="width:60%;">
                <div class="metabox-holder">	
                    <div class="meta-box-sortables">
                    <?php
                        echo $content;
                    ?>
                    <p class="submit"> 
                        <input type="submit" name="wp_biographia_option_submitted" class="button-primary" value="Save Changes" /> 
                    </p> 
                    <br /><br />
                    </div>
                  </div>
                </div>
                <div class="postbox-container" style="width:30%;">
                  <div class="metabox-holder">	
                    <div class="meta-box-sortables">
                    <?php
						echo wp_biographia_show_colophon ();
                    ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
<?php
}
?>