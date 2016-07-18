<?php

/**
 * Get the developer dashboard page
 * Prints directly to output
 * 
 * @return void
 */
function tyk_dev_portal_dashboard() {
	wp_enqueue_script('dashboard');

	$params = array(
		'actionUrl' => esc_url(admin_url('admin-ajax.php')),
		'generalErrorMessage' => __('An error occurred. Please try again.')
		);
	wp_localize_script('dashboard', 'scriptParams', $params);

	include_once TYK_DEV_PORTAL_PLUGIN_PATH . '/templates/api_subscribe_form.php';
}
