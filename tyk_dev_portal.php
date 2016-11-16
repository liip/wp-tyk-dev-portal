<?php
/**
 * Plugin Name: Tyk Developer Portal
 * Description: Integrates a developer portal of a Tyk API Gateway in your WordPress site
 * Author: Liip <be-dev@liip.ch>
 * Version: 1.0.0
 * Date: 04.07.2016
 * Text Domain: tyk-dev-portal
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

define('TYK_DEV_PORTAL_PLUGIN_PATH', dirname( __FILE__ ));
define('TYK_DEV_PORTAL_TPL_PATH', TYK_DEV_PORTAL_PLUGIN_PATH . '/templates');
define('TYK_DEV_PORTAL_PLUGIN_FILE', __FILE__);

require_once TYK_DEV_PORTAL_PLUGIN_PATH . '/classes/dev_portal.php';
require_once TYK_DEV_PORTAL_PLUGIN_PATH . '/classes/portal_user.php';
require_once TYK_DEV_PORTAL_PLUGIN_PATH . '/classes/api_manager.php';
require_once TYK_DEV_PORTAL_PLUGIN_PATH . '/classes/dashboard_ajax_provider.php';
require_once TYK_DEV_PORTAL_PLUGIN_PATH . '/classes/token.php';
require_once TYK_DEV_PORTAL_PLUGIN_PATH . '/classes/tyk_interaction.php';
require_once TYK_DEV_PORTAL_PLUGIN_PATH . '/classes/tyk_api.php';
require_once TYK_DEV_PORTAL_PLUGIN_PATH . '/classes/tyk_gateway.php';
require_once TYK_DEV_PORTAL_PLUGIN_PATH . '/template_tags.php';

$plugin = new Tyk_Dev_Portal();
$plugin->register_hooks();
$plugin->register_actions();
$plugin->register_shortcodes();

/**
 * Get url to this plugin's dir
 *
 * @param string $path Path to the plugin file you want the url for
 * @return string
 */
function tyk_dev_portal_plugin_url($path) {
	return plugin_dir_url(__FILE__) . $path;
}
