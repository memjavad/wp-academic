## 2024-04-22 - Optimize get_post_meta calls in Course Frontend & Builder
**Learning:** Calling `get_post_meta` inside a loop can lead to N+1 queries if the post meta cache was not properly primed beforehand. Although `get_posts` with `-1` or standard WP queries attempt to prime caches, edge cases or specific loop iterations fetching additional metadata not primed can cause hidden performance drains.
**Action:** When fetching an array of posts, always use `update_postmeta_cache( wp_list_pluck( $items, 'ID' ) )` before a `foreach` loop that relies on metadata to fetch all necessary postmeta in a single query.

---

## 2024-04-22 - Ensure layout is array if stored as JSON string
**Learning:** When validating variables decoded from JSON, especially inputs going into foreach loops or layout builders, fallback to default types instead of just skipping processing. `is_array( $decoded ) ? $decoded : []` is better than just a conditional re-assignment.
**Action:** Always enforce a type guarantee at boundaries where JSON is decoded and directly manipulated as array.

---

## 2025-01-28 - Optimize post counts with direct wpdb query
**Learning:** Using `WP_Query` solely to get `$q->found_posts` triggers `SQL_CALC_FOUND_ROWS`, which is very slow. This codebase specifically lacked the `no_found_rows` parameter in multiple places, but even so, direct counts are better.
**Action:** When only counting items with specific meta values, use a direct aggregate query like `$wpdb->get_var("SELECT COUNT(*) ...")` instead of `WP_Query` to avoid unnecessary object retrieval and performance overhead.

---

## 2024-04-19 - Unused Heavy Query in Frontend Filter
**Learning:** We found an expensive, uncached `get_posts` query being executed on every course page load simply to calculate a count (`$lessons_count`) that was completely unused. Retrieving full post objects is extremely wasteful when only counting them, and especially when the count is never utilized.
**Action:** Always verify if fetched data is actually consumed, and use `fields => 'ids'` or `wp_count_posts`/custom `COUNT()` queries when merely counting, instead of loading full WP_Post objects into memory.

---

## 2024-06-25 - Prevent expensive SQL_CALC_FOUND_ROWS in WP_Query
**Learning:** In WP_Query, if pagination is not needed, `SQL_CALC_FOUND_ROWS` is still executed by default, which can cause significant database overhead on large tables.
**Action:** Always add `'no_found_rows' => true` to `WP_Query` arguments when the query results do not require pagination (i.e. when we don't need to know the total number of matching posts). Also remember to use `$q->post_count` instead of `$q->found_posts` if just counting the retrieved items.

---

## 2024-05-28 - Performance: Memoize Reading Time Calculation
**Learning:** Calculating reading time dynamically on every page load with `str_word_count(strip_tags())` can be expensive on large academic texts.
**Action:** Caching the result in `post_meta` avoids the overhead, as WP preloads meta data. Using a `save_post` hook ensures the cache invalidates appropriately.
