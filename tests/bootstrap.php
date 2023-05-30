<?php
/**
 * PHPUnit bootstrap file
 * bash bin/install-wp-tests.sh wp_test root '' localhost latest
 * @package Tyk_Dev_Portal
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/tyk_dev_portal.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';



define( 'TYK_API_ENDPOINT', '' );
define( 'TYK_API_KEY', '' );  // add a key for a management user
define( 'TYK_AUTO_APPROVE_KEY_REQUESTS', true );
define( 'TYK_TEST_API_POLICY', '' );  // add the id of a test api policy tagged with 'allow_regsitration'
