<?php

use PHPUnit\Framework\TestCase;

/**
 * Base class for tyk portal plugin test cases
 *
 * @see https://github.com/10up/wp_mock Maybe expand tests later on
 * @see https://wordpress.stackexchange.com/questions/164121/testing-hooks-callback
 */
abstract class Tyk_Dev_Portal_Testcase extends WP_UnitTestCase {
	/**
	 * Create a new user, registered with Tyk and ready for testing
	 * 
	 * @return Tyk_Portal_User
	 */
	protected function createPortalUser() {
		// create a new user with developer role
		$user_id = $this->createTestUser();
		$this->assertGreaterThanOrEqual(1, $user_id);

		$user = new Tyk_Portal_User($user_id);
		$user->register_with_tyk();
		$this->assertTrue($user->has_tyk_id());

		return $user;
	}

	/**
	 * Create a test wordpress user
	 * 
	 * @return integer User ID
	 */
	protected function createTestUser() {
		return wp_insert_user(array(
			'user_login' => 'test_developer',
			'user_pass' => '123456789',
			'user_email' => 'unittest@example.org',
			'role' => 'developer',
			));
	}

	/**
	 * Get access to a protected method of a class
	 * 
	 * @param string $class
	 * @param string $method
	 * @return ReflectionMethod
	 */
	protected function getProtectedMethod($class, $method) {
		$class = new ReflectionClass($class);
		$method = $class->getMethod($method);
		$method->setAccessible(true);
		return $method;
	}
}