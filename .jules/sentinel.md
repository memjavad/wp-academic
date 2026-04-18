## 2024-05-18 - [SQL Wildcard Injection in Glossary Search]
**Vulnerability:** A `LIKE` clause using unsanitized user input (`$letter . '%'`) was discovered in `includes/glossary/frontend.php` allowing for wildcard injection (`%` or `_`).
**Learning:** Even when using `$wpdb->prepare` with `%s`, if the resulting string is used in a `LIKE` clause with wildcards, the user input itself must have its own wildcards escaped using `$wpdb->esc_like()` to prevent Denial of Service or unintended data disclosure.
**Prevention:** Always use `$wpdb->esc_like( $input )` before appending `%` or `_` and using it in a `$wpdb->prepare` query with `LIKE %s`.
