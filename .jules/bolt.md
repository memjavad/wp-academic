## 2024-05-28 - [Performance: Memoize Reading Time Calculation]
**Learning:** Calculating reading time dynamically on every page load with `str_word_count(strip_tags())` can be expensive on large academic texts.
**Action:** Caching the result in `post_meta` avoids the overhead, as WP preloads meta data. Using a `save_post` hook ensures the cache invalidates appropriately.
