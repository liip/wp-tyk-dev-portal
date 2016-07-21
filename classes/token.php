<?php

/**
 * This class represents a key/token request for a Tyk API policy
 */
class Tyk_Token
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
	 * API/policy id
	 * @var string
	 */
	protected $policy;

	/**
	 * Tyk API handler
	 * @var Tyk_API
	 */
	protected $api;

	/**
	 * Setup the class
	 */
	public function __construct(array $token = null) {
		$this->api = new Tyk_API();
		if (!is_null($token)) {
			if (isset($token['token_id']) && isset($token['api_id'])) {
				$this->id = $token['token_id'];
				$this->policy = $token['api_id'];
			}
			else {
				throw new InvalidArgumentException('Invalid token specified');
			}
		}
	}

	/**
	 * Get the key request id (not the actual token)
	 * 
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Set the key request id
	 * You shouldn't use this, this is for testing
	 * 
	 * @return string
	 */
	public function set_id($id) {
		$this->id = $id;
	}

	/**
	 * Get the key/access token
	 * 
	 * @return string
	 */
	public function get_key() {
		return $this->key;
	}

	/**
	 * Get the api policy ID
	 * 
	 * @return string
	 */
	public function get_policy() {
		return $this->policy;
	}

	/**
	 * Make a key request for a tyk api plan/policy
	 *
	 * @param Portal_User $user
	 * @param string $policy
	 * @return string
	 */
	public function request(Tyk_Portal_User $user, $policy) {
		$key = $this->api->post('/portal/requests', array(
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
			$token = $this->api->put('/portal/requests/approve', $this->id);
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