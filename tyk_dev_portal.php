<?php
/**
 * Plugin Name: Tyk Developer Portal
 * Description: Plugin to use your WordPress as a developer portal for Tyk.io
 * Author: Liip <be-dev@liip.ch>
 * Version: 1.0.0
 * Date: 04.07.2016
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

define('TYK_DEV_PORTAL_PLUGIN_PATH', dirname( __FILE__ ));

require_once dirname(__FILE__) . '/portal_user.php';
//require_once dirname(__FILE__) . '/api_manager.php';
require_once dirname(__FILE__) . '/tyk_api.php';

class Tyk_Dev_Portal
{
	/**
	 * Name of this plugin
	 */
	const PLUGIN_NAME = 'Tyk Developer Portal';

	/**
	 * Slug of the dashboard page
	 */
	const DASHBOARD_SLUG = 'dev-dashboard';

	/**
	 * Register any hooks
	 * 
	 * @return void
	 */
	public function register_hooks() {
		register_activation_hook( __FILE__, array($this, 'on_activate') );
	}

	/**
	 * Register any actions
	 * 
	 * @return void
	 */
	public function register_actions() {
		/**
		 * Hook into wordpress "onregister" of a user and register the
	 	 * user as a tyk developer if the user has the "developer" role
		 */
		add_action('user_register', array($this, 'register_user_with_tyk'), 10, 1);
	}

	/**
	 * Register any content filters
	 * 
	 * @return void
	 */
	public function register_filters() {
		add_filter('page_template', array($this, 'dashboard_page_template'));
	}

	/**
	 * Register a user as a developer with Tyk
	 * 
	 * @param integer $user_id
	 * @return void
	 */
	public function register_user_with_tyk($user_id) {
		$user = new Tyk_Portal_User($user_id);
		if ($user->is_developer() && !$user->has_tyk_id()) {
			$user->register_with_tyk();
		}
	}

	/**
	 * Hook that is fired when this plugin is activated
	 * 
	 * @return void
	 */
	public function on_activate() {
		$this->create_developer_role();
		$this->create_dashboard_page();
	}

	/**
	 * Automatically set page template for dashboard page
	 * 
	 * @return string|void
	 */
	public function dashboard_page_template() {
		if (is_page(self::DASHBOARD_SLUG)) {
			$page_template = TYK_DEV_PORTAL_PLUGIN_PATH . '/page_dashboard.php';
			return $page_template;
		}
	}

	/**
	 * Create a role "developer"
	 * 
	 * @return void
	 */
	private function create_developer_role() {
		$result = add_role(self::DEVELOPER_ROLE_NAME, __('Developer'));
		if ($result === false) {
			trigger_error(sprintf('Could not create role "%s" for plugin %s',
				self::DEVELOPER_ROLE_NAME,
				self::PLUGIN_NAME
			));
		}
	}

	/**
	 * Create a page for the developer dashboard
	 * 
	 * @return void
	 */
	private function create_dashboard_page() {
		// @todo check if the page already exists
		$page = array(
			'post_title' => 'Developer Dashboard',
			'post_name' => self::DASHBOARD_SLUG,
			'post_content' => '',
			'post_status' => 'publish',
			'post_type' => 'page',
			'post_author' => 1, // @todo get an admin user id here
		);
		$post_id = wp_insert_post($page);
		// @todo we should probably save the slug of the created page here
	}
}

// setup plugin if not in backend
if (!is_admin()) {
	$plugin = new Tyk_Dev_Portal();
	$plugin->register_hooks();
	$plugin->register_actions();
	$plugin->register_filters();
}

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