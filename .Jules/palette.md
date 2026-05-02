## 2026-04-19 - Added ARIA labels to social sharing buttons
**Learning:** Icon-only social sharing buttons rendered dynamically in PHP lacked accessible names, meaning screen readers couldn't identify the service name or purpose (like PDF download or RIS export).
**Action:** Always include localized `aria-label` attributes for links or buttons that rely solely on icons, particularly when generating dynamic HTML elements.
## 2026-05-02 - FAQ Accordion Accessibility
**Learning:** Adding ARIA attributes (aria-expanded, aria-controls) and corresponding JavaScript logic to visually animated accordions ensures they are usable by screen readers and properly communicate state changes.
**Action:** When implementing show/hide UI components like accordions, always pair visual state changes (like max-height animations) with semantic ARIA attribute updates in JavaScript to maintain accessibility.
