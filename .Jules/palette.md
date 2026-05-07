## 2026-04-19 - Added ARIA labels to social sharing buttons
**Learning:** Icon-only social sharing buttons rendered dynamically in PHP lacked accessible names, meaning screen readers couldn't identify the service name or purpose (like PDF download or RIS export).
**Action:** Always include localized `aria-label` attributes for links or buttons that rely solely on icons, particularly when generating dynamic HTML elements.
## 2024-05-07 - Add ARIA Labels to Social Share Links
**Learning:** Found social share buttons built strictly with raw SVG icons and no text in `templates/single-wpa_news.php`, which resulted in completely inaccessible links for screen reader users. The links also used `target="_blank"` without `rel="noopener noreferrer"`.
**Action:** Always ensure icon-only buttons include descriptive `aria-label` attributes using localized strings (`esc_attr_e()`), and attach `rel="noopener noreferrer"` to any dynamically generated off-site social links for security and performance.
