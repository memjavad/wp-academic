## 2024-04-22 - Prevent N+1 Queries in Course Builder
**Learning:** Looping through `$lessons` and calling `get_post_meta()` on each item results in an N+1 query problem, slowing down page loads as the number of lessons increases.
**Action:** When fetching an array of posts, always use `update_postmeta_cache( wp_list_pluck( $posts, 'ID' ) )` before a `foreach` loop that relies on metadata to fetch all necessary postmeta in a single query.
