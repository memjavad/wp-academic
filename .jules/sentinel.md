## 2023-11-06 - Wildcard Injection via LIKE query in WordPress

**Vulnerability:** Found a LIKE query where user input (`$letter`) was concatenated directly with a `%` wildcard inside `$wpdb->prepare` without escaping the wildcards: `post_title LIKE %s`, `$letter . '%'`.

**Learning:** `$wpdb->prepare` protects against standard SQL injection by escaping quotes, but it **does not** escape SQL wildcard characters (`%` and `_`). An attacker supplying `%` or `_` can change the query logic, leading to unexpected data exposure or Denial of Service via slow queries.

**Prevention:** Always use `$wpdb->esc_like( $user_input )` before appending wildcard characters (`%` or `_`) when constructing LIKE queries with `$wpdb->prepare`.
