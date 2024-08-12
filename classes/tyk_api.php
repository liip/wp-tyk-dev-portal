<?php
/**
 * Tyk API adapter
 *
 * @package Tyk_Dev_Portal\Tyk_API
 */

/**
 * Class to handle interaction with Tyk API
 */
class Tyk_API extends Tyk_Interaction
{
	/**
	 * Send a post request to Tyk API
	 * 
	 * @param string $path Path to endpoint
	 * @param array $body
	 *
	 * @throws Exception When API sends invalid response
	 * @throws Exception When API reports a NOT OK status
	 * 
	 * @return array
	 */
	public function post($path, array $body) {
		$api_response = wp_remote_post($this->get_url_for_path($path), array(
			'headers' => array(
				'Authorization' => TYK_API_KEY,
			),
			'body' => json_encode($body),
			));

		// analyse response
		$response = $this->parse_response($api_response);
		if (is_object($response) && isset($response->Status) && isset($response->Message)) {
			if ($response->Status == 'OK') {
				return $response->Message;
			}
			else {
				throw new Exception($response->Message);
			}
		}
		else {
			throw new Exception('Received invalid response from API');
		}
	}

	/**
	 * Send a get request to Tyk API
	 * 
	 * @param string $path
	 * @param array $args  Query string args
	 *
	 * @throws Exception When API sends invalid response
	 * 
	 * @return array
	 */
	public function get($path, array $args = null) {
		$api_response = wp_remote_get($this->get_url_for_path($path, $args), array(
			'headers' => array(
				'Authorization' => TYK_API_KEY,
			)));

		$response = $this->parse_response($api_response);
		if (is_object($response)) {
			return $response;
		}
		else {
			throw new Exception('Received invalid response from API');
		}
	}

	/**
	 * Send a put request to Tyk API
	 * 
	 * @param string $path
	 * @param string $location
	 *
	 * @throws Exception When API sends invalid response
	 * 
	 * @return array
	 */
	public function put($path, $location) {
		$base_url = $this->get_url_for_path($path);
		$url = $base_url . '/' . $location;

		$api_response = wp_remote_request($url, array(
			'method' => 'PUT',
			'headers' => array(
				'Authorization' => TYK_API_KEY,
			)));
		$response = $this->parse_response($api_response);
		if (is_object($response)) {
			return $response;
		}
		else {
			throw new Exception('Received invalid response from API');
		}
	}

	/**
	 * Send a delete request to Tyk API
	 * 
	 * @throws Exception When API sends invalid response
	 * 
	 * @param string $path
	 * @return stdClass
	 */
	public function delete($path) {
        $url = $this->get_url_for_path($path);
        $api_response = wp_remote_request($url, array(
            'method' => 'DELETE',
            'headers' => array(
                'Authorization' => TYK_API_KEY,
            )
        ));

        $response = $this->parse_response($api_response);
        if (is_object($response)) {
            return $response;
        }
        else {
            throw new Exception('Received invalid response from API');
        }
    }

	/**
	 * Get absolute url to api endpoint for a path
	 * 
     * @param string $path
	 * @return string
	 */
	protected function get_url_for_path($path, array $args = null) {
		// build query string out of args if they're set
		$qs = '';
		if (is_array($args)) {
			$qs = '?' . http_build_query($args);
        }
		return sprintf('%s/%s%s', 
			rtrim(TYK_API_ENDPOINT, '/'), 
			ltrim($path, '/'),
			$qs
			);
	}
}
