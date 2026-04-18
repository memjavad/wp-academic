# Project Overview

This project is a WordPress plugin designed to enhance blog posts for the academic sector, with a strong focus on Search Engine Optimization (SEO), Large Language Models (LLMs), and User Experience. It provides functionality to add structured data, table of contents, author information, citation options, and performance optimizations.

## Key Features

*   **Structured Data:** Adds JSON-LD schema markup to blog posts for better SEO.
*   **Table of Contents (TOC):** Automatically generates a TOC for posts.
*   **Citation:** Provides a way for readers to cite articles in various formats (APA, MLA, etc.) and download citations.
*   **Performance:** Includes critical CSS, database optimization, and asset minification tools.
*   **Reading Experience:** Adds reading time estimates and progress bars.
*   **SMTP:** Configures reliable email sending.
*   **Social Sharing:** Adds social sharing buttons.
*   **Course Management:** A full-featured LMS component for creating courses, lessons, and quizzes.
*   **Field News AI:** Leverages AI (Google Gemini) and academic APIs (Scopus) to generate research-based news articles.
*   **Unified Academic Layout:** Consistent header, footer, and navigation across custom post types (Courses, News) and the homepage.

# Technologies

*   **Backend:** Modern, object-oriented PHP with a modular architecture.
*   **Frontend:** Vanilla JavaScript and CSS. Shared Design System via CSS Variables.
*   **No Build Tools:** The plugin does not require Node.js build steps.
*   **Asset Management:** CSS/JS are organized by module and enqueued conditionally.

# Design System & UI Kit

The plugin uses a unified frontend core to ensure visual consistency and performance.

### Core Assets
*   `assets/css/wpa-global-theme.css`: Central repository for CSS variables and UI utility classes.
*   `assets/css/wpa-builder-frontend.css`: Styles for the Homepage Builder components (Hero, Slider, Stats, etc.).
*   `assets/js/wpa-global-theme.js`: Optimized logic for mobile menus, sticky headers, animations, and the universal slider engine.

### CSS Variables
Always use global variables for consistency:
*   `--wpa-accent`: Primary brand color.
*   `--wpa-text-main`: Default body text color.
*   `--wpa-border-color`: Standardized border color.
*   `--wpa-radius-lg`: Consistent corner rounding.

### UI Components
Prefer these utility classes over creating new styles:
*   **Buttons:** `.wpa-btn` with modifiers `.wpa-btn-primary`, `.wpa-btn-secondary`, `.wpa-btn-outline`, `.wpa-btn-sm`.
*   **Cards:** `.wpa-card` with children `.wpa-card-header`, `.wpa-card-body`, `.wpa-card-footer`.
*   **Alerts:** `.wpa-alert` with variants `.wpa-alert-success`, `.wpa-alert-error`, etc.
*   **Meta:** `.wpa-meta-row` and `.wpa-meta-item` for author/date/stats layouts.

# Templates & Layout

### Unified Header/Footer
Custom post types and the homepage should use the unified layout functions:
*   `wpa_get_header($args)`: Renders the Academic Header (Logo, Sticky Nav, Mobile Menu).
*   `wpa_get_footer($args)`: Renders the Academic Footer (Copyright, Social, JS Logic).

### Custom Templates
The `theme` module (`includes/theme/theme.php`) intercepts the template hierarchy to serve:
*   `templates/academic-homepage.php`: Custom homepage builder.
*   `templates/single-wpa_course.php`: Full-width course layout with hero and curriculum.
*   `templates/single-wpa_lesson.php`: Distraction-free lesson viewer with sidebar navigation.
*   `templates/single-wpa_news.php`: Academic news article layout.
*   `templates/archive-wpa_course.php`: Grid layout for course catalog.
*   `templates/archive-wpa_news.php`: Grid layout for news stories.
*   `templates/page-wpa.php`: Global wrapper for standard Pages/Posts (optional setting).

### Configuration
Admin settings for the global layout are located in:
**Academic Post > Custom Theme**. 
The "Apply Globally" setting enforces this layout across all Academic Post related pages.

# Development

*   **Prerequisites:** Local WordPress environment (Local by Flywheel, XAMPP, etc.).
*   **File Structure:**
    *   `wp-academic-post-enhanced.php`: Main plugin entry point.
    *   `assets/`: CSS and JS files, organized by feature.
    *   `includes/`: Core logic, divided into modules:
        *   `advanced/`: Performance and optimization tools.
        *   `citation/`: Citation generation and handling.
        *   `theme/`: Unified theme logic, templates, and builder.
        *   `reading/`: Reading time and progress features.
        *   `schema/`: JSON-LD Schema markup generation.
        *   `social/`: Social sharing functionality.

# WordPress Hooks Reference

The plugin uses extensive hooks. Key actions include:
*   `wp_head`: Injecting Schema and Critical CSS.
*   `the_content`: Injecting TOC, Citations, and Author info.
*   `admin_menu`: Registering the main dashboard and sub-pages.
*   `widgets_init`: Registering custom widgets.

# Asset Optimization

*   **Conditional Loading:** Assets are enqueued only when the specific feature is active and relevant to the current page.
*   **Minification:** CSS/JS files should be kept minified where possible.
*   **JS Performance:** Use `requestAnimationFrame` for scroll listeners and `IntersectionObserver` for animations.

# Development Roadmap

*   **v2.9 (Current):**
    *   Unified Header & Footer architecture.
    *   Global Design System and CSS Variables.
    *   Integrated AI-powered news generation (Field News AI).
    *   Expanded LMS capabilities with Course Management.
*   **Future:**
    *   Enhanced AI integration for content summaries.
    *   Expanded Schema types.

# Maintenance & Versioning

### **Mandatory: Version Incrementing**
After **EVERY** code change, bug fix, or feature update, you MUST:
1.  **Increment the version number** in the plugin header of `wp-academic-post-enhanced.php`.
2.  **Increment the `WPA_VERSION` constant** in `wp-academic-post-enhanced.php`.
3.  Ensure the new version is higher than the previous one (e.g., 3.0.1 -> 3.0.2).

**Why?** This is critical because the plugin uses an `admin_init` update checker (`WPA_Activator::check_update`). If the version is not incremented, new default settings, database migrations, or permalink flushes will **NOT** run on the production site after transfer.

# WordPress Development Best Practices

## Security
*   **Nonces:** Use for all admin actions.
*   **Sanitization:** Strictly sanitize all inputs (`sanitize_text_field`, `absint`).
*   **Escaping:** Escape all outputs (`esc_html`, `esc_attr`).
*   **Capabilities:** Check `current_user_can('manage_options')` for admin actions.

## Performance
*   **Transients:** Cache expensive queries.
*   **DB Optimization:** Use optimization tools provided in the Advanced module.

## Coding Standards
*   **PSR-4:** Autoloading for classes where possible.
*   **Prefixing:** Ensure all functions/classes are prefixed with `WPA_` or `wp_academic_post_`.