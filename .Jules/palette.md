## 2026-04-19 - Added ARIA labels to social sharing buttons
**Learning:** Icon-only social sharing buttons rendered dynamically in PHP lacked accessible names, meaning screen readers couldn't identify the service name or purpose (like PDF download or RIS export).
**Action:** Always include localized `aria-label` attributes for links or buttons that rely solely on icons, particularly when generating dynamic HTML elements.
## 2026-04-27 - Added ARIA labels to slider navigation buttons
**Learning:** Icon-only slider navigation buttons generated dynamically in PHP lacked accessible names, making them difficult for screen reader users to understand their purpose.
**Action:** Always include localized `aria-label` attributes for structural or functional icon-only buttons (like slider controls) to improve keyboard and screen reader accessibility.
