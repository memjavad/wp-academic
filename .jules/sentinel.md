## 2024-05-18 - Missing Authorization Check in AJAX Handlers
**Vulnerability:** The `ajax_fetch_studies` and `ajax_generate` endpoints in `includes/field-news/repo-admin.php` verified a nonce but failed to verify if the user had sufficient privileges (e.g., `manage_options`) to perform these administrative tasks. This could allow any authenticated user (or potentially unauthenticated if nonces were leaked or weak) to trigger heavy scraping tasks or generate posts.
**Learning:** Checking a nonce only verifies intent and protects against CSRF; it does not verify authorization. Any function performing privileged operations must explicitly check the user's capabilities.
**Prevention:** Always pair `check_ajax_referer` with a `current_user_can` check for administrative AJAX handlers.

---

Date: 2024-05-24
Vulnerability: Host Header Injection in Rewrite Rules
Learning: The `$_SERVER['HTTP_HOST']` variable is easily manipulated by attackers since it comes directly from the HTTP Request Host header. Using it to generate server-side configuration like `.htaccess` hotlink protection exposes the application to Host Header Injection. An attacker can set arbitrary hosts, potentially corrupting configuration or whitelisting malicious domains.
Prevention: Always use `site_url()` or `home_url()` combined with safe parsing mechanisms like `wp_parse_url()` to get the canonical host name safely, rather than relying on `$_SERVER['HTTP_HOST']`.
