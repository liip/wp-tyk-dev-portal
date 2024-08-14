<?php

declare(strict_types=1);

require_once 'TykDevPortalTestcase.php';

class TykDevPortalPluginTest extends Tyk_Dev_Portal_Testcase
{
    // test if developer role can be created
    public function testRoleCreation(): void
    {
        $plugin = new Tyk_Dev_Portal();
        $plugin->create_developer_role();

        $role = get_role($plugin::DEVELOPER_ROLE_NAME);
        $this->assertInstanceOf('WP_Role', $role);
    }

    // test that dashboard page can be created
    public function testDashboardPageCreation(): void
    {
        $plugin = new Tyk_Dev_Portal();
        $plugin->create_dashboard_page();

        $page = get_page_by_path($plugin::DASHBOARD_SLUG);
        $this->assertInstanceOf('WP_Post', $page);
    }
}
