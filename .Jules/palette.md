## 2026-04-19 - Added ARIA labels to social sharing buttons
**Learning:** Icon-only social sharing buttons rendered dynamically in PHP lacked accessible names, meaning screen readers couldn't identify the service name or purpose (like PDF download or RIS export).
**Action:** Always include localized `aria-label` attributes for links or buttons that rely solely on icons, particularly when generating dynamic HTML elements.
## 2026-04-30 - Add ARIA Labels to Icon-Only Social Share Links
**Learning:** Icon-only social share links (using SVG icons) frequently lack accessible names, making them unreadable to screen readers. In WordPress plugins, standard functions like `esc_attr_e()` should be used to provide localized, safely escaped text for `aria-label` attributes.
**Action:** Whenever identifying or implementing icon-only interactive elements (like buttons or links), explicitly verify that an `aria-label` or visually hidden text is present, and use localization functions when providing text.
