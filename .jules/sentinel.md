## 2024-05-24 - [Information Disclosure in AJAX Error Handlers]
**Vulnerability:** AJAX handlers in `includes/field-news/repo-admin.php` exposed internal server file paths by returning `$e->getFile()` and `$e->getLine()` to the frontend via `wp_send_json_error()`.
**Learning:** Returning raw exception details directly to the client exposes server architecture and sensitive internals, which constitutes a Medium-priority security risk.
**Prevention:** Always use generic error messages for client-facing error responses (e.g. "An error occurred. Check server logs.") and log the detailed exception information server-side using `error_log()` securely.
