## 2024-04-22 - Optimize get_post_meta calls in Course Frontend
**Learning:** Calling `get_post_meta` inside a loop can lead to N+1 queries if the post meta cache was not properly primed beforehand. Although `get_posts` with `-1` or standard WP queries attempt to prime caches, edge cases or specific loop iterations fetching additional metadata not primed can cause hidden performance drains.
**Action:** Always manually call `update_postmeta_cache( wp_list_pluck( $items, 'ID' ) )` when retrieving a list of posts that will have metadata accessed in a subsequent loop, ensuring a single bulk query caches everything needed.

---

## 2024-04-22 - Ensure layout is array if stored as JSON string
**Learning:** When validating variables decoded from JSON, especially inputs going into foreach loops or layout builders, fallback to default types instead of just skipping processing. `is_array( $decoded ) ? $decoded : []` is better than just a conditional re-assignment.
**Action:** Always enforce a type guarantee at boundaries where JSON is decoded and directly manipulated as array.

---

## 2025-01-28 - Optimize post counts with direct wpdb query
**Learning:** Using `WP_Query` solely to get `$q->found_posts` triggers `SQL_CALC_FOUND_ROWS`, which is very slow. This codebase specifically lacked the `no_found_rows` parameter in multiple places, but even so, direct counts are better.
**Action:** When only counting items with specific meta values, use a direct aggregate query like `$wpdb->get_var("SELECT COUNT(*) ...")` instead of `WP_Query` to avoid unnecessary object retrieval and performance overhead.
