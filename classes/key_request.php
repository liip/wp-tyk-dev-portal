<?php

/**
 * This class represents a key/token request for a Tyk API policy
 */
class Tyk_Key_Request
{
	/**
	 * Key request ID
	 * @var string
	 */
	protected $id;

	/**
	 * Key/access token
	 * @var string
	 */
	protected $key;

	/**
	 * Tyk API handler
	 * @var Tyk_API
	 */
	protected $api;

	/**
	 * Get the key request id (not the actual token)
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Set the key request id
	 * You shouldn't use this, this is for testing
	 * @return string
	 */
	public function set_id($id) {
		$this->id = $id;
	}

	/**
	 * Get the key/access token
	 * @return string
	 */
	public function get_key() {
		return $this->key;
	}

	/**
	 * Setup the class
	 */
	public function __construct() {
		$this->api = new Tyk_API();
	}

	/**
	 * Make a key request for a tyk api plan/policy
	 *
	 * @param Portal_User $user
	 * @param string $policy
	 * @return string
	 */
	public function send(Tyk_Portal_User $user, $policy) {
		$tyk = new Tyk_API();
		$key = $tyk->post('/portal/requests', array(
			'by_user' => $user->get_tyk_id(),
			'for_plan' => $policy,
			// it's possible to have key requests approved manually
			'approved' => TYK_AUTO_APPROVE_KEY_REQUESTS,
			// this is a bit absurd but tyk api doesn't set this by itself
			'date_created' => date('c'),
			));

		// save key request id
		if (is_string($key)) {
			$this->id = $key;
		}
		else {
			throw new UnexpectedValueException('Received an invalid response for key request');
		}
	}

	/**
	 * Approve a key request
	 * 
	 * Unfortunately, tyk api doesn't support making and approving a key
	 * request in the same request, so this method must be invoked after
	 * issuing {@link this::send()}.
	 *
	 * @throws Exception When we don't get a token bac from API
	 * 
	 * @return string Actual access token
	 */
	public function approve() {
		if (!is_string($this->id) || empty($this->id)) {
			throw new InvalidArgumentException('Invalid key request');
		}

		try {
			$tyk = new Tyk_API();
			$token = $tyk->put('/portal/requests/approve', $this->id);
			if (is_object($token) && isset($token->RawKey)) {
				$this->key = $token->RawKey;
				return $this->key;
			}
			else {
				throw new Exception('Could not register for API');
			}
		}
		catch (Exception $e) {
			throw new UnexpectedValueException($e->getMessage());
		}
	}
}