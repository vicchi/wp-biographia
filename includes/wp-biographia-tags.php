<?php

if (!defined('WPBIOGRAPHIA_INCLUDE_SENTRY')) {
	die ('The way is shut. It was made by those who are dead, and the dead keep it. The way is shut.');
}

if (!function_exists ('wpb_get_biography_box')) {
	/**
	 * Template tag: retrieves the Biography Box. This template tags renders the Biography Box
	 * and returns it to the caller as a string. To display the Biography Box immediately, use
	 * the wpb_the_biography_box template tag. See also the documentation for the shortcode
	 * in the plugin's readme.txt.
	 *
	 * @see wpb_the_biography_box
	 *
	 * @param string mode Optional; override the Biography Box mode (raw|configured)
	 * @param string user Optional; override the source user (login-name|*)
	 * @param string prefix Optional; override the Biography Box title prefix
	 * @param string name Optional; override the user's name format (account-name|first-last-name|nickname|display-name|none)
	 * @param string role Optional; override the selected user's role when used in wildcard mode (administrator|editor|author|contributor|subscriber)
	 * @param string type Optional; override the biography text (full|excerpt)
	 * @param string order Optional; override the sort order when used in wildcard mode (account-name|first-name|last-name|nickname|display-name|login-id)
	 * @return string The formatted HTML of the Biography Box.
	 */
	function wpb_get_biography_box ($mode='raw', $user=NULL, $prefix=NULL, $name=NULL, $role=NULL, $type='full', $order='account-name') {
		$instance = WP_Biographia::get_instance ();
		$ret = $instance->biography_box ($mode, $user, $prefix, $name, $role, $type, $order);
		$content = $ret['content'];
		return implode ('', $content);
	}	
}

if (!function_exists ('wpb_the_biography_box')) {
	/**
	 * Template tag: displays the Biography Box. This template tag renders the Biography Box
	 * and displays it immediately. To get the current Biography Box as a string, use the
	 * wpb_get_biography_box template tag. See also the documentation for the shortcode
	 * in the plugin's readme.txt.
	 *
	 * @see wpb_get_biography_box
	 *
	 * @param string mode Optional; override the Biography Box mode (raw|configured)
	 * @param string user Optional; override the source user (login-name|*)
	 * @param string prefix Optional; override the Biography Box title prefix
	 * @param string name Optional; override the user's name format (account-name|first-last-name|nickname|display-name|none)
	 * @param string role Optional; override the selected user's role when used in wildcard mode (administrator|editor|author|contributor|subscriber)
	 * @param string type Optional; override the type of biography text (full|excerpt)
	 * @param string order Optional; override the sort order when used in wildcard mode (account-name|first-name|last-name|nickname|display-name|login-id)
	 */
	function wpb_the_biography_box ($mode='raw', $user=NULL, $prefix=NULL, $name=NULL, $role=NULL, $type='full', $order='account-name') {
		echo wpb_get_biography_box ($mode, $user, $prefix, $name, $role, $type, $order);
	}
}

?>