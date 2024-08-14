<?php

declare(strict_types=1);
/**
 * Contains the "plugin manager" class.
 */

/**
 * Tyk developer portal plugin manager class
 * Handles hooking into WordPress functionality and dispatching to the appropriate place.
 * Let's keep this class as lean as possible and do the heavy lifting in domain-specific classes.
 */
class Tyk_Dev_Portal
{
    /**
     * Name of this plugin.
     */
    public const PLUGIN_NAME = 'Tyk Developer Portal';

    /**
     * Version of this plugin.
     */
    public const PLUGIN_VERSION = '1.2';

    /**
     * Slug of the dashboard page.
     */
    public const DASHBOARD_SLUG = 'dev-dashboard';

    /**
     * This plugins text domain.
     */
    public const TEXT_DOMAIN = 'tyk-dev-portal';

    /**
     * Name of the role for developers this plugin will create.
     */
    public const DEVELOPER_ROLE_NAME = 'developer';

    /**
     * Tyk configuration types
     * we use these for consistency when checking TYK_CONFIGURATION throughout the code.
     */
    public const CONFIGURATION_ON_PREMISE = 'on-premise';
    public const CONFIGURATION_HYBRID = 'hybrid';
    public const CONFIGURATION_CLOUD = 'cloud';

    /**
     * Register any hooks.
     */
    public function register_hooks(): void
    {
        // in backend: register activation hook for this plugin
        if (is_admin()) {
            register_activation_hook(TYK_DEV_PORTAL_PLUGIN_FILE, array($this, 'activate_plugin'));
        }
    }

    /**
     * Register any actions.
     */
    public function register_actions(): void
    {
        /*
         * Hook into wordpress "onregister" of a user and register the
         * user as a tyk developer if the user has the "developer" role
         */
        add_action('user_register', array($this, 'register_user_with_tyk'));

        $dashboard_ajax_provider = new Tyk_Dashboard_Ajax_Provider();
        $dashboard_ajax_provider->register_actions();

        add_action('init', array($this, 'register_scripts'));
        add_action('init', array($this, 'register_styles'));
        add_action('wp', array($this, 'enqueue_assets'));
        add_action('wp', array($this, 'environment_is_ready'));
        add_action('plugins_loaded', array($this, 'load_plugin_textdomain'));
    }

    /**
     * Register any shortcodes.
     */
    public function register_shortcodes(): void
    {
        add_shortcode('tyk_dev_dashboard', 'tyk_dev_portal_dashboard');
    }

    /**
     * Make sure environment is ready for our plugin.
     */
    public function environment_is_ready(): void
    {
        // make sure we have a tyk user in case registration failed
        $this->register_user_with_tyk();
    }

    /**
     * Enqueue scripts and styles for the appropriate page.
     *
     * We do this in 'wp' hook because it's after the theme has setup
     * and enqueued it's styles so we can react accordingly.
     */
    public function enqueue_assets(): void
    {
        $user = new Tyk_Portal_User();
        // if this is our dashboard page, enqueue our assets
        if ($user->is_logged_in() && is_page(self::DASHBOARD_SLUG)) {
            // enqueue and localize our dashboard script
            wp_enqueue_script('tyk-dev-portal-dashboard');
            $params = array(
                'actionUrl'           => esc_url(admin_url('admin-ajax.php')),
                'label_used'          => __('Used', self::TEXT_DOMAIN),
                'label_remaining'     => __('Remaining', self::TEXT_DOMAIN),
                'label_success'       => __('Success', self::TEXT_DOMAIN),
                'label_errors'        => __('Errors', self::TEXT_DOMAIN),
                'error_invalid_data'  => __('Invalid or insufficient data', self::TEXT_DOMAIN),
                'label_valid'         => __('valid', Tyk_Dev_Portal::TEXT_DOMAIN),
                'label_invalid'       => __('invalid', Tyk_Dev_Portal::TEXT_DOMAIN),
                'msg_unlimited_quota' => __('This token has unlimited quota', Tyk_Dev_Portal::TEXT_DOMAIN),
            );
            wp_localize_script('tyk-dev-portal-dashboard', 'scriptParams', $params);

            wp_enqueue_script('chart.js');

            // only enqueue our bootstrap styles if the current theme isn't using bootstrap
            if (!wp_style_is('bootstrap', 'enqueued')) {
                if (!defined('TYK_FORCE_DISABLE_BOOTSTRAP') || TYK_FORCE_DISABLE_BOOTSTRAP !== true) {
                    wp_enqueue_style('tyk-dev-portal-bootstrap');
                }
            }
        }
    }

    /**
     * Register javascript files.
     */
    public function register_scripts(): void
    {
        // enqueue vue.js
        $vue_file = (WP_DEBUG === true)
            ? 'vue.js'
            : 'vue.min.js';
        $vendor_version = (WP_DEBUG === true)
            ? time()
            : self::PLUGIN_VERSION;
        wp_register_script('vue', tyk_dev_portal_plugin_url('assets/js/vendor/' . $vue_file), array(), $vendor_version, true);
        wp_register_script('underscore', tyk_dev_portal_plugin_url('assets/js/vendor/underscore.min.js'), array(), $vendor_version, true);

        wp_register_script('chart.js', tyk_dev_portal_plugin_url('assets/js/vendor/chart.min.js'), array(), $vendor_version, false);

        // enqueue dashboard.js
        $dashboard_ver = (WP_DEBUG === true)
            ? time()
            : self::PLUGIN_VERSION;
        wp_register_script('tyk-dev-portal-dashboard', tyk_dev_portal_plugin_url('assets/js/dashboard.js'), array('jquery', 'vue', 'underscore'), $dashboard_ver, true);
    }

    /**
     * Register styles.
     *
     * Registers a minimal bootstrap theme that only contains the stuff we need
     * Note: this style is only enqueued if the activate theme does not enqueue a bootstrap file
     */
    public function register_styles(): void
    {
        $style_ver = (WP_DEBUG === true)
            ? time()
            : self::PLUGIN_VERSION;
        wp_register_style('tyk-dev-portal-bootstrap', tyk_dev_portal_plugin_url('assets/css/bootstrap.min.css'), null, $style_ver);
    }

    /**
     * load the localized strings.
     */
    public function load_plugin_textdomain(): void
    {
        load_plugin_textdomain(self::TEXT_DOMAIN, false, basename(TYK_DEV_PORTAL_PLUGIN_PATH) . '/languages');
    }

    /**
     * Register a user as a developer with Tyk.
     *
     * @param int $user_id
     */
    public function register_user_with_tyk($user_id = null): void
    {
        $user = new Tyk_Portal_User($user_id);
        if ($user->is_logged_in() && !$user->has_tyk_id()) {
            $user->register_with_tyk();
        }
    }

    /**
     * Hook that is fired when this plugin is activated.
     */
    public function activate_plugin(): void
    {
        $this->create_developer_role();
        $this->create_dashboard_page();
    }

    /**
     * Create a role "developer".
     */
    public function create_developer_role(): void
    {
        $result = add_role(self::DEVELOPER_ROLE_NAME, __('Developer', self::TEXT_DOMAIN));
        if (false === $result) {
            trigger_error(sprintf(
                'Could not create role "%s" for plugin %s',
                self::DEVELOPER_ROLE_NAME,
                self::PLUGIN_NAME,
            ));
        }
    }

    /**
     * Create a page for the developer dashboard.
     */
    public function create_dashboard_page(): void
    {
        // @todo check if the page already exists
        $page = array(
            'post_title' => __('Developer Dashboard', self::TEXT_DOMAIN),
            'post_name' => self::DASHBOARD_SLUG,
            'post_content' => '[tyk_dev_dashboard]',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_author' => 1, // @todo get an admin user id here
        );
        $post_id = wp_insert_post($page);
        // @todo we should probably save the slug of the created page here
    }

    /**
     * Are we using Tyk Cloud?
     *
     * @return bool
     */
    public static function is_cloud()
    {
        return self::CONFIGURATION_CLOUD == strtolower(TYK_CONFIGURATION);
    }

    /**
     * Are we using Tyk Hybrid?
     *
     * @return bool
     */
    public static function is_hybrid()
    {
        return self::CONFIGURATION_HYBRID == strtolower(TYK_CONFIGURATION);
    }

    /**
     * Are we using Tyk On-Premise?
     *
     * @return bool
     */
    public static function is_on_premise()
    {
        return self::CONFIGURATION_ON_PREMISE == strtolower(TYK_CONFIGURATION);
    }
}
