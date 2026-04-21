## 2024-05-18 - Prevent LIKE wildcard injection in wpdb queries
**Vulnerability:** User input was directly concatenated with a `%` wildcard in `$wpdb->prepare` for a `LIKE` query in `includes/glossary/frontend.php`.
**Learning:** `LIKE` wildcards `%` and `_` inside the user input are not escaped by `$wpdb->prepare` itself, allowing attackers to broaden queries, which can lead to information disclosure or DoS.
**Prevention:** Always use `$wpdb->esc_like( $input )` before appending wildcards in `LIKE` queries.
