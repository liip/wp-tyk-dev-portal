<?php
/**
 * Contains the "plugin manager" class
 * 
 * @package Tyk_Dev_Portal\WordPress
 */

/**
 * Tyk developer portal plugin manager class
 * Handles hooking into WordPress functionality and dispatching to the appropriate place.
 * Let's keep this class as lean as possible and do the heavy lifting in domain-specific classes.
 */
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
	 * This plugins text domain
	 */
	const TEXT_DOMAIN = 'tyk-dev-portal';

	/**
	 * Register any hooks
	 * 
	 * @return void
	 */
	public function register_hooks() {
		// in backend: register activation hook for this plugin
		if (!is_admin()) {
			register_activation_hook(__FILE__, array($this, 'on_activate'));
		}
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

		// Process ajax get_token action
		add_action('wp_ajax_get_token', array($this, 'register_for_api'));
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
	 * Ajax event-hook when "register for api" form is submitted:
	 * Request a user-token for the corresponding tyk API
	 *
	 * @return void Sends output to output buffer
	 */
	public function register_for_api() {
		$response = array();
		// @todo check if this is a valid api
		if (isset($_POST['api'])) {
			try {
				$apiManager = new Tyk_API_Manager();
				$user = new Tyk_Portal_User();
				$key = $apiManager->registerForAPI($user, $_POST['api']);
				// build a nice little message
				$message = sprintf('<p>%s</p><p>%s</p>',
					sprintf(__('Your token for this API is: %s', Tyk_Dev_Portal::TEXT_DOMAIN), $key),
					__('We will only show this once. Please save it somewhere now.', Tyk_Dev_Portal::TEXT_DOMAIN)
					);
				wp_send_json_success(array(
					'message' => $message,
					'key' => $key
					));
			}
			catch (Exception $e) {
				wp_send_json_error($e->getMessage());
			}
		}
		wp_send_json_error('Invalid request');
	}

	/**
	 * Create a role "developer"
	 * 
	 * @return void
	 */
	private function create_developer_role() {
		$result = add_role(self::DEVELOPER_ROLE_NAME, __('Developer', self::TEXT_DOMAIN));
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