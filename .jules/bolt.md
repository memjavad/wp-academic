## 2024-04-18 - Optimize redundant get_posts calls in course curriculum
**Learning:** In WordPress plugins, when fetching data that is needed for multiple reasons (e.g. counting vs listing), doing separate count(get_posts(...)) and get_posts(...) with the same parameters on the same page load will execute two DB queries.
**Action:** Store the result of get_posts in a variable early on if you need the count and the list in the same request, and use count($lessons) instead of querying twice.
