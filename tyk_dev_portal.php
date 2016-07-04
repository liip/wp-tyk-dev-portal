<?php
/**
 * Plugin Name: Tyk Developer Portal
 * Description: Plugin to use your WordPress as a developer portal for Tyk.io
 * Author: Team Abmoss <be-dev@liip.ch>
 * Version: 1.0.0
 * Date: 04.07.2016
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if ( ! class_exists( 'Tyk_API', false ) ) {
	/**
	 * Class to handle interaction with Tyk API
	 */
	class Tyk_API
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
		public function post($path, $body) {
			$url = sprintf('%s/%s', 
				TYK_API_ENDPOINT, 
				$path
				);

			$api_response = wp_remote_post($url, array(
				'headers' => array(
					'Authorization' => TYK_API_KEY,
				),
				'body' => json_encode($body),
				));

			// parse and analyse response		
			$response = json_decode(wp_remote_retrieve_body($api_response));
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
	}
}