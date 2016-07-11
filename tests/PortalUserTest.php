<?php

require_once 'TykDevPortalTestcase.php';

class TykPortalUserTest extends Tyk_Dev_Portal_Testcase {
	// test the check if a user is a developer
	function testIsDeveloperCheck() {
		// create a new user with developer role
		$user_id = $this->createTestUser();
		$this->assertGreaterThanOrEqual(1, $user_id);

		// make sure our check gets it right
		$user = new Tyk_Portal_User($user_id);
		$this->assertTrue($user->is_developer());
	}

	// test developer registration with tyk
	function testTykRegistration() {
		// create a new user with developer role
		$user_id = $this->createTestUser();
		$this->assertGreaterThanOrEqual(1, $user_id);

		$user = new Tyk_Portal_User($user_id);
		$user->register_with_tyk();
		$tyk_id = $user->get_tyk_id();
		
		// it's hard to check if the id is valid, but let's make sure 
		// it's not empty and is at leaast 5 chars long
		$this->assertNotEmpty($tyk_id);
		$this->assertTrue(strlen($tyk_id) > 5);
	}
}