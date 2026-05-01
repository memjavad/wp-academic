## 2026-04-19 - Added ARIA labels to social sharing buttons
**Learning:** Icon-only social sharing buttons rendered dynamically in PHP lacked accessible names, meaning screen readers couldn't identify the service name or purpose (like PDF download or RIS export).
**Action:** Always include localized `aria-label` attributes for links or buttons that rely solely on icons, particularly when generating dynamic HTML elements.
## 2026-05-01 - Added ARIA labels to dynamic slider dots
**Learning:** Dynamically created interactive elements in JS (like slider dots) often miss accessibility attributes because they aren't written in semantic HTML. Screen readers just hear 'button' without context.
**Action:** Always use `.setAttribute('aria-label', ...)` when generating icon-only or non-text interactive elements in JavaScript.
