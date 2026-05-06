## 2026-04-19 - Added ARIA labels to social sharing buttons
**Learning:** Icon-only social sharing buttons rendered dynamically in PHP lacked accessible names, meaning screen readers couldn't identify the service name or purpose (like PDF download or RIS export).
**Action:** Always include localized `aria-label` attributes for links or buttons that rely solely on icons, particularly when generating dynamic HTML elements.
## 2024-05-06 - Adding accessibility to non-native accordion toggles
**Learning:** When using non-native HTML elements (like `<h5>`) as interactive toggles (accordions), adding `role="button"` and `tabindex="0"` is critical for screen reader and keyboard accessibility.
**Action:** Always ensure that custom interactive elements have semantic roles, keyboard navigation (Enter/Space support), and dynamic ARIA attributes (like `aria-expanded`) reflecting their state.
