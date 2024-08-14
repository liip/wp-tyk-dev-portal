<?php

declare(strict_types=1);

require_once 'TykDevPortalTestcase.php';

class TykAPIManagerTest extends Tyk_Dev_Portal_Testcase
{
    // test getting policies
    public function testAvailablePolicies(): void
    {
        $apiManager = new Tyk_API_Manager();
        $apis = $apiManager->available_policies();
        $this->assertIsArray($apis);
        $this->assertTrue(sizeof($apis) > 0);
    }

    // test getting apis
    public function testAvailableApis(): void
    {
        $apiManager = new Tyk_API_Manager();
        $apis = $apiManager->available_apis();
        $this->assertIsArray($apis);
        $this->assertTrue(sizeof($apis) > 0);
    }
}
