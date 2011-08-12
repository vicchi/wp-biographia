<?php

if (defined('WP_UNINSTALL_PLUGIN')) {
	delete_option ('wp_biographia_settings');
}

else {
	exit ();
}

?>
