<?php

/**
 * Get the form tag for the api subscription form
 * 
 * @param string $class An optional class to add to the form tag
 * @return string
 */
function tyk_dev_portal_api_subscribe_form($class = '') {
	return printf('<form action="%s" class="%s" method="post" id="tyk-api-subscribe-form">
		<input type="hidden" name="action" value="get_token">',
		esc_url(admin_url('admin-ajax.php')),
		$class
		);
}

/**
 * Get <select> pulldown for api subscription
 *
 * @param string $class An optional class to add to the select tag
 * @return string
 */
function tyk_dev_portal_api_select($class = '') {
	$opts = array();
	foreach (Tyk_API_Manager::available_apis() as $api) {
		$opts[] = sprintf('<option value="%s">%s</option>', 
			$api->id, 
			$api->name
			);
	}
	return printf('<select name="api" id="tyk-api-select" class="%s">%s</select>',
		$class,
		join("\n", $opts)
		);
}