=== WP Biographia ===
Contributors: Vicchi, wpsmith
Donate link: http://www.vicchi.org/codeage/wp-biographia/
Tags: bio, biography, bio box, biography box, twitter, facebook, linkedin, googleplus, google+, website, about, author, about author, author box
Requires at least: 3.2
Tested up to: 3.2.1
Stable tag: 1.1.0

Add and display a customizable author biography for all single post types (posts, pages, etc), in RSS feeds, in archives and on each entry on the landing page.

== Description ==

This plugin allows you to add a customizable biography to posts, to RSS feeds to pages, to archives and to each post on your blog's landing page. It integrates out of the box with the information that can be provided in each user's profile.

Settings and options include:

1. Choose when to display a Biography Box; on the front page, in archives, on individual posts, pages, or any other custom post type and in RSS feeds.
1. Choose the border style and background color of the Biography Box
1. Choose the amount of user profile information displayed in the Biography Box
1. Choose the gravatar image size
1. Choose to display the bio at the top or the bottom (universally)

The plugin also expands and enhances the Contact Info section of your user profile, adding support for Twitter, Facebook, LinkedIn and Google+ profile links as well as Yahoo! Messenger, AIM, Windows Live Messenger and Jabber/Google Talk instant messaging profiles.

The plugin also has an added filter and shortcode ([wp_biographia]) to add further customization. For example, if you use page templates to display custom post types or your blog, you can simply use filter ('wp_biographia_pattern') to decide how you'd like to customize. Or, if you want the bios to appear at the bottom but on archive pages, you want them at the top, then the filter can do that as well. Or use the shortcode to control where it appears in a post of any post type.

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
Add this code in your functions.php file:
<code>remove_filter( 'pre_user_description' , 'wp_filter_kses' );</code>

= How do I remove the bio on pages using page templates? =
Add this code in your functions.php file:
<code>add_action( 'wp_head' , 'remove_author_box_page_template' );
function remove_author_box_page_template() {
  if ( is_page_template ( 'page_blog.php' ) )
		add_filter( 'wp_biographia_pattern' , 'content_only_pattern' );
}
function content_only_pattern( $pattern ) {
	return '%1s';
}</code>

= How do I remove the bio on pages using page templates? =
Add this code in your functions.php file:
<code>add_action( 'wp_head' , 'remove_author_box_page_template' );
function remove_author_box_page_template() {
	if ( is_archive() )
		add_filter( 'wp_biographia_pattern' , 'content_only_pattern' );
}
function content_only_pattern( $pattern ) {
	return '%2s %1s';
}</code>

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

The current version is 1.1 (2011.10.19)

= 1.1 =
* Added the ability to set image size
* Added a simple shortcode
* Fixed CSS issue for gravatar
* Added Custom Post Types support with the ability to exclude based on post IDs
* Added ability to set the bio at the top or the bottom
* Add a filter to short circuit for further customization

= 1.0 =
* First version of WP Biographia released

== Upgrade Notice ==

= 1.0 =
* This is the first version of WP Biographia