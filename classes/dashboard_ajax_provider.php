<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 sts=0 foldmethod=marker: */

/**
 * Provide ajax actions for dashboard GUI
 */
class Tyk_Dashboard_Ajax_Provider 
{
	/**
	 * Register all supported ajax actions
	 * 
	 * @return void
	 */
	public function register_actions() {
		// Process ajax actions
		add_action('wp_ajax_get_token', array($this, 'register_for_api'));
		add_action('wp_ajax_get_tokens', array($this, 'get_user_tokens'));
		add_action('wp_ajax_get_available_apis', array($this, 'get_available_policies'));
		add_action('wp_ajax_revoke_token', array($this, 'revoke_token'));
		add_action('wp_ajax_get_token_usage', array($this, 'get_token_usage'));
		add_action('wp_ajax_get_token_quota', array($this, 'get_token_quota'));
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

					// we removed the dot for User Experience,
					// since the User copies the dot with the key and therefore the key is corupted.
					$message = sprintf(
						__('Your token for this API is: %s and we will only show this once. Please save it somewhere now.', Tyk_Dev_Portal::TEXT_DOMAIN), 
				
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
					'key' => $token->get_key(),
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
	 * @return void
	 */
	public function get_user_tokens() {
		try {
			$user = new Tyk_Portal_User();
			wp_send_json_success($user->get_access_tokens());
		}
		catch (Exception $e) {
			wp_send_json_error(sprintf('An error occured: %s', $e->getMessage()));
		}
	}

	/**
	 * Get available policies
	 * These are communicated as APIs to the user because that's what they're interested in
	 * 
	 * @return void
	 */
	public function get_available_policies() {
		try {
			wp_send_json_success(Tyk_API_Manager::available_policies());
		}
		catch (Exception $e) {
			wp_send_json_error(sprintf('An error occured: %s', $e->getMessage()));
		}
	}

	/**
	 * Revoke a user token
	 * 
	 * @return void
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
				if (strpos(strtolower($e->getMessage()), 'not found') === false) {
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
	 * Get usage quota
	 * 
	 * @return void
	 */
	public function get_token_quota() {
		if (isset($_POST['token'])) {
			try {
				$user = new Tyk_Portal_User();
				$token = Tyk_Token::init_from_key(sanitize_text_field($_POST['token']), $user);
				wp_send_json_success($token->get_usage_quota());
			}
			catch (Exception $e) {
				if (strtolower($e->getMessage()) != 'not found') {
					wp_send_json_error($e->getMessage());
				}
				else {
					wp_send_json_error(__('Token could not be found. Please note that you must use the token at least once before you can request the remaining quota.', Tyk_Dev_Portal::TEXT_DOMAIN));
				}
			}
		}
		wp_send_json_error(__('Invalid request'));
	}

	/**
	 * Get token usage
	 * 
	 * @return void
	 */
	public function get_token_usage() {
		if (isset($_GET['token'])) {
			try {
				$from_date = isset($_GET['from'])
					? $_GET['from']
					: null;
				$to_date = isset($_GET['to'])
					? $_GET['to']
					: null;
				$user = new Tyk_Portal_User();
				$token = $user->get_access_token($_GET['token']);
				wp_send_json_success($token->get_usage($from_date, $to_date));
			}
			catch (Exception $e) {
				wp_send_json_error($e->getMessage());
			}
		}
		wp_send_json_error(__('Invalid request'));
	}
}
