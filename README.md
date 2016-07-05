# How to activate

1. *ProfilePress Lite* plugin aktivieren
	* ProfilePress Einstellungen:
		* Redirections / Login: 
2. *Tyk Developer Portal* plugin aktivieren
3. *Einstellungen > Allgemein*
	* *Standardrolle eines neuen Benutzers* = "Entwickler"
	* *Mitgliedschaft* = "Jeder kann sich registrieren"
4. Tyk konfigurieren in `wp-config.php`:
```
    define( 'TYK_API_ENDPOINT', 'https://admin.cloud.tyk.io/api/' );
    define( 'TYK_API_KEY', '<key>' );
    define( 'TYK_AUTO_APPROVE_KEY_REQUESTS', true );
```
`<key>` erhält man unter *Tyk Dashboard > Users > [User] > Edit > Tyk Dashboard API Access Credentials*.
Wobei `[User]` ein dedizierter User für diesen Zugang sein sollte (z.B. *Dev Portal Dashboard Manager*).


## Anderes

1. *ODPCH temporär* theme aktivieren
2. Die automatisch erstellte Seite "Dashboard" bearbeiten und Template auf *Developer Dashboard* setzen