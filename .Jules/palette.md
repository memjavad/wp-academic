## 2026-04-19 - Added ARIA labels to social sharing buttons
**Learning:** Icon-only social sharing buttons rendered dynamically in PHP lacked accessible names, meaning screen readers couldn't identify the service name or purpose (like PDF download or RIS export).
**Action:** Always include localized `aria-label` attributes for links or buttons that rely solely on icons, particularly when generating dynamic HTML elements.

## 2024-05-24 - Interactive Accordions Missing Accessibility
**Learning:** Non-native interactive elements (like `<h5>` used as sidebar accordion toggles) often miss critical accessibility attributes, rendering them invisible or inoperable to screen readers and keyboard users.
**Action:** When creating or identifying custom interactive toggles in JavaScript, always programmatically add `role="button"`, `tabindex="0"`, listen for both `click` and `keypress` (Enter/Space), and dynamically update `aria-expanded` state.
