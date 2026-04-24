## 2024-05-18 - Prevent Host Header Injection in rewrite rules
**Vulnerability:** The unvalidated `$_SERVER['HTTP_HOST']` variable was used directly in `.htaccess` rewrite rules, which could allow an attacker to perform Host Header Injection if they supplied a malicious Host header.
**Learning:** Never trust the client-provided Host header when constructing configuration files or redirect rules. Always use a canonical value (e.g., from `site_url()`).
**Prevention:** Instead of `$_SERVER['HTTP_HOST']`, use `wp_parse_url( site_url(), PHP_URL_HOST )` to safely extract the canonical hostname when writing server configurations.
