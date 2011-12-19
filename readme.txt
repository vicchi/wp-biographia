=== WP Biographia ===
Contributors: vicchi, wpsmith
Donate link: http://www.vicchi.org/codeage/donate/
Tags: bio, biography, bio box, biography box, twitter, facebook, linkedin, googleplus, google+, website, about, author, about author, author box
Requires at least: 3.3
Tested up to: 3.3
Stable tag: 2.1.0

Add and display a customizable author biography for all single post types, in RSS feeds, in archives and on each entry on the landing page.

== Description ==

This plugin allows you to add a customizable biography to posts, to RSS feeds, to pages, to archives and to each post on your blog's landing page. It integrates out of the box with the information that can be provided in each user's profile. Display of the Biography Box can be suppressed on a per user basis for posts and for pages.

Settings and options include:

1. Choose when to display a Biography Box; on the front page, in archives, on individual posts, pages, or any other custom post type and in RSS feeds.
1. Choose the border style and background color of the Biography Box
1. Choose the amount of user profile information displayed in the Biography Box
1. Choose the avatar image size
1. Choose to display the Biography Box at the top or the bottom of content (universally)
1. Choose to suppress the display of the Biography Box for pages, posts and posts/pages on a per user basis

The plugin expands and enhances the Contact Info section of your user profile, adding support for Twitter, Facebook, LinkedIn and Google+ profile links as well as Yahoo! Messenger, AIM, Windows Live Messenger and Jabber/Google Talk instant messaging profiles.

The plugin also has an added filter and shortcode (`[wp_biographia]`) to add further customization. For example, if you use page templates to display custom post types on your blog, you can simply use `filter ('wp_biographia_pattern')` to decide how you'd like to customize. If you want the Biography Box to appear at the bottom but on archive pages, you want them at the top, then the filter can do that as well, or simply use the shortcode to control where it appears in a post of any post type.

== Installation ==

1. You can install WP Biographia automatically from the WordPress admin panel. From the Dashboard, navigate to the Plugins / Add New page and search for "WP Biographia" and click on the "Install Now" link.
1. Or you can install WP Biographia manually. Download the plugin Zip archive and uncompress it. Copy or upload the `wp-biographia` folder to the `wp-content/plugins` folder on your web server.
1. Activate the plugin. From the Dashboard, navigate to Plugins and click on the "Activate" link under the entry for WP Biographia.
1. Enhance your WordPress user profile. From the Dashboard, navigate to Users and click on the "Edit" link under your profile.
1. Edit your WordPress user profile. Add your biography to the "Biographical Info" text box. WP Biographia also adds to the list of Contact Info you can associate with your profile, adding support for Twitter, Facebook, LinkedIn and Google+. Click on the "Update Profile" link to save your changes.
1. Customize and configure what information WP Biographia displays; From the Dashboard, navigate to the Settings / WP Biographia page or click on the "Settings" link from the Plugins page on the Dashboard.
1. You can can control display settings, style settings and content settings for the Biography Box.
1. Click on the "Save Changes" button to preserve your chosen settings and options.
1. If you enable the display of the post author's image, make sure avatar support is turned on; from the Dashboard, navigate to Settings / Discussion and ensure that Show Avatars is enabled.
1. Users with the `manage_options` capability can edit their profile via Users / Your Profile from the Dashboard to suppress the display of the Biography Box on posts and/or on pages and also the profiles of other users via the Users / All Users / Edit from the Dashboard.
1. Suppression of the display of the Biography Box on posts and/or on pages can also be configured from the Dashboard; navigate to Settings / WP Biographia / Biography Box Display Settings.


== Frequently Asked Questions ==

= Is there a web site for this plugin? =

Absolutely. Go to the [WP Biographia home page](http://www.vicchi.org/codeage/wp-biographia/) for the latest information. There's also the official [WordPress plugin directory page](http://wordpress.org/extend/plugins/) and the [source for the plugin is on GitHub](http://vicchi.github.com/wp-biographia/) as well.

= I've configured WP Biographia to display on post archives but it's not working; what's happening here? =

Some themes display the Biography Box for post archives, but for other themes the Biography Box never appears. This often turns out to be down to the way in which the theme renders the archive page. If the theme’s <code>archive.php</code> uses <code>the_content()</code> as part of the WordPress Loop then the Biography Box appears as it should, but if the theme uses <code>the_excerpt()</code> as part of the Loop, then either the first 55 characters of the post or the post’s specific excerpt will be displayed. As WP Biographia appends the Biography Box to the end of each post’s content, themes which use <code>the_excerpt()</code> will, sadly, never display as intended when used with WP Biographia. Thankfully, this is less a shortcoming of the plugin or of the theme, it’s simply the way in which WordPress handles post excerpts.

= I've configured WP Biographia to display the author's image but it's not working; what's happening here? =

Author profile pictures, or avatars, are part of the WordPress core but enabling them isn't done at the level of the user profile, instead it's part of the way in which comments are configured. If you enable the display of the post author’s image, make sure avatar support is turned on; from the Dashboard, navigate to Settings / Discussion and ensure that Show Avatars is enabled.

= I want to upload my author's images, host them on my web server and not use Gravatars; how do I do this? =

WP Biographia uses the `get_avatar` [pluggable function](http://codex.wordpress.org/Pluggable_Functions) to output the author's avatar image. Theoretically, any plugin that supports locally hosted avatar images and which overrides the default WordPress implementation of `get_avatar` should be able to be used. In practice, whether this approach will work for you or not depends on the combination of the theme you're using and the interactions that the other plugins that you're using has with the WordPress core and with your theme. The [Simple Local Avatars](http://wordpress.org/extend/plugins/simple-local-avatars/) plugin plugs `get_avatar` and cooperates nicely with WP Biographia, at least in my local testing environment; your mileage may vary.

= How do I add HTML to the Biographical Info section of a user's profile? =

Add this code to your theme's <code>functions.php</code> file:

<code>remove_filter('pre_user_description', 'wp_filter_kses');</code>

This may be a configurable setting in a future version of the plugin to avoid the need to hack your theme's <code>functions.php</code> file.

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

This may be a configurable setting in a future version of the plugin to avoid the need to hack your theme's <code>functions.php</code> file.

= This plugin looks very much like the WP About Author; what's the connection? =

WP Biographia is inspired by and based on the [WP About Author](http://wordpress.org/extend/plugins/wp-about-author/) plugin by [Jon Bishop](http://www.jonbishop.com/). Thanks and kudos must go to Jon for writing a well structured, working WordPress plugin released under a software license that enables other plugins such as this one to be written or derived in the first place. Jon's written [other WordPress plugins](http://profiles.wordpress.org/users/JonBishop/) as well; you should take a look.

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

The current version is 2.1 (2011.11.01)

= 2.1 =
* Add ability to suppress the Biography Box from being displayed on posts, on pages and on posts and pages on a per user basis
* Add settings link to Settings / WP Biographia admin page from the plugin's entry on the Dashboard / Plugins page
* Add checks for avatar display in the Biography Box being requested with avatar support not enabled in the Settings / Discussions admin page
* Add Help & Support sidebar box to Settings / WP Biographia admin page
* Handle upgrades to configuration settings gracefully; fixed bug that didn't persist unused/unchanged configuration settings
* Cleaned up the wording for the Settings / WP Biographia admin page and made terminology consistent across all configurable options
* Tweaked admin CSS to introduce padding between the settings container and sidebar container that changed in WordPress 3.3

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
= 2.1 =
* This is the third version of WP Biographia; adds support for per user suppression of displaying the Biography Box on posts, on pages and on posts and pages, plus bug fixes and minor UI changes.

= 2.0 =
* This is the second version of WP Biographia and incorporates new features and bug fixes as well as some under-the-hood plumbing changes; in particular the plugin file locations have changed to reflect WordPress plugin development recommendations

= 1.0 =
* This is the first version of WP Biographia