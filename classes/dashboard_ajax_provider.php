<?php

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
		add_action('wp_ajax_get_available_apis', array($this, 'get_available_apis'));
		add_action('wp_ajax_revoke_token', array($this, 'revoke_token'));
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
}