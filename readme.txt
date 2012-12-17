=== WP Biographia ===
Contributors: vicchi, wpsmith
Donate link: http://www.vicchi.org/codeage/donate/
Tags: wp-biographia, wp biographia, biographia, bio, biography, bio box, biography box, twitter, facebook, linkedin, googleplus, google+, delicious, flickr, picasa, vimeo, youtube, reddit, website, about, author, user, about author, user box, author box, contributors, author biography, user biography, avatar, gravatar, guest post, guest author
Requires at least: 3.4
Tested up to: 3.5.0
Stable tag: 3.3.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Add and display a customisable user biography to the front page, single post types, archives and RSS feeds as well as via a widget in your sidebar.

== Description ==

This plugin allows you to add a customisable biography to posts, to RSS feeds, to pages, to archives and to each post on your blog's landing page as well as via a widget in your sidebar. It integrates out of the box with the information that can be provided in each user's profile and supports custom post types. Display of the Biography Box can be hidden on a global or per user basis for posts, pages and custom post types as well as on a per category basis.

Settings and options include:

1. Choose when to display a Biography Box; on the front page, in archives, on individual posts, pages, or any other custom post type and in RSS feeds.
1. Choose the border style and background colour of the Biography Box
1. Choose the amount of user profile information displayed in the Biography Box
1. Choose the avatar image size
1. Choose to display the Biography Box at the top or the bottom of content (universally)
1. Choose to hide the display of the Biography Box for pages, posts and posts/pages on a per user basis
1. Choose whether to display the Biography Box in the sidebar of your blog.

The plugin expands and enhances the Contact Info section of your user profile, adding support for Twitter, Facebook, LinkedIn, Google+, Delicious, Flickr, Picasa, Vimeo, YouTube and Reddit profile links as well as Yahoo! Messenger, AIM, Windows Live Messenger and Jabber/Google Talk instant messaging profiles. Your Contact Info links can then be displayed as part of the Biography Box, either as plain text links or as icon links. Further contact links can easily be added to the Biography Box by using the `wp_biographia_contact_info` and `wp_biographia_link_items` filters.

The position of the Biography Box can be controlled by the plugin's supported settings and options, or manually via the plugin's shortcode (`[wp_biographia]`) (see the *Shortcode Support And Usage* section for more information) or via template tags in your theme's template files (see the *Template Tags* section for more information).

The position and content of the Biography Box, including adding support for new contact links, changing the content of the Biography Box when displayed via the shortcode, the format of the contact links and the overall format of the Biography Box can be modified by the plugin's filters. See the *Filter Support And Usage* section for more information.

The plugin also supports displaying a reduced version of the Biography Box via a sidebar widget and provides a shorter, more terse, biography in the user's Profile to prevent the full length biography text taking up too much space on the sidebar.

== Installation ==

1. You can install WP Biographia automatically from the WordPress admin panel. From the Dashboard, navigate to the *Plugins / Add New* page and search for *"WP Biographia"* and click on the *"Install Now"* link.
1. Or you can install WP Biographia manually. Download the plugin Zip archive and uncompress it. Copy or upload the `wp-biographia` folder to the `wp-content/plugins` folder on your web server.
1. Activate the plugin. From the Dashboard, navigate to Plugins and click on the *"Activate"* link under the entry for WP Biographia.
1. Enhance your WordPress user profile. From the Dashboard, navigate to Users and click on the "Edit" link under your profile.
1. Edit your WordPress user profile. Add your biography to the *"Biographical Info"* text box. WP Biographia also adds to the list of Contact Info you can associate with your profile, adding support for Twitter, Facebook, LinkedIn and Google+ and other contact profiles. Click on the *"Update Profile"* link to save your changes.
1. Customise and configure what information WP Biographia displays; From the Dashboard, navigate to the *Settings / WP Biographia* page or click on the *"Settings"* link from the Plugins page on the Dashboard.
1. You can can control display settings, style settings and content settings for the Biography Box.
1. Click on the *"Save Changes"* button to preserve your chosen settings and options.
1. If you enable the display of the post user's image, make sure avatar support is turned on; from the Dashboard, navigate to *Settings / Discussion* and ensure that *Show Avatars* is enabled. Don't forget to save your changes.
1. Users with the `manage_options` capability can edit their profile via *Users / Your Profile* from the Dashboard to hide the display of the Biography Box on posts and/or on pages and also the profiles of other users via the *Users / All Users / Edit* from the Dashboard.
1. Hiding of the display of the Biography Box on posts and/or on pages can also be configured from the Dashboard; navigate to *Settings / WP Biographia / Biography Box Display Settings* and click on the *Exclusions* tab.

== Frequently Asked Questions ==

= How do I get help or support for this plugin? =

In short, very easily. But before you read any further, take a look at [Asking For WordPress Plugin Help And Support Without Tears](http://www.vicchi.org/codeage/asking-for-wordpress-plugin-help-and-support-without-tears/) before firing off a question. In order of preference, you can ask a question on the [WordPress support forum](http://wordpress.org/support/plugin/wp-biographia); this is by far the best way so that other users can follow the conversation. You can ask me a question on Twitter; I'm [@vicchi](http://twitter.com/vicchi). Or you can drop me an email instead. I can't promise to answer your question but I do promise to answer and do my best to help.

= Is there a web site for this plugin? =

Absolutely. Go to the [WP Biographia home page](http://www.vicchi.org/codeage/wp-biographia/) for the latest information. There's also the official [WordPress plugin repository page](http://wordpress.org/extend/plugins/wp-biographia/) and the [source for the plugin is on GitHub](http://vicchi.github.com/wp-biographia/) as well.

= I've configured WP Biographia to display the user's image but it's not working; what's happening here? =

User profile pictures, or avatars, are part of the WordPress core but enabling them isn't done at the level of the user profile, instead it's part of the way in which comments are configured. If you enable the display of the post user’s image, make sure avatar support is turned on; from the Dashboard, navigate to *Settings / Discussion* and ensure that *Show Avatars* is enabled. WordPress uses the email address that is part of your user's profile to look up the right avatar image from [gravatar.com](http://gravatar.com/), so you need to ensure that you're using the same email address on your site as well as for your avatar.

= I want to upload my user's images, host them on my web server and not use Gravatars; how do I do this? =

WP Biographia uses the `get_avatar` [pluggable function](http://codex.wordpress.org/Pluggable_Functions) to output the user's avatar image. Theoretically, any plugin that supports locally hosted avatar images and which overrides the default WordPress implementation of `get_avatar` should be able to be used. In practice, whether this approach will work for you or not depends on the combination of the theme you're using and the interactions that the other plugins that you're using has with the WordPress core and with your theme. The [Simple Local Avatars](http://wordpress.org/extend/plugins/simple-local-avatars/) plugin plugs `get_avatar` and cooperates nicely with WP Biographia, at least in my local testing environment; your mileage may vary.

= I've configured WP Biographia to show my website/Twitter/Facebook/etc links but I don't see them in the Biography Box; where do I define these links? =

WP Biographia adds a number of social media and web link fields to your WordPress user profile; from the Dashboard, navigate to *Users / Your Profile* and enter the links you want displayed to the fields in the Contact Info section.

= I've installed and configured WP Biographia and now I see not one but two differing Biography Boxes; what's going on? =

There's probably one of two things going on here. Firstly, you've already got another plugin that makes a Biography Box installed and active and this plugin, as well as WP Biographia, are doing their job properly. Secondly, the theme you're using hard codes a Biography Box into the theme templates. Both the TwentyTen and TwentyEleven themes supplied as part of a standard WordPress install do this.

= I only want to show the Biography Box for certain users and not for others; can I do this? =

WP Biographia allows you to hide the Biography Box from being displayed on a per user basis. You can hide for posts only, for pages only or for both posts and pages. There's two ways of configuring this. If your user has the `manage_options` capability, you can choose the degree of hiding, if any, from your user profile or for any other user's profile; from the Dashboard, navigate to Users and check the *Hide From Posts* and/or *Hide From Pages* checkbox options. You can also configure this easily from the plugin's Settings And Options; from the Dashboard, navigate to the *Settings / WP Biographia* page, click on the *Exclusions* tab and under *User Hiding Settings*, add and/or remove the users to fit your model of who should have the Biography Box displayed.

= I want to show the Biography Box for all users but only for certain categories; can I do this? =

From the Dashboard, navigate to the *Settings / WP Biographia* page, click on the *Exclusions* tab and under *Category Exclusion Settings*, add and/or remove the categories to fit your model of when the Biography Box should be displayed.

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

= How do I remove the bio on pages using page templates? =

Add this code to your theme's <code>functions.php</code> file:

<code>add_action ('wp_head', 'remove_user_box_page_template');

function remove_user_box_page_template() {
  if (is_page_template ('page_blog.php'))
		add_filter ('wp_biographia_pattern' , 'content_only_pattern');
}

function content_only_pattern($pattern) {
	return '%1s';
}</code>

= I want to use my own icon set for my user's contact links; how do I do this? =

Firstly select the icon set you want to use. You'll need to ensure that the icon files are in `.png` format and are named to match the icon set that WP Biographia ships with; take a look in `wp-biographia/images` to see the naming convention. Upload your icon set to your web server and note the URL (not the local path) to where your icons will live. Navigate to *Settings / WP Biographia* and click on the *Content* tab, ensure that the *Use Alternate Icon Set* option is checked and the URL to your alternate icons is specified in the *Alternate Icon Set URL* text box. By default, WP Biographia sizes the contact link icons at 32x32 pixels; you can override this in your local CSS file by redefining the `.wp-biographia-item-icon` CSS class (see `wp-biographia/css/wp-biographia.css`).

You can also override the icon file name and source URL on a per contact link basis via the `$icon_url_dir` parameter via the `wp_biographia_link_items` filter.

So to recap, the plugin uses its own default set of icons, followed by the *Alternate Icon Set URL* to allow you to point to an entire alternate set of icons, if the supplied ones aren't to your liking, followed by link specific overrides via the `wp_biographia_link_items` filter. The order of precedence looks something like ...

1. the plugin's icon set - for all icons - typically this is `/wp-content/plugins/wp-biographia/images`.
1. the alternate icon set - for all icons (even added via the filter, if no override takes place on `$icon_url_dir`)
1. an override of the icon set URL for the single contact method you're adding via `wp_biographia_link_items` (assuming it's also added via `wp_biographia_contact_info`)

See the *Filter Support And Usage* section for more information on the plugin's filters.

= I want to change the CSS used to format the Biography Box; how do I do this? =

The HTML and CSS classes that the plugin emits follows a consistent structure and naming convention. See [Hacking WP Biographia’s Appearance With CSS](http://www.vicchi.org/codeage/wp-biographia/hacking-wp-biographias-appearance-with-css/) for more information.

= I've changed the Biography Box CSS but my changes aren't showing up; what's happening? =

WP Biographia uses *minified* CSS files to improve the speed at which a page loads. If you make changes to the plugin's CSS in `/wp-content/plugins/wp-biographia/css/wp-biographia.css` the plugin will still load the minified version in `/wp-content/plugins/wp-biographia/css/wp-biographia.min.css`. So if you make any site specific changes to the plugin's CSS, which isn't recommended (see [Hacking WP Biographia’s Appearance With CSS](http://www.vicchi.org/codeage/wp-biographia/hacking-wp-biographias-appearance-with-css/)), you'll need to ensure you (re)minify the master CSS to get the changes to be picked up by the plugin.

= WP Biographia doesn't support social network FOO; can you add this to the next version? =

*Yes*. But also *no*. One of the wonderful things about today's web is the vast amount of ways we have to interact with each other. I can't keep up. No, really. In practical terms, this would mean that the plugin's settings and options panels would soon get out of hand, plus the overhead of adding, testing and releasing a new version of the plugin would get out of hand before the settings and options do. But ... see the next FAQ for the answer.

= WP Biographia doesn't support social network or contact method BAR; how can I add this? =

With the cunning use of the filters that WP Biographia supports, you can add support for as many social networks and/or contact methods as you like. You'll need to do two things for each link you want to add to the plugin.

1. In your theme's `functions.php` add support for the new link to the user's profile by way of the `wp_biographia_contact_info` filter.
1. Still in your theme's `functions.php` add support for the new link to be displayed, with an icon if you wish, via the `wp_biographia_link_items` filter.

See the *Filter Support And Usage* section for a working example of these two filters to add support for a new contact link.

= The "More Posts" link in the Biography Box links to my site's landing page and not an author's archive page; what's happening? =

If you're using an SEO plugin, this might be optimising out the *More Posts* link. Specifically, [Yoast's WordPress SEO](http://wordpress.org/extend/plugins/wordpress-seo/) plugin has this side effect as this plugin allows you to enable/disable author archive pages. Thankfully, the SEO plugin has a setting called *Disable Author Archives* that, if disabled, allows WP Biographia to successfully link to an author's archive page.

= The Biography Box is showing up in places it shouldn't; such as the footer or within widgets in the sidebar; why is this happening and how can I stop this? =

*Why is this happening?* Some themes or plugins use the WordPress `the_content` and/or `the_excerpt` filters as part of a secondary query to select posts or pages to show as content in the theme's sidebars, footers or as part of a plugin's widget. As WP Biographia also uses these filters to add the Biography Box, this means that the Biography Box can sometimes show up unexpectedly, in places where it shouldn't be.

*How can I stop this?* You can lock the plugin to run only in the context of the main WordPress Loop; in other words, WP Biographia will run when selecting posts or pages to be displayed on your site's landing page, archive pages or any other template, but not when run in the context of a secondary query loop in a sidebar or footer. From the Dashboard, navigate to *Settings* / *WP Biographia* / *Admin* / *Content And Excerpt Settings* and ensure that the *Lock Display Of The Biography Box To The Main Loop* setting is checked.

= My site features guest posts; can I override my contributor account's biography for each of my guest authors? =

Yes. Firstly you'll need to enable *post specific overrides* in the plugin. From the Dashboard, navigate to *Settings* / *WP Biographia* / *Admin* / *Biography Box Override Settings* and ensure that the *Enable Post Specific Overrides* setting is checked.

Now edit a post or a page. In the Biography Box Post Options meta-box (for posts), or the Biography Box Page Options meta-box (for pages), you'll find that four new settings are now visible.

*   *Override Biography Text For This Post* - checking this setting will show a text box, pre-populated with the currently logged in user's biography, which can be overriden. If you want to revert to the currently logged in user's biography, clicking on the *Reload Default Profile Biography* will reset your changes.
*   *Override Biography Title For This Post* - checking this setting will show a text box that can be used to override the default Biography Box title.
*   *Suppress Avatar For This Post* - checking this setting will stop the post or page's author's avatar from being displayed as part of the Biography Box.
*   *Suppress Contact Links For This Post* - checking this setting will stop the contact links being displayed as part of the Biography Box.

All of the above settings are post or page specific; in other words they will only override the post or page that is currently being edited.

The thinking behind these settings is that if you have guest posts, you'll probably have one or more accounts with a *contributor* role. That account's biography text, avatar or contact links won't make much sense in the context of a guest post, so you can override them or suppress them in a way which does not impact the Biography Boxes produced for other users on your site.

= WP Biographia isn't available in my language; can I submit a translation? =

WordPress and this plugin use the gettext tools to support internationalisation. The source file containing each string that needs to be translated ships with the plugin in `wp-biographia/lang/src/wp-biographia.po`. See the [I18n for WordPress Developers](http://codex.wordpress.org/I18n_for_WordPress_Developers) page for more information or get in touch for help and hand-holding.

= This plugin looks very much like the WP About Author; what's the connection? =

Version 1 of WP Biographia was inspired by and based on the [WP About Author](http://wordpress.org/extend/plugins/wp-about-author/) plugin by [Jon Bishop](http://www.jonbishop.com/). Thanks and kudos must go to Jon for writing a well structured, working WordPress plugin released under a software license that enables other plugins such as this one to be written or derived in the first place. Jon's written [other WordPress plugins](http://profiles.wordpress.org/users/JonBishop/) as well; you should take a look.

= I want to amend/hack/augment this plugin; can I do the same? =

Totally; like the original plugin by Jon, this plugin is licensed under the GNU General Public License v2 (GPLV2). See http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt for the full license terms.

= Where does the name WP Biographia come from? =

WP Biographia is named after the etymology of the modern English word biography. The word first appeared in the 1680s, probably from the latin biographia which itself derived from the Greek bio, meaning "life" and graphia, meaning "record" or "account" which derived from graphein, "to write".

== Screenshots ==

1. Settings and Options: Display Tab
1. Settings and Options: Admin Tab - New User Settings
1. Settings and Options: Admin Tab - User Profile Settings
1. Settings and Options: Admin Tab - Content and Excerpt Settings
1. Settings and Options: Admin Tab - Biography Box Override Settings
1. Settings and Options: Exclusions Tab - Post, Page and Custom Post Type Exclusion and Category Exclusion Settings
1. Settings and Options: Exclusions Tab - User Hiding Settings
1. Settings and Options: Style Tab
1. Settings and Options: Content Tab
1. Settings and Options: Content Tab, continued
1. Settings and Options: Defaults Tab
1. Settings and Options: Colophon Tab - Colophon
1. Settings and Options: Colophon Tab - Plugin Configuration Settings
1. Post Install and Upgrade: What's New Pointer
1. Plugin Tour Pointer: Display Tab
1. Plugin Tour Pointer: Admin Tab
1. Plugin Tour Pointer: Exclusions Tab
1. Plugin Tour Pointer: Style Tab
1. Plugin Tour Pointer: Content Tab
1. Plugin Tour Pointer: Defaults Tab
1. Plugin Tour Pointer: Colophon Tab
1. Settings and Options: Contact link verification in a user's profile
1. Edit Post: Biography Box Post Options meta-box
1. Sample Biography Box; Contact links shown as text
1. Sample Biography Box; Contact links shown as icons
1. Widget Settings and Options
1. Sample Widget Output

== Changelog ==

The current version is 3.3.1 (2012.12.18)

= 3.3.1 =
* Released 2012.12.18
* Fixed: Ensure the non-minified versions of the plugin's CSS and JS files are enqueued if `WP_DEBUG` or `WPBIOGRAPHIA_DEBUG` are defined and set to `true`.
* Other: Updated and tested to ensure compatibility with WordPress v3.5 "Elvin Jones".

= 3.3 =
* Released 2012.10.25
* Added: `wp_biographia_content_title` filter; allow the Biography Boxes's title to be changed.
* Added: Support for sites using the Simple Local Avatars plugin. If installed/active use the `simple_local_avatars` filter to fixup the avatar's CSS; hook into both `simple_local_avatars` and `get_avatars` to support site with mixed Gravatars and locally hosted avatars.
* Added: Support for the shortcode's `order` attribute; allow custom sort orders when in wildcard mode.
* Added: Support for post specific Biography Box overrides.
* Added: Two new template tags: `wpb_get_biography_box` and `wpb_the_biography_box`.
* Added: Multiple, comma separated, roles can now be specified for the shortcode and template tags.
* Added: Support for enabling/disabling contact links in the user's profile, in the *Admin* and *Display* tabs and when rendering the Biography Box.
* Added: 5px spacing between contact link icons to support custom icon sets with no border.
* Added: The contact links in a user's profile are now validated to check that they are valid URLs; an admin error message is now displayed if a link is deemed to be invalid.
* Added: Support for displaying the full or excerpt biography text.
* Added: The display of the Biography Box can now be locked to the main WordPress Loop to prevent it being displayed in the sidebar or when `the_content` or `the_excerpt` are used as part of themes or other plugins.
* Added: The colour of the Biography Box border can now be selected from the plugin's *Style* tab.
* Added: `wp_biographia_link_item` filter; allow the constituent elements of each contact link to be overriden.
* Added: The visibility of all user profile Biography Options (biography excerpt, hide on posts, hide on pages) are now controlled by the Admin User Profile Settings.
* Fixed: The dismissed pointers flag is now removed for each user when the plugin is uninstalled.
* Fixed: The Biography Box is now styled correctly when no avatar image is present.
* Fixed: Shortcode and template tag roles are now validated against `$wp_roles` and not against a hard-coded roles list.
* Fixed: Bug which hid the *Display On Individual Pages* setting on the *Display* tab when *Display On All Post Archives* was checked.
* Fixed: The contact links in the Biography Box now align with the left hand edge of the biography text.
* Other: Moved all support source files into the `includes` directory.
* Other: Ensure plugin source files are being invoked within the context of the plugin itself, otherwise die.
* Other: The non-minified versions of the plugin's CSS and JS files and now enqueued if `WP_DEBUG` is set to `true` or if `WPBIOGRAPHIA_DEBUG` is defined.
* Other: Cleaned up wording for the biography section of a user's profile; repurposed the *shorter biography* text box to be the *biography excerpt* for use by the shortcode, template tags, widget and biography selection settings.

= 3.2.1 =
* Released 2012.07.31
* Added: Stopped other themes and/or plugins overriding the size of the contact links icons via the !important CSS specifier.
* Fixed: Fixed bug that caused the Biography Box to be duplicated in some RSS feeds.

= 3.2 =
* Released 2012.07.23
* Added: Support for synchronising the use of `wpautop` via the `the_content` and `the_excerpt` filters to ensure these filters fire before the Biography Box is produced when the plugin's filter priority is less than the default filter priority to avoid formatting issues for contact links.
* Added: Support for WordPress Pointers to display "what's new" information post install or upgrade and to provide a "guided tour" of the plugin's settings and options.
* Added: Support for displaying the Biography Box as a widget.
* Added: Support for a shorter biography to the user's profile to be used in conjunction with the Biography Box widget.
* Added: Support to display the Biography Box for all types of archive page; author, category, date and tag.
* Added: Custom meta boxes to the post/page/custom-post creation/editing screens to hide the Biography Box, making it easier to define the Admin screen's Exclusion settings.
* Added: Support for the shortcode's `user` attribute; deprecating support for the `author` attribute.
* Added: Increased the width of text and select boxes for the Admin and Exclusion admin tabs to allow for longer category names and longer lists of post IDs to be displayed.
* Added: Wrap the plugin's avatars (if present) in plugin specific CSS code to prevent theme specific CSS bleeding into the Biography Box.
* Fixed: The layout of the Biography Box for feeds now ignore displaying contact links as icons and formats them as plain text.
* Fixed: Example use of the `wp_biographia_feed` filter in `readme.txt`.
* Fixed: Bug where the `wp_biographia_feed` filter was never called in the context of a feed.
* Fixed: Formatting of HTML for the Biography Box post/page hiding options in the user's profile.
* Fixed: Use the term "hide" consistently across the plugin and documentation; previous versions used "hide" and "suppress" interchangeably.
* Fixed: Use the term "user" consistently across the plugin and documentation; previous versions used "author" and "user" interchangeably.
* Fixed: Bug where the last page of a multiply paged post was not correctly detected, resulting in the Biography Box being displayed for all pages.

= 3.1 =
* Released 2012.05.03
* Added: Support for shortcode `role` attribute to further filter display of users when the shortcode is used in *wildcard* mode.
* Added: The *Colophon* tab in the admin settings screen now displays a dump of the plugin's setting and options without the need to extract that information from the database via MySQL or phpMyAdmin.
* Added: New *Admin* tab in the admin settings screen. This allows a suitably permissioned administrator to hide the Biography Box settings from a user's profile according to the user's role and to automatically hide display of the Biography Box for newly created users according to that user's role.
* Added: Introductory help text to each post-box in each tab in the admin settings screen.
* Added: The filter priorities for both `the_content` and `the_excerpt` can now be individually defined to cope with priority clashes with other themes and plugins which use these filters.
* Fixed: Bug where the `wp_biographia_category_exclusions` setting was not defined in the database upon plugin upgrade, causing an *undefined index* notice message when the PHP error reporting level is set to `E_NOTICE`.
* Fixed: Bug in settings initialisation; post_exclusions, global_post_exclusions and page_exclusions are now pre-defined and initialised correctly on both install and upgrade.

= 3.0.1 =
* Released 2012.04.20
* Fixed: Bug in plugin initialisation that incorrectly named the Vimeo content display option.
* Fixed: Bug that caused a post's user not to be refreshed in the front page and archive pages.
* Fixed: Bug that caused a post's user to be determined as the user of the enclosing page where a custom Loop is being used.

= 3.0 =
* Released 2012.04.11
* Summary: A substantial rewrite of the plugin's structure with a reworked tabbed admin interface and substantial customisation options via the WordPress filter mechanism.
* Added: Filter wp_biographia_default_settings
* Added: Filter wp_biographia_contact_info
* Added: Filter wp_biographia_link_items
* Added: Filter wp_biographia_pre
* Added: Filter wp_biographia_shortcode
* Added: Filter wp_biographia_links
* Added: Filter wp_biographia_feed
* Added: Filter wp_biographia_biography_box
* Added: Support for the enclosing form of the wp_biographia shortcode in addition to the self-closing form.
* Added: Support for resetting the plugin's settings/options to their initial default values from within the admin screen.
* Added: Support for hiding display of the Biography Box from posts, archives and the front page by category.
* Added: Tabbed settings/options in the admin screen.
* Fixed: Bug that caused an empty contact link to be displayed when an user's profile has an empty corresponding contact field.
* Fixed: CSS bug that prevented WP Touch from working in non-restricted mode.
* Fixed: Bug that caused extended contact links in an user's profile to be persisted after plugin uninstallation.

= 2.4.4 =
* Released 2012.02.22
* Fixed bug where Vimeo contact link setting was not persisted across settings changes.
* Fix bug where "More Posts" link linked to the current page URL.
* Minor CSS tweak.

= 2.4.3 =
* Released 2012.02.17
* Fixed bug where page exclusion settings were not persisted to the back-end database configuration settings.

= 2.4.2 =
* Released 2012.02.16
* Correct version number in plugin header.

= 2.4.1 =
* Released 2012.02.16
* Fixed regression bug in v2.4 where a contact link items displayed as an empty link if enabled in WP Biographia but if the corresponding link in the user's profile was empty.
* Fixed regression bug in v2.4 where the user profile Biography Box settings text was not properly displayed.
* Tweak v2.4 CSS to clear up styling issues and to align list item styling with best practice.

= 2.4 =
* Released 2012.02.16
* Add internationalisation support; add Spanish and Turkish language files.
* Add configuration setting to control the user's name in the Biography Box as a link to "More Posts By This User".
* Add support for displaying the user's contact links as icons as well as plain text links.
* Add support for using an alternate link icon set.

= 2.3 =
* Released 2012.01.26
* Hide the display of the "More Posts" link in the Biography Box (if configured) if the user has no posts.
* Add `author`, `prefix` and `name` short code attribute support.
* Add support for global (across single, archive and front page templates) post exclusions in built-in post types and custom post types.
* Tightened wording in admin screen around post exclusions.

= 2.2 =
* Released 2012.01.17
* Add enhanced short code support (`raw` and `configured` modes)
* Add support for displaying the Biography Box on archive pages that use excerpts
* Enhance contact information and Biography Box links to support Delicious, Flickr, Picasa, Vimeo, YouTube and Reddit
* Fixed bug that caused the Biography Box to be displayed for every page of a multiple page post
* Fixed bugs in avatar image size handling; non-default avatar image size was not persisted across settings changes; avatar image container div was not resized to new non-default avatar image size
* Migrate use of wp_print_styles to wp_enqueue_scripts; see (http://wpdevel.wordpress.com/2011/12/12/use-wp_enqueue_scripts-not-wp_print_styles-to-enqueue-scripts-and-styles-for-the-frontend/)
* Made terminology and control ordering for custom post types consistent in admin pages

= 2.1.1 =
* Released 2011.12.21
* Fixed bug in per user hiding due to debug code being left in the release

= 2.1 =
* Released 2011.12.20
* Add ability to hide the Biography Box from being displayed on posts, on pages and on posts and pages on a per user basis
* Add settings link to Settings / WP Biographia admin page from the plugin's entry on the Dashboard / Plugins page
* Add checks for avatar display in the Biography Box being requested with avatar support not enabled in the Settings / Discussions admin page
* Add Help & Support sidebar box to Settings / WP Biographia admin page
* Handle upgrades to configuration settings gracefully; fixed bug that didn't persist unused/unchanged configuration settings
* Cleaned up the wording for the Settings / WP Biographia admin page and made terminology consistent across all configurable options
* Tweaked admin CSS to introduce padding between the settings container and sidebar container that changed in WordPress 3.3

= 2.0 =
* Released 2011.11.18
* Added the ability to set image size
* Added a simple shortcode
* Added Custom Post Types support with the ability to exclude based on post IDs
* Added ability to set the bio at the top or the bottom
* Added a filter to short circuit for further customization
* Added ability to include the post user's email link
* Refactored plugin file locations in line with WordPress plugin development recommendations
* Fixed CSS issue for gravatar

= 1.0 =
* Released 2011.08.03
* First version of WP Biographia released

== Upgrade Notice ==

= 3.3.1 =
This version ensures the minified versions of the plugin's CSS/JS files are loaded if in a production environment and supports WordPress v3.5.

= 3.3 =
This version fixes 5 bugs and adds 13 new features including new filters, template tags and per post overrides to support guest authors.

= 3.2.1 =
This version fixes an issue for RSS feeds where the Biography Box could be duplicated and locks the size of the contact link icons, if used.

= 3.2 =
This version adds support for the Biography Box as a widget and to select display of the Biography Box for all archive types (author, category, date), plus bug fixes.

= 3.1 =
This version adds a new *Admin* tab to the settings screen, the ability to set the content and excerpt filter priorities and support for the shortcode `role` attribute This is the 13th version of WP Biographia.

= 3.0.1 =
This version fixes several bugs that affected the correct user being associated with the Biography Box on front/archive pages and when called from within a custom Loop. This is 12th version of WP Biographia.

= 3.0 =
This is the 11th version of WP Biographia and is a major rewrite of the plugin's structure and functionality to use a PHP class. This version fixes several bugs as well as adding support for additional filters and a restructure of the admin settings/options screen to use a tabbed interface.

= 2.4.4 =
This is the 10th version of WP Biographia and is a bug fix release; fixed bugs in persisting Vimeo contact links settings, in "More Posts Link" incorrectly linking to the current page/post URL and minor CSS tweaks.

= 2.4.3 =
This is the 9th version of WP Biographia and is a bug fix release; fixed bug where page exclusion settings were not persisted to the back-end database configuration settings.

= 2.4.2 =
This is the 8th version of WP Biographia and is a bug fix release, clearing up several regression bugs that appeared in v2.4.

= 2.4.1 =
This is the 8th version of WP Biographia and is a bug fix release, clearing up several regression bugs that appeared in v2.4.

= 2.4 =
This is the 7th version of WP Biographia; adds internationalisation support plus Spanish and Turkish translations; adds support for displaying the user's contact links as icons and for using an alternate icon set.

= 2.3 =
This is the 6th version of WP Biographia; adds support for global post exclusions, enhanced shortcode options and hiding of the "More Posts" link if the user has no posts.

= 2.2 =
* This is the fifth version of WP Biographia; adds support for enhanced shortcode usage, excerpt support on archive pages and additional social media profiles and links as well as several bug fixes.

= 2.1.1 =
* This is the fourth version of WP Biographia which fixes a fatal bug in v2.1; please upgrade to this version and skip v2.0.

= 2.1 =
* This is the third version of WP Biographia; adds support for per user hiding of displaying the Biography Box on posts, on pages and on posts and pages, plus bug fixes and minor UI changes.

= 2.0 =
* This is the second version of WP Biographia and incorporates new features and bug fixes as well as some under-the-hood plumbing changes; in particular the plugin file locations have changed to reflect WordPress plugin development recommendations

= 1.0 =
* This is the first version of WP Biographia

== Shortcode Support And Usage ==

WP Biographia supports a single shortcode, `[wp_biographia]`. Adding this shortcode to the content of a post or page or into a theme template as content, expands the shortcode and replaces it with a Biography Box.

The shortcode also supports multiple *attributes* which allow you to customise the way in which the shortcode is expanded into the Biography Box:

* the `mode` attribute
* the `user` attribute
* the `author` attribute *(deprecated)*
* the `role` attribute
* the `order` attribute
* the `prefix` attribute
* the `name` attribute
* the `type` attribute

= The "mode" Attribute =

In `raw` mode, which is the default (specified as `[wp_biographia mode="raw"]` or simply as `[wp_biographia]`), the plugin inserts the Biography Box in *you've asked for it, you've got it* mode.

Or to put it another way, the plugin will honour the settings that you specify under *Dashboard / Settings/ WP Biographia* for *Biography Box Style Settings* and for *Biography Box Content Settings* but will ignore the *Biography Box Display Settings* and *Biography Box Per User Settings*.

In `configured` mode, specified as `[wp_biographia mode="configured"]`, the plugin inserts the Biography Box and will honour all the settings under *Dashboard / Settings / WP Biographia* with the exception of *Display On Front Page*, *Display On Individual Post*, *Display On Post Archives* and *Display On Individual Pages*, as well as their equivalents for any custom post types you may have created.

The thinking behind this is that you probably want to honour post or page exclusions and per user exclusions, but by using the shortcode in your posts, you want to be in control of how and where the Biography Box is displayed.

= The "user" Attribute =

If the `user` attribute is omitted, which is the default, the shortcode assumes it's being used within the [WordPress Loop](http://codex.wordpress.org/The_Loop) and will display the Biography Box once for the current post's, page's or custom post type's user.

Specifying a user's login name as the `user` attribute overrides this behaviour and allows multi-user sites to use the plugin to create a *contributors* page, where you use the shortcode as `[wp_biographia user="login-name"]` once for each of your site's users that you want to appear, replacing `"login-name"` with a valid login name for one of your users.

You call also use the `user` attribute in *wildcard* mode, specifying the user's login name as `*` as `[wp_biographia user="*"]`; this will then loop over all of the users that have logins on your site, displaying the Biography Box once for each user, ordered alphabetically by login name.

Specifying an invalid login name (`[wp_biographia user="idontexist"]`) will result in no Biography Box being displayed. Specifying an empty login name (`[wp_biographia user=""]`) will cause the `user` attribute to be ignored and may result in undefined behaviour, such as a partially populated Biography Box being displayed as the shortcode is being used outside of the Loop and thus no user information is made available to the plugin by WordPress.

= The "author" Attribute =

The `author` attribute is now deprecated in favour of the `user` attribute introduced in v3.2.0 of the plugin. If the `author` attribute is used and no `user` attribute is present, the `author` attribute will act in the same way as the `user` attribute, described above. If both the `user` and `author` attributes are present, the `author` attribute will be ignored in favour of the `user` attribute.

In other words, if you use the shortcode and supply both the `author` and `user` attributes, such as `[wp_biographia author="foo" user="bar"]`, the Biography Box will be displayed for the user `bar`, not the user `foo`. Likewise, `[wp_biographia user="*" author="foo"]` will display the Biography Box in *wildcard* mode, not for the user `foo`.

Support for the `author` attribute will be likely be removed in a future release of the plugin to avoid confusion over the `author` and `user` attributes.

= The "role" Attribute =

Valid only when used in conjunction with the `user` attribute in *wildcard* mode, the `role` attribute allows you to filter the users that have user accounts on your blog according to their [WordPress Role](http://codex.wordpress.org/Roles_and_Capabilities). The `role` attribute takes one or more comma separated arguments which define the WordPress role(s); at the time of writing, these can be one of:

* `administrator`
* `editor`
* `author`
* `contributor`
* `subscriber`

For example, if you want to display the Biography Box for all users of your blog who have a role of `author` you can use the `role` attribute plus the `author` attribute in *wildcard* mode to do this, along the lines of `[wp_biographia user="*" role="author"]`. If you want to display the Biography Box for all users with a role of `author` or `contributor`, you can specify both roles, along the lines of `[wp_biographia user="*" role="author,contributor"]`.

Specifying an invalid role (`[wp_biographia user="*" role="foo"])` will result in no Biography Box being displayed. Specifying the `role` attribute without the `user` attribute in *wildcard* mode will have no effect.

= The "order" Attribute =

Valid only when used in conjunction with the `user` attribute in *wildcard* mode, the `order` attribute allows you to specify the display order for the users that have user accounts on your blog. The `order` attribute takes a single argument which defines the sorting order; at the time of writing, this can be one of:

* `account-name` *(the default)*
* `first-name`
* `last-name`
* `nickname`
* `display-name`
* `login-id`

For example, if you want to display the Biography Box for all users of your blog ordered according to their last name you can use the `order` attribute plus the `author` attribute in *wildcard* mode to do this, along the lines of `[wp_biographia user="*" order="last-name"]`.

Specifying an invalid role (`[wp_biographia user="*" order="foo"])` will result in the default account name sort order being used. Specifying the `order` attribute without the `user` attribute in *wildcard* mode will have no effect.

It's important to note that the in order to successfully sort by `first-name` or by `last-name`, the *First Name* and/or *Last Name* fields must be populated in each user's Profile; by default, these fields are not populated during the creation of a user's WordPress account. The sorting of the user accounts when the `order` attribute is specified uses [*natural order string comparison*](http://sourcefrog.net/projects/natsort/), which means than an empty *First Name* or *Last Name* will appear **before** a *First Name* or *Last Name* which actually contains a value. If your WordPress site doesn't have these name fields populated in your user's Profiles, you may not see the sorting results you expect.

= The "prefix" Attribute =

If the `prefix` attribute is omitted, which is the default, the Biography Box will be displayed with *Biography Prefix* text configured in *Settings/ WP Biographuia / Biography Box Content Settings* before the user's name. This can be overridden by using the `prefix` attribute, along the lines of `[wp_biographia prefix="All About"]`.

= The "name" Attribute =

If the `name` attribute is omitted, which is the default, the Biography Box will be displayed with the user's name as configured by *User's Name* in *Settings / WP Biographia /Biography Box Content Settings*. This can be overriden by supplying one of the following for the `name` attribute's argument:

* `account-name`
* `first-last-name`
* `nickname`
* `display-name`
* `none`

= The "type" Attribute =

If the `type` attribute is omitted, which is the default, the Biography Box will be displayed with the user's full biography text, taken from the *Biographical Info* field in the user's profile. If the `type` attribute is specified with a value of `full`, this is equivalent to the default behaviour. If the `type` attribute is specified with a value of `excerpt`, the *Biographical Excerpt* field in the user's profile will be used instead, providing the user has filled out this field in their profile. Specifying an invalid `type` attribute value (`[wp_biographia type="foo"]`) will result in the default behaviour of the full biography being used.

== Filter Support And Usage ==

WP Biographia supports multiple filters, which are described in more detail below. The plugin's filters allow you to:

* change the default set of installation settings and options at plugin activation time
* modify and/or enhance the set of contact information fields the plugin adds to the user's profile
* modify and/or enhance the contact links that are added to the Biography Box by the plugin
* modify the position of the Biography Box to before or after the post content returned by `the_content()` and/or `the_excerpt()`
* hide the display of the Biography Box entirely under user-defined circumstances
* modify and/or enhance the Biography Box that is produced by the `[wp_biographia]` shortcode
* modify and/or enhance the format and content of the contact links that are added to the Biography Box by the plugin
* modify and/or enhance the Biography Box that is produced for an RSS feed
* modify and/or enhance the entirety of the Biography Box

= wp_biographia_default_settings =

Applied to the default set of plugin settings and options. Note that this filter is called once, upon plugin activation, when there are no WP Biographia settings/options existing in the database.

*Example:* Add the date and time that the plugin was first activated

`add_filter ('wp_biographia_default_settings', 'add_activation_timestamp');

function add_activation_timestamp ($options) {
	// options = array (option name => option value)
	$options['plugin_activation_timestamp'] = date (DATE_ATOM);
	
	return $options;
}`

= wp_biographia_contact_info =

Applied to the default set of contact information fields that are added to an user's profile by the plugin. Note that in order to add and display a new contact link to the Biography Box, the contact link must be added to the value returned by the `wp_biographia_link_items` filter as well as the value returned by this filter.

*Example:* Add Pinterest as a supported contact information field

`add_filter ('wp_biographia_contact_info', 'add_pinterest_support');

function add_pinterest_support ($contacts) {
	// contacts = array (field => array (field => field-name, contactmethod => description))
	$contacts['pinterest'] = array (
		'field' => 'pinterest',
		'contactmethod' => __('Pinterest')
	);
	
	return $contacts;
}`

= wp_biographia_link_items =

Applied to the default set of contact links that are added to the Biography Box by the plugin. Note that in order to add and display a new contact link, the contact information field must be added to the value returned by the `wp_biographia_contact_info` filter as well as the value returned by this filter. Note that `$icon_dir_url` will by default contain the URL of the images directory within the plugin directory, which will look something like `/wp-content/plugins/wp-biographia/images/` (the trailing slash is important). If an alternate icon directory has been specified in the plugin's settings and options, then `$icon_dir_url` will contain this alternate, configured, directory URL. If the icon you want to add for a new contact link doesn't reside in the directory URL mentioned previously, you'll need to set `$icon_dir_url` to point to your own custom location.

*Example:* Add Pinterest as a supported contact link in the Biography Box

`add_filter ('wp_biographia_link_items', 'add_pinterest_link', 2);

function add_pinterest_link ($links, $icon_dir_url) {
	// links = array (field => array (link_title => title, link_text => text, link_icon => URL)
	$links['pinterest'] = array (
		'link_title' => __('Pinterest'),
		'link_text' => __('Pinterest'),
		'link_icon' => $icon_dir_url . 'pinterest.png'
		);

		return $links;
}`

= wp_biographia_pattern =

Applied to the format string used to position the Biography Box before the post content or after the post content that is returned by `the_content()` and/or `the_excerpt()`.

*Example:* Insert a header between post content and Biography Box

`add_filter ('wp_biographia_pattern', 'insert_biography_header');

function insert_biography_header ($pattern) {
	return '%1$s<p class="biography-header">About The User</p>%2$s';
}`

= wp_biographia_pre =

Allows display of the Biography Box to be hidden under user-defined circumstances. This only affects the display of the Biography Box that is configured via the plugin's admin screen or via the shortcode in configured mode.

*Example:* Hide the Biography Box

`add_filter ('wp_biographia_pre', 'hide_biography_box');

function hide_biography_box ($flag) {
	return true;
}`

= wp_biographia_shortcode =

Applied to the current instance of the Biography Box that is produced via the `[wp_biographia]` shortcode.

*Example:* Apply shortcode specific CSS to the Biography Box

`add_filter ('wp_biographia_links', 'add_shortcode_css', 10, 2);

function add_shortcode_css ($content, $params) {
	// params = array (mode => shortcode-mode, user => author-id, prefix => prefix-string,
						name => name-option)

	return '<div class="custom-shortcode-css">' . $content . '</div>';
}`

= wp_biographia_content_title =

Applied to the title of the Biography Box.

*Example:* Override the name prefix for all uses of the Biography Box.

`add_filter ('wp_biographia_content_title', 'override_name_prefix', 10, 3);
function override_name_prefix ($content, $name_prefix, $formatted_name) {
	return 'This is ' . $formatted_name;
}`

= wp_biographia_links =

Applied to the formatted set of contact links for the current instance of the Biography Box.

*Example:* Replace the default text link separator character (the pipe symbol "|") with a dash ("-").

`add_filter ('wp_biographia_links', 'replace_link_separator', 10, 3);

function replace_link_separator ($content, $links, $params) {
	// links = array (link-item)
	// params = array (glue => separator-string, class => link-item-css-class-name,
	//					prefix => links-prefix-html, postfix => links-postfix-html)
	
	return str_replace ($params['glue'], ' - ', $content);
}`

*Example:* Wrap the formatted content links in an additional HTML div.

`add_filter ('wp_biographia_links', 'wrap_links', 10, 3);

function wrap_links ($content, $links, $params) {
	// links = array (link-item)
	// params = array (glue => separator-string, class => link-item-css-class-name,
	//					prefix => links-prefix-html, postfix => links-postfix-html)
	
	$new_prefix = '<div class="custom-link-class">' . $params['prefix'];
	$new_postfix = $params['postfix'] . '</div>';
	
	return $new_prefix . implode ($params['glue'], $links) . $new_postfix;
}`

= wp_biographia_link_item =

Applied to each active contact link, in the order in which they are processed by the plugin.

*Example:* Force all links that point to the current site to open in a new window.

`add_filter ('wp_biographia_link_item', 'filter_link_item', 10, 2);

function filter_link_item ($content, $params) {
	// $params = array (
	//		'type' => 'link type (icon|text)',
	//		'format' => 'link format string',
	//		'meta' => 'additional anchor attributes',
	//		'title' => 'link title',
	//		'url' => 'link URL',
	//		'body' => 'link body text',
	//		'link-class' => 'link CSS class name',
	//		'item-class' => 'link item CSS class name (icons only)'
	//	);
	
	$site_url = site_url ();
	$pos = strpos ($params['url'], $site_url);
	if ($pos !== false) {
		$params['meta'] = 'target="_blank"';
	}
	
	if ($params['type'] === 'icon') {
		$content = sprintf ($params['format'], $params['url'], $params['meta'], $params['title'], $params['link-class'], $params['body'], $params['item-class']);
	}
	
	else {
		$content = sprintf ($params['format'], $params['url'], $params['meta'], $params['title'], $params['link-class'], $params['body']);
	}

	return $content;
}`

= wp_biographia_feed =

Applied to the current instance of the Biography Box that is produced via the site's RSS feed.

*Example:* Apply RSS feed specific CSS to the Biography Box

`add_filter ('wp_biographia_feed', 'add_feed_css');

function add_feed_css ($content) {
	return '<div class="custom-feed-css">' . $content . '</div>';
}`


= wp_biographia_biography_box =

Applied to the entire content of the current instance of the Biography Box.

*Example:* Remove all WP Biographia CSS classes commencing `wp-biographia-` and replace them with custom CSS classes that adhere to the plugin's CSS class naming convention.

`add_filter ('wp_biographia_biography_box', 'replace_css_classes', 10, 2);

function replace_css_classes ($biography, $items) {
	$new_content = array ();
	
	foreach ($items as $item) {
		$new_content[] = str_replace ('wp-biographia-', 'custom-', $item);
	}
	
	return implode ('', $new_content);
}`

== Template Tags ==

WP Biographia supports two [template tags](https://codex.wordpress.org/Template_Tags) that can be used in your theme's template files. These are described in more detail below; for a full description of the use of each tag's argument, see the *Shortcode Support And Usage* section. The plugin's tags allow you to:

* produce the Biography Box and assign the HTML for the Biography Box to a string.
* produce the Biography Box and echo the results immediately.

= wpb_get_biography_box =

*Description:* Retrieves the Biography Box. This template tags renders the Biography Box and returns it to the caller as a string. To display the Biography Box immediately, use the `wpb_the_biography_box` template tag.

*Usage:*

`<?php $biography_box = wpb_get_biography_box ($mode, $user, $prefix, $name, $role, $type, $order); ?>`

*Parameters:*

*   `$mode` - (string) (*optional*) Override the Biography Box mode (`raw`|`configured`). Default: `raw`.
*   `$user` - (string) (*optional*) Override the source user (`login-name`|`*`). Default: the current user's login name.
*   `$prefix` - (string) (*optional*) Override the Biography Box title prefix. Default: use the plugin's settings.
*   `$name` - (string) (*optional*) Override the selected user's name format (`account-name`|`first-last-name`|`nickname`|`display-name`|`none`). Default: use the plugin's settings.
*   `$role` - (string) (*optional*) Override the selected user's role when used in *wildcard mode*. Specify one or more of the following, as a comma separated list (`administrator`|`editor`|`author`|`contributor`|`subscriber`). Default: none.
*   `$type` - (string) (*optional*) Override the type of the biography text (`full`|`excerpt`). Default: use the plugin's settings.
*   `$order` - (string) (*optional*) Override the sort order when used in *wildcard mode* (`account-name`|`first-name`|`last-name`|`nickname`|`display-name`|`login-id`). Default: `account-name`.

= wpb_the_biography_box =

*Description:* Displays the Biography Box. This template tags renders the Biography Box and displays it immediately. To get the current Biography Box as a string, use the `wpb_get_biography_box` template tag.

*Usage:*

`<?php wpb_the_biography_box ($mode, $user, $prefix, $name, $role, $type, $order); ?>`

*Parameters:*

*   `$mode` - (string) (*optional*) Override the Biography Box mode (`raw`|`configured`). Default: `raw`.
*   `$user` - (string) (*optional*) Override the source user (`login-name`|`*`). Default: the current user's login name.
*   `$prefix` - (string) (*optional*) Override the Biography Box title prefix. Default: use the plugin's settings.
*   `$name` - (string) (*optional*) Override the selected user's name format (`account-name`|`first-last-name`|`nickname`|`display-name`|`none`). Default: use the plugin's settings.
*   `$role` - (string) (*optional*) Override the selected user's role when used in *wildcard mode*. Specify one or more of the following, as a comma separated list (`administrator`|`editor`|`author`|`contributor`|`subscriber`). Default: none.
*   `$type` - (string) (*optional*) Override the type of the biography text (`full`|`excerpt`). Default: use the plugin's settings.
*   `$order` - (string) (*optional*) Override the sort order when used in *wildcard mode* (`account-name`|`first-name`|`last-name`|`nickname`|`display-name`|`login-id`). Default: `account-name`.
