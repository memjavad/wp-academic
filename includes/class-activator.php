<?php
/**
 * Fired during plugin activation and updates.
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPA_Activator {

    /**
     * Initialize defaults for all modules.
     */
    public static function activate() {
        self::init_settings();
        
        // Mark current version
        update_option( 'wpa_version', WPA_VERSION );
        
        // Flush rewrite rules for CPTs
        flush_rewrite_rules();
    }

    /**
     * Check if an update is needed.
     */
    public static function check_update() {
        $saved_version = get_option( 'wpa_version', '1.0' );
        
        if ( version_compare( $saved_version, WPA_VERSION, '<' ) ) {
            self::init_settings();
            update_option( 'wpa_version', WPA_VERSION );
            
            // Flush rewrite rules to ensure new CPTs and slugs are recognized
            flush_rewrite_rules();
        }
    }

    /**
     * Initialize or fill missing settings with defaults.
     */
    public static function init_settings() {
        // 1. Feature Toggles (Main Autoloader)
        $toggles = [
            'wp_academic_post_enhanced_author_enabled'   => 1,
            'wp_academic_post_enhanced_citation_enabled' => 1,
            'wp_academic_post_enhanced_schema_enabled'   => 1,
            'wpa_social_enabled'                         => 1,
            'wp_academic_post_enhanced_toc_enabled'      => 1,
            'wp_academic_post_enhanced_smtp_enabled'     => 0,
            'wp_academic_post_enhanced_advanced_enabled' => 1,
            'wpa_reading_enabled'                        => 1,
            'wpa_course_enabled'                         => 1,
            'wpa_field_news_enabled'                     => 1,
            'wpa_homepage_enabled'                       => 1,
            'wpa_glossary_enabled'                       => 1,
        ];

        foreach ( $toggles as $option => $default ) {
            if ( get_option( $option ) === false ) {
                update_option( $option, $default );
            }
        }

        // 2. Module Settings Arrays
        
        // Field News Defaults
        $news_defaults = [
            'target_language' => 'ar',
            'post_status'     => 'publish',
            'scopus_date_range' => 'all',
            'scopus_doc_type'   => 'ar',
            'selection_strategy' => 'impact',
            'selection_batch_size' => 5,
            'google_model_selector' => 'gemini-2.0-flash',
            'google_model_title'    => 'gemini-2.0-flash',
            'google_model_body'     => 'gemini-2.0-flash',
            'google_model_tags'     => 'gemini-2.0-flash',
            'ai_tone'               => 'professional',
            'ai_length'             => 'medium',
            'ai_audience'           => 'general',
            'ai_structure'          => 'news',
            'auto_post_enable'      => 0,
            'auto_post_interval'    => 'daily',
            'repo_auto_fetch'       => 0,
            'repo_fetch_interval'   => 'daily',
            'meta_box_enable'       => 1,
            'meta_box_position'     => 'after',
        ];
        self::ensure_option( 'wpa_field_news_settings', $news_defaults );

        // Theme / Homepage Defaults
        $theme_defaults = [
            'enable_homepage' => 1,
            'homepage_force_builder' => 1,
            'enable_news_template' => 1,
            'enable_news_archive'  => 1,
            'enable_global_header_footer' => 1,
            'header_sticky' => 1,
            'accent_color' => '#2563eb',
            'header_bg' => 'rgba(255, 255, 255, 0.95)',
            'header_text_color' => '#1f2937',
            'footer_bg' => '#f8fafc',
            'font_heading' => 'Lexend',
            'font_body' => 'Inter',
            'font_size_body' => '1.15',
            'line_height_body' => '1.8',
            'container_width' => 'standard',
            'news_meta_box_title' => 'About the Study',
            'news_show_meta_journal' => 1,
            'news_show_meta_authors' => 1,
            'news_show_meta_date' => 1,
            'news_show_meta_type' => 1,
            'news_show_author_box' => 1,
            'page_sidebar_pos' => 'none',
            'page_show_title' => 1,
            'page_show_featured' => 1,
            'post_sidebar_pos' => 'none',
            'post_show_title' => 1,
            'post_show_meta' => 1,
        ];
        self::ensure_option( 'wpa_homepage_settings', $theme_defaults );

        // Reading Experience Defaults
        $reading_defaults = [
            'time_enabled' => 1,
            'time_label' => 'min read',
            'time_position' => 'before_content',
            'progress_enabled' => 1,
            'progress_color' => '#2563eb',
            'progress_height' => '4',
            'progress_position' => 'top',
            'resizer_enabled' => 1,
            'resizer_position' => 'before_content',
        ];
        self::ensure_option( 'wpa_reading_settings', $reading_defaults );

        // Citation Defaults
        $citation_defaults = [
            'enabled' => 1,
            'title' => 'Cite this article',
            'styles' => ['apa', 'mla', 'chicago'],
            'position' => 'after_content',
            'default_style' => 'apa',
            'background_color' => '#f9f9f9',
            'border_color' => '#e2e8f0',
            'text_color' => '#1f2937',
            'post_types' => ['post', 'wpa_news'],
            'pdf_download_enabled' => 1,
            'pdf_font' => 'amiri',
            'pdf_elements' => ['header', 'footer', 'cover_title', 'cover_meta', 'cover_citation'],
        ];
        self::ensure_option( 'wpa_citation_settings', $citation_defaults );

        // Social Defaults
        $social_defaults = [
            'enabled' => 1,
            'platforms' => ['facebook', 'twitter', 'linkedin', 'whatsapp'],
            'style' => 'minimal',
            'shape' => 'rounded',
            'size' => 'medium',
            'position' => 'after_content',
        ];
        self::ensure_option( 'wpa_social_settings', $social_defaults );

        // TOC Defaults
        $toc_defaults = [
            'enabled' => 1,
            'title' => 'Table of Contents',
            'post_types' => ['post', 'page', 'wpa_news'],
            'min_headings' => 2,
        ];
        self::ensure_option( 'wpa_toc_settings', $toc_defaults );

        // Author Defaults
        $author_defaults = [
            'enabled' => 1,
            'show_avatar' => 1,
            'show_bio' => 1,
        ];
        self::ensure_option( 'wpa_author_settings', $author_defaults );

        // 3. Glossary & Linkify Defaults (NEW)
        $glossary_defaults = [
            'enabled' => 1,
            'linkify_enabled' => 'yes',
            'linkify_post_types' => ['post', 'wpa_news', 'wpa_course'],
            'linkify_term_limit' => 2,
            'linkify_on_front_page' => 'no',
            'linkify_case_sensitive' => 'no',
        ];
        
        // Use individual options matching helpers.php
        if ( get_option( 'wpa_glossary_activate_linkify' ) === false ) {
            update_option( 'wpa_glossary_activate_linkify', 'yes' );
        }
        if ( get_option( 'wpa_glossary_linkify_post_types' ) === false ) {
            update_option( 'wpa_glossary_linkify_post_types', ['post', 'wpa_news', 'wpa_course'] );
        }
    }

    /**
     * Helper to merge defaults with existing options.
     */
    private static function ensure_option( $option_name, $defaults ) {
        $current = get_option( $option_name );
        
        if ( $current === false ) {
            update_option( $option_name, $defaults );
        } elseif ( is_array( $current ) ) {
            $updated = array_merge( $defaults, $current );
            if ( $updated !== $current ) {
                update_option( $option_name, $updated );
            }
        }
    }
}
