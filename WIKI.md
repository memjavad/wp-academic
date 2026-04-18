# WP Academic Post Enhanced - Official Wiki

Welcome to the official documentation for the **WP Academic Post Enhanced** plugin. This wiki provides a comprehensive guide for administrators, content creators, and developers to leverage the full power of this academic-focused WordPress toolkit.

---

## 📖 Table of Contents
1.  [Overview](#-overview)
2.  [Core Modules](#-core-modules)
    *   [Homepage Builder](#homepage-builder)
    *   [Field News AI](#field-news-ai)
    *   [Citation & PDF Engine](#citation--pdf-engine)
    *   [Learning Management System (LMS)](#learning-management-system-lms)
    *   [Glossary System](#glossary-system)
3.  [Configuration Guide](#-configuration-guide)
    *   [API Setup](#api-setup)
    *   [Unified Layout](#unified-layout)
4.  [Developer Reference](#-developer-reference)
    *   [Shortcodes](#shortcodes)
    *   [CSS Design System](#css-design-system)
5.  [Versioning & Maintenance](#-versioning--maintenance)

---

## 🌟 Overview
**WP Academic Post Enhanced** is a high-performance WordPress plugin designed specifically for the academic and research sector. It transforms a standard WordPress site into a professional academic portal with structured data, AI-powered news generation, a full LMS, and a unified design system.

**Author:** Mohammed looti  
**Version:** 3.9.4  
**Primary Focus:** SEO, Academic Credibility, and User Experience.

---

## 🛠 Core Modules

### Homepage Builder
The plugin includes a **modular block-based homepage builder** that allows you to create premium, responsive academic landing pages without high-overhead page builders.

**Available Blocks:**
- **Hero:** Impactful headline with background media and call-to-action.
- **Slider:** Advanced slider with multiple styles (*Classic, Fade, Card Carousel*).
- **Stats:** Animated counters for key institutional metrics.
- **News:** Automated grid or list of Academic News (`wpa_news`).
- **Courses:** Visual catalog of academic courses (`wpa_course`).
- **Glossary:** Quick-access terms with a "badged" interface.
- **Partners & Team:** Logo strips and faculty profiles.
- **FAQ:** Standardized academic accordions.

*Access via: Academic Post > Theme Builder*

### Field News AI
The ultimate tool for research-driven content. It uses **Google Gemini AI** to synthesize real academic data from the **Scopus API** into readable news articles.

- **Auto-Generation:** Schedule AI to write articles based on specific research tags.
- **Default Post Author:** Choose a specific user in WordPress to be the author of all AI-generated posts.
- **Topic Groups:** Define multiple research terms (e.g., "Quantum Computing", "Clinical Psychology") and map them to specific WordPress categories.
- **Source Filtering:** Filter studies by date range, minimum citations, and Open Access status.
- **AI Peer Review:** Optional second AI pass to review for tone, factual accuracy, and academic rigor.
- **Fact-Checking Engine:** Cross-references generated content with original study abstracts to ensure zero hallucination.
- **Image Integration:** Automatically fetches relevant high-quality cover images via Unsplash.

### Citation & PDF Engine
Enhance your site's academic authority by allowing readers to cite and download your content.

- **Global Styles:** Support for APA, MLA, Harvard, Chicago, and Vancouver.
- **PDF Generation:** One-click "Download PDF" with custom cover pages and academic formatting.
- **RIS Export:** Export citations directly to Reference Managers (EndNote, Zotero).
- **Cache Management:** Automatically prunes old PDF files to save server space (Default: 50 file limit).

### Learning Management System (LMS)
A lightweight but powerful LMS built directly into the plugin.

- **Hierarchical Content:** Manage `Courses` -> `Lessons` -> `Quizzes`.
- **Student Dashboard:** Dedicated profile page for enrolled students to track progress.
- **Distraction-Free Mode:** Lessons use a specialized "Focus" template with sidebar navigation.
- **Access Control:** Manage lesson availability based on enrollment status.

### Glossary System
Build a searchable database of academic terminology.
- **Shortcode:** `[wpa_glossary_list]` displays an interactive list of terms.
- **Automatic Linking:** (Optional) Automatically links keywords in your content to their glossary definitions.

---

## 🚀 Quick Start
1.  **Install & Activate:** Upload the plugin to your WordPress site.
2.  **API Integration:** Navigate to `Academic Post > Field News AI` and enter your Scopus and Google Gemini keys.
3.  **Unified Theme:** Go to `Academic Post > Custom Theme`, set your Primary Accent Color, and enable "Apply Globally".
4.  **Create a Course:** Go to `Courses > Add New`, define your curriculum, and add lessons.
5.  **Build your Homepage:** Go to `Academic Post > Theme Builder` and add your Hero, Slider, and News blocks.

---

## ⚙ Configuration Guide

### API Setup
To unlock all features, configure the following keys in **Academic Post > Field News AI**:
1.  **Google Gemini API:** Required for AI content generation.
2.  **Scopus API:** Required for fetching real research data.
3.  **Unsplash API:** Required for automated article cover images.
4.  **Institutional Token:** (Optional) If your Scopus access is institutional, provide the token for higher rate limits.

### Unified Layout
The plugin enforces a consistent "Academic Style" across the site.
- **Settings:** Enable "Apply Globally" in **Custom Theme** settings to wrap all pages in the unified header and footer.
- **Branding:** Upload logos and set primary accent colors (`--wpa-accent`) in the theme settings.

---

## 💻 Developer Reference

### Shortcodes
| Shortcode | Description |
| :--- | :--- |
| `[wpa_toc]` | Manually inserts the Table of Contents. |
| `[wpa_citation]` | Displays the citation box for the current post. |
| `[wpa_glossary_list]` | Renders the full glossary terms. |
| `[wpa_student_dashboard]` | Displays the student's enrolled courses and progress. |
| `[wpa_social_share]` | Inserts social sharing buttons. |

### CSS Design System
The plugin uses a unified core based on CSS Variables. Always use these in custom CSS to ensure consistency:

**Colors:**
- `--wpa-accent`: Primary brand color (Default: Blue).
- `--wpa-accent-dark`: Darker shade for hovers.
- `--wpa-text-main`: Body text color.
- `--wpa-text-muted`: Gray text for meta information.
- `--wpa-success`: Success alerts and status.

**Layout & Shapes:**
- `--wpa-radius-lg`: 16px corner rounding (Standard for cards).
- `--wpa-radius`: 8px corner rounding.
- `--wpa-border-color`: UI borders and dividers.
- `--wpa-shadow`: Subtle base shadow.
- `--wpa-shadow-hover`: Elevating shadow for interactive items.

**Typography:**
- `--wpa-font-body`: The primary sans-serif font stack.
- `--wpa-font-heading`: Bold heading stack.

---

## 🔄 Versioning & Maintenance
The plugin uses an internal update checker. **Always increment the version** in `wp-academic-post-enhanced.php` when pushing updates to ensure settings and database migrations are processed correctly.

© 2026 Mohammed looti. Built for the Academic Sector.
