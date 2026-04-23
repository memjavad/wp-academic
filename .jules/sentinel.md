## 2024-05-18 - [HIGH] Fix SQL Injection
**Vulnerability:** Wildcard injection in `WPDB::prepare()` with `LIKE`.
**Learning:** Using `$wpdb->prepare()` with `%s` and string concatenation for wildcards (e.g. `$letter . "%"`) leaves queries open to wildcard injection, allowing attackers to potentially bypass matching rules and retrieve unexpected rows or craft expensive database queries.
**Prevention:** Always use `$wpdb->esc_like()` to sanitize inputs meant to be used inside `LIKE` conditions *before* appending wildcard characters.
