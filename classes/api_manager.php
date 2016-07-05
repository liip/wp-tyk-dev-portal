<?php
/**
 * Tyk API manager
 *
 * @package Tyk_Dev_Portal\Tyk_API
 */

/**
 * Handles API management, not actual API communication
 */
class Tyk_API_Manager 
{
	/**
	 * Get available APIs defined on Tyk
	 * 
	 * @return array
	 */
	public static function available_apis() {
		$tyk = new Tyk_API();
		$response = $tyk->get('/apis');
		// a generator would be nice here but alas, php 5.4 is still very common
		$active_apis = array();
		foreach ($response->apis as $api) {
			// only return active apis
			if ($api->api_definition->active) {
				$active_apis[] = $api->api_definition;
			}
		}
		return $active_apis;
	}

	/**
	 * Register a developer for a tyk API
	 * 
	 * @param Portal_User $user
	 * @param string $policy
	 * @return string The key
	 */
	public function registerForAPI(Tyk_Portal_User $user, $policy) {
		$tyk = new Tyk_API();
		$key = $tyk->post('/portal/requests', array(
			'by_user' => $user->get_tyk_id(),
			'for_plan' => $policy,
			// it's possible to have key requests approved manually
			'approved' => TYK_AUTO_APPROVE_KEY_REQUESTS,
			// this is a bit absurd
			'date_created' => date('c')
			));
		return $key;
	}
}