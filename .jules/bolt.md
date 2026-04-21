## 2024-06-25 - Prevent expensive SQL_CALC_FOUND_ROWS in WP_Query
**Learning:** In WP_Query, if pagination is not needed, `SQL_CALC_FOUND_ROWS` is still executed by default, which can cause significant database overhead on large tables.
**Action:** Always add `'no_found_rows' => true` to `WP_Query` arguments when the query results do not require pagination (i.e. when we don't need to know the total number of matching posts). Also remember to use `$q->post_count` instead of `$q->found_posts` if just counting the retrieved items.
