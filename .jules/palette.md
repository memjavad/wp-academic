## 2026-04-19 - Added ARIA labels to social sharing buttons
**Learning:** Icon-only social sharing buttons rendered dynamically in PHP lacked accessible names, meaning screen readers couldn't identify the service name or purpose (like PDF download or RIS export).
**Action:** Always include localized `aria-label` attributes for links or buttons that rely solely on icons, particularly when generating dynamic HTML elements.

## 2024-05-04 - ARIA Labels for Icon-Only Buttons
**Learning:** Icon-only navigation buttons in custom sliders lack accessible names, making them invisible or confusing to screen reader users.
**Action:** Always add localized `aria-label` attributes to icon-only buttons (like slider previous/next controls) to ensure they are accessible.

## 2024-05-04 - ARIA Attributes for FAQ Accordions
**Learning:** Hardcoding `aria-expanded="false"` on accordion buttons is an anti-pattern if the state is not dynamically updated by JavaScript when the accordion opens/closes.
**Action:** Always ensure that `aria-expanded` attributes are toggled dynamically via JavaScript when implementing accordion functionality.
