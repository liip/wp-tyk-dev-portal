<?php

class Tyk_Dev_Portal_Testcase extends WP_UnitTestCase {
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