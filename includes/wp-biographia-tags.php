<?php

if (!defined('WPBIOGRAPHIA_INCLUDE_SENTRY')) {
	die ('The way is shut. It was made by those who are dead, and the dead keep it. The way is shut.');
}

if (!function_exists ('wpb_get_biography_box')) {
	function wpb_get_biography_box ($mode='raw', $user=NULL, $prefix=NULL, $name=NULL, $role=NULL, $type='full', $order='account-name') {
		$instance = WP_Biographia::get_instance ();
		$ret = $instance->biography_box ($mode, $user, $prefix, $name, $role, $type, $order);
		$content = $ret['content'];
		return implode ('', $content);
	}	
}

if (!function_exists ('wpb_the_biography_box')) {
	function wpb_the_biography_box ($mode='raw', $user=NULL, $prefix=NULL, $name=NULL, $role=NULL, $type='full', $order='account-name') {
		echo wpb_get_biography_box ($mode, $user, $prefix, $name, $role, $type, $order);
	}
}

?>