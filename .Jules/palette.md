## 2024-04-20 - Missing Focus-Visible Styles
**Learning:** Found that `.wpa-btn` missing `:focus-visible` styles can make keyboard navigation difficult, and using `:focus` might trigger outlines on mouse clicks which can be annoying. We should add `:focus-visible` to standard interactive elements like buttons and inputs.
**Action:** Always include `.wpa-btn:focus-visible` styles with sensible outlines (e.g., `outline: 2px solid var(--wpa-accent); outline-offset: 2px;`) to ensure proper accessibility for keyboard users without degrading mouse user experience.
