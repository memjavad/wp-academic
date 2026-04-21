## 2026-04-21 - Adding context to repetitive screen reader announcements
**Learning:** In lists of features or settings, screen readers can read identical strings (e.g. "Activate" or "Settings") over and over again, leaving the user with no context. Adding `aria-label` attributes that dynamically include the feature name makes the context immediately clear.
**Action:** When adding loops that render interactive elements, always consider adding a dynamic `aria-label` to clarify context for screen reader users.
