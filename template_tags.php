<?php

/**
 * Get the developer dashboard page
 * Prints directly to output
 * 
 * @return void
 */
function tyk_dev_portal_dashboard() {
	$user = new Tyk_Portal_User;
	if ($user->is_logged_in()) {
		include_once TYK_DEV_PORTAL_PLUGIN_PATH . '/templates/api_subscribe_form.php';
	}
	else {
		$login_page = get_page_by_path('log-in');
		printf(__('This page is only available when you are <a href="%1$s">logged in</a>.', Tyk_Dev_Portal::TEXT_DOMAIN), get_permalink($login_page));
	}
}
