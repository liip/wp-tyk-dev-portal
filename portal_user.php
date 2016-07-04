<?php

/**
 * Not an actual user abstraction, but gathers common functionality regarding
 * developer portal users
 */
class Tyk_Portal_User 
{
	/**
	 * The name of the key in wordpress user meta data where tyk user id is stored
	 */
	const META_TYK_USER_ID_KEY = 'tyk_user_id';

	/**
	 * Name of the developer role this plugin creates
	 */
	const DEVELOPER_ROLE_NAME = 'developer';

	/**
	 * The actual wordpress user object
	 * 
	 * @var WP_User
	 */
	private $user;

	/**
	 * Setup portal user
	 * 
	 * @param integer $user_id Defaults to current user's id
	 */
	public function __construct($user_id = null) {
		if (is_null($user_id)) {
			$user_id = get_current_user_id();
		}
		$this->user = get_userdata($user_id);
	}

	/**
	 * Check if current user is logged in and is a developer
	 * 
	 * @return boolean
	 */
	public function is_developer() {
		return $this->user->exists() && in_array(self::DEVELOPER_ROLE_NAME, $this->user->roles);
	}

	/**
	 * Check if we already stored a tyk id for this developer
	 * 
	 * @return boolean
	 */
	public function has_tyk_id() {
		$tyk_user_id = get_user_meta($this->user->ID, self::META_TYK_USER_ID_KEY, true);
		return !empty($tyk_user_id);	
	}

	/**
	 * Set the user's tyk user id
	 * 
	 * @param string $id
	 */
	public function set_tyk_id($id) {
		update_user_meta($this->user->ID, self::META_TYK_USER_ID_KEY, $id);
	}

	/**
	 * Get the user's tyk user id
	 * 
	 * @return string
	 */
	public function get_tyk_id() {
		return get_user_meta($this->user->ID, self::META_TYK_USER_ID_KEY, true);
	}

	/**
	 * Get tyk access token
	 * 
	 * @return array
	 */
	public function register_with_tyk() {
		try {
			$tyk = new Tyk_API();
			$user_id = $tyk->post('/portal/developers', array(
				'email' => $this->user->user_email,
				));
			$this->set_tyk_id($user_id);
		}
		catch (Exception $e) {
			trigger_error(sprintf('Could not register user for API: %s', $e->getMessage()), E_USER_WARNING);
		}
	}
}