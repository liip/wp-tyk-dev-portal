<?php

require_once 'TykDevPortalTestcase.php';

class TykAPIManagerTest extends Tyk_Dev_Portal_Testcase {
	// test making a key request for an api policy
	function testKeyRequest() {
		$user = $this->createPortalUser();
		
		$apiManager = new Tyk_API_Manager();
		$method = $this->getProtectedMethod(get_class($apiManager), 'make_key_request');
		$keyRequest = $method->invokeArgs($apiManager, array($user, TYK_TEST_API_POLICY));

		// it's hard to check if the key is valid, but let's make sure 
		// it's not empty and is at leaast 5 chars long
		$this->assertNotEmpty($keyRequest);
		$this->assertTrue(strlen($keyRequest) > 5);
	}

	// test making and approving a key request to get an access token
	function testKeyApproval() {
		$user = $this->createPortalUser();
		
		// we'll just use the register_for_api method here as it does the exact thing we'd
		// do here otherwise: make key request, then approve it
		$apiManager = new Tyk_API_Manager();
		$method = $this->getProtectedMethod(get_class($apiManager), 'register_for_api');
		$token = $method->invokeArgs($apiManager, array($user, TYK_TEST_API_POLICY));
		
		// it's hard to check if the token is valid, but let's make sure 
		// it's not empty and is at leaast 5 chars long
		$this->assertNotEmpty($token);
		$this->assertTrue(strlen($token) > 5);	
	}
}