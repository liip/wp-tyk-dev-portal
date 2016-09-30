<?php

require_once 'TykDevPortalTestcase.php';

class TykAPIManagerTest extends Tyk_Dev_Portal_Testcase {
	// test getting policies
	function testAvailablePolicies() {
		$apiManager = new Tyk_API_Manager;
		$apis = $apiManager->available_policies();
		$this->assertTrue(is_array($apis));
		$this->assertTrue(sizeof($apis) > 0);
	}

	// test getting apis
	function testAvailableApis() {
		$apiManager = new Tyk_API_Manager;
		$apis = $apiManager->available_apis();
		$this->assertTrue(is_array($apis));
		$this->assertTrue(sizeof($apis) > 0);
	}
}