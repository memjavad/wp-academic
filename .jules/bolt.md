## 2025-01-28 - Optimize post counts with direct wpdb query
**Learning:** Using `WP_Query` solely to get `$q->found_posts` triggers `SQL_CALC_FOUND_ROWS`, which is very slow. This codebase specifically lacked the `no_found_rows` parameter in multiple places, but even so, direct counts are better.
**Action:** When only counting items with specific meta values, use a direct aggregate query like `$wpdb->get_var("SELECT COUNT(*) ...")` instead of `WP_Query` to avoid unnecessary object retrieval and performance overhead.
