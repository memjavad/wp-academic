<?php
/**
 * Theme Module Admin
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once plugin_dir_path( __FILE__ ) . 'class-theme-builder.php';
require_once plugin_dir_path( __FILE__ ) . 'theme-metaboxes.php';

/**
 * Register Admin Menu.
 */
function wpa_homepage_add_admin_menu() {
    add_submenu_page(
        'wp-academic-post-enhanced',
        __( 'Custom Theme', 'wp-academic-post-enhanced' ),
        __( 'Theme', 'wp-academic-post-enhanced' ),
        'manage_options',
        'wp-academic-post-enhanced-homepage',
        'wpa_homepage_settings_page'
    );
}
add_action( 'admin_menu', 'wpa_homepage_add_admin_menu' );

/**
 * Register Settings.
 */
function wpa_homepage_register_settings() {
    register_setting( 'wpa_homepage_options', 'wpa_homepage_settings' );
    register_setting( 'wpa_homepage_options', 'wpa_homepage_layout' );

    // --- 1. General Section ---
    add_settings_section( 'wpa_homepage_general', __( 'General Configuration', 'wp-academic-post-enhanced' ), 'wpa_homepage_section_callback', 'wpa_homepage' );
    add_settings_field( 'enable_homepage', __( 'Enable Custom Academic Theme', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_homepage_general', ['label_for' => 'enable_homepage', 'desc' => 'Activate the Academic design system.'] );
    add_settings_field( 'homepage_force_builder', __( 'Force Builder on Front Page', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_homepage_general', ['label_for' => 'homepage_force_builder', 'default' => 1, 'desc' => 'If unchecked, WordPress will use its default homepage (Static Page or Latest Posts) while still using the Academic Header/Footer.'] );
    add_settings_field( 'enable_glassmorphism', __( 'Enable Glassmorphism', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_homepage_general', ['label_for' => 'enable_glassmorphism', 'desc' => 'Apply modern glassmorphism effect to UI elements.'] );
    add_settings_field( 'accent_color', __( 'Accent Color', 'wp-academic-post-enhanced' ), 'wpa_homepage_color_field', 'wpa_homepage', 'wpa_homepage_general', ['label_for' => 'accent_color', 'default' => '#2563eb'] );
    add_settings_field( 'container_width', __( 'Global Page Width', 'wp-academic-post-enhanced' ), 'wpa_homepage_select_field', 'wpa_homepage', 'wpa_homepage_general', ['label_for' => 'container_width', 'options' => ['standard' => 'Standard (1200px)', 'narrow' => 'Narrow (800px)', 'wide' => 'Wide (90%)', 'full' => 'Full Width (100%)', 'custom' => 'Custom']] );
    add_settings_field( 'container_width_custom', __( 'Global Custom Width (px or %)', 'wp-academic-post-enhanced' ), 'wpa_homepage_text_field', 'wpa_homepage', 'wpa_homepage_general', ['label_for' => 'container_width_custom', 'desc' => 'Enter value if "Custom" is selected (e.g., 1400px or 95%).'] );
    
    // Layout Defaults
    add_settings_section( 'wpa_theme_layout_defaults', __( 'Layout Defaults by Post Type', 'wp-academic-post-enhanced' ), 'wpa_homepage_section_callback', 'wpa_homepage' );
    
    $width_options = [
        'default' => 'Default (Global)',
        'standard' => 'Standard (1200px)',
        'narrow' => 'Narrow (800px)',
        'wide' => 'Wide (90%)',
        'full' => 'Full Width (100%)',
        'custom' => 'Custom'
    ];

    add_settings_field( 'width_post', __( 'Single Post Width', 'wp-academic-post-enhanced' ), 'wpa_homepage_select_field', 'wpa_homepage', 'wpa_theme_layout_defaults', ['label_for' => 'width_post', 'options' => $width_options] );
    add_settings_field( 'width_page', __( 'Single Page Width', 'wp-academic-post-enhanced' ), 'wpa_homepage_select_field', 'wpa_homepage', 'wpa_theme_layout_defaults', ['label_for' => 'width_page', 'options' => $width_options] );
    add_settings_field( 'width_course', __( 'Single Course Width', 'wp-academic-post-enhanced' ), 'wpa_homepage_select_field', 'wpa_homepage', 'wpa_theme_layout_defaults', ['label_for' => 'width_course', 'options' => $width_options] );
    add_settings_field( 'width_lesson', __( 'Single Lesson Width', 'wp-academic-post-enhanced' ), 'wpa_homepage_select_field', 'wpa_homepage', 'wpa_theme_layout_defaults', ['label_for' => 'width_lesson', 'options' => $width_options] );
    add_settings_field( 'width_news', __( 'Single News Width', 'wp-academic-post-enhanced' ), 'wpa_homepage_select_field', 'wpa_homepage', 'wpa_theme_layout_defaults', ['label_for' => 'width_news', 'options' => $width_options] );
    add_settings_field( 'width_glossary', __( 'Single Glossary Term Width', 'wp-academic-post-enhanced' ), 'wpa_homepage_select_field', 'wpa_homepage', 'wpa_theme_layout_defaults', ['label_for' => 'width_glossary', 'options' => $width_options] );

    // --- 1.5 Standard Page Layouts ---
    add_settings_section( 'wpa_theme_page_layouts', __( 'Standard Page & Post Layouts', 'wp-academic-post-enhanced' ), 'wpa_homepage_section_callback', 'wpa_homepage' );
    add_settings_field( 'page_sidebar_pos', __( 'Default Page Sidebar', 'wp-academic-post-enhanced' ), 'wpa_homepage_select_field', 'wpa_homepage', 'wpa_theme_page_layouts', ['label_for' => 'page_sidebar_pos', 'options' => ['none' => 'No Sidebar', 'right' => 'Right Sidebar', 'left' => 'Left Sidebar'], 'default' => 'none'] );
    add_settings_field( 'page_show_title', __( 'Show Title on Pages', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_theme_page_layouts', ['label_for' => 'page_show_title', 'default' => 1] );
    add_settings_field( 'page_show_featured', __( 'Show Featured Image on Pages', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_theme_page_layouts', ['label_for' => 'page_show_featured', 'default' => 1] );
    
    add_settings_field( 'post_sidebar_pos', __( 'Default Post Sidebar', 'wp-academic-post-enhanced' ), 'wpa_homepage_select_field', 'wpa_homepage', 'wpa_theme_page_layouts', ['label_for' => 'post_sidebar_pos', 'options' => ['none' => 'No Sidebar', 'right' => 'Right Sidebar', 'left' => 'Left Sidebar'], 'default' => 'none'] );
    add_settings_field( 'post_show_title', __( 'Show Title on Posts', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_theme_page_layouts', ['label_for' => 'post_show_title', 'default' => 1] );
    add_settings_field( 'post_show_meta', __( 'Show Meta (Date/Author) on Posts', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_theme_page_layouts', ['label_for' => 'post_show_meta', 'default' => 1] );

    // --- 2. Typography Section ---
    add_settings_section( 'wpa_theme_typography', __( 'Typography & Background', 'wp-academic-post-enhanced' ), 'wpa_homepage_section_callback', 'wpa_homepage' );
    add_settings_field( 'font_heading', __( 'Headings Font', 'wp-academic-post-enhanced' ), 'wpa_theme_font_select', 'wpa_homepage', 'wpa_theme_typography', ['label_for' => 'font_heading', 'default' => 'Lexend'] );
    add_settings_field( 'font_body', __( 'Body Font', 'wp-academic-post-enhanced' ), 'wpa_theme_font_select', 'wpa_homepage', 'wpa_theme_typography', ['label_for' => 'font_body', 'default' => 'Inter'] );
    add_settings_field( 'font_size_body', __( 'Body Font Size (rem)', 'wp-academic-post-enhanced' ), 'wpa_homepage_text_field', 'wpa_homepage', 'wpa_theme_typography', ['label_for' => 'font_size_body', 'default' => '1.15'] );
    add_settings_field( 'line_height_body', __( 'Line Height', 'wp-academic-post-enhanced' ), 'wpa_homepage_text_field', 'wpa_homepage', 'wpa_theme_typography', ['label_for' => 'font_size_body', 'default' => '1.8'] );

    add_settings_field( 'bg_pattern_style', __( 'Background Pattern Design', 'wp-academic-post-enhanced' ), 'wpa_homepage_select_field', 'wpa_homepage', 'wpa_theme_typography', [
        'label_for' => 'bg_pattern_style', 
        'options' => [
            'none' => 'None',
            'radial' => 'Radial Glow (Soft)',
            'dots' => 'Subtle Dots',
            'grid' => 'Professional Grid',
            'blueprint' => 'Academic Blueprint',
            'mesh' => 'Modern Mesh Gradient',
            'topo' => 'Topographic Lines',
            'waves' => 'Abstract Waves',
            'circuit' => 'Cyber Circuit',
            'zenith' => 'Zenith Gradient',
            'hex' => 'Hexagonal Honeycomb',
            'stars' => 'Starry Night',
            'cube' => 'Isometric Cubes',
            'carbon' => 'Carbon Fiber',
            'lines' => 'Diagonal Lines'
        ]
    ] );

    add_settings_field( 'dark_mode_theme', __( 'Dark Mode Color Theme', 'wp-academic-post-enhanced' ), 'wpa_homepage_select_field', 'wpa_homepage', 'wpa_theme_typography', [
        'label_for' => 'dark_mode_theme', 
        'options' => [
            'default' => 'Default (Midnight Blue)',
            'deep-red' => 'Crimson Academic',
            'deep-green' => 'Emerald Scholar',
            'deep-black' => 'Midnight Black',
            'deep-purple' => 'Royal Purple',
            'deep-slate' => 'Neutral Slate'
        ]
    ] );
    add_settings_field( 'bg_pattern_intensity', __( 'Pattern Intensity', 'wp-academic-post-enhanced' ), 'wpa_homepage_select_field', 'wpa_homepage', 'wpa_theme_typography', [
        'label_for' => 'bg_pattern_intensity', 
        'options' => [
            'subtle' => 'Subtle (Default)',
            'medium' => 'Clear',
            'strong' => 'Strong',
            'intense' => 'High Contrast'
        ]
    ] );

    // --- 2.5 Header Menu Section ---
    add_settings_section( 'wpa_theme_menu', __( 'Header Menu', 'wp-academic-post-enhanced' ), 'wpa_homepage_section_callback', 'wpa_homepage' );
    add_settings_field( 'menu_type', __( 'Menu Type', 'wp-academic-post-enhanced' ), 'wpa_homepage_select_field', 'wpa_homepage', 'wpa_theme_menu', ['label_for' => 'menu_type', 'options' => ['wp' => 'WordPress Menu', 'custom' => 'Custom Links']] );
    add_settings_field( 'menu_wp_id', __( 'Select WP Menu', 'wp-academic-post-enhanced' ), 'wpa_homepage_menu_select_field', 'wpa_homepage', 'wpa_theme_menu', ['label_for' => 'menu_wp_id'] );
    add_settings_field( 'menu_custom_links', __( 'Custom Links', 'wp-academic-post-enhanced' ), 'wpa_homepage_textarea_field', 'wpa_homepage', 'wpa_theme_menu', ['label_for' => 'menu_custom_links', 'desc' => 'Format: Label|URL (one per line). Example: Home|/'] );
    add_settings_field( 'header_show_btn', __( 'Show Header Button', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_theme_menu', ['label_for' => 'header_show_btn', 'default' => 1, 'desc' => 'Show the "Get Started" button in the header.'] );

    // --- 2.6 Global Layout Control ---
    add_settings_section( 'wpa_theme_global_control', __( 'Global Layout Control', 'wp-academic-post-enhanced' ), 'wpa_homepage_section_callback', 'wpa_homepage' );
    add_settings_field( 'global_layout_post_types', __( 'Apply Layout to Post Types', 'wp-academic-post-enhanced' ), 'wpa_homepage_post_types_field', 'wpa_homepage', 'wpa_theme_global_control', ['label_for' => 'global_layout_post_types'] );

    // --- 3. Header & Footer Section ---
    add_settings_section( 'wpa_homepage_header_footer', __( 'Header & Footer', 'wp-academic-post-enhanced' ), 'wpa_homepage_section_callback', 'wpa_homepage' );
    add_settings_field( 'enable_global_header_footer', __( 'Apply Globally', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_homepage_header_footer', ['label_for' => 'enable_global_header_footer', 'default' => 1, 'desc' => 'Use this header/footer on all Academic Post pages.'] );
    add_settings_field( 'enable_custom_header', __( 'Enable Custom Header', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_homepage_header_footer', ['label_for' => 'enable_custom_header', 'desc' => 'Replace theme header.'] );
    add_settings_field( 'header_bg', __( 'Header Background', 'wp-academic-post-enhanced' ), 'wpa_homepage_color_field', 'wpa_homepage', 'wpa_homepage_header_footer', ['label_for' => 'header_bg', 'default' => 'rgba(255, 255, 255, 0.95)'] );
    add_settings_field( 'header_text_color', __( 'Header Text/Logo Color', 'wp-academic-post-enhanced' ), 'wpa_homepage_color_field', 'wpa_homepage', 'wpa_homepage_header_footer', ['label_for' => 'header_text_color', 'default' => '#1f2937'] );
    add_settings_field( 'header_logo', __( 'Header Logo URL', 'wp-academic-post-enhanced' ), 'wpa_homepage_image_field', 'wpa_homepage', 'wpa_homepage_header_footer', ['label_for' => 'header_logo'] );
    add_settings_field( 'header_sticky', __( 'Sticky Header', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_homepage_header_footer', ['label_for' => 'header_sticky', 'default' => 1] );
    add_settings_field( 'header_btn_text', __( 'Header Button Text', 'wp-academic-post-enhanced' ), 'wpa_homepage_text_field', 'wpa_homepage', 'wpa_homepage_header_footer', ['label_for' => 'header_btn_text'] );
    add_settings_field( 'header_btn_url', __( 'Header Button URL', 'wp-academic-post-enhanced' ), 'wpa_homepage_text_field', 'wpa_homepage', 'wpa_homepage_header_footer', ['label_for' => 'header_btn_url'] );
    
    add_settings_field( 'enable_custom_footer', __( 'Enable Custom Footer', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_homepage_header_footer', ['label_for' => 'enable_custom_footer', 'desc' => 'Replace theme footer.'] );
    add_settings_field( 'footer_bg', __( 'Footer Background', 'wp-academic-post-enhanced' ), 'wpa_homepage_color_field', 'wpa_homepage', 'wpa_homepage_header_footer', ['label_for' => 'footer_bg', 'default' => '#f8fafc'] );
    add_settings_field( 'footer_copyright', __( 'Footer Copyright', 'wp-academic-post-enhanced' ), 'wpa_homepage_text_field', 'wpa_homepage', 'wpa_homepage_header_footer', ['label_for' => 'footer_copyright', 'default' => '&copy; ' . date('Y') . ' ' . get_bloginfo('name')] );
    add_settings_field( 'footer_social', __( 'Social Links', 'wp-academic-post-enhanced' ), 'wpa_homepage_textarea_field', 'wpa_homepage', 'wpa_homepage_header_footer', ['label_for' => 'footer_social', 'desc' => 'One URL per line (Twitter, Facebook, LinkedIn, etc.)'] );

    // --- 4. News Layout Section ---
    // General
    add_settings_section( 'wpa_theme_news_general', __( 'General & Archive', 'wp-academic-post-enhanced' ), 'wpa_homepage_section_callback', 'wpa_homepage' );
    add_settings_field( 'enable_news_template', __( 'Enable Single News Template', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_theme_news_general', ['label_for' => 'enable_news_template', 'default' => 1] );
    add_settings_field( 'enable_news_archive', __( 'Enable News Archive Grid', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_theme_news_general', ['label_for' => 'enable_news_archive', 'default' => 1] );
    add_settings_field( 'news_bg_color', __( 'Background Color', 'wp-academic-post-enhanced' ), 'wpa_homepage_color_field', 'wpa_homepage', 'wpa_theme_news_general', ['label_for' => 'news_bg_color', 'default' => '#ffffff'] );
    add_settings_field( 'news_text_color', __( 'Text Color', 'wp-academic-post-enhanced' ), 'wpa_homepage_color_field', 'wpa_homepage', 'wpa_theme_news_general', ['label_for' => 'news_text_color', 'default' => '#333333'] );
    add_settings_field( 'news_heading_color', __( 'Heading Color', 'wp-academic-post-enhanced' ), 'wpa_homepage_color_field', 'wpa_homepage', 'wpa_theme_news_general', ['label_for' => 'news_heading_color', 'default' => '#111827'] );
    add_settings_field( 'news_custom_width', __( 'Content Width Override (px)', 'wp-academic-post-enhanced' ), 'wpa_homepage_number_field', 'wpa_homepage', 'wpa_theme_news_general', ['label_for' => 'news_custom_width'] );
    add_settings_field( 'news_img_style', __( 'Featured Image Style', 'wp-academic-post-enhanced' ), 'wpa_homepage_select_field', 'wpa_homepage', 'wpa_theme_news_general', ['label_for' => 'news_img_style', 'options' => ['standard' => 'Standard', 'wide' => 'Wide', 'hidden' => 'Hidden']] );
    
    // Meta Visibility
    add_settings_section( 'wpa_theme_news_meta', __( 'Meta Data & Visibility', 'wp-academic-post-enhanced' ), 'wpa_homepage_section_callback', 'wpa_homepage' );
    add_settings_field( 'news_meta_box_title', __( 'Details Box Title', 'wp-academic-post-enhanced' ), 'wpa_homepage_text_field', 'wpa_homepage', 'wpa_theme_news_meta', ['label_for' => 'news_meta_box_title', 'default' => 'About the Study'] );
    add_settings_field( 'news_show_meta_journal', __( 'Show Journal', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_theme_news_meta', ['label_for' => 'news_show_meta_journal', 'default' => 1] );
    add_settings_field( 'news_show_journal_logo', __( 'Show Journal Logo', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_theme_news_meta', ['label_for' => 'news_show_journal_logo', 'default' => 1] );
    add_settings_field( 'news_show_meta_authors', __( 'Show Authors', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_theme_news_meta', ['label_for' => 'news_show_meta_authors', 'default' => 1] );
    add_settings_field( 'news_show_meta_affiliations', __( 'Show Affiliations', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_theme_news_meta', ['label_for' => 'news_show_meta_affiliations', 'default' => 1] );
    add_settings_field( 'news_show_meta_date', __( 'Show Date', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_theme_news_meta', ['label_for' => 'news_show_meta_date', 'default' => 1] );
    add_settings_field( 'news_show_meta_type', __( 'Show Study Type', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_theme_news_meta', ['label_for' => 'news_show_meta_type', 'default' => 1] );
    add_settings_field( 'news_show_meta_citations', __( 'Show Citation Count', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_theme_news_meta', ['label_for' => 'news_show_meta_citations', 'default' => 1] );
    add_settings_field( 'news_show_meta_doi', __( 'Show DOI Link', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_theme_news_meta', ['label_for' => 'news_show_meta_doi', 'default' => 1] );
    add_settings_field( 'news_show_meta_openaccess', __( 'Show Open Access Badge', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_theme_news_meta', ['label_for' => 'news_show_meta_openaccess', 'default' => 1] );
    add_settings_field( 'news_show_meta_concepts', __( 'Show AI Concepts', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_theme_news_meta', ['label_for' => 'news_show_meta_concepts', 'default' => 1] );
    add_settings_field( 'news_show_meta_sdgs', __( 'Show SDGs', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_theme_news_meta', ['label_for' => 'news_show_meta_sdgs', 'default' => 1] );
    add_settings_field( 'news_show_meta_keywords', __( 'Show Keywords', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_theme_news_meta', ['label_for' => 'news_show_meta_keywords', 'default' => 1] );
    add_settings_field( 'news_show_author_box', __( 'Show Details Box', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_theme_news_meta', ['label_for' => 'news_show_author_box', 'default' => 1] );
    add_settings_field( 'news_meta_box_position', __( 'Details Box Position', 'wp-academic-post-enhanced' ), 'wpa_homepage_select_field', 'wpa_homepage', 'wpa_theme_news_meta', ['label_for' => 'news_meta_box_position', 'options' => ['after' => 'After Content', 'before' => 'Before Content', 'bottom' => 'Bottom']] );

    // Sidebar
    add_settings_section( 'wpa_theme_news_sidebar', __( 'Sidebar & Widgets', 'wp-academic-post-enhanced' ), 'wpa_homepage_section_callback', 'wpa_homepage' );
    add_settings_field( 'news_show_sidebar', __( 'Enable Sidebar', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_theme_news_sidebar', ['label_for' => 'news_show_sidebar', 'default' => 1] );
    add_settings_field( 'news_sidebar_recent', __( 'Widget: Recent News', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_theme_news_sidebar', ['label_for' => 'news_sidebar_recent', 'default' => 1] );
    add_settings_field( 'news_sidebar_share', __( 'Widget: Share Buttons', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_theme_news_sidebar', ['label_for' => 'news_sidebar_share', 'default' => 1] );
    add_settings_field( 'news_show_google_news', __( 'Show Google News Button', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_theme_news_sidebar', ['label_for' => 'news_show_google_news', 'default' => 0] );
    add_settings_field( 'news_google_news_url', __( 'Google News URL', 'wp-academic-post-enhanced' ), 'wpa_homepage_text_field', 'wpa_homepage', 'wpa_theme_news_sidebar', ['label_for' => 'news_google_news_url'] );

    // --- 5. Course Layout Section ---
    // General
    add_settings_section( 'wpa_theme_course_general', __( 'General & Archive', 'wp-academic-post-enhanced' ), 'wpa_homepage_section_callback', 'wpa_homepage' );
    add_settings_field( 'enable_course_template', __( 'Enable Single Course Template', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_theme_course_general', ['label_for' => 'enable_course_template', 'default' => 1] );
    add_settings_field( 'enable_course_archive', __( 'Enable Course Archive Grid', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_theme_course_general', ['label_for' => 'enable_course_archive', 'default' => 1] );
    add_settings_field( 'course_grid_cols', __( 'Grid Columns', 'wp-academic-post-enhanced' ), 'wpa_homepage_number_field', 'wpa_homepage', 'wpa_theme_course_general', ['label_for' => 'course_grid_cols', 'default' => 3] );
    
    // Display
    add_settings_section( 'wpa_theme_course_display', __( 'Display Elements', 'wp-academic-post-enhanced' ), 'wpa_homepage_section_callback', 'wpa_homepage' );
    add_settings_field( 'course_curriculum_style', __( 'Curriculum Style', 'wp-academic-post-enhanced' ), 'wpa_homepage_select_field', 'wpa_homepage', 'wpa_theme_course_display', ['label_for' => 'course_curriculum_style', 'options' => ['modern' => 'Modern', 'clean' => 'Clean', 'classic' => 'Classic']] );
    add_settings_field( 'course_show_curriculum_duration', __( 'Show Lesson Duration', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_theme_course_display', ['label_for' => 'course_show_curriculum_duration', 'default' => 1] );
    add_settings_field( 'course_show_curriculum_icons', __( 'Show Lesson Icons', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_theme_course_display', ['label_for' => 'course_show_curriculum_icons', 'default' => 1] );
    add_settings_field( 'course_show_duration', __( 'Show Course Duration', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_theme_course_display', ['label_for' => 'course_show_duration', 'default' => 1] );
    add_settings_field( 'course_show_level', __( 'Show Difficulty Level', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_theme_course_display', ['label_for' => 'course_show_level', 'default' => 1] );
    add_settings_field( 'course_header_bg', __( 'Hero Background Color', 'wp-academic-post-enhanced' ), 'wpa_homepage_color_field', 'wpa_homepage', 'wpa_theme_course_display', ['label_for' => 'course_header_bg', 'default' => '#1e293b'] );
    add_settings_field( 'course_header_text', __( 'Hero Text Color', 'wp-academic-post-enhanced' ), 'wpa_homepage_color_field', 'wpa_homepage', 'wpa_theme_course_display', ['label_for' => 'course_header_text', 'default' => '#ffffff'] );

    // Labels
    add_settings_section( 'wpa_theme_course_labels', __( 'Text Labels', 'wp-academic-post-enhanced' ), 'wpa_homepage_section_callback', 'wpa_homepage' );
    add_settings_field( 'label_start_course', __( 'Button: Start Course', 'wp-academic-post-enhanced' ), 'wpa_homepage_text_field', 'wpa_homepage', 'wpa_theme_course_labels', ['label_for' => 'label_start_course', 'default' => 'Start Course'] );
    add_settings_field( 'label_curriculum', __( 'Heading: Curriculum', 'wp-academic-post-enhanced' ), 'wpa_homepage_text_field', 'wpa_homepage', 'wpa_theme_course_labels', ['label_for' => 'label_curriculum', 'default' => 'Curriculum'] );

    // --- 6. Lesson Layout Section ---
    // General
    add_settings_section( 'wpa_theme_lesson_general', __( 'General Configuration', 'wp-academic-post-enhanced' ), 'wpa_homepage_section_callback', 'wpa_homepage' );
    add_settings_field( 'enable_lesson_template', __( 'Enable Lesson Template', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_theme_lesson_general', ['label_for' => 'enable_lesson_template', 'default' => 1] );
    add_settings_field( 'lesson_enable_focus_mode', __( 'Enable Focus Mode', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_theme_lesson_general', ['label_for' => 'lesson_enable_focus_mode', 'default' => 1] );
    add_settings_field( 'lesson_sidebar_pos', __( 'Sidebar Position', 'wp-academic-post-enhanced' ), 'wpa_homepage_select_field', 'wpa_homepage', 'wpa_theme_lesson_general', ['label_for' => 'lesson_sidebar_pos', 'options' => ['right' => 'Right', 'left' => 'Left']] );
    add_settings_field( 'lesson_video_pos', __( 'Video Position', 'wp-academic-post-enhanced' ), 'wpa_homepage_select_field', 'wpa_homepage', 'wpa_theme_lesson_general', ['label_for' => 'lesson_video_pos', 'options' => ['top' => 'Top (Above Content)', 'bottom' => 'Bottom (Below Content)']] );

    // Display
    add_settings_section( 'wpa_theme_lesson_display', __( 'Display Elements', 'wp-academic-post-enhanced' ), 'wpa_homepage_section_callback', 'wpa_homepage' );
    add_settings_field( 'lesson_show_breadcrumbs', __( 'Show Breadcrumbs', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_theme_lesson_display', ['label_for' => 'lesson_show_breadcrumbs', 'default' => 1] );
    add_settings_field( 'lesson_show_materials', __( 'Show Materials', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_theme_lesson_display', ['label_for' => 'lesson_show_materials', 'default' => 1] );
    add_settings_field( 'lesson_show_index', __( 'Show Index (Lesson X of Y)', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_theme_lesson_display', ['label_for' => 'lesson_show_index', 'default' => 1] );
    add_settings_field( 'lesson_show_nav', __( 'Show Navigation Buttons', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_theme_lesson_display', ['label_for' => 'lesson_show_nav', 'default' => 1] );
    add_settings_field( 'lesson_show_sidebar_progress', __( 'Show Sidebar Progress', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_theme_lesson_display', ['label_for' => 'lesson_show_sidebar_progress', 'default' => 1] );
    add_settings_field( 'lesson_show_instructor', __( 'Show Instructor Card', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_theme_lesson_display', ['label_for' => 'lesson_show_instructor', 'default' => 1] );

    // Labels
    add_settings_section( 'wpa_theme_lesson_labels', __( 'Text Labels', 'wp-academic-post-enhanced' ), 'wpa_homepage_section_callback', 'wpa_homepage' );
    add_settings_field( 'label_mark_complete', __( 'Button: Mark Complete', 'wp-academic-post-enhanced' ), 'wpa_homepage_text_field', 'wpa_homepage', 'wpa_theme_lesson_labels', ['label_for' => 'label_mark_complete', 'default' => 'Mark Complete'] );
    add_settings_field( 'label_next_lesson', __( 'Nav: Next Lesson', 'wp-academic-post-enhanced' ), 'wpa_homepage_text_field', 'wpa_homepage', 'wpa_theme_lesson_labels', ['label_for' => 'label_next_lesson', 'default' => 'Next Lesson'] );

    // --- 7. 404 Page Section ---
    add_settings_section( 'wpa_theme_404', __( '404 Error Page', 'wp-academic-post-enhanced' ), 'wpa_homepage_section_callback', 'wpa_homepage' );
    add_settings_field( 'error_404_title', __( '404 Title', 'wp-academic-post-enhanced' ), 'wpa_homepage_text_field', 'wpa_homepage', 'wpa_theme_404', ['label_for' => 'error_404_title', 'default' => 'Page Not Found'] );
    add_settings_field( 'error_404_desc', __( '404 Description', 'wp-academic-post-enhanced' ), 'wpa_homepage_textarea_field', 'wpa_homepage', 'wpa_theme_404', ['label_for' => 'error_404_desc', 'default' => 'Sorry, the page you are looking for does not exist or has been moved.'] );
    add_settings_field( 'error_404_btn_text', __( 'Button Text', 'wp-academic-post-enhanced' ), 'wpa_homepage_text_field', 'wpa_homepage', 'wpa_theme_404', ['label_for' => 'error_404_btn_text', 'default' => 'Return Home'] );
    add_settings_field( 'error_404_btn_url', __( 'Button URL', 'wp-academic-post-enhanced' ), 'wpa_homepage_text_field', 'wpa_homepage', 'wpa_theme_404', ['label_for' => 'error_404_btn_url', 'default' => home_url('/')] );
    add_settings_field( 'error_404_image', __( 'Background/Hero Image URL', 'wp-academic-post-enhanced' ), 'wpa_homepage_image_field', 'wpa_homepage', 'wpa_theme_404', ['label_for' => 'error_404_image'] );

    // --- 8. Glossary Styling (Integrated) ---
    add_settings_section( 'wpa_theme_glossary_styling', __( 'Glossary Index Styling', 'wp-academic-post-enhanced' ), 'wpa_homepage_section_callback', 'wpa_homepage' );
    
    add_settings_field( 'wpa_glossary_title', __( 'Glossary Title', 'wp-academic-post-enhanced' ), 'wpa_homepage_text_field', 'wpa_homepage', 'wpa_theme_glossary_styling', ['label_for' => 'wpa_glossary_title', 'default' => 'Glossary'] );
    add_settings_field( 'glossary_show_title', __( 'Show Title on Index', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_theme_glossary_styling', ['label_for' => 'glossary_show_title', 'default' => 1] );
    add_settings_field( 'glossary_show_single_label', __( 'Show Label on Single Term', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_theme_glossary_styling', ['label_for' => 'glossary_show_single_label', 'default' => 1, 'desc' => 'Show the "Glossary" text above the term title.'] );
    add_settings_field( 'wpa_glossary_slug', __( 'Glossary Slug', 'wp-academic-post-enhanced' ), 'wpa_homepage_text_field', 'wpa_homepage', 'wpa_theme_glossary_styling', ['label_for' => 'wpa_glossary_slug', 'default' => 'glossary'] );
    add_settings_field( 'wpa_glossary_archive', __( 'Enable CPT Archive', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_theme_glossary_styling', ['label_for' => 'wpa_glossary_archive', 'default' => 1] );
    add_settings_field( 'wpa_glossary_page_id', __( 'Glossary Index Page ID/Slug', 'wp-academic-post-enhanced' ), 'wpa_homepage_text_field', 'wpa_homepage', 'wpa_theme_glossary_styling', ['label_for' => 'wpa_glossary_page_id'] );
    
    add_settings_field( 'glossary_style', __( 'Global Glossary Style', 'wp-academic-post-enhanced' ), 'wpa_homepage_select_field', 'wpa_homepage', 'wpa_theme_glossary_styling', [
        'label_for' => 'glossary_style',
        'options' => [
            'modern'    => 'Modern Grid',
            'list'      => 'Clean List',
            'accordion' => 'Interactive Accordion',
            'badges'    => 'Horizontal Badges',
            'columns'   => 'Multi-column Grid',
            'images'    => 'Visual Cards (Images)',
        ],
        'default' => 'modern'
    ] );
    add_settings_field( 'glossary_sidebar_pos', __( 'Single Term Sidebar', 'wp-academic-post-enhanced' ), 'wpa_homepage_select_field', 'wpa_homepage', 'wpa_theme_glossary_styling', [
        'label_for' => 'glossary_sidebar_pos',
        'options' => [
            'right' => 'Right Sidebar',
            'none'  => 'No Sidebar (Full Width)',
        ],
        'default' => 'right'
    ] );
    add_settings_field( 'glossary_terms_per_row', __( 'Terms per Row', 'wp-academic-post-enhanced' ), 'wpa_homepage_number_field', 'wpa_homepage', 'wpa_theme_glossary_styling', ['label_for' => 'glossary_terms_per_row', 'default' => 3] );
    
    add_settings_field( 'wpa_glossary_search', __( 'Enable Search Bar', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_theme_glossary_styling', ['label_for' => 'wpa_glossary_search', 'default' => 0] );
    add_settings_field( 'wpa_glossary_search_label', __( 'Search Placeholder', 'wp-academic-post-enhanced' ), 'wpa_homepage_text_field', 'wpa_homepage', 'wpa_theme_glossary_styling', ['label_for' => 'wpa_glossary_search_label', 'default' => 'Search by Keyword ...'] );
    add_settings_field( 'glossary_animation', __( 'Enable Animation', 'wp-academic-post-enhanced' ), 'wpa_homepage_checkbox_field', 'wpa_homepage', 'wpa_theme_glossary_styling', ['label_for' => 'glossary_animation', 'default' => 1] );

    add_settings_field( 'glossary_alphabet_set', __( 'Custom Alphabet Set', 'wp-academic-post-enhanced' ), 'wpa_homepage_textarea_field', 'wpa_homepage', 'wpa_theme_glossary_styling', [
        'label_for' => 'glossary_alphabet_set',
        'default' => 'A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z',
        'desc' => 'Comma separated. Use new lines for multiple rows.'
    ] );

    // Tooltip settings also moved
    add_settings_field( 'glossary_tooltip_theme', __( 'Tooltip Theme', 'wp-academic-post-enhanced' ), 'wpa_homepage_select_field', 'wpa_homepage', 'wpa_theme_glossary_styling', [
        'label_for' => 'glossary_tooltip_theme',
        'options' => [ 'default' => 'Default', 'light' => 'Light', 'noir' => 'Noir', 'punk' => 'Punk', 'shadow' => 'Shadow' ],
        'default' => 'default'
    ] );
    add_settings_field( 'glossary_tooltip_content_type', __( 'Tooltip Content Source', 'wp-academic-post-enhanced' ), 'wpa_homepage_select_field', 'wpa_homepage', 'wpa_theme_glossary_styling', [
        'label_for' => 'glossary_tooltip_content_type',
        'options' => [ 'excerpt' => 'Excerpt', 'content' => 'Full Content' ],
        'default' => 'excerpt'
    ] );

    // --- Styling Integration (from styling-admin.php) ---
    wpa_register_styling_settings_integrated();
}
add_action( 'admin_init', 'wpa_homepage_register_settings' );

/**
 * Integrated registration for styling settings.
 */
function wpa_register_styling_settings_integrated() {
    $option_group = 'wpa_homepage_options'; // Use main theme option group

    // Heading Styling
    register_setting($option_group, 'wpa_heading_styling_settings', ['type' => 'array', 'sanitize_callback' => 'wpa_sanitize_heading_styling_settings']);
    add_settings_section('wpa_heading_styling_section', __('Heading Styling', 'wp-academic-post-enhanced'), 'wpa_heading_styling_section_callback', 'wpa_styling');
    add_settings_field('wpa_heading_styling_color_field', __('Heading Color', 'wp-academic-post-enhanced'), 'wpa_heading_styling_color_field_callback', 'wpa_styling', 'wpa_heading_styling_section');
    add_settings_field('wpa_heading_styling_color_dark_field', __('Heading Color (Dark Mode)', 'wp-academic-post-enhanced'), 'wpa_heading_styling_color_dark_field_callback', 'wpa_styling', 'wpa_heading_styling_section');
    add_settings_field('wpa_heading_styling_font_size_field', __('Font Size (px)', 'wp-academic-post-enhanced'), 'wpa_heading_styling_font_size_field_callback', 'wpa_styling', 'wpa_heading_styling_section');
    add_settings_field('wpa_heading_styling_font_weight_field', __('Font Weight', 'wp-academic-post-enhanced'), 'wpa_heading_styling_font_weight_field_callback', 'wpa_styling', 'wpa_heading_styling_section');
    add_settings_field('wpa_heading_styling_text_decoration_field', __('Text Decoration', 'wp-academic-post-enhanced'), 'wpa_heading_styling_text_decoration_field_callback', 'wpa_styling', 'wpa_heading_styling_section');
    add_settings_field('wpa_heading_styling_targeted_headings_field', __('Apply to Headings', 'wp-academic-post-enhanced'), 'wpa_heading_styling_targeted_headings_field_callback', 'wpa_styling', 'wpa_heading_styling_section');
    add_settings_field('wpa_heading_styling_text_shadow_offset_x_field', __('Text Shadow X Offset (px)', 'wp-academic-post-enhanced'), 'wpa_heading_styling_text_shadow_offset_x_field_callback', 'wpa_styling', 'wpa_heading_styling_section');
    add_settings_field('wpa_heading_styling_text_shadow_offset_y_field', __('Text Shadow Y Offset (px)', 'wp-academic-post-enhanced'), 'wpa_heading_styling_text_shadow_offset_y_field_callback', 'wpa_styling', 'wpa_heading_styling_section');
    add_settings_field('wpa_heading_styling_text_shadow_blur_radius_field', __('Text Shadow Blur Radius (px)', 'wp-academic-post-enhanced'), 'wpa_heading_styling_text_shadow_blur_radius_field_callback', 'wpa_styling', 'wpa_heading_styling_section');
    add_settings_field('wpa_heading_styling_text_shadow_color_field', __('Text Shadow Color', 'wp-academic-post-enhanced'), 'wpa_heading_styling_text_shadow_color_field_callback', 'wpa_styling', 'wpa_heading_styling_section');

    // TOC Styling
    register_setting($option_group, 'wpa_toc_styling_settings', ['type' => 'array', 'sanitize_callback' => 'wpa_sanitize_toc_styling_settings']);
    add_settings_section('wpa_toc_styling_section', __('Table of Contents Styling', 'wp-academic-post-enhanced'), 'wpa_toc_styling_section_callback', 'wpa_styling');
    add_settings_field('wpa_toc_styling_bg_color_field', __('Background Color', 'wp-academic-post-enhanced'), 'wpa_toc_styling_bg_color_field_callback', 'wpa_styling', 'wpa_toc_styling_section');
    add_settings_field('wpa_toc_styling_border_color_field', __('Border Color', 'wp-academic-post-enhanced'), 'wpa_toc_styling_border_color_field_callback', 'wpa_styling', 'wpa_toc_styling_section');
    add_settings_field('wpa_toc_styling_title_color_field', __('Title Color', 'wp-academic-post-enhanced'), 'wpa_toc_styling_title_color_field_callback', 'wpa_styling', 'wpa_toc_styling_section');
    add_settings_field('wpa_toc_styling_link_color_field', __('Link Color', 'wp-academic-post-enhanced'), 'wpa_toc_styling_link_color_field_callback', 'wpa_styling', 'wpa_toc_styling_section');
    add_settings_field('wpa_toc_styling_title_font_size_field', __('Title Font Size (px)', 'wp-academic-post-enhanced'), 'wpa_toc_styling_title_font_size_field_callback', 'wpa_styling', 'wpa_toc_styling_section');
    add_settings_field('wpa_toc_styling_list_font_size_field', __('List Font Size (px)', 'wp-academic-post-enhanced'), 'wpa_toc_styling_list_font_size_field_callback', 'wpa_styling', 'wpa_toc_styling_section');
    add_settings_field('wpa_toc_styling_list_style_type_field', __('List Style Type', 'wp-academic-post-enhanced'), 'wpa_toc_styling_list_style_type_field_callback', 'wpa_styling', 'wpa_toc_styling_section');
    add_settings_field('wpa_toc_styling_container_padding_top_bottom_field', __('Container Padding (Top/Bottom, px)', 'wp-academic-post-enhanced'), 'wpa_toc_styling_container_padding_top_bottom_field_callback', 'wpa_styling', 'wpa_toc_styling_section');
    add_settings_field('wpa_toc_styling_container_padding_left_right_field', __('Container Padding (Left/Right, px)', 'wp-academic-post-enhanced'), 'wpa_toc_styling_container_padding_left_right_field_callback', 'wpa_styling', 'wpa_toc_styling_section');
    add_settings_field('wpa_toc_styling_container_border_radius_field', __('Container Border Radius (px)', 'wp-academic-post-enhanced'), 'wpa_toc_styling_container_border_radius_field_callback', 'wpa_styling', 'wpa_toc_styling_section');

    // Citation Styling
    register_setting($option_group, 'wpa_citation_styling_settings', ['type' => 'array', 'sanitize_callback' => 'wpa_sanitize_citation_styling_settings']);
    add_settings_section('wpa_citation_styling_section', __('Citation Styling', 'wp-academic-post-enhanced'), 'wpa_citation_styling_section_callback', 'wpa_styling');
    add_settings_field('wpa_citation_styling_bg_color_field', __('Background Color', 'wp-academic-post-enhanced'), 'wpa_citation_styling_bg_color_field_callback', 'wpa_styling', 'wpa_citation_styling_section');
    add_settings_field('wpa_citation_styling_border_color_field', __('Border Color', 'wp-academic-post-enhanced'), 'wpa_citation_styling_border_color_field_callback', 'wpa_styling', 'wpa_citation_styling_section');
    add_settings_field('wpa_citation_styling_text_color_field', __('Text Color', 'wp-academic-post-enhanced'), 'wpa_citation_styling_text_color_field_callback', 'wpa_styling', 'wpa_citation_styling_section');

    // Social Styling
    register_setting($option_group, 'wpa_social_styling_settings', ['type' => 'array', 'sanitize_callback' => 'wpa_sanitize_social_styling_settings']);
    add_settings_section('wpa_social_styling_section', __('Social Sharing Styling', 'wp-academic-post-enhanced'), 'wpa_social_styling_section_callback', 'wpa_styling');
    add_settings_field('wpa_social_styling_bg_color_field', __('Background Color', 'wp-academic-post-enhanced'), 'wpa_social_styling_bg_color_field_callback', 'wpa_styling', 'wpa_social_styling_section');
    add_settings_field('wpa_social_styling_border_color_field', __('Border Color', 'wp-academic-post-enhanced'), 'wpa_social_styling_border_color_field_callback', 'wpa_styling', 'wpa_social_styling_section');
    add_settings_field('wpa_social_styling_text_color_field', __('Text Color', 'wp-academic-post-enhanced'), 'wpa_social_styling_text_color_field_callback', 'wpa_styling', 'wpa_social_styling_section');

    // Unified Box Shadow Styling
    register_setting($option_group, 'wpa_unified_box_shadow_settings', ['type' => 'array', 'sanitize_callback' => 'wpa_sanitize_unified_box_shadow_settings']);
    add_settings_section('wpa_unified_box_shadow_section', __('Unified Box Shadow', 'wp-academic-post-enhanced'), 'wpa_unified_box_shadow_section_callback', 'wpa_styling');
    add_settings_field('wpa_unified_box_shadow_offset_x_field', __('Box Shadow X Offset (px)', 'wp-academic-post-enhanced'), 'wpa_unified_box_shadow_offset_x_field_callback', 'wpa_styling', 'wpa_unified_box_shadow_section');
    add_settings_field('wpa_unified_box_shadow_offset_y_field', __('Box Shadow Y Offset (px)', 'wp-academic-post-enhanced'), 'wpa_unified_box_shadow_offset_y_field_callback', 'wpa_styling', 'wpa_unified_box_shadow_section');
    add_settings_field('wpa_unified_box_shadow_blur_radius_field', __('Box Shadow Blur Radius (px)', 'wp-academic-post-enhanced'), 'wpa_unified_box_shadow_blur_radius_field_callback', 'wpa_styling', 'wpa_unified_box_shadow_section');
    add_settings_field('wpa_unified_box_shadow_color_field', __('Box Shadow Color', 'wp-academic-post-enhanced'), 'wpa_unified_box_shadow_color_field_callback', 'wpa_styling', 'wpa_unified_box_shadow_section');
}

/**
 * Settings Page with Builder.
 */
function wpa_homepage_settings_page() {
    ?>
    <div class="wrap wpa-settings-wrapper wpa-homepage-wrap">
        <h1><?php esc_html_e( 'Custom Theme Builder', 'wp-academic-post-enhanced' ); ?></h1>
        
        <form method="post" action="options.php">
            <?php settings_fields( 'wpa_homepage_options' ); ?>
            
            <div class="wpa-vertical-layout">
                <!-- Vertical Menu -->
                <div class="wpa-vertical-nav">
                    <ul>
                        <li><a href="#group-global" class="wpa-vtab active" data-target="group-global"><?php esc_html_e( 'Global Settings', 'wp-academic-post-enhanced' ); ?></a></li>
                        <li><a href="#group-hf" class="wpa-vtab" data-target="group-hf"><?php esc_html_e( 'Header & Footer', 'wp-academic-post-enhanced' ); ?></a></li>
                        <li><a href="#group-layouts" class="wpa-vtab" data-target="group-layouts"><?php esc_html_e( 'Page Layouts', 'wp-academic-post-enhanced' ); ?></a></li>
                        <li><a href="#group-cpt" class="wpa-vtab" data-target="group-cpt"><?php esc_html_e( 'Post Types', 'wp-academic-post-enhanced' ); ?></a></li>
                        <li><a href="#group-styling" class="wpa-vtab" data-target="group-styling"><?php esc_html_e( 'Design & Components', 'wp-academic-post-enhanced' ); ?></a></li>
                        <li><a href="#group-home" class="wpa-vtab" data-target="group-home"><?php esc_html_e( 'Homepage Builder', 'wp-academic-post-enhanced' ); ?></a></li>
                    </ul>
                </div>

                <!-- Content Area -->
                <div class="wpa-vertical-content">
                    
                    <!-- 1. Global Settings -->
                    <div id="group-global" class="wpa-group-content active">
                        <h2 class="nav-tab-wrapper">
                            <a href="#tab-general" class="nav-tab nav-tab-active"><?php esc_html_e( 'General', 'wp-academic-post-enhanced' ); ?></a>
                            <a href="#tab-typo" class="nav-tab"><?php esc_html_e( 'Typography', 'wp-academic-post-enhanced' ); ?></a>
                            <a href="#tab-global-ctrl" class="nav-tab"><?php esc_html_e( 'Global Control', 'wp-academic-post-enhanced' ); ?></a>
                            <a href="#tab-labels" class="nav-tab"><?php esc_html_e( 'Labels', 'wp-academic-post-enhanced' ); ?></a>
                        </h2>
                        
                        <div id="tab-general" class="tab-content active">
                            <?php wpa_render_specific_section( 'wpa_homepage', 'wpa_homepage_general' ); ?>
                        </div>
                        <div id="tab-typo" class="tab-content" style="display:none;">
                            <?php wpa_render_specific_section( 'wpa_homepage', 'wpa_theme_typography' ); ?>
                        </div>
                        <div id="tab-global-ctrl" class="tab-content" style="display:none;">
                            <?php wpa_render_specific_section( 'wpa_homepage', 'wpa_theme_global_control' ); ?>
                            <p class="description"><?php esc_html_e( 'Select post types to force the Global Header, Footer, and Layout styling.', 'wp-academic-post-enhanced' ); ?></p>
                        </div>
                        <div id="tab-labels" class="tab-content" style="display:none;">
                            <?php wpa_render_specific_section( 'wpa_homepage', 'wpa_theme_labels_section' ); ?>
                        </div>
                    </div>

                    <!-- 2. Header & Footer -->
                    <div id="group-hf" class="wpa-group-content">
                        <h2 class="nav-tab-wrapper">
                            <a href="#tab-hf-config" class="nav-tab nav-tab-active"><?php esc_html_e( 'Configuration', 'wp-academic-post-enhanced' ); ?></a>
                            <a href="#tab-menu" class="nav-tab"><?php esc_html_e( 'Menu', 'wp-academic-post-enhanced' ); ?></a>
                        </h2>
                        
                        <div id="tab-hf-config" class="tab-content active">
                            <?php wpa_render_specific_section( 'wpa_homepage', 'wpa_homepage_header_footer' ); ?>
                        </div>
                        <div id="tab-menu" class="tab-content" style="display:none;">
                            <?php wpa_render_specific_section( 'wpa_homepage', 'wpa_theme_menu' ); ?>
                        </div>
                    </div>

                    <!-- 3. Page Layouts -->
                    <div id="group-layouts" class="wpa-group-content">
                        <h2 class="nav-tab-wrapper">
                            <a href="#tab-standard" class="nav-tab nav-tab-active"><?php esc_html_e( 'Standard Pages', 'wp-academic-post-enhanced' ); ?></a>
                            <a href="#tab-404" class="nav-tab"><?php esc_html_e( '404 Page', 'wp-academic-post-enhanced' ); ?></a>
                        </h2>
                        
                        <div id="tab-standard" class="tab-content active">
                            <?php 
                            wpa_render_specific_section( 'wpa_homepage', 'wpa_theme_layout_defaults' );
                            wpa_render_specific_section( 'wpa_homepage', 'wpa_theme_page_layouts' );
                            ?>
                        </div>
                        <div id="tab-404" class="tab-content" style="display:none;">
                            <?php wpa_render_specific_section( 'wpa_homepage', 'wpa_theme_404' ); ?>
                        </div>
                    </div>

                    <!-- 4. Post Types -->
                    <div id="group-cpt" class="wpa-group-content">
                        <h2 class="nav-tab-wrapper">
                            <a href="#tab-news" class="nav-tab nav-tab-active"><?php esc_html_e( 'News', 'wp-academic-post-enhanced' ); ?></a>
                            <a href="#tab-course" class="nav-tab"><?php esc_html_e( 'Courses', 'wp-academic-post-enhanced' ); ?></a>
                            <a href="#tab-lesson" class="nav-tab"><?php esc_html_e( 'Lessons', 'wp-academic-post-enhanced' ); ?></a>
                            <a href="#tab-glossary" class="nav-tab"><?php esc_html_e( 'Glossary', 'wp-academic-post-enhanced' ); ?></a>
                        </h2>
                        
                        <div id="tab-news" class="tab-content active">
                            <?php 
                            wpa_render_specific_section( 'wpa_homepage', 'wpa_theme_news_general' );
                            wpa_render_specific_section( 'wpa_homepage', 'wpa_theme_news_meta' );
                            wpa_render_specific_section( 'wpa_homepage', 'wpa_theme_news_sidebar' );
                            ?>
                        </div>
                        <div id="tab-course" class="tab-content" style="display:none;">
                            <?php 
                            wpa_render_specific_section( 'wpa_homepage', 'wpa_theme_course_general' );
                            wpa_render_specific_section( 'wpa_homepage', 'wpa_theme_course_display' );
                            wpa_render_specific_section( 'wpa_homepage', 'wpa_theme_course_labels' );
                            ?>
                        </div>
                        <div id="tab-lesson" class="tab-content" style="display:none;">
                            <?php 
                            wpa_render_specific_section( 'wpa_homepage', 'wpa_theme_lesson_general' );
                            wpa_render_specific_section( 'wpa_homepage', 'wpa_theme_lesson_display' );
                            wpa_render_specific_section( 'wpa_homepage', 'wpa_theme_lesson_labels' );
                            ?>
                        </div>
                        <div id="tab-glossary" class="tab-content" style="display:none;">
                            <?php wpa_render_specific_section( 'wpa_homepage', 'wpa_theme_glossary_styling' ); ?>
                            <div class="wpa-section-card" style="margin-top: 20px;">
                                <h3><?php esc_html_e( 'Additional Settings', 'wp-academic-post-enhanced' ); ?></h3>
                                <p><a href="<?php echo admin_url('edit.php?post_type=wpa_glossary&page=wpa-glossary-settings'); ?>" class="button button-secondary"><?php esc_html_e( 'Advanced Glossary Features (Linkify, Tooltips)', 'wp-academic-post-enhanced' ); ?></a></p>
                            </div>
                        </div>
                    </div>

                    <!-- 5. Design & Components -->
                    <div id="group-styling" class="wpa-group-content">
                        <h2 class="nav-tab-wrapper">
                            <a href="#tab-style-headings" class="nav-tab nav-tab-active"><?php esc_html_e( 'Headings', 'wp-academic-post-enhanced' ); ?></a>
                            <a href="#tab-style-shadow" class="nav-tab"><?php esc_html_e( 'Box Shadow', 'wp-academic-post-enhanced' ); ?></a>
                            <a href="#tab-style-toc" class="nav-tab"><?php esc_html_e( 'TOC Box', 'wp-academic-post-enhanced' ); ?></a>
                            <a href="#tab-style-cite" class="nav-tab"><?php esc_html_e( 'Citation Box', 'wp-academic-post-enhanced' ); ?></a>
                            <a href="#tab-style-social" class="nav-tab"><?php esc_html_e( 'Social Box', 'wp-academic-post-enhanced' ); ?></a>
                        </h2>
                        
                        <div id="tab-style-headings" class="tab-content active">
                            <?php wpa_render_specific_section( 'wpa_styling', 'wpa_heading_styling_section' ); ?>
                        </div>
                        <div id="tab-style-shadow" class="tab-content" style="display:none;">
                            <?php wpa_render_specific_section( 'wpa_styling', 'wpa_unified_box_shadow_section' ); ?>
                        </div>
                        <div id="tab-style-toc" class="tab-content" style="display:none;">
                            <?php wpa_render_specific_section( 'wpa_styling', 'wpa_toc_styling_section' ); ?>
                        </div>
                        <div id="tab-style-cite" class="tab-content" style="display:none;">
                            <?php wpa_render_specific_section( 'wpa_styling', 'wpa_citation_styling_section' ); ?>
                        </div>
                        <div id="tab-style-social" class="tab-content" style="display:none;">
                            <?php wpa_render_specific_section( 'wpa_styling', 'wpa_social_styling_section' ); ?>
                        </div>
                    </div>

                    <!-- 6. Homepage Builder -->
                    <div id="group-home" class="wpa-group-content">
                        <div class="tab-content active">
                            <?php 
                            wpa_render_specific_section( 'wpa_homepage', 'wpa_homepage_builder_section' ); 
                            if ( class_exists( 'WPA_Theme_Builder' ) ) {
                                WPA_Theme_Builder::get_instance()->render_builder_ui(); 
                            }
                            ?>
                        </div>
                    </div>

                </div>
            </div>

            <?php submit_button(); ?>
        </form>
        
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top: 30px; border-top: 1px solid #ddd; padding-top: 20px;">
            <input type="hidden" name="action" value="wpa_theme_reset_defaults">
            <?php wp_nonce_field( 'wpa_theme_reset_defaults_nonce', 'wpa_theme_reset_nonce' ); ?>
            <p class="description" style="color: #b32d2e;"><?php esc_html_e( 'Warning: This will reset all Custom Theme settings to their default values.', 'wp-academic-post-enhanced' ); ?></p>
            <?php submit_button( __( 'Reset to Defaults', 'wp-academic-post-enhanced' ), 'delete', 'wpa_reset_theme', false ); ?>
        </form>
    </div>
    <?php
}

/**
 * Handle Theme Reset Action.
 */
function wpa_theme_handle_reset() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Unauthorized access.', 'wp-academic-post-enhanced' ) );
    }

    check_admin_referer( 'wpa_theme_reset_defaults_nonce', 'wpa_theme_reset_nonce' );

    delete_option( 'wpa_homepage_settings' );
    delete_option( 'wpa_homepage_layout' );

    wp_redirect( admin_url( 'admin.php?page=wp-academic-post-enhanced-homepage&msg=reset-success' ) );
    exit;
}
add_action( 'admin_post_wpa_theme_reset_defaults', 'wpa_theme_handle_reset' );

// Callbacks
function wpa_homepage_section_callback() {}

function wpa_theme_font_select( $args ) {
    $options = get_option( 'wpa_homepage_settings' );
    $key = $args['label_for'];
    $default = isset($args['default']) ? $args['default'] : 'Inter';
    $val = isset( $options[$key] ) ? $options[$key] : $default;
    
    $fonts = [
        'Inter' => 'Inter (Modern Sans)',
        'Lexend' => 'Lexend (Readable Sans)',
        'Urbanist' => 'Urbanist (Clean Sans)',
        'Roboto' => 'Roboto (Sans)',
        'Open Sans' => 'Open Sans (Sans)',
        'Lato' => 'Lato (Sans)',
        'Montserrat' => 'Montserrat (Sans)',
        'Merriweather' => 'Merriweather (Academic Serif)',
        'Playfair Display' => 'Playfair Display (Elegant Serif)',
        'Lora' => 'Lora (Book Serif)',
        'Oswald' => 'Oswald (Condensed)',
        'Raleway' => 'Raleway (Light Sans)',
        'Poppins' => 'Poppins (Geometric Sans)'
    ];
    
    echo '<select name="wpa_homepage_settings[' . esc_attr( $key ) . ']">';
    foreach ( $fonts as $value => $label ) {
        echo '<option value="' . esc_attr( $value ) . '" ' . selected( $val, $value, false ) . '>' . esc_html( $label ) . '</option>';
    }
    echo '</select>';
}

function wpa_homepage_select_field( $args ) {
    $options = get_option( 'wpa_homepage_settings' );
    $key = $args['label_for'];
    $val = isset( $options[$key] ) ? $options[$key] : ( isset($args['default']) ? $args['default'] : '' );
    
    echo '<select name="wpa_homepage_settings[' . esc_attr( $key ) . ']">';
    foreach ( $args['options'] as $value => $label ) {
        echo '<option value="' . esc_attr( $value ) . '" ' . selected( $val, $value, false ) . '>' . esc_html( $label ) . '</option>';
    }
    echo '</select>';
}

function wpa_homepage_checkbox_field( $args ) {
    $options = get_option( 'wpa_homepage_settings' );
    $key = $args['label_for'];
    $default = isset($args['default']) ? $args['default'] : 0;
    $val = ! empty($options) ? ( isset($options[$key]) ? $options[$key] : 0 ) : $default;
    echo '<input type="hidden" name="wpa_homepage_settings[' . esc_attr( $key ) . ']" value="0">';
    echo '<label><input type="checkbox" name="wpa_homepage_settings[' . esc_attr( $key ) . ']" value="1" ' . checked( 1, $val, false ) . '> ' . esc_html( isset($args['desc']) ? $args['desc'] : '' ) . '</label>';
}

function wpa_homepage_color_field( $args ) {
    $options = get_option( 'wpa_homepage_settings' );
    $key = $args['label_for'];
    $default = isset($args['default']) ? $args['default'] : '#2563eb';
    $val = isset( $options[$key] ) ? $options[$key] : $default;
    echo '<input type="text" name="wpa_homepage_settings[' . esc_attr( $key ) . ']" value="' . esc_attr( $val ) . '" class="wpa-color-picker" data-default-color="' . esc_attr( $default ) . '">';
}

function wpa_homepage_text_field( $args ) {
    $options = get_option( 'wpa_homepage_settings' );
    $key = $args['label_for'];
    $val = isset( $options[$key] ) ? $options[$key] : ( isset($args['default']) ? $args['default'] : '' );
    echo '<input type="text" name="wpa_homepage_settings[' . esc_attr( $key ) . ']" value="' . esc_attr( $val ) . '" class="regular-text">';
}

function wpa_homepage_textarea_field( $args ) {
    $options = get_option( 'wpa_homepage_settings' );
    $key = $args['label_for'];
    $val = isset( $options[$key] ) ? $options[$key] : ( isset($args['default']) ? $args['default'] : '' );
    echo '<textarea name="wpa_homepage_settings[' . esc_attr( $key ) . ']" rows="3" cols="50" class="large-text">' . esc_textarea( $val ) . '</textarea>';
    if ( ! empty( $args['desc'] ) ) echo '<p class="description">' . esc_html( $args['desc'] ) . '</p>';
}

function wpa_homepage_image_field( $args ) {
    $options = get_option( 'wpa_homepage_settings' );
    $key = $args['label_for'];
    $val = isset( $options[$key] ) ? $options[$key] : '';
    echo '<input type="text" name="wpa_homepage_settings[' . esc_attr( $key ) . ']" value="' . esc_attr( $val ) . '" class="regular-text" placeholder="https://...">';
    echo '<p class="description">' . __( 'Enter an image URL.', 'wp-academic-post-enhanced' ) . '</p>';
}

function wpa_homepage_number_field( $args ) {
    $options = get_option( 'wpa_homepage_settings' );
    $key = $args['label_for'];
    $val = isset( $options[$key] ) ? $options[$key] : ( isset($args['default']) ? $args['default'] : '' );
    echo '<input type="number" name="wpa_homepage_settings[' . esc_attr( $key ) . ']" value="' . esc_attr( $val ) . '" class="small-text">';
}

function wpa_homepage_menu_select_field( $args ) {
    $options = get_option( 'wpa_homepage_settings' );
    $key = $args['label_for'];
    $val = isset( $options[$key] ) ? $options[$key] : '';
    
    $menus = wp_get_nav_menus();
    
    echo '<select name="wpa_homepage_settings[' . esc_attr( $key ) . ']">';
    echo '<option value="">' . __( '-- Select Menu --', 'wp-academic-post-enhanced' ) . '</option>';
    foreach ( $menus as $menu ) {
        echo '<option value="' . esc_attr( $menu->term_id ) . '" ' . selected( $val, $menu->term_id, false ) . '>' . esc_html( $menu->name ) . '</option>';
    }
    echo '</select>';
    echo '<p class="description">' . __( 'If selected, this menu will override the default location check.', 'wp-academic-post-enhanced' ) . '</p>';
}

function wpa_homepage_post_types_field( $args ) {
    $options = get_option( 'wpa_homepage_settings' );
    $key = $args['label_for'];
    $selected_types = isset( $options[$key] ) && is_array( $options[$key] ) ? $options[$key] : [];
    
    $post_types = get_post_types( ['public' => true], 'objects' );
    
    echo '<fieldset><legend class="screen-reader-text"><span>' . __( 'Post Types', 'wp-academic-post-enhanced' ) . '</span></legend>';
    foreach ( $post_types as $pt ) {
        if ( $pt->name === 'attachment' ) continue;
        $checked = in_array( $pt->name, $selected_types ) ? 'checked="checked"' : '';
        echo '<label style="display:block; margin-bottom: 5px;"><input type="checkbox" name="wpa_homepage_settings[' . esc_attr( $key ) . '][]" value="' . esc_attr( $pt->name ) . '" ' . $checked . '> ' . esc_html( $pt->label ) . ' (<code>' . esc_html( $pt->name ) . '</code>)</label>';
    }
    echo '</fieldset>';
}

// Styling Callbacks (Integrated)
function wpa_heading_styling_section_callback() { echo '<p>' . esc_html__( 'Customize the styling of post headings.', 'wp-academic-post-enhanced' ) . '</p>'; }
function wpa_heading_styling_color_field_callback() { $options = get_option('wpa_heading_styling_settings'); $color = isset($options['color']) ? $options['color'] : '#000000'; echo '<input type="text" name="wpa_heading_styling_settings[color]" value="' . esc_attr($color) . '" class="wpa-color-picker" />'; }
function wpa_heading_styling_color_dark_field_callback() { $options = get_option('wpa_heading_styling_settings'); $color = isset($options['color_dark']) ? $options['color_dark'] : '#ffffff'; echo '<input type="text" name="wpa_heading_styling_settings[color_dark]" value="' . esc_attr($color) . '" class="wpa-color-picker" />'; }
function wpa_heading_styling_font_size_field_callback() { $options = get_option('wpa_heading_styling_settings'); $font_size = isset($options['font_size']) ? $options['font_size'] : '24'; echo '<input type="number" name="wpa_heading_styling_settings[font_size]" value="' . esc_attr($font_size) . '" min="1" />'; }
function wpa_heading_styling_font_weight_field_callback() { $options = get_option('wpa_heading_styling_settings'); $font_weight = isset($options['font_weight']) ? $options['font_weight'] : 'bold'; $weights = ['normal', 'bold', 'bolder', 'lighter', '100', '200', '300', '400', '500', '600', '700', '800', '900']; echo '<select name="wpa_heading_styling_settings[font_weight]">'; foreach ($weights as $weight) { echo '<option value="' . esc_attr($weight) . '" ' . selected($font_weight, $weight, false) . '>' . esc_html(ucfirst($weight)) . '</option>'; } echo '</select>'; }
function wpa_heading_styling_text_decoration_field_callback() { $options = get_option('wpa_heading_styling_settings'); $text_decoration = isset($options['text_decoration']) ? $options['text_decoration'] : 'none'; $decorations = ['none', 'underline', 'overline', 'line-through']; echo '<select name="wpa_heading_styling_settings[text_decoration]">'; foreach ($decorations as $decoration) { echo '<option value="' . esc_attr($decoration) . '" ' . selected($text_decoration, $decoration, false) . '>' . esc_html(ucfirst($decoration)) . '</option>'; } echo '</select>'; }
function wpa_heading_styling_targeted_headings_field_callback() { $options = get_option('wpa_heading_styling_settings'); $targeted_headings = isset($options['targeted_headings']) ? $options['targeted_headings'] : []; $available_headings = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']; foreach ($available_headings as $heading) { echo '<label><input type="checkbox" name="wpa_heading_styling_settings[targeted_headings][]" value="' . esc_attr($heading) . '" ' . checked(in_array($heading, $targeted_headings), true, false) . ' /> ' . esc_html(strtoupper($heading)) . '</label><br />'; } }
function wpa_heading_styling_text_shadow_offset_x_field_callback() { $options = get_option('wpa_heading_styling_settings'); $offset_x = isset($options['text_shadow_offset_x']) ? $options['text_shadow_offset_x'] : 0; echo '<input type="number" name="wpa_heading_styling_settings[text_shadow_offset_x]" value="' . esc_attr($offset_x) . '" />px'; }
function wpa_heading_styling_text_shadow_offset_y_field_callback() { $options = get_option('wpa_heading_styling_settings'); $offset_y = isset($options['text_shadow_offset_y']) ? $options['text_shadow_offset_y'] : 0; echo '<input type="number" name="wpa_heading_styling_settings[text_shadow_offset_y]" value="' . esc_attr($offset_y) . '" />px'; }
function wpa_heading_styling_text_shadow_blur_radius_field_callback() { $options = get_option('wpa_heading_styling_settings'); $blur_radius = isset($options['text_shadow_blur_radius']) ? $options['text_shadow_blur_radius'] : 0; echo '<input type="number" name="wpa_heading_styling_settings[text_shadow_blur_radius]" value="' . esc_attr($blur_radius) . '" min="0" />px'; }
function wpa_heading_styling_text_shadow_color_field_callback() { $options = get_option('wpa_heading_styling_settings'); $color = isset($options['text_shadow_color']) ? $options['text_shadow_color'] : '#000000'; echo '<input type="text" name="wpa_heading_styling_settings[text_shadow_color]" value="' . esc_attr($color) . '" class="wpa-color-picker" />'; }

function wpa_toc_styling_section_callback(){ echo '<p>' . esc_html__('Customize the styling of the Table of Contents.', 'wp-academic-post-enhanced') . '</p>'; }
function wpa_toc_styling_bg_color_field_callback(){ $options = get_option('wpa_toc_styling_settings'); $color = isset($options['background_color']) ? $options['background_color'] : '#f9f9f9'; echo '<input type="text" name="wpa_toc_styling_settings[background_color]" value="' . esc_attr($color) . '" class="wpa-color-picker" />'; }
function wpa_toc_styling_border_color_field_callback(){ $options = get_option('wpa_toc_styling_settings'); $color = isset($options['border_color']) ? $options['border_color'] : '#ccc'; echo '<input type="text" name="wpa_toc_styling_settings[border_color]" value="' . esc_attr($color) . '" class="wpa-color-picker" />'; }
function wpa_toc_styling_title_color_field_callback(){ $options = get_option('wpa_toc_styling_settings'); $color = isset($options['title_color']) ? $options['title_color'] : '#333'; echo '<input type="text" name="wpa_toc_styling_settings[title_color]" value="' . esc_attr($color) . '" class="wpa-color-picker" />'; }
function wpa_toc_styling_link_color_field_callback(){ $options = get_option('wpa_toc_styling_settings'); $color = isset($options['link_color']) ? $options['link_color'] : '#0073aa'; echo '<input type="text" name="wpa_toc_styling_settings[link_color]" value="' . esc_attr($color) . '" class="wpa-color-picker" />'; }
function wpa_toc_styling_title_font_size_field_callback() { $options = get_option('wpa_toc_styling_settings'); $size = isset($options['title_font_size']) ? $options['title_font_size'] : 20; echo '<input type="number" name="wpa_toc_styling_settings[title_font_size]" value="' . esc_attr($size) . '" min="1" />'; }
function wpa_toc_styling_list_font_size_field_callback() { $options = get_option('wpa_toc_styling_settings'); $size = isset($options['list_font_size']) ? $options['list_font_size'] : 16; echo '<input type="number" name="wpa_toc_styling_settings[list_font_size]" value="' . esc_attr($size) . '" min="1" />'; }
function wpa_toc_styling_list_style_type_field_callback() { $options = get_option('wpa_toc_styling_settings'); $type = isset($options['list_style_type']) ? $options['list_style_type'] : 'disc'; $types = ['none', 'disc', 'circle', 'square', 'decimal']; echo '<select name="wpa_toc_styling_settings[list_style_type]">'; foreach ($types as $t) { echo '<option value="' . esc_attr($t) . '" ' . selected($type, $t, false) . '>' . esc_html(ucfirst($t)) . '</option>'; } echo '</select>'; }
function wpa_toc_styling_container_padding_top_bottom_field_callback() { $options = get_option('wpa_toc_styling_settings'); $padding = isset($options['container_padding_top_bottom']) ? $options['container_padding_top_bottom'] : 20; echo '<input type="number" name="wpa_toc_styling_settings[container_padding_top_bottom]" value="' . esc_attr($padding) . '" min="0" />'; }
function wpa_toc_styling_container_padding_left_right_field_callback() { $options = get_option('wpa_toc_styling_settings'); $padding = isset($options['container_padding_left_right']) ? $options['container_padding_left_right'] : 20; echo '<input type="number" name="wpa_toc_styling_settings[container_padding_left_right]" value="' . esc_attr($padding) . '" min="0" />'; }
function wpa_toc_styling_container_border_radius_field_callback() { $options = get_option('wpa_toc_styling_settings'); $radius = isset($options['container_border_radius']) ? $options['container_border_radius'] : 5; echo '<input type="number" name="wpa_toc_styling_settings[container_border_radius]" value="' . esc_attr($radius) . '" min="0" />'; }

function wpa_citation_styling_section_callback(){ echo '<p>' . esc_html__('Customize the styling of the Citation box.', 'wp-academic-post-enhanced') . '</p>'; }
function wpa_citation_styling_bg_color_field_callback(){ $options = get_option('wpa_citation_styling_settings'); $color = isset($options['background_color']) ? $options['background_color'] : '#f9f9f9'; echo '<input type="text" name="wpa_citation_styling_settings[background_color]" value="' . esc_attr($color) . '" class="wpa-color-picker" />'; }
function wpa_citation_styling_border_color_field_callback(){ $options = get_option('wpa_citation_styling_settings'); $color = isset($options['border_color']) ? $options['border_color'] : '#ccc'; echo '<input type="text" name="wpa_citation_styling_settings[border_color]" value="' . esc_attr($color) . '" class="wpa-color-picker" />'; }
function wpa_citation_styling_text_color_field_callback(){ $options = get_option('wpa_citation_styling_settings'); $color = isset($options['text_color']) ? $options['text_color'] : '#000'; echo '<input type="text" name="wpa_citation_styling_settings[text_color]" value="' . esc_attr($color) . '" class="wpa-color-picker" />'; }

function wpa_social_styling_section_callback(){ echo '<p>' . esc_html__('Customize the styling of the Social Sharing box.', 'wp-academic-post-enhanced') . '</p>'; }
function wpa_social_styling_bg_color_field_callback(){ $options = get_option('wpa_social_styling_settings'); $color = isset($options['background_color']) ? $options['background_color'] : '#f9f9f9'; echo '<input type="text" name="wpa_social_styling_settings[background_color]" value="' . esc_attr($color) . '" class="wpa-color-picker" />'; }
function wpa_social_styling_border_color_field_callback(){ $options = get_option('wpa_social_styling_settings'); $color = isset($options['border_color']) ? $options['border_color'] : '#ccc'; echo '<input type="text" name="wpa_social_styling_settings[border_color]" value="' . esc_attr($color) . '" class="wpa-color-picker" />'; }
function wpa_social_styling_text_color_field_callback(){ $options = get_option('wpa_social_styling_settings'); $color = isset($options['text_color']) ? $options['text_color'] : '#000'; echo '<input type="text" name="wpa_social_styling_settings[text_color]" value="' . esc_attr($color) . '" class="wpa-color-picker" />'; }

function wpa_unified_box_shadow_section_callback(){ echo '<p>' . esc_html__('Customize the box shadow for all feature boxes.', 'wp-academic-post-enhanced') . '</p>'; }
function wpa_unified_box_shadow_offset_x_field_callback(){ $options = get_option('wpa_unified_box_shadow_settings'); $offset_x = isset($options['box_shadow_offset_x']) ? $options['box_shadow_offset_x'] : 0; echo '<input type="number" name="wpa_unified_box_shadow_settings[box_shadow_offset_x]" value="' . esc_attr($offset_x) . '" />px'; }
function wpa_unified_box_shadow_offset_y_field_callback(){ $options = get_option('wpa_unified_box_shadow_settings'); $offset_y = isset($options['box_shadow_offset_y']) ? $options['box_shadow_offset_y'] : 0; echo '<input type="number" name="wpa_unified_box_shadow_settings[box_shadow_offset_y]" value="' . esc_attr($offset_y) . '" />px'; }
function wpa_unified_box_shadow_blur_radius_field_callback(){ $options = get_option('wpa_unified_box_shadow_settings'); $blur_radius = isset($options['box_shadow_blur_radius']) ? $options['box_shadow_blur_radius'] : 0; echo '<input type="number" name="wpa_unified_box_shadow_settings[box_shadow_blur_radius]" value="' . esc_attr($blur_radius) . '" min="0" />px'; }
function wpa_unified_box_shadow_color_field_callback(){ $options = get_option('wpa_unified_box_shadow_settings'); $color = isset($options['box_shadow_color']) ? $options['box_shadow_color'] : '#000000'; echo '<input type="text" name="wpa_unified_box_shadow_settings[box_shadow_color]" value="' . esc_attr($color) . '" class="wpa-color-picker" />'; }

// Sanitization functions from styling-admin.php
function wpa_sanitize_heading_styling_settings( $input ) {
    $sanitized = [];
    $defaults = [
        'color' => '#000000',
        'color_dark' => '#ffffff',
        'font_size' => '24',
        'font_weight' => 'bold',
        'text_decoration' => 'none',
        'targeted_headings' => ['h1', 'h2', 'h3'],
        'text_shadow_offset_x' => 0,
        'text_shadow_offset_y' => 0,
        'text_shadow_blur_radius' => 0,
        'text_shadow_color' => '#000000',
    ];
    $input = wp_parse_args( $input, $defaults );
    $sanitized['color'] = sanitize_hex_color( $input['color'] );
    $sanitized['color_dark'] = sanitize_hex_color( $input['color_dark'] );
    $sanitized['font_size'] = absint( $input['font_size'] );
    $sanitized['font_weight'] = in_array( $input['font_weight'], ['normal', 'bold', 'bolder', 'lighter', '100', '200', '300', '400', '500', '600', '700', '800', '900'] ) ? $input['font_weight'] : 'bold';
    $sanitized['text_decoration'] = in_array( $input['text_decoration'], ['none', 'underline', 'overline', 'line-through'] ) ? $input['text_decoration'] : 'none';
    $available_headings = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
    if ( is_array( $input['targeted_headings'] ) ) {
        $sanitized['targeted_headings'] = array_intersect( $input['targeted_headings'], $available_headings );
    } else {
        $sanitized['targeted_headings'] = $defaults['targeted_headings'];
    }
    $sanitized['text_shadow_offset_x'] = intval( $input['text_shadow_offset_x'] );
    $sanitized['text_shadow_offset_y'] = intval( $input['text_shadow_offset_y'] );
    $sanitized['text_shadow_blur_radius'] = absint( $input['text_shadow_blur_radius'] );
    $sanitized['text_shadow_color'] = sanitize_hex_color( $input['text_shadow_color'] );
    return $sanitized;
}

function wpa_sanitize_toc_styling_settings($input){ 
    $sanitized = []; 
    $defaults = [
        'background_color' => '#f9f9f9', 
        'border_color' => '#ccc', 
        'title_color' => '#333', 
        'link_color' => '#0073aa', 
        'title_font_size' => 20, 
        'list_font_size' => 16, 
        'list_style_type' => 'disc', 
        'container_padding_top_bottom' => 20, 
        'container_padding_left_right' => 20, 
        'container_border_radius' => 5,
    ]; 
    $input = wp_parse_args($input, $defaults); 
    $sanitized['background_color'] = sanitize_hex_color($input['background_color']); 
    $sanitized['border_color'] = sanitize_hex_color($input['border_color']); 
    $sanitized['title_color'] = sanitize_hex_color($input['title_color']); 
    $sanitized['link_color'] = sanitize_hex_color($input['link_color']); 
    $sanitized['title_font_size'] = absint($input['title_font_size']); 
    $sanitized['list_font_size'] = absint($input['list_font_size']); 
    $sanitized['list_style_type'] = in_array($input['list_style_type'], ['none', 'disc', 'circle', 'square', 'decimal']) ? $input['list_style_type'] : 'disc'; 
    $sanitized['container_padding_top_bottom'] = absint($input['container_padding_top_bottom']); 
    $sanitized['container_padding_left_right'] = absint($input['container_padding_left_right']); 
    $sanitized['container_border_radius'] = absint($input['container_border_radius']); 
    return $sanitized; 
}

function wpa_sanitize_citation_styling_settings($input){ 
    $sanitized = []; 
    $defaults = [
        'background_color' => '#f9f9f9', 
        'border_color' => '#ccc', 
        'text_color' => '#000',
    ]; 
    $input = wp_parse_args($input, $defaults); 
    $sanitized['background_color'] = sanitize_hex_color($input['background_color']); 
    $sanitized['border_color'] = sanitize_hex_color($input['border_color']); 
    $sanitized['text_color'] = sanitize_hex_color($input['text_color']); 
    return $sanitized; 
}

function wpa_sanitize_social_styling_settings($input){ 
    $sanitized = []; 
    $defaults = [
        'background_color' => '#f9f9f9', 
        'border_color' => '#ccc', 
        'text_color' => '#000',
    ]; 
    $input = wp_parse_args($input, $defaults); 
    $sanitized['background_color'] = sanitize_hex_color($input['background_color']); 
    $sanitized['border_color'] = sanitize_hex_color($input['border_color']); 
    $sanitized['text_color'] = sanitize_hex_color($input['text_color']); 
    return $sanitized; 
}

function wpa_sanitize_unified_box_shadow_settings($input){
    $sanitized = [];
    $defaults = [
        'box_shadow_offset_x' => 0,
        'box_shadow_offset_y' => 0,
        'box_shadow_blur_radius' => 0,
        'box_shadow_color' => '#000000',
    ];
    $input = wp_parse_args($input, $defaults);
    $sanitized['box_shadow_offset_x'] = intval( $input['box_shadow_offset_x'] );
    $sanitized['box_shadow_offset_y'] = intval( $input['box_shadow_offset_y'] );
    $sanitized['box_shadow_blur_radius'] = absint( $input['box_shadow_blur_radius'] );
    $sanitized['box_shadow_color'] = sanitize_hex_color( $input['box_shadow_color'] );
    return $sanitized;
}
