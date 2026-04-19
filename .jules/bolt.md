## 2024-04-19 - Unused Heavy Query in Frontend Filter
**Learning:** We found an expensive, uncached `get_posts` query being executed on every course page load simply to calculate a count (`$lessons_count`) that was completely unused. Retrieving full post objects is extremely wasteful when only counting them, and especially when the count is never utilized.
**Action:** Always verify if fetched data is actually consumed, and use `fields => 'ids'` or `wp_count_posts`/custom `COUNT()` queries when merely counting, instead of loading full WP_Post objects into memory.
