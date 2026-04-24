## 2024-04-20 - Missing Focus-Visible Styles
**Learning:** Found that `.wpa-btn` missing `:focus-visible` styles can make keyboard navigation difficult, and using `:focus` might trigger outlines on mouse clicks which can be annoying. We should add `:focus-visible` to standard interactive elements like buttons and inputs.
**Action:** Always include `.wpa-btn:focus-visible` styles with sensible outlines (e.g., `outline: 2px solid var(--wpa-accent); outline-offset: 2px;`) to ensure proper accessibility for keyboard users without degrading mouse user experience.

---

## 2026-04-21 - Adding context to repetitive screen reader announcements
**Learning:** In lists of features or settings, screen readers can read identical strings (e.g. "Activate" or "Settings") over and over again, leaving the user with no context. Adding `aria-label` attributes that dynamically include the feature name makes the context immediately clear.
**Action:** When adding loops that render interactive elements, always consider adding a dynamic `aria-label` to clarify context for screen reader users.
