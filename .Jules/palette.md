## 2026-04-19 - Added ARIA labels to social sharing buttons
**Learning:** Icon-only social sharing buttons rendered dynamically in PHP lacked accessible names, meaning screen readers couldn't identify the service name or purpose (like PDF download or RIS export).
**Action:** Always include localized `aria-label` attributes for links or buttons that rely solely on icons, particularly when generating dynamic HTML elements.
## 2026-04-28 - Adding aria-labels to icon-only buttons
**Learning:** When adding accessibility attributes like aria-label to frontend components or UI elements in WordPress templates, always use translation functions with escaping, such as `esc_attr_e()`, to ensure security and localization.
**Action:** Use `esc_attr_e()` for ARIA labels in WordPress themes.
