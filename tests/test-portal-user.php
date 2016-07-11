<?php

class TykPortalUserTest extends WP_UnitTestCase {
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
		$this->assertTrue(strlen($tyk_id) > 5)
	}

	/**
	 * Create a test wordpress user
	 * @return integer User ID
	 */
	private function createTestUser() {
		return wp_insert_user(array(
			'user_login' => 'test_developer',
			'user_pass' => '123456789',
			'user_email' => 'unittest@example.org',
			'role' => 'developer',
			));
	}
}