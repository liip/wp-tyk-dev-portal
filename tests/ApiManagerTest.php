<?php

require_once 'TykDevPortalTestcase.php';

class TykAPIManagerTest extends Tyk_Dev_Portal_Testcase {
	// test registration for an api policy
	function testAPIManager() {
		// create a new user with developer role
		$user_id = $this->createTestUser();
		$this->assertGreaterThanOrEqual(1, $user_id);
		$user = new Tyk_Portal_User($user_id);
		$apiManager = new Tyk_API_Manager();
		$key = $apiManager->register_for_api($user, TYK_TEST_API_POLICY);

		// it's hard to check if the key is valid, but let's make sure 
		// it's not empty and is at leaast 5 chars long
		$this->assertNotEmpty($key);
		$this->assertTrue(strlen($key) > 5);
	}
}