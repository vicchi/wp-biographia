=== WP Biographia ===
Contributors: vicchi, wpsmith
Donate link: http://www.vicchi.org/codeage/wp-biographia/
Tags: bio, biography, bio box, biography box, twitter, facebook, linkedin, googleplus, google+, website, about, author, about author, author box
Requires at least: 3.2
Tested up to: 3.2.1
Stable tag: 2.0.0

Add and display a customizable author biography for all single post types, in RSS feeds, in archives and on each entry on the landing page.

== Description ==

This plugin allows you to add a customizable biography to posts, to RSS feeds to pages, to archives and to each post on your blog's landing page. It integrates out of the box with the information that can be provided in each user's profile.

Settings and options include:

1. Choose when to display a Biography Box; on the front page, in archives, on individual posts, pages, or any other custom post type and in RSS feeds.
1. Choose the border style and background color of the Biography Box
1. Choose the amount of user profile information displayed in the Biography Box
1. Choose the gravatar image size
1. Choose to display the bio at the top or the bottom (universally)

The plugin also expands and enhances the Contact Info section of your user profile, adding support for Twitter, Facebook, LinkedIn and Google+ profile links as well as Yahoo! Messenger, AIM, Windows Live Messenger and Jabber/Google Talk instant messaging profiles.

The plugin also has an added filter and shortcode (<code>[wp_biographia]</code>) to add further customization. For example, if you use page templates to display custom post types on your blog, you can simply use <code>filter ('wp_biographia_pattern')</code> to decide how you'd like to customize. Or, if you want the Biography Box to appear at the bottom but on archive pages, you want them at the top, then the filter can do that as well. Or use the shortcode to control where it appears in a post of any post type.

== Installation ==

1. You can install WP Biographia automatically from the WordPress admin panel. From the Dashboard, navigate to the Plugins / Add New page and search for "WP Biographia" and click on the "Install Now" link.
1. Or you can install WP Biographia manually. Download the plugin Zip archive and uncompress it. Copy or upload the wp-biographia folder to the wp-content/plugins folder on your web server.
1. Activate the plugin. From the Dashboard, navigate to Plugins and click on the "Activate" link under the entry for WP Biographia.
1. Enhance your WordPress user profile. From the Dashboard, navigate to Users and click on the "Edit" link under your profile.
1. Edit your WordPress user profile. Add your biography to the "Biographical Info" text box. WP Biographia also adds to the list of Contact Info you can associate with your profile, adding support for Twitter, Facebook, LinkedIn and Google+. Click on the "Update Profile" link to save your changes.
1. Customize and configure what information WP Biographia displays; From the Dashboard, navigate to the Settings / WP Biographia page.
1. You can can control display settings, style settings and content settings for the Biography Box.
1. Click on the "Save Changes" button to preserve your chosen settings and options.
1. If you enable the display of the post author's image, make sure avatar support is turned on; from the Dashboard, navigate to Settings / Discussion and ensure that Show Avatars is enabled.

== Frequently Asked Questions ==

= Is there a web site for this plugin? =

Absolutely. Go to http://www.vicchi.org/codeage/wp-biographia/ for the latest information. There's also the official WordPress plugin directory page at http://wordpress.org/extend/plugins/ and the source for the plugin is on GitHub as well at https://github.com/vicchi/wp-biographia.

= How do I add HTML to the description? =

Add this code to your theme's <code>functions.php</code> file:

<code>remove_filter('pre_user_description', 'wp_filter_kses');</code>

This will be a configurable setting in a future version of the plugin to avoid the need to hack your theme's <code>functions.php</code> file.

= How do I remove the bio on pages using page templates? =

Add this code to your theme's <code>functions.php</code> file:

<code>add_action ('wp_head', 'remove_author_box_page_template');

function remove_author_box_page_template() {
  if (is_page_template ('page_blog.php'))
		add_filter ('wp_biographia_pattern' , 'content_only_pattern');
}

function content_only_pattern($pattern) {
	return '%1s';
}</code>

This will be a configurable setting in a future version of the plugin to avoid the need to hack your theme's <code>functions.php</code> file.

= How do I remove the bio on pages using page templates? =

Add this code to your theme's <code>functions.php</code> file:

<code>add_action ('wp_head', 'remove_author_box_page_template');
function remove_author_box_page_template () {
	if (is_archive ())
		add_filter ('wp_biographia_pattern', 'content_only_pattern');
}

function content_only_pattern($pattern) {
	return '%2s %1s';
}</code>

This will be a configurable setting in a future version of the plugin to avoid the need to hack your theme's <code>functions.php</code> file.

= I've configured WP Biographia to display on post archives but it's not working; what's happening here? =

Some themes display the Biography Box for post archives, but for other themes the Biography Box never appears. This often turns out to be down to the way in which the theme renders the archive page. If the theme’s <code>archive.php</code> uses <code>the_content()</code> as part of the WordPress Loop then the Biography Box appears as it should, but if the theme uses <code>the_excerpt()</code> as part of the Loop, then either the first 55 characters of the post or the post’s specific excerpt will be displayed. As WP Biographia appends the Biography Box to the end of each post’s content, themes which use <code>the_excerpt()</code> will, sadly, never display as intended when used with WP Biographia. Thankfully, this is less a shortcoming of the plugin or of the theme, it’s simply the way in which WordPress handles post excerpts.

= I've configured WP Biographia to display the author's image but it's not working; what's happening here? =

Author profile pictures, or avatars, are part of the WordPress core but enabling them isn't done at the level of the user profile, instead it's part of the way in which comments are configured. If you enable the display of the post author’s image, make sure avatar support is turned on; from the Dashboard, navigate to Settings / Discussion and ensure that Show Avatars is enabled.

= This plugin looks very much like the WP About Author; what's the connection? =

WP Biographia is inspired by and based on the WP About Author plugin (http://wordpress.org/extend/plugins/wp-about-author/) by Jon Bishop (http://www.jonbishop.com/). Thanks and kudos must go to Jon for writing a well structured, working WordPress plugin released under a software license that enables other plugins such as this one to be written or derived in the first place. Jon's written other WordPress plugins as well (http://profiles.wordpress.org/users/JonBishop/); you should take a look.

= I want to amend/hack/augment this plugin; can I do the same? =

Totally; like the original plugin by Jon, this plugin is licensed under the GNU General Public License v2 (GPLV2). See http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt for the full license terms.

= Where does the name WP Biographia come from? =

WP Biographia is named after the etymology of the modern English word biography. The word first appeared in the 1680s, probably from the latin biographia which itself derived from the Greek bio, meaning "life" and graphia, meaning "record" or "account" which derived from graphein, "to write".

== Screenshots ==

1. WP Biographia Settings And Options: Biography Box Display Settings
1. WP Biographia Settings And Options: Biography Box Style Settings
1. WP Biographia Settings And Options: Biography Box Content Settings
1. Sample biography, shown below an individual post


== Changelog ==

The current version is 2.0 (2011.11.01)

= 2.0 =
* Added the ability to set image size
* Added a simple shortcode
* Added Custom Post Types support with the ability to exclude based on post IDs
* Added ability to set the bio at the top or the bottom
* Added a filter to short circuit for further customization
* Added ability to include the post author's email link
* Refactored plugin file locations in line with WordPress plugin development recommendations
* Fixed CSS issue for gravatar

= 1.0 =
* First version of WP Biographia released

== Upgrade Notice ==
= 2.0 =
* This is the second version of WP Biographia and incorporates new features and bug fixes as well as some under-the-hood plumbing changes; in particular the plugin file locations have changed to reflect WordPress plugin development recommendations

= 1.0 =
* This is the first version of WP Biographia