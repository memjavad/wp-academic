## 2024-06-18 - Replacing `WP_Query` count queries
**Learning:** Using `WP_Query` with `fields => ids` just to read `$q->found_posts` generates slow `SQL_CALC_FOUND_ROWS` queries. While `'no_found_rows' => true` skips the count, we still need the count but don't need the actual post IDs.
**Action:** Replace `WP_Query` with direct `$wpdb` count queries or aggregate queries (e.g. `GROUP BY meta_value`) when only post counts are needed.
