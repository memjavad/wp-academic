## 2026-04-22 - Div-based Progress Bars
**Learning:** When using divs to create progress bars (like `.wpa-course-progress-bar`), screen readers won't recognize them as progress indicators by default.
**Action:** Always add `role="progressbar"`, `aria-valuenow`, `aria-valuemin`, and `aria-valuemax` attributes to div-based progress bars so screen reader users understand the context and current progress.
