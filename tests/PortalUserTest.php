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

	// test adding an access token
	// @todo test adding token that already exists (same token_id)
	// @todo test adding multiple tokens and make sure they all get saved
	function testAddAccessToken() {
		$user = $this->createPortalUser();
		// save a token
		$testToken = array(
			'api_id' => 'api-id',
			'token_name' => 'Unittest Token',
			'hash' => 'token-id',
			);
		$user->save_access_token($testToken['api_id'], $testToken['token_name'], $testToken['hash']);

		// save it again to check that it updates and doesn't duplicate
		$user->save_access_token($testToken['api_id'], $testToken['token_name'], $testToken['hash']);		

		// get all tokens
		$tokens = $user->get_access_tokens();

		// make sure it didn't duplicate
		$this->assertEquals(count($tokens), 1);
		$this->assertArraySubset(array($testToken), $tokens);
	}

	// make sure we always get an array of access tokens
	function testGetAccessTokens() {
		$user = $this->createPortalUser();

		$this->assertTrue(is_array($user->get_access_tokens()));
	}

	// test that we can delete tokens
	function testDeleteAccessToken() {
		$user = $this->createPortalUser();
		
		// let's add three tokens
		$user->save_access_token('api-id', 'My favorite token', 'token-id-1');
		$user->save_access_token('api-id', 'My 2nd-favorite token', 'token-id-2');
		$user->save_access_token('api-id', 'My 3rd-favorite token', 'token-id-3');

		$tokens = $user->get_access_tokens();
		$this->assertEquals(count($tokens), 3);

		// delete the 2nd one
		$user->delete_access_token('token-id-2');

		$tokens = $user->get_access_tokens();
		$found = false;
		foreach ($tokens as $token) {
			if ($token['hash'] == 'token-id-2') {
				$found = true;
			}
		}
		$this->assertFalse($found);
		$this->assertEquals(count($tokens), 2);
	}

	// test getting a token
	function testGetExistingToken() {
		$user = $this->createPortalUser();
		// save a token
		$testToken = array(
			'api_id' => 'api-id',
			'token_name' => 'Unittest Token',
			'hash' => 'token-id-4',
			);
		$user->save_access_token($testToken['api_id'], $testToken['token_name'], $testToken['hash']);

		$token = $user->get_access_token($testToken['hash']);

		$this->assertInstanceOf('Tyk_Token', $token);
		// note: the token name isn't relevant for the Tyk_Token class
		$this->assertEquals($token->get_hash(), $testToken['hash']);
		$this->assertEquals($token->get_policy(), $testToken['api_id']);
	}

	/**
	 * test that you can't get a on existent token
	 * @expectedException OutOfBoundsException
	 */
	function testGetNonExistentToken() {
		$user = $this->createPortalUser();
		$user->get_access_token("surely this won't work");
	}

	// test if we can pull user data from tyk
	function testFetchUserFromTyk() {
		$user = $this->createPortalUser();

		$developer = $user->fetch_from_tyk();
		$this->assertInstanceOf('stdClass', $developer);
		$this->assertEquals($developer->id, $user->get_tyk_id());
	}
}