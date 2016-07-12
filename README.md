# How to activate

1. Activate *ProfilePress Lite* plugin
2. Activate *Tyk Developer Portal* plugin. This should create the role "Developer" and the page "Dashboard".
3. In WordPress: *Settings > General*
	* *New User Default Role* = "Developer"
	* *Membership* = "Anyone can register"
4. Configure ProfilePress settings:
	* Redirections / Login: Dashboard
	* Redirections / Logout: Log In
4. In Tyk Dashboard: *System Management > Policies*
	* Name your policies accordingly, these will be shown to user for access token registration
	* Create a dedicated management user at *System Management > Users*, save, then generate an access
	token for this user on the same page.
5. Configure Tyk in `wp-config.php`:
`<key>` is the access token you created in step 4.
```php
    define( 'TYK_API_ENDPOINT', 'https://admin.cloud.tyk.io/api/' );
    define( 'TYK_API_KEY', '<key>' );
    // automatically approve key requests
    // handling for approving key requests is not  implemented on the plugin side
    define( 'TYK_AUTO_APPROVE_KEY_REQUESTS', true );
```

## Anderes

Diesen Teil der Anleitung auslagern an einen anderen Ort

1. *ODPCH temporär* theme aktivieren
2. Die automatisch erstellte Seite "Dashboard" bearbeiten und Template auf *Developer Dashboard* setzen