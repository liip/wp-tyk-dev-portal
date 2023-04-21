<?php

/**
 * Get the developer dashboard page
 * Returns the html code or an error message when user is not logged in
 * 
 * @return string
 */
function tyk_dev_portal_dashboard() {
	$user = new Tyk_Portal_User;
	if ($user->is_logged_in()) {
		ob_start();
		include_once TYK_DEV_PORTAL_PLUGIN_PATH . '/templates/api_subscribe_form.php';
		return ob_get_clean();
	}
	else {
		$login_page = get_page_by_path('log-in');
		return sprintf(__('This page is only available when you are <a href="%1$s">logged in</a>.', Tyk_Dev_Portal::TEXT_DOMAIN), get_permalink($login_page));
	}
}
