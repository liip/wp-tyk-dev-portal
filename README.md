# How to activate

1. Activate *ProfilePress Lite* plugin
2. Activate *Tyk Developer Portal* plugin. This should create the role "Developer" and the page "Dashboard".
3. In WordPress: *Settings > General*
	* *New User Default Role* = "Developer"
	* *Membership* = "Anyone can register"
4. Configure ProfilePress settings:
	* Redirections / Login: Dashboard
	* Redirections / Logout: Log In
5. In Tyk Dashboard: *System Management > Policies*
	* Name your policies accordingly, these will be shown to user for access token registration
	* Tag policies that developers may register for with `allow_registration`
	* Create a dedicated management user at *System Management > Users*, save, then generate an access
	token for this user on the same page.
6. Review portal settings page
7. Configure Tyk in `wp-config.php`:
`<key>` is the access token you created in step 4.
```php
    define( 'TYK_API_ENDPOINT', 'https://admin.cloud.tyk.io/api/' );
    define( 'TYK_API_KEY', '<key>' );
    define( 'TYK_AUTO_APPROVE_KEY_REQUESTS', true );
```

## Key approval
When `TYK_AUTO_APPROVE_KEY_REQUESTS` is set to `true` developers may register for access tokens autonomously, they will be approved automatically. When set to `false` the key requests must be manually approved by an administrator in the Tyk dashboard. There is no handling for the follow-up, the key (or a rejection message) must be emailed to the developer manually.

## Contributing

### Unit testing

1. Document how to install wp-cli stuff
2. Document bootstrap.php or add to repo
3. Document creating cloud account and configuring policy and api key
4. Document `TYK_TEST_API_POLICY`
