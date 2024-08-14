<?php

declare(strict_types=1);
/**
 * Tyk portal user.
 */

/**
 * Not an actual user abstraction, but gathers common functionality regarding
 * developer portal users.
 */
class Tyk_Portal_User
{
    /**
     * The name of the key in wordpress user meta data where tyk user id is stored.
     */
    public const META_TYK_USER_ID_KEY = 'tyk_user_id';

    /**
     * The name of the key in wordpress user meta data where tyk access tokens are stored
     * Note that the actual tokens are not stored, just the name and id so we can manage them.
     */
    public const META_TYK_ACCESS_TOKENS_KEY = 'tyk_access_token';

    /**
     * Name of the developer role this plugin creates.
     */
    public const DEVELOPER_ROLE_NAME = 'developer';

    /**
     * The actual wordpress user object.
     *
     * @var WP_User
     */
    private $user;

    /**
     * User's policy subscriptions according to Tyk.
     *
     * @var array
     */
    private $tyk_subscriptions;

    /**
     * Setup portal user.
     *
     * @param int $user_id Defaults to current user's id
     */
    public function __construct($user_id = null)
    {
        if (is_null($user_id)) {
            $user_id = get_current_user_id();
        }
        $this->user = get_userdata($user_id);
    }

    /**
     * Check if we're dealing with a logged in user.
     *
     * @return bool
     */
    public function is_logged_in()
    {
        return is_a($this->user, 'WP_User');
    }

    /**
     * Check if current user is logged in and is a developer.
     *
     * @return bool
     */
    public function is_developer()
    {
        return $this->user->exists() && in_array(self::DEVELOPER_ROLE_NAME, $this->user->roles);
    }

    /**
     * Save a tyk access token
     * Note that the actual tokens are not stored, just the name and id so we can manage them.
     *
     * @param string $api_id     ID of tyk API policy
     * @param string $token_name User-given name of token
     * @param string $hash       Token hash
     */
    public function save_access_token($api_id, $token_name, $hash): void
    {
        $data = array(
            'api_id'     => sanitize_text_field($api_id),
            'token_name' => sanitize_text_field($token_name),
            'hash'       => sanitize_text_field($hash),
        );

        // check if token already exists
        $tokens = $this->get_access_tokens();

        $key = false;
        if (count($tokens)) {
            $ids = wp_list_pluck($tokens, 'hash');
            $key = array_search($hash, $ids);
        }

        // this is a new token
        if (false === $key) {
            $tokens[] = $data;
        }
        // this is an existing token
        else {
            $tokens[$key] = $data;
        }

        // re-index array and update key in user meta storage
        update_user_meta($this->user->ID, self::META_TYK_ACCESS_TOKENS_KEY, array_values($tokens));
    }

    /**
     * Get a single access token.
     *
     * @param string $hash
     *
     * @return Tyk_Token
     *
     * @throws OutOfBoundsException When token id is invalid
     */
    public function get_access_token($hash)
    {
        $tokens = $this->get_access_tokens();
        foreach ($tokens as $token) {
            if ($token['hash'] == $hash) {
                return Tyk_Token::init($token, $this);
            }
        }

        // if we get here, we didn't find the token
        throw new OutOfBoundsException('Invalid token id');
    }

    /**
     * Get user's access tokens.
     *
     * @return array
     */
    public function get_access_tokens()
    {
        $tokens = get_user_meta($this->user->ID, self::META_TYK_ACCESS_TOKENS_KEY, true);
        if (!is_array($tokens)) {
            return array();
        }

        // lambda to add an 'is_valid' attribute to each token
        $map_is_valid = function ($token) {
            return array_merge($token, array(
                'is_valid' => $this->has_token($token['hash']),
            ));
        };

        return array_map($map_is_valid, $tokens);
    }

    /**
     * Delete an access token by it's ID.
     *
     * @param string $hash
     */
    public function delete_access_token($hash): void
    {
        $tokens = $this->get_access_tokens();
        $found = false;
        foreach ($tokens as $i => $token) {
            if ($token['hash'] == $hash) {
                $found = true;

                break;
            }
        }
        if (true === $found) {
            unset($tokens[$i]);
            // re-index array update the dataset
            update_user_meta($this->user->ID, self::META_TYK_ACCESS_TOKENS_KEY, array_values($tokens));
        }
    }

    /**
     * Check if we already stored a tyk id for this developer.
     *
     * @return bool
     */
    public function has_tyk_id()
    {
        $tyk_user_id = get_user_meta($this->user->ID, self::META_TYK_USER_ID_KEY, true);

        return !empty($tyk_user_id);
    }

    /**
     * Set the user's tyk user id.
     *
     * @param string $id
     */
    public function set_tyk_id($id): void
    {
        update_user_meta($this->user->ID, self::META_TYK_USER_ID_KEY, $id);
    }

    /**
     * Get the user's tyk user id.
     *
     * @return string
     */
    public function get_tyk_id()
    {
        return get_user_meta($this->user->ID, self::META_TYK_USER_ID_KEY, true);
    }

    /**
     * Get tyk access token.
     *
     * @return array
     */
    public function register_with_tyk()
    {
        try {
            $tyk = new Tyk_API();
            $user_id = $tyk->post('/portal/developers', array(
                'email' => $this->user->user_email,
            ));
            $this->set_tyk_id($user_id);
        } catch (Exception $e) {
            trigger_error(sprintf('Could not register user for API: %s', $e->getMessage()), E_USER_WARNING);
        }
    }

    /**
     * Check if the user still has <token> according to tyk.
     *
     * @param string $hash
     *
     * @return bool
     *
     * @throws Exception If subscriptions info is missing in Tyk user data
     */
    private function has_token($hash)
    {
        if (!isset($this->tyk_subscriptions) || !is_array($this->tyk_subscriptions)) {
            $user_data = $this->fetch_from_tyk();
            if (!isset($user_data->subscriptions)) {
                throw new Exception('Missing policy subscriptions');
            }
            $this->tyk_subscriptions = array_flip((array) $user_data->subscriptions);
        }

        return isset($this->tyk_subscriptions[$hash]);
    }

    /**
     * Fetch developer data from Tyk.
     *
     * @return stdClass
     */
    public function fetch_from_tyk()
    {
        try {
            $tyk = new Tyk_API();
            $developer = $tyk->get(sprintf('/portal/developers/%s', $this->get_tyk_id()));
            if (!is_object($developer) || !isset($developer->id)) {
                throw new Exception('Received invalid response');
            }

            return $developer;
        } catch (Exception $e) {
            trigger_error(sprintf('Could not fetch developer from API: %s', $e->getMessage()), E_USER_WARNING);
        }
    }
}
