## 2026-04-22 - [Add PHPUnit testing for feature toggles]
**Learning:** Testing WordPress plugin features requires careful mocking of internal WP functions. Tools like `Brain\Monkey` are perfect for this as they allow flexible stubbing without bootstrapping the whole WP installation.
**Action:** When adding unit tests for plugins, set up a dedicated `bootstrap.php` that establishes necessary dummy functions for essential WordPress features (like `add_shortcode`, `esc_url`, `wp_verify_nonce`, etc.) that `wp-academic-post-enhanced.php` relies on during loading.
