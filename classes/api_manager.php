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
	 * Name of tag that a policy must have on tyk api
	 * so users may register for it freely
	 */
	const POLICY_TAG = 'allow_registration';

	/**
	 * Get available policies defined on Tyk
	 * 
	 * @return array
	 */
	public static function available_policies() {
		$tyk = new Tyk_API;
		// available apis are actually available policies/plans that allow access
		// to certain apis under certain restrictions
        // normally only the first 10 policies are returned, we disabled this limit using p=-1 as param
		$response = $tyk->get('/portal/policies', ['p' => -1]);
		// a generator would be nice here but alas, php 5.4 is still very common
		$active_apis = array();
		if (is_object($response) && isset($response->Data)) {
			foreach ($response->Data as $policy) {
				// only return active apis
				if ($policy->active && !$policy->is_inactive) {
					if (isset($policy->tags) && is_array($policy->tags) && in_array(self::POLICY_TAG, $policy->tags))
					$active_apis[] = array(
						'id' => $policy->_id,
						'name' => $policy->name,
						);
				}
			}
		}

		return $active_apis;
	}

	/**
	 * Get APIs defined on Tyk
	 *
	 * @throws Exception When no apis are found in response
	 * @return array
	 */
	public static function available_apis() {
		$tyk = new Tyk_API;
		$response = $tyk->get('/apis');
		// a generator would be nice here but alas, php 5.4 is still very common
		$apis = array();
		if (is_object($response) && isset($response->apis)) {
			return $response->apis;
		}
		else {
			throw new Exception('No apis defined');
		}

		return $apis;
	}
}