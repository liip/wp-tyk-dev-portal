<?php
/**
 * Plugin Name: Tyk Developer Portal
 * Description: Plugin to use your WordPress as a developer portal for Tyk.io
 * Author: Liip <be-dev@liip.ch>
 * Version: 1.0.0
 * Date: 04.07.2016
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

require_once dirname(__FILE__) . '/portal_user.php';
//require_once dirname(__FILE__) . '/api_manager.php';
require_once dirname(__FILE__) . '/tyk_api.php';

class Tyk_Dev_Portal
{
	/**
	 * Name of this plugin
	 * @var string
	 */
	const PLUGIN_NAME = 'Tyk Developer Portal';

	/**
	 * Hook that is fired when this plugin is activated
	 * 
	 * @return void
	 */
	public static function on_activate() {
		self::create_developer_role();
	}

	/**
	 * Create a role "developer"
	 * 
	 * @return void
	 */
	private static function create_developer_role() {
		$role_name = 'developer';
		$result = add_role(
			$role_name,
			__('Developer')
			);
		if ($result === false) {
			trigger_error(sprintf('Could not create role "%s" for plugin %s',
				$role_name,
				self::PLUGIN_NAME
			));
		}
	}
}

register_activation_hook( __FILE__, array('Tyk_Dev_Portal', 'on_activate') );

/**
 * Hook into wordpress "onregister" of a user and register the
 * user as a tyk developer if the user has the "developer" role
 * 
 * @param  integer $user_id
 */
function register_user_with_tyk($user_id) {
	$user = new Portal_User($user_id);
	if ($user->is_developer() && !$user->has_tyk_id()) {
		$user->register_with_tyk();
	}
}
add_action('user_register', 'register_user_with_tyk', 10, 1);


/**
 * Event-hook when "register for api" form is submitted:
 * register the user for a token for the corresponding tyk API
 */
function get_tyk_token() {
	if (isset($_POST['api'])) {
		$apiManager = new API_Manager();
		$user = new Portal_User();
		$apiManager->registerForAPI($user, $_POST['api']);
	}
}
add_action('admin_post_get_token', 'get_tyk_token');
