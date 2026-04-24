## 2024-04-18 - Citation Tabs Accessibility & Usability Improvements
**Learning:**
1. Using `display: none` on radio inputs for custom tabs breaks keyboard accessibility because the inputs become un-focusable. Screen reader users and keyboard navigators cannot interact with the tabs.
2. Adding a "Copy" button inside citation blocks with `aria-live="polite"` improves the experience significantly, as it provides immediate feedback ("Copied!") that screen readers can announce.
**Action:**
1. Use visually-hidden CSS properties (like absolute positioning, 1px size, and `clip`) instead of `display: none` for inputs that need to remain focusable for keyboard accessibility. Add a `:focus-visible` state to visually highlight the active label.
2. Ensure interactive utility buttons (like "Copy") use `aria-live` to provide audible state changes for screen reader users.
