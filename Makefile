zip:
	rm wp_tyk_dev_portal.zip; zip -r wp_tyk_dev_portal . -x ".*" -x "tests/*" -x "./Makefile" -x "./README.md"