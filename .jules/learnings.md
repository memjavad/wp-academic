# 2024 - Dynamic Form Re-indexing

**Learning:** When dealing with dynamic list forms (add/remove/sortable), it is safer and more robust to use a single `regex` replace against the `name` attribute of input elements (`oldName.replace(/wpa_quiz\[.*?\]/, 'wpa_quiz[' + index + ']')`) rather than explicitly querying and altering elements by their specific types (`input[type="radio"]`, `textarea`, etc).

**Action:** Whenever implementing a re-index or re-order functionality in the UI for arrays that will be submitted to a PHP backend, rely on attribute manipulation targeting the prefix rather than hardcoding DOM structures. Always verify form logic before planning edits.
