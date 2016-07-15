<?php

require_once 'TykDevPortalTestcase.php';

class KeyRequestTest extends Tyk_Dev_Portal_Testcase {
	// test making a key request for an api policy
	function testKeyRequest() {
		$user = $this->createPortalUser();
		
		$keyRequest = new Tyk_Key_Request();
		$keyRequest->send($user, TYK_TEST_API_POLICY);

		// it's hard to check if the key is valid, but let's make sure 
		// it's not empty and is at leaast 5 chars long
		$this->assertNotEmpty($keyRequest->get_id());
		$this->assertTrue(strlen($keyRequest->get_id()) > 5);
	}

	/**
	 * Disabled because Tyk API doesn't care if the policy exists or not
	 * @see https://github.com/TykTechnologies/tyk/issues/272
	 * expectedException UnexpectedValueException
	 *
	function testInvalidKeyRequest() {
		$user = $this->createPortalUser();
		
		$keyRequest = new Tyk_Key_Request();
		$keyRequest->send($user, 'invalid api');
		print $keyRequest->get_id();
	}*/

	// test making and approving a key request to get an access token
	// @todo test failure when using an invalid key
	function testKeyApproval() {
		$user = $this->createPortalUser();

		$keyRequest = new Tyk_Key_Request();
		$keyRequest->send($user, TYK_TEST_API_POLICY);
		$keyRequest->approve();
		
		// it's hard to check if the token is valid, but let's make sure 
		// it's not empty and is at leaast 5 chars long
		$this->assertNotEmpty($keyRequest->get_key());
		$this->assertTrue(strlen($keyRequest->get_key()) > 5);
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	function testEmptyKeyApproval() {
		$user = $this->createPortalUser();

		$keyRequest = new Tyk_Key_Request();
		$keyRequest->send($user, TYK_TEST_API_POLICY);
		// let's set the internal id to something invalid
		$keyRequest->set_id(null);
		$keyRequest->approve();
	}

	/**
	 * @expectedException UnexpectedValueException
	 */
	function testInvalidKeyApproval() {
		$user = $this->createPortalUser();

		$keyRequest = new Tyk_Key_Request();
		$keyRequest->send($user, TYK_TEST_API_POLICY);
		// let's set the internal id to an id that isn't on tyk
		$keyRequest->set_id('not an actual id');
		$keyRequest->approve();
	}
}