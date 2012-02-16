=== WP Biographia ===
Contributors: vicchi, wpsmith
Donate link: http://www.vicchi.org/codeage/donate/
Tags: wp-biographia, wp biographia, biographia, bio, biography, bio box, biography box, twitter, facebook, linkedin, googleplus, google+, delicious, flickr, picasa, vimeo, youtube, reddit, website, about, author, about author, author box, contributors
Requires at least: 3.3
Tested up to: 3.3.1
Stable tag: 2.4.1

Add and display a customisable author biography for all single post types, in RSS feeds, in archives and on each entry on the landing page.

== Description ==

This plugin allows you to add a customisable biography to posts, to RSS feeds, to pages, to archives and to each post on your blog's landing page. It integrates out of the box with the information that can be provided in each user's profile and supports custom post types. Display of the Biography Box can be suppressed on a global or per user basis for posts, pages and custom post types.

Settings and options include:

1. Choose when to display a Biography Box; on the front page, in archives, on individual posts, pages, or any other custom post type and in RSS feeds.
1. Choose the border style and background color of the Biography Box
1. Choose the amount of user profile information displayed in the Biography Box
1. Choose the avatar image size
1. Choose to display the Biography Box at the top or the bottom of content (universally)
1. Choose to suppress the display of the Biography Box for pages, posts and posts/pages on a per user basis

The plugin expands and enhances the Contact Info section of your user profile, adding support for Twitter, Facebook, LinkedIn, Google+, Delicious, Flickr, Picasa, Vimeo, YouTube and Reddit profile links as well as Yahoo! Messenger, AIM, Windows Live Messenger and Jabber/Google Talk instant messaging profiles. Your Contact Info links can then be displayed as part of the Biography Box, either as plain text links or as icon links.

The plugin also has an added filter and shortcode (`[wp_biographia]`) to add further customisation. For example, if you use page templates to display custom post types on your blog, you can simply use `filter ('wp_biographia_pattern')` to decide how you'd like to customise. If you want the Biography Box to appear at the bottom but on archive pages, you want them at the top, then the filter can do that as well, or simply use the shortcode to control where it appears in a post of any post type. For more information on how to use the `[wp_biographia]` shortcode, see the *Shortcode Support And Usage* section.

== Installation ==

1. You can install WP Biographia automatically from the WordPress admin panel. From the Dashboard, navigate to the *Plugins / Add New* page and search for *"WP Biographia"* and click on the *"Install Now"* link.
1. Or you can install WP Biographia manually. Download the plugin Zip archive and uncompress it. Copy or upload the `wp-biographia` folder to the `wp-content/plugins` folder on your web server.
1. Activate the plugin. From the Dashboard, navigate to Plugins and click on the *"Activate"* link under the entry for WP Biographia.
1. Enhance your WordPress user profile. From the Dashboard, navigate to Users and click on the "Edit" link under your profile.
1. Edit your WordPress user profile. Add your biography to the *"Biographical Info"* text box. WP Biographia also adds to the list of Contact Info you can associate with your profile, adding support for Twitter, Facebook, LinkedIn and Google+ and other contact profiles. Click on the *"Update Profile"* link to save your changes.
1. Customise and configure what information WP Biographia displays; From the Dashboard, navigate to the *Settings / WP Biographia* page or click on the *"Settings"* link from the Plugins page on the Dashboard.
1. You can can control display settings, style settings and content settings for the Biography Box.
1. Click on the *"Save Changes"* button to preserve your chosen settings and options.
1. If you enable the display of the post author's image, make sure avatar support is turned on; from the Dashboard, navigate to *Settings / Discussion* and ensure that *Show Avatars* is enabled. Don't forget to save your changes.
1. Users with the `manage_options` capability can edit their profile via *Users / Your Profile* from the Dashboard to suppress the display of the Biography Box on posts and/or on pages and also the profiles of other users via the *Users / All Users / Edit* from the Dashboard.
1. Suppression of the display of the Biography Box on posts and/or on pages can also be configured from the Dashboard; navigate to *Settings / WP Biographia / Biography Box Display Settings*.

== Frequently Asked Questions ==

= How do I get help or support for this plugin? =

In order of preference, you can ask a question on the [WordPress support forum](http://wordpress.org/tags/wp-biographia?forum_id=10); this is by far the best way so that other users can follow the conversation. You can ask me a question on Twitter; I'm [@vicchi](http://twitter.com/vicchi). Or you can drop me an email instead. I can't promise to answer your question but I do promise to answer and do my best to help.

= Is there a web site for this plugin? =

Absolutely. Go to the [WP Biographia home page](http://www.vicchi.org/codeage/wp-biographia/) for the latest information. There's also the official [WordPress plugin repository page](http://wordpress.org/extend/plugins/wp-biographia/) and the [source for the plugin is on GitHub](http://vicchi.github.com/wp-biographia/) as well.

= I've configured WP Biographia to display the author's image but it's not working; what's happening here? =

Author profile pictures, or avatars, are part of the WordPress core but enabling them isn't done at the level of the user profile, instead it's part of the way in which comments are configured. If you enable the display of the post author’s image, make sure avatar support is turned on; from the Dashboard, navigate to *Settings / Discussion* and ensure that *Show Avatars* is enabled.

= I want to upload my author's images, host them on my web server and not use Gravatars; how do I do this? =

WP Biographia uses the `get_avatar` [pluggable function](http://codex.wordpress.org/Pluggable_Functions) to output the author's avatar image. Theoretically, any plugin that supports locally hosted avatar images and which overrides the default WordPress implementation of `get_avatar` should be able to be used. In practice, whether this approach will work for you or not depends on the combination of the theme you're using and the interactions that the other plugins that you're using has with the WordPress core and with your theme. The [Simple Local Avatars](http://wordpress.org/extend/plugins/simple-local-avatars/) plugin plugs `get_avatar` and cooperates nicely with WP Biographia, at least in my local testing environment; your mileage may vary.

= I've configured WP Biographia to show my website/Twitter/Facebook/etc links but I don't see them in the Biography Box; where do I define these links? =

WP Biographia adds a number of social media and web link fields to your WordPress user profile; from the Dashboard, navigate to *Users / Your Profile* and enter the links you want displayed to the fields in the Contact Info section.

= I've installed and configured WP Biographia and now I see not one but two differing Biography Boxes; what's going on? =

There's probably one of two things going on here. Firstly, you've already got another plugin that makes a Biography Box installed and active and this plugin, as well as WP Biographia, are doing their job properly. Secondly, the theme you're using hard codes a Biography Box into the theme templates. Both the TwentyTen and TwentyEleven themes supplied as part of a standard WordPress install do this.

= I only want to show the Biography Box for certain users and not for others; can I do this? =

As of v2.1, WP Biographia allows you to suppress the Biography Box being displayed on a per user basis. You can suppress for posts only, for pages only or for both posts and pages. There's two ways of configuring this. If your user has the `manage_options` capability, you can choose the degree of suppression, if any, from your user profile or for any other user's profile; from the Dashboard, navigate to Users and check the *Suppress From Posts* and/or *Suppress From Pages* checkbox options. You can also configure this easily from the plugin's Settings And Options; from the Dashboard, navigate to the *Settings / WP Biographia* page and under *Biography Box Per User Settings*, add and/or remove the users to fit your model of who should have the Biography Box displayed.

= How do I add HTML to the Biographical Info section of a user's profile? =

In previous releases of the plugin, I've recommended that you add this code to your theme's <code>functions.php</code> file:

<code>remove_filter('pre_user_description', 'wp_filter_kses');</code>

But as [WebEndev](http://wordpress.org/support/profile/munman) helpfully pointed out on the [WordPress forums](http://wordpress.org/support/topic/plugin-wp-biographia-biographical-info-formatting-issue-avatar-exclude-posts?replies=7#post-2562773), this allows *all* HTML to be added to the Biography Info section of a user's profile, which may be going *too* far. The following code, in your theme's `functions.php`, will allow line breaks to be honoured but filter out any HTML tags and attributes which are not allowed by the `$allowedposttags` WordPress global.

<code>
remove_filter('pre_user_description', 'wp_filter_kses');
add_filter('pre_user_description', 'wp_filter_post_kses');
add_filter('pre_user_description', 'wptexturize');
add_filter('pre_user_description', 'wpautop');
add_filter('pre_user_description', 'convert_chars');
add_filter('pre_user_description', 'balanceTags', 50);
</code>

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

= I want to use my own icon set for my author's contact links; how do I do this? =

Firstly select the icon set you want to use. You'll need to ensure that the icon files are in `.png` format and are named to match the icon set that WP Biographia ships with; take a look in `wp-biographia/images` to see the naming convention. Upload your icon set to your web server and note the URL (not the local path) to where your icons will live. Navigate to *Settings / WP Biographia / Biography Box Content Settings*, ensure that the *Use Alternate Icon Set* option is checked and the URL to your alternate icons is specified in the *Alternate Icon Set URL* text box. By default, WP Biographia sizes the contact link icons at 32x32 pixels; you can override this in your local CSS file by redefining the `.wp-biographia-item-icon` CSS class (see `wp-biographia/css/wp-biographia.css`).

= WP Biographia isn't available in my language; can I submit a translation? =

WordPress and this plugin use the gettext tools to support internationalisation. The source file containing each string that needs to be translated ships with the plugin in `wp-biographia/lang/src/wp-biographia.pot`. See the [I18n for WordPress Developers](http://codex.wordpress.org/I18n_for_WordPress_Developers) page for more information or get in touch for help and hand-holding.

= This plugin looks very much like the WP About Author; what's the connection? =

WP Biographia is inspired by and based on the [WP About Author](http://wordpress.org/extend/plugins/wp-about-author/) plugin by [Jon Bishop](http://www.jonbishop.com/). Thanks and kudos must go to Jon for writing a well structured, working WordPress plugin released under a software license that enables other plugins such as this one to be written or derived in the first place. Jon's written [other WordPress plugins](http://profiles.wordpress.org/users/JonBishop/) as well; you should take a look.

= I want to amend/hack/augment this plugin; can I do the same? =

Totally; like the original plugin by Jon, this plugin is licensed under the GNU General Public License v2 (GPLV2). See http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt for the full license terms.

= Where does the name WP Biographia come from? =

WP Biographia is named after the etymology of the modern English word biography. The word first appeared in the 1680s, probably from the latin biographia which itself derived from the Greek bio, meaning "life" and graphia, meaning "record" or "account" which derived from graphein, "to write".

== Screenshots ==

1. WP Biographia Settings And Options: Biography Box Display Settings
1. WP Biographia Settings And Options: Biography Box Per User Settings
1. WP Biographia Settings And Options: Biography Box Style Settings
1. WP Biographia Settings And Options: Biography Box Content Settings
1. WP Biographia Settings And Options: Biography Box Content Settings, continued
1. Sample Biography Box; contact links shown as text
1. Sample Biography Box; contact links shown as icons

== Changelog ==

The current version is 2.4.1 (2012.02.16)

= 2.4.1 =
* Fixed regression bug in v2.4 where a contact link items displayed as an empty link if enabled in WP Biographia but if the corresponding link in the user's profile was empty.
* Fixed regression bug in v2.4 where the user profile Biography Box settings text was not properly displayed.
* Tweak v2.4 CSS to clear up styling issues and to align list item styling with best practice.

= 2.4 =
* Add internationalisation support; add Spanish and Turkish language files.
* Add configuration setting to control the author's name in the Biography Box as a link to "More Posts By This Author".
* Add support for displaying the author's contact links as icons as well as plain text links.
* Add support for using an alternate link icon set.

= 2.3 =
* Suppress display of "More Posts" link in the Biography Box (if configured) if the user/author has no posts.
* Add `author`, `prefix` and `name` short code attribute support.
* Add support for global (across single, archive and front page templates) post exclusions in built-in post types and custom post types.
* Tightened wording in admin screen around post exclusions.

= 2.2 =
* Add enhanced short code support (`raw` and `configured` modes)
* Add support for displaying the Biography Box on archive pages that use excerpts
* Enhance contact information and Biography Box links to support Delicious, Flickr, Picasa, Vimeo, YouTube and Reddit
* Fixed bug that caused the Biography Box to be displayed for every page of a multiple page post
* Fixed bugs in avatar image size handling; non-default avatar image size was not persisted across settings changes; avatar image container div was not resized to new non-default avatar image size
* Migrate use of wp_print_styles to wp_enqueue_scripts; see (http://wpdevel.wordpress.com/2011/12/12/use-wp_enqueue_scripts-not-wp_print_styles-to-enqueue-scripts-and-styles-for-the-frontend/)
* Made terminology and control ordering for custom post types consistent in admin pages

= 2.1.1 =
* Fixed bug in per user suppression due to debug code being left in the release

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

= 2.4.1 =
This is the 8th version of WP Biographia and is a bug fix release, clearing up several regression bugs that appeared in v2.4.

= 2.4 =
This is the 7th version of WP Biographia; adds internationalisation support plus Spanish and Turkish translations; adds support for displaying the author's contact links as icons and for using an alternate icon set.

= 2.3 =
This is the 6th version of WP Biographia; adds support for global post exclusions, enhanced shortcode options and suppression of the "More Posts" link if the author has no posts.

= 2.2 =
* This is the fifth version of WP Biographia; adds support for enhanced shortcode usage, excerpt support on archive pages and additional social media profiles and links as well as several bug fixes.

= 2.1.1 =
* This is the fourth version of WP Biographia which fixes a fatal bug in v2.1; please upgrade to this version and skip v2.0.

= 2.1 =
* This is the third version of WP Biographia; adds support for per user suppression of displaying the Biography Box on posts, on pages and on posts and pages, plus bug fixes and minor UI changes.

= 2.0 =
* This is the second version of WP Biographia and incorporates new features and bug fixes as well as some under-the-hood plumbing changes; in particular the plugin file locations have changed to reflect WordPress plugin development recommendations

= 1.0 =
* This is the first version of WP Biographia

== Shortcode Support And Usage ==

WP Biographia supports a single shortcode, `[wp_biographia]`. Adding this shortcode to the content of a post or page or into a theme template as content, expands the shortcode and replaces it with a Biography Box.

The shortcode also supports multiple *attributes* which allow you to customise the way in which the shortcode is expanded into the Biography Box:

* the `mode` attribute
* the `author` attribute
* the `prefix` attribute
* the `name` attribute

= The "mode" Attribute =

In `raw` mode, which is the default (specified as `[wp_biographia mode="raw"]` or simply as `[wp_biographia]`), the plugin inserts the Biography Box in *you've asked for it, you've got it* mode.

Or to put it another way, the plugin will honour the settings that you specify under *Dashboard / Settings/ WP Biographia* for *Biography Box Style Settings* and for *Biography Box Content Settings* but will ignore the *Biography Box Display Settings* and *Biography Box Per User Settings*.

In `configured` mode, specified as `[wp_biographia mode="configured"]`, the plugin inserts the Biography Box and will honour all the settings under *Dashboard / Settings / WP Biographia* with the exception of *Display On Front Page*, *Display On Individual Post*, *Display On Post Archives* and *Display On Individual Pages*, as well as their equivalents for any custom post types you may have created.

The thinking behind this is that you probably want to honour post or page exclusions and per user exclusions, but by using the shortcode in your theme templates, you want to be in control of how and where the Biography Box is displayed.

= The "author" Attribute =

If the `author` attribute is omitted, which is the default, the shortcode assumes it's being used within the [WordPress Loop](http://codex.wordpress.org/The_Loop) and will display the Biography Box once for the current post's, page's or custom post type's author.

Specifying a user's login name as the `author` attribute overrides this behaviour and allows multi-user sites to use the plugin to create a *contributors* page, where you use the shortcode as `[wp_biographia user="login-name"]` once for each of your site's authors that you want to appear, replacing `"login-name"` with a valid login name for one of your authors.

You call also use the `author` attribute in *wildcard* mode, specifying the author's login name as `*` as `[wp_biographia author="*"]`; this will then loop over all of the authors that have logins on your site, displaying the Biography Box once for each author, ordered alphabetically by login name.

Specifying an invalid login name (`[wp_biographia author="idontexist"]`) will result in no Biography Box being displayed. Specifying an empty login name (`[wp_biographia author=""]`) will cause the `author` parameter to be ignored and may result in undefined behaviour, such as a partially populated Biography Box being displayed as the shortcode is being used outside of the Loop and thus no author information is made available to the plugin by WordPress.

= The "prefix" Attribute =

If the `prefix` attribute is omitted, which is the default, the Biography Box will be displayed with *Biography Prefix* text configured in *Settings/ WP Biographuia / Biography Box Content Settings* before the author's name. This can be overridden by using the `prefix` attribute, along the lines of `[wp_biographia prefix="All About"]`.

= The "name" Attribute =

If the `name` attribute is omitted, which is the default, the Biography Box will be displayed with the author's name as configured by *Author's Name* in *Settings / WP Biographia /Biography Box Content Settings*. This can be overriden by supplying one of the following for the `name` attribute's argument:

* `account-name`
* `first-last-name`
* `nickname`
* `display-name`
* `none`

