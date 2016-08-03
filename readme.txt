=== Tyk Dev Portal ===
Contributors: teamamboss
Tags: api, api-management, tyk
Requires at least: 4.6
Test up to: 4.6
Stable tag: 4.6
License: MIT
License URI: https://opensource.org/licenses/MIT

Integrates a developer portal of a Tyk API Gateway in your WordPress site

== Description ==

If you are using the [Tyk API Gateway](http://www.tyk.io) and have a WordPress site you can use this plugin to integrate a developer portal into your site. This is handy when your API requires a complementary website with information e.g. about the service and you want the developer portal in the same place. It's main goal is to offer developer sign up and obtaining access tokens from your WordPress site.

This plugin is a work in progress and currently offers the following features:

* automatic developer registration on Tyk when developers sign up in WordPress
* configuration of API policies available for token registration
* developers may request an access token for the available API policies
* automatic or manual approval of key requests
* storage of token (references) by name and API policy
* revoking of tokens by developer

What this plugin does not (try to) offer:

* Management of Tyk API Gateway (the Tyk Dashboard is best suited for that)
* WordPress user registration (there are enough plugins that do that quite well)

= Support =

Please not that we, the plugin authors, cannot offer support for this plugin. The code is on [GitHub](https://github.com/liip/wp-tyk-dev-portal) however and we are happy to accept pull requests fixing bugs or adding functionality. Also feel free to report any [issues](https://github.com/liip/wp-tyk-dev-portal/issues) although we cannot promise when and if they will be fixed.

== Installation ==

1. Upload the plugin zip file or install the plugin through the WordPress plugins screen directly
2. Optional: choose and install a plugin that offers a better registration experience for WordPress users. This plugin was tested with [ProfilePress](https://wordpress.org/plugins/ppress/) and [Profile Builder](https://wordpress.org/plugins/profile-builder/), it should work with most or any registration/profile plugin though.
3. Activate the plugins through the 'Plugins' screen in WordPress. Activation of this plugin should have triggered the creation of the user role "Developer" an the page "Developer Dashboard".
4. Setup your Tyk Gateway in the Tyk Dashboard: *System Management > Policies*
	* Name your policies accordingly, these will be shown to user for access token registration
	* Tag policies that developers may register for with `allow_registration`
	* Create a dedicated management user at *System Management > Users*, save, then generate an access
	token for this user on the same page.
5. Review portal settings page
6. Finally, add the following configuration to your `wp-config.php` file:

```php
define( 'TYK_API_ENDPOINT', 'http://odpch-api.begasoft.ch:3000/api/' );
define( 'TYK_API_KEY', '8b259a9a2c3e493a69d4e6386ef33b30' );
define( 'TYK_AUTO_APPROVE_KEY_REQUESTS', true );
```

== Screenshots ==




== Changelog ==

= 1.0 =
* Initial release offering the features mentioned in the description