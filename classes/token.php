<?php

/**
 * This class represents a key/token request for a Tyk API policy
 *
 * This class is a bit of a mess. It represents a key request and a token.
 * Maybe we should refactor it into two classes that each serve one purpose.
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
	 * This is the unhashed access token we show to user but do not save
	 * @var string
	 */
	protected $key;

	/**
	 * Key/access token hash
	 * This is the hashed access token we save
	 * @var string
	 */
	protected $hash;

	/**
	 * API/policy id
	 * @var string
	 */
	protected $policy;

	/**
	 * Tyk API interaction handler
	 * @var Tyk_API
	 */
	protected $api;

	/**
	 * Tyk Gateway interaction handler
	 * @var Tyk_Gateway
	 */
	protected $gateway;

	/**
	 * Tyk portal user
	 * @var Tyk_Portal_User
	 */
	protected $user;

	/**
	 * Setup the class
	 *
	 * @param Portal_User $user
	 * @param string $policy optional, not required for all actions
	 */
	public function __construct(Tyk_Portal_User $user, $policy = null) {
		$this->api = new Tyk_API;
		$this->gateway = new Tyk_Gateway;
		$this->user = $user;
		if (!is_null($policy)) {
			$this->policy = $policy;
		}
	}

	/**
	 * Set an existing token
	 * 
	 * @param array $token
	 * @param Portal_User $user
	 * @return Tyk_Token
	 */
	public static function init(array $token, Tyk_Portal_User $user) {
		if (isset($token['api_id']) && isset($token['hash'])) {
			$instance = new Tyk_Token($user, $token['api_id']);
			$instance->set_hash($token['hash']);
			$instance->set_name($token['token_name']);
			return $instance;
		}
		else {
			throw new InvalidArgumentException('Invalid token specified');
		}
	}

	/**
	 * Setup token from the key
	 * 
	 * @param string $key
	 * @param Tyk_Portal_User $user
	 * @return Tyk_Token
	 */
	public static function init_from_key($key, Tyk_Portal_User $user) {
		$token = new Tyk_Token($user);
		$token->set_key($key);
		return $token;

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
	 * Set the unhashed key
	 * 
	 * @param string $key
	 */
	public function set_key($key) {
		$this->key = $key;
	}

	/**
	 * Set the hashed token
	 * 
	 * @param string $hash
	 */
	public function set_hash($hash) {
		$this->hash = $hash;
	}

	/**
	 * Get the hashed token
	 * 
	 * @return string
	 */
	public function get_hash() {
		return $this->hash;
	}

	/**
	 * Set the name
	 * 
	 * @param string $name
	 */
	public function set_name($name) {
		$this->name = $name;
	}

	/**
	 * Get the name
	 * Note: the name isn't always set, only when token is setup with self::init()
	 * 
	 * @return string
	 */
	public function get_name() {
		return $this->name;
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
	 * @throws UnexpectedValueException When we get an invalid response from API
	 * 
	 * @return string
	 */
	public function request() {
		$request_id = $this->api->post('/portal/requests', array(
			'by_user' => $this->user->get_tyk_id(),
			'for_plan' => $this->policy,
			// it's possible to have key requests approved manually
			'approved' => TYK_AUTO_APPROVE_KEY_REQUESTS,
			// this is a bit absurd but tyk api doesn't set this by itself
			'date_created' => date('c'),
			'version' => 'v2',
			));

		// save key request id
		if (is_string($request_id)) {
			$this->id = $request_id;
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
	 * issuing {@link this::request()}.
	 *
	 * @throws InvalidArgumentException When the key request ID is missing
	 * @throws UnexpectedValueException When we don't get a token back from API
	 *
	 * @return void
	 */
	public function approve() {
		if (!is_string($this->id) || empty($this->id)) {
			throw new InvalidArgumentException('Invalid key request');
		}

		try {
			$token = $this->api->put('/portal/requests/approve', $this->id);
			$developer = $this->user->fetch_from_tyk();
			if (is_object($token) && isset($token->RawKey) && !empty($token->RawKey)) {
				$this->key = $token->RawKey;

				if (is_object($developer) && isset($developer->subscriptions)) {
					if (isset($developer->subscriptions->{$this->policy})) {
						$this->hash = $developer->subscriptions->{$this->policy};
					}
				}
			}
			else {
				throw new Exception('Could not approve token request');
			}
		}
		catch (Exception $e) {
			throw new UnexpectedValueException($e->getMessage());
		}
	}

	/**
	 * Revoke (delete!) a token
	 *
	 * @throws InvalidArgumentException When this class doesn't have all the data it needs
	 * @throws UnexpectedValueException When API does not respond as expected
	 * 
	 * @return boolean True when successful
	 */
	public function revoke() {
		if (!is_string($this->policy) || !is_string($this->hash) || !is_a($this->user, 'Tyk_Portal_User')) {
			throw new InvalidArgumentException('Missing token information');
		}

		try {
			$response = $this->api->delete(sprintf('/portal/developers/key/%s/%s/%s',
				$this->policy,
				$this->hash,
				$this->user->get_tyk_id()
				));
			if (is_object($response) && isset($response->Status) && $response->Status == 'OK') {
				return true;
			}
			else {
				throw new Exception('Received invalid response from API');
			}
		}
		catch (Exception $e) {
			throw new UnexpectedValueException($e->getMessage());
		}
	}

	/**
	 * Get usage quota of this token
	 * Requires the unhashed key of the token, unfortunately.
	 *
	 * @throws InvalidArgumentException When the key isn't set
	 * @throws UnexpectedValueException When API does not respond as expected
	 * 
	 * @return object Usage quota
	 */
    public function get_usage_quota() {
        if (!is_string($this->key)) {
			throw new InvalidArgumentException('Missing token key');
		}

		try {
			/**
			 * Hybrid Tyk
			 * Get usage quota from gateways, as this info isn't synced back to cloud
			 */
			if (Tyk_Dev_Portal::is_hybrid()) {
				$response = $this->gateway->get(sprintf('/keys/%s', $this->key));
				if (is_object($response) && isset($response->quota_remaining)) {
				    return (object) array(
				    	'quota_remaining' => $response->quota_remaining,
				        'quota_max' => $response->quota_max
				        );
				}
				else {
				    throw new Exception('Received invalid response from Gateway');
				}
			}
			/**
			 * Cloud and on-premise Tyk
			 * Get usage quota from API
			 */
			else {
				// first: we need an api id on which to request the tokens
				// sounds weird I know, here's the explanation: https://community.tyk.io/t/several-questions/1041/3
				$apiManager = new Tyk_API_Manager;
				$apis = $apiManager->available_apis();
				if (is_array($apis)) {
					$firstApi = array_shift($apis);
					$response = $this->api->get(sprintf('/apis/%s/keys/%s',
						$firstApi->api_definition->api_id,
						$this->key
					));
				}
				if (is_object($response) && isset($response->data)) {
					return $response->data;
				}
				else {
					throw new Exception('Received invalid response from API');
				}
			}
        }
		catch (Exception $e) {
			throw new UnexpectedValueException($e->getMessage());
		}
	}

	/**
	 * Get usage stats for this token
	 *
	 * @throws InvalidArgumentException When the hash isn't set
	 * @throws UnexpectedValueException When API does not respond as expected
	 *
	 * @param string $from_date From this date forward
	 * @param string $to_date To this date
	 * @return object Usage data
	 */
	public function get_usage($from_date = null, $to_date = null) {
		if (!is_string($this->hash)) {
			throw new InvalidArgumentException('Missing token hash');
		}

		// use from_date or today-1 month
		$from = is_null($from_date)
			? strtotime('-1 week')
			: strtotime($from_date);
		// use to_date or <now>
		$to = is_null($to_date)
			? time()
			: strtotime($to_date);

		try {
			$response = $this->api->get(sprintf('/activity/keys/%s/%s/%s',
				$this->hash,
				date('j/n/Y', $from),
				date('j/n/Y', $to)
			), array('res' => 'day'));

			if (is_object($response) && property_exists($response, 'data')) {
				return $response->data;
			}
			else {
				throw new Exception('Received invalid response from API');
			}
		}
		catch (Exception $e) {
			throw new UnexpectedValueException($e->getMessage());
		}
	}
}


