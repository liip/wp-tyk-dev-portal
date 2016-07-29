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
	 * Version of this plugin
	 */
	const PLUGIN_VERSION = '1';

	/**
	 * Slug of the dashboard page
	 */
	const DASHBOARD_SLUG = 'dev-dashboard';

	/**
	 * This plugins text domain
	 */
	const TEXT_DOMAIN = 'tyk-dev-portal';

	/**
	 * Name of the role for developers this plugin will create
	 */
	const DEVELOPER_ROLE_NAME = 'developer';

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
		add_action('user_register', array($this, 'register_user_with_tyk'));

		// Process ajax actions
		add_action('wp_ajax_get_token', array($this, 'register_for_api'));
		add_action('wp_ajax_get_tokens', array($this, 'get_user_tokens'));
		add_action('wp_ajax_get_available_apis', array($this, 'get_available_apis'));
		add_action('wp_ajax_revoke_token', array($this, 'revoke_token'));

		add_action('init', array($this, 'register_scripts'));
		add_action('init', array($this, 'register_styles'));
		add_action('wp', array($this, 'enqueue_assets'));
		add_action('wp', array($this, 'environment_is_ready'));
	}

	/**
	 * Make sure environment is ready for our plugin
	 * 
	 * @return void
	 */
	public function environment_is_ready() {
		// make sure we have a tyk user in case registration failed
		$this->register_user_with_tyk();
	}

	/**
	 * Enqueue scripts and styles for the appropriate page
	 *
	 * We do this in 'wp' hook because it's after the theme has setup
	 * and enqueued it's styles so we can react accordingly.
	 * 
	 * @return void
	 */
	public function enqueue_assets() {
		// if this is our dashboard page, enqueue our assets
		if (is_page(self::DASHBOARD_SLUG)) {
			// enqueue and localize our dashboard script
			wp_enqueue_script('tyk-dev-portal-dashboard');
			$params = array(
				'actionUrl' => esc_url(admin_url('admin-ajax.php')),
				);
			wp_localize_script('tyk-dev-portal-dashboard', 'scriptParams', $params);

			// only enqueue our bootstrap styles if the current theme isn't using bootstrap
			if (!wp_style_is('bootstrap', 'enqueued')) {
				wp_enqueue_style('tyk-dev-portal-bootstrap');
			}
		}
	}

	/**
	 * Register javascript files
	 * 
	 * @return void
	 */
	public function register_scripts() {
		// enqueue vue.js
		$vue_file = (WP_DEBUG === true)
			? 'vue.js'
			: 'vue.min.js';
		$vue_ver = (WP_DEBUG === true)
			? time()
			: self::PLUGIN_VERSION;
		wp_register_script('vue', tyk_dev_portal_plugin_url('assets/js/vendor/' . $vue_file), array(), $vue_ver, true);
		
		// enqueue dashboard.js
		$dashboard_ver = (WP_DEBUG === true)
			? time()
			: self::PLUGIN_VERSION;
		wp_register_script('tyk-dev-portal-dashboard', tyk_dev_portal_plugin_url('assets/js/dashboard.js'), array('jquery', 'vue'), $dashboard_ver, true);
	}

	/**
	 * Register styles
	 * 
	 * Registers a minimal bootstrap theme that only contains the stuff we need
	 * Note: this style is only enqueued if the activate theme does not enqueue a bootstrap file
	 * 
	 * @return void
	 */
	public function register_styles() {
		$bootstrap_ver = (WP_DEBUG === true)
			? time()
			: self::PLUGIN_VERSION;
		wp_register_style('tyk-dev-portal-bootstrap', tyk_dev_portal_plugin_url('assets/css/bootstrap.min.css'), null, $bootstrap_ver);
	}

	/**
	 * Register a user as a developer with Tyk
	 * 
	 * @param integer $user_id
	 * @return void
	 */
	public function register_user_with_tyk($user_id = null) {
		$user = new Tyk_Portal_User($user_id);
		if ($user->is_logged_in() && !$user->has_tyk_id()) {
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
				$user = new Tyk_Portal_User();
				$token = new Tyk_Token($user, $_POST['api']);
				$token->request();

				// when keys are approved automatically
				if (TYK_AUTO_APPROVE_KEY_REQUESTS) {
					$token->approve();
					// save the access token information
					$user->save_access_token($_POST['api'], $_POST['token_name'], $token->get_hash());

					$message = sprintf(
						__('Your token for this API is: %s. We will only show this once. Please save it somewhere now.', Tyk_Dev_Portal::TEXT_DOMAIN), 
						$token->get_key()
						);
				}
				// when keys await manual approval
				else {
					$message = sprintf(
						__('Your key request is pending review. You will receive an Email when your request is processed. Your request id is %s. This is not your access token. Please refer to the request ID when contacting us by Email.', 
						Tyk_Dev_Portal::TEXT_DOMAIN),
						$token->get_id()
						);
				}

				wp_send_json_success(array(
					'message' => $message,
					'key' => $key,
					'approved' => TYK_AUTO_APPROVE_KEY_REQUESTS,
					));
			}
			catch (Exception $e) {
				wp_send_json_error($e->getMessage());
			}
		}
		wp_send_json_error(__('Invalid request'));
	}

	/**
	 * Get user tokens
	 * 
	 * @return array
	 */
	public function get_user_tokens() {
		$user = new Tyk_Portal_User();
		wp_send_json_success($user->get_access_tokens());
	}

	/**
	 * Get available APIs
	 * 
	 * @return array
	 */
	public function get_available_apis() {
		wp_send_json_success(Tyk_API_Manager::available_apis());
	}

	/**
	 * Revoke a user token
	 * 
	 * @return array Array of remaining tokens
	 */
	public function revoke_token() {
		if (isset($_POST['token'])) {
			try {
				$user = new Tyk_Portal_User();
				// revoke token on tyk api
				// this will throw an exception if token is invalid or revoking fails
				$token = $user->get_access_token($_POST['token']);
				$token->revoke();
			}
			catch (Exception $e) {
				// treat everything as an error except when tyk can't find the token
				// in which case we'll assume it's gone on their side and delete it on our side as well
				if (strtolower($e->getMessage()) != 'not found') {
					wp_send_json_error($e->getMessage());
				}
			}
			// we can't assume PHP 5.5 otherwise we could use the finally directive

			// delete local storage of hashed token
			$user->delete_access_token($_POST['token']);
			$message = sprintf(__('Your token "%s" was revoked permanently and is invalidated effective immediately.', Tyk_Dev_Portal::TEXT_DOMAIN), $token->get_name());
			wp_send_json_success(array(
				'message' => $message,
				));
		}
		wp_send_json_error(__('Invalid request'));
	}

	/**
	 * Create a role "developer"
	 * 
	 * @return void
	 */
	public function create_developer_role() {
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
	public function create_dashboard_page() {
		// @todo check if the page already exists
		$page = array(
			'post_title' => __('Developer Dashboard', self::TEXT_DOMAIN),
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