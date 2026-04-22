Date: 2024-05-24
Vulnerability: Host Header Injection in Rewrite Rules
Learning: The `$_SERVER['HTTP_HOST']` variable is easily manipulated by attackers since it comes directly from the HTTP Request Host header. Using it to generate server-side configuration like `.htaccess` hotlink protection exposes the application to Host Header Injection. An attacker can set arbitrary hosts, potentially corrupting configuration or whitelisting malicious domains.
Prevention: Always use `site_url()` or `home_url()` combined with safe parsing mechanisms like `wp_parse_url()` to get the canonical host name safely, rather than relying on `$_SERVER['HTTP_HOST']`.
