<?php
/**
 * Settings Registration and Sanitization for Courses.
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register Course settings.
 */
function wpa_register_course_settings() {
    $defaults = wpa_get_course_settings();

    register_setting(
        'wpa_course_options',
        'wpa_course_settings',
        [
            'type' => 'array',
            'sanitize_callback' => 'wpa_sanitize_course_settings',
            'default' => $defaults,
        ]
    );

    // Section: General
    add_settings_section('wpa_course_section_general', __('General Settings', 'wp-academic-post-enhanced'), 'wpa_course_section_general_callback', 'wpa_course');
    add_settings_field('wpa_course_slug_field', __('Course URL Slug', 'wp-academic-post-enhanced'), 'wpa_course_slug_field_callback', 'wpa_course', 'wpa_course_section_general');
    add_settings_field('wpa_course_lesson_slug_field', __('Lesson URL Slug', 'wp-academic-post-enhanced'), 'wpa_course_lesson_slug_field_callback', 'wpa_course', 'wpa_course_section_general');
    add_settings_field('wpa_course_sequential_field', __('Enforce Sequential Order', 'wp-academic-post-enhanced'), 'wpa_course_sequential_field_callback', 'wpa_course', 'wpa_course_section_general');

    // Section: Features
    add_settings_section('wpa_course_section_features', __('Active Modules', 'wp-academic-post-enhanced'), 'wpa_course_section_features_callback', 'wpa_course');
    add_settings_field('wpa_course_enable_sections', __('Enable Course Sections', 'wp-academic-post-enhanced'), 'wpa_course_enable_sections_callback', 'wpa_course', 'wpa_course_section_features');
    add_settings_field('wpa_course_enable_certificates', __('Enable Certificates', 'wp-academic-post-enhanced'), 'wpa_course_enable_certificates_callback', 'wpa_course', 'wpa_course_section_features');
    add_settings_field('wpa_course_enable_drip_content', __('Enable Drip Content', 'wp-academic-post-enhanced'), 'wpa_course_enable_drip_content_callback', 'wpa_course', 'wpa_course_section_features');
    add_settings_field('wpa_course_enable_emails', __('Enable Email Notifications', 'wp-academic-post-enhanced'), 'wpa_course_enable_emails_callback', 'wpa_course', 'wpa_course_section_features');

    // Section: Quizzes
    add_settings_section('wpa_course_section_quizzes', __('Quiz Settings', 'wp-academic-post-enhanced'), 'wpa_course_section_quizzes_callback', 'wpa_course');
    add_settings_field('wpa_course_enable_quizzes_field', __('Enable Quizzes', 'wp-academic-post-enhanced'), 'wpa_course_enable_quizzes_callback', 'wpa_course', 'wpa_course_section_quizzes');
    add_settings_field('wpa_course_quiz_label_field', __('Quiz Title Label', 'wp-academic-post-enhanced'), 'wpa_course_quiz_label_field_callback', 'wpa_course', 'wpa_course_section_quizzes');
    add_settings_field('wpa_course_quiz_success_msg_field', __('Success Message', 'wp-academic-post-enhanced'), 'wpa_course_quiz_success_msg_field_callback', 'wpa_course', 'wpa_course_section_quizzes');
    add_settings_field('wpa_course_quiz_error_msg_field', __('Error Message', 'wp-academic-post-enhanced'), 'wpa_course_quiz_error_msg_field_callback', 'wpa_course', 'wpa_course_section_quizzes');
    add_settings_field('wpa_course_quiz_show_correct_field', __('Show Correct Answer on Failure', 'wp-academic-post-enhanced'), 'wpa_course_quiz_show_correct_field_callback', 'wpa_course', 'wpa_course_section_quizzes');

    // Section: Course Page
    add_settings_section('wpa_course_section_course_page', __('Course Page Elements', 'wp-academic-post-enhanced'), 'wpa_course_section_course_page_callback', 'wpa_course');
    add_settings_field('wpa_course_grid_columns_field', __('Grid Columns (Archive/Shortcode)', 'wp-academic-post-enhanced'), 'wpa_course_grid_columns_field_callback', 'wpa_course', 'wpa_course_section_course_page');
    add_settings_field('wpa_course_lesson_list_style_field', __('Curriculum Layout', 'wp-academic-post-enhanced'), 'wpa_course_lesson_list_style_field_callback', 'wpa_course', 'wpa_course_section_course_page');
    add_settings_field('wpa_course_curriculum_style_field', __('Curriculum Design Style', 'wp-academic-post-enhanced'), 'wpa_course_curriculum_style_field_callback', 'wpa_course', 'wpa_course_section_course_page');
    add_settings_field('wpa_course_show_curriculum_duration_field', __('Show Lesson Duration', 'wp-academic-post-enhanced'), 'wpa_course_show_curriculum_duration_field_callback', 'wpa_course', 'wpa_course_section_course_page');
    add_settings_field('wpa_course_show_curriculum_icons_field', __('Show Lesson Icons', 'wp-academic-post-enhanced'), 'wpa_course_show_curriculum_icons_field_callback', 'wpa_course', 'wpa_course_section_course_page');
    add_settings_field('wpa_course_show_meta_header_field', __('Show Course Header Meta', 'wp-academic-post-enhanced'), 'wpa_course_show_meta_header_field_callback', 'wpa_course', 'wpa_course_section_course_page');
    add_settings_field('wpa_course_show_duration_field', __('Show Duration', 'wp-academic-post-enhanced'), 'wpa_course_show_duration_field_callback', 'wpa_course', 'wpa_course_section_course_page');
    add_settings_field('wpa_course_show_level_field', __('Show Difficulty Level', 'wp-academic-post-enhanced'), 'wpa_course_show_level_field_callback', 'wpa_course', 'wpa_course_section_course_page');
    add_settings_field('wpa_course_header_bg_color_field', __('Header Background Color', 'wp-academic-post-enhanced'), 'wpa_course_header_bg_color_field_callback', 'wpa_course', 'wpa_course_section_course_page');
    add_settings_field('wpa_course_header_text_color_field', __('Header Text Color', 'wp-academic-post-enhanced'), 'wpa_course_header_text_color_field_callback', 'wpa_course', 'wpa_course_section_course_page');

    // Section: Main Page (Filtering & Hero)
    add_settings_section('wpa_course_section_filtering', __('Main Page & Filtering', 'wp-academic-post-enhanced'), 'wpa_course_section_filtering_callback', 'wpa_course');
    
    // Slider Settings Sub-Section
    add_settings_field('wpa_course_section_slider_header', '<h3>' . __('Slider Settings', 'wp-academic-post-enhanced') . '</h3>', '__return_empty_string', 'wpa_course', 'wpa_course_section_filtering');
    add_settings_field('wpa_course_slider_enable_field', __('Enable Recent Posts Slider', 'wp-academic-post-enhanced'), 'wpa_course_slider_enable_field_callback', 'wpa_course', 'wpa_course_section_filtering');
    add_settings_field('wpa_course_slider_count_field', __('Number of Slides', 'wp-academic-post-enhanced'), 'wpa_course_slider_count_field_callback', 'wpa_course', 'wpa_course_section_filtering');
    add_settings_field('wpa_course_slider_autoplay_field', __('Autoplay', 'wp-academic-post-enhanced'), 'wpa_course_slider_autoplay_field_callback', 'wpa_course', 'wpa_course_section_filtering');
    add_settings_field('wpa_course_slider_interval_field', __('Autoplay Interval (ms)', 'wp-academic-post-enhanced'), 'wpa_course_slider_interval_field_callback', 'wpa_course', 'wpa_course_section_filtering');
    add_settings_field('wpa_course_slider_show_arrows_field', __('Show Navigation Arrows', 'wp-academic-post-enhanced'), 'wpa_course_slider_show_arrows_field_callback', 'wpa_course', 'wpa_course_section_filtering');
    add_settings_field('wpa_course_slider_show_dots_field', __('Show Pagination Dots', 'wp-academic-post-enhanced'), 'wpa_course_slider_show_dots_field_callback', 'wpa_course', 'wpa_course_section_filtering');
    add_settings_field('wpa_course_slider_pause_hover_field', __('Pause on Hover', 'wp-academic-post-enhanced'), 'wpa_course_slider_pause_hover_field_callback', 'wpa_course', 'wpa_course_section_filtering');
    
    // Existing Fields
    add_settings_field('wpa_course_section_hero_header', '<h3>' . __('Hero Header Settings', 'wp-academic-post-enhanced') . '</h3>', '__return_empty_string', 'wpa_course', 'wpa_course_section_filtering');
    add_settings_field('wpa_course_archive_hero_enable_field', __('Enable Hero Header', 'wp-academic-post-enhanced'), 'wpa_course_archive_hero_enable_field_callback', 'wpa_course', 'wpa_course_section_filtering');
    add_settings_field('wpa_course_archive_hero_title_field', __('Hero Title', 'wp-academic-post-enhanced'), 'wpa_course_archive_hero_title_field_callback', 'wpa_course', 'wpa_course_section_filtering');
    add_settings_field('wpa_course_archive_hero_text_field', __('Hero Description', 'wp-academic-post-enhanced'), 'wpa_course_archive_hero_text_field_callback', 'wpa_course', 'wpa_course_section_filtering');
    add_settings_field('wpa_course_archive_hero_image_field', __('Hero Image URL', 'wp-academic-post-enhanced'), 'wpa_course_archive_hero_image_field_callback', 'wpa_course', 'wpa_course_section_filtering');
    add_settings_field('wpa_course_archive_hero_layout_field', __('Hero Layout Style', 'wp-academic-post-enhanced'), 'wpa_course_archive_hero_layout_field_callback', 'wpa_course', 'wpa_course_section_filtering');
    
    add_settings_field('wpa_course_hide_archive_title_field', __('Hide Default Page Title', 'wp-academic-post-enhanced'), 'wpa_course_hide_archive_title_field_callback', 'wpa_course', 'wpa_course_section_filtering');
    add_settings_field('wpa_course_enable_course_filters_field', __('Enable Course Filters', 'wp-academic-post-enhanced'), 'wpa_course_enable_course_filters_field_callback', 'wpa_course', 'wpa_course_section_filtering');
    add_settings_field('wpa_course_filter_by_level_field', __('Show "Level" Filter', 'wp-academic-post-enhanced'), 'wpa_course_filter_by_level_field_callback', 'wpa_course', 'wpa_course_section_filtering');
    add_settings_field('wpa_course_filter_by_price_field', __('Show "Price" Filter', 'wp-academic-post-enhanced'), 'wpa_course_filter_by_price_field_callback', 'wpa_course', 'wpa_course_section_filtering');
    add_settings_field('wpa_course_filter_by_search_field', __('Show Search Bar', 'wp-academic-post-enhanced'), 'wpa_course_filter_by_search_field_callback', 'wpa_course', 'wpa_course_section_filtering');

    // Section: Student Dashboard
    add_settings_section('wpa_course_section_dashboard', __('Student Dashboard', 'wp-academic-post-enhanced'), 'wpa_course_section_dashboard_callback', 'wpa_course');
    add_settings_field('wpa_course_dashboard_hide_title_field', __('Hide Page Title', 'wp-academic-post-enhanced'), 'wpa_course_dashboard_hide_title_field_callback', 'wpa_course', 'wpa_course_section_dashboard');
    add_settings_field('wpa_course_dashboard_hero_bg_field', __('Header Background Image', 'wp-academic-post-enhanced'), 'wpa_course_dashboard_hero_bg_field_callback', 'wpa_course', 'wpa_course_section_dashboard');
    add_settings_field('wpa_course_dashboard_show_avatar_field', __('Show Student Avatar', 'wp-academic-post-enhanced'), 'wpa_course_dashboard_show_avatar_field_callback', 'wpa_course', 'wpa_course_section_dashboard');
    add_settings_field('wpa_course_dashboard_welcome_text_field', __('Welcome Message', 'wp-academic-post-enhanced'), 'wpa_course_dashboard_welcome_text_field_callback', 'wpa_course', 'wpa_course_section_dashboard');

    // Section: Lesson Page
    add_settings_section('wpa_course_section_lesson_page', __('Lesson Page Elements', 'wp-academic-post-enhanced'), 'wpa_course_section_lesson_page_callback', 'wpa_course');
    add_settings_field('wpa_course_show_breadcrumbs_field', __('Show Breadcrumbs', 'wp-academic-post-enhanced'), 'wpa_course_show_breadcrumbs_field_callback', 'wpa_course', 'wpa_course_section_lesson_page');
    add_settings_field('wpa_course_enable_focus_mode_field', __('Enable Focus Mode', 'wp-academic-post-enhanced'), 'wpa_course_enable_focus_mode_field_callback', 'wpa_course', 'wpa_course_section_lesson_page');
    add_settings_field('wpa_course_focus_mode_pos_field', __('Focus Mode Button Position', 'wp-academic-post-enhanced'), 'wpa_course_focus_mode_pos_field_callback', 'wpa_course', 'wpa_course_section_lesson_page');
    add_settings_field('wpa_course_show_lesson_index_field', __('Show Lesson Index (e.g. Lesson 1 of 5)', 'wp-academic-post-enhanced'), 'wpa_course_show_lesson_index_field_callback', 'wpa_course', 'wpa_course_section_lesson_page');
    add_settings_field('wpa_course_show_lesson_author_field', __('Show Lesson Author', 'wp-academic-post-enhanced'), 'wpa_course_show_lesson_author_field_callback', 'wpa_course', 'wpa_course_section_lesson_page');
    add_settings_field('wpa_course_show_lesson_date_field', __('Show Last Updated Date', 'wp-academic-post-enhanced'), 'wpa_course_show_lesson_date_field_callback', 'wpa_course', 'wpa_course_section_lesson_page');
    add_settings_field('wpa_course_materials_field', __('Downloadable Materials', 'wp-academic-post-enhanced'), 'wpa_course_materials_field_callback', 'wpa_course', 'wpa_course_section_lesson_page');
    add_settings_field('wpa_course_show_sidebar_field', __('Show Lesson Sidebar', 'wp-academic-post-enhanced'), 'wpa_course_show_sidebar_field_callback', 'wpa_course', 'wpa_course_section_lesson_page');
    add_settings_field('wpa_course_sidebar_pos_field', __('Sidebar Position', 'wp-academic-post-enhanced'), 'wpa_course_sidebar_pos_field_callback', 'wpa_course', 'wpa_course_section_lesson_page');
    add_settings_field('wpa_course_show_sidebar_progress_field', __('Show Sidebar Progress Bar', 'wp-academic-post-enhanced'), 'wpa_course_show_sidebar_progress_field_callback', 'wpa_course', 'wpa_course_section_lesson_page');
    add_settings_field('wpa_course_video_pos_field', __('Video Position', 'wp-academic-post-enhanced'), 'wpa_course_video_pos_field_callback', 'wpa_course', 'wpa_course_section_lesson_page');
    add_settings_field('wpa_course_show_nav_buttons_field', __('Show Navigation Buttons (Prev/Next/Complete)', 'wp-academic-post-enhanced'), 'wpa_course_show_nav_buttons_field_callback', 'wpa_course', 'wpa_course_section_lesson_page');
    add_settings_field('wpa_course_show_lesson_instructor_field', __('Show Instructor Card in Sidebar', 'wp-academic-post-enhanced'), 'wpa_course_show_lesson_instructor_field_callback', 'wpa_course', 'wpa_course_section_lesson_page');

    // Section: Certificates
    add_settings_section('wpa_course_section_certificates', __('Certificate Design', 'wp-academic-post-enhanced'), 'wpa_course_section_certificates_callback', 'wpa_course');
    add_settings_field('wpa_course_cert_style_field', __('Layout Style', 'wp-academic-post-enhanced'), 'wpa_course_cert_style_field_callback', 'wpa_course', 'wpa_course_section_certificates');
    add_settings_field('wpa_course_cert_title_field', __('Certificate Title', 'wp-academic-post-enhanced'), 'wpa_course_cert_title_field_callback', 'wpa_course', 'wpa_course_section_certificates');
    add_settings_field('wpa_course_cert_subtitle_field', __('Subtitle / Intro Text', 'wp-academic-post-enhanced'), 'wpa_course_cert_subtitle_field_callback', 'wpa_course', 'wpa_course_section_certificates');
    add_settings_field('wpa_course_cert_completion_text_field', __('Completion Text', 'wp-academic-post-enhanced'), 'wpa_course_cert_completion_text_field_callback', 'wpa_course', 'wpa_course_section_certificates');
    add_settings_field('wpa_course_cert_logo_field', __('Logo URL', 'wp-academic-post-enhanced'), 'wpa_course_cert_logo_field_callback', 'wpa_course', 'wpa_course_section_certificates');
    add_settings_field('wpa_course_cert_text_color_field', __('Text Color', 'wp-academic-post-enhanced'), 'wpa_course_cert_text_color_field_callback', 'wpa_course', 'wpa_course_section_certificates');
    add_settings_field('wpa_course_cert_bg_color_field', __('Background Color', 'wp-academic-post-enhanced'), 'wpa_course_cert_bg_color_field_callback', 'wpa_course', 'wpa_course_section_certificates');
    add_settings_field('wpa_course_cert_border_color_field', __('Border Color', 'wp-academic-post-enhanced'), 'wpa_course_cert_border_color_field_callback', 'wpa_course', 'wpa_course_section_certificates');
    add_settings_field('wpa_course_cert_border_width_field', __('Border Width (px)', 'wp-academic-post-enhanced'), 'wpa_course_cert_border_width_field_callback', 'wpa_course', 'wpa_course_section_certificates');
    add_settings_field('wpa_course_cert_orientation_field', __('Orientation', 'wp-academic-post-enhanced'), 'wpa_course_cert_orientation_field_callback', 'wpa_course', 'wpa_course_section_certificates');
    add_settings_field('wpa_course_cert_font_field', __('Font Family', 'wp-academic-post-enhanced'), 'wpa_course_cert_font_field_callback', 'wpa_course', 'wpa_course_section_certificates');
    add_settings_field('wpa_course_cert_bg_image_field', __('Background Image URL', 'wp-academic-post-enhanced'), 'wpa_course_cert_bg_image_field_callback', 'wpa_course', 'wpa_course_section_certificates');
    add_settings_field('wpa_course_cert_instructor_name_field', __('Instructor Name', 'wp-academic-post-enhanced'), 'wpa_course_cert_instructor_name_field_callback', 'wpa_course', 'wpa_course_section_certificates');
    add_settings_field('wpa_course_cert_signature_field', __('Signature Image URL', 'wp-academic-post-enhanced'), 'wpa_course_cert_signature_field_callback', 'wpa_course', 'wpa_course_section_certificates');

    // Section: Emails
    add_settings_section('wpa_course_section_emails', __('Email Templates', 'wp-academic-post-enhanced'), 'wpa_course_section_emails_callback', 'wpa_course');
    add_settings_field('wpa_course_email_welcome_subject_field', __('Welcome Email Subject', 'wp-academic-post-enhanced'), 'wpa_course_email_welcome_subject_field_callback', 'wpa_course', 'wpa_course_section_emails');
    add_settings_field('wpa_course_email_welcome_body_field', __('Welcome Email Body', 'wp-academic-post-enhanced'), 'wpa_course_email_welcome_body_field_callback', 'wpa_course', 'wpa_course_section_emails');
    add_settings_field('wpa_course_email_complete_subject_field', __('Completion Email Subject', 'wp-academic-post-enhanced'), 'wpa_course_email_complete_subject_field_callback', 'wpa_course', 'wpa_course_section_emails');
    add_settings_field('wpa_course_email_complete_body_field', __('Completion Email Body', 'wp-academic-post-enhanced'), 'wpa_course_email_complete_body_field_callback', 'wpa_course', 'wpa_course_section_emails');
    add_settings_field('wpa_course_email_quiz_passed_subject_field', __('Quiz Passed Subject', 'wp-academic-post-enhanced'), 'wpa_course_email_quiz_passed_subject_field_callback', 'wpa_course', 'wpa_course_section_emails');
    add_settings_field('wpa_course_email_quiz_passed_body_field', __('Quiz Passed Body', 'wp-academic-post-enhanced'), 'wpa_course_email_quiz_passed_body_field_callback', 'wpa_course', 'wpa_course_section_emails');
}
add_action( 'admin_init', 'wpa_register_course_settings' );

function wpa_sanitize_course_settings( $input ) {
    $sanitized = [];
    $sanitized['course_slug'] = sanitize_title( $input['course_slug'] );
    $sanitized['lesson_slug'] = sanitize_title( $input['lesson_slug'] );
    $sanitized['enable_sections'] = isset( $input['enable_sections'] ) ? 1 : 0;
    $sanitized['enable_certificates'] = isset( $input['enable_certificates'] ) ? 1 : 0;
    $sanitized['enable_drip_content'] = isset( $input['enable_drip_content'] ) ? 1 : 0;
    $sanitized['enable_emails'] = isset( $input['enable_emails'] ) ? 1 : 0;
    $sanitized['enable_quizzes'] = isset( $input['enable_quizzes'] ) ? 1 : 0;
    $sanitized['quiz_label'] = sanitize_text_field( $input['quiz_label'] );
    $sanitized['quiz_success_msg'] = sanitize_text_field( $input['quiz_success_msg'] );
    $sanitized['quiz_error_msg'] = sanitize_text_field( $input['quiz_error_msg'] );
    $sanitized['quiz_show_correct'] = isset( $input['quiz_show_correct'] ) ? 1 : 0;
    
    $sanitized['show_course_meta_header'] = isset( $input['show_course_meta_header'] ) ? 1 : 0;
    $sanitized['show_course_duration'] = isset( $input['show_course_duration'] ) ? 1 : 0;
    $sanitized['show_course_level'] = isset( $input['show_course_level'] ) ? 1 : 0;
    $sanitized['course_header_bg_color'] = sanitize_hex_color( $input['course_header_bg_color'] );
    $sanitized['course_header_text_color'] = sanitize_hex_color( $input['course_header_text_color'] );

    $sanitized['cert_title'] = sanitize_text_field( $input['cert_title'] );
    $sanitized['cert_subtitle'] = sanitize_text_field( $input['cert_subtitle'] );
    $sanitized['cert_completion_text'] = sanitize_text_field( $input['cert_completion_text'] );
    $sanitized['cert_logo'] = esc_url_raw( $input['cert_logo'] );
    $sanitized['cert_background_image'] = esc_url_raw( $input['cert_background_image'] );
    
    $sanitized['cert_style'] = in_array( $input['cert_style'], ['classic', 'minimal', 'fancy'] ) ? $input['cert_style'] : 'classic';
    $sanitized['cert_text_color'] = sanitize_hex_color( $input['cert_text_color'] );
    $sanitized['cert_bg_color'] = sanitize_hex_color( $input['cert_bg_color'] );
    $sanitized['cert_border_color'] = sanitize_hex_color( $input['cert_border_color'] );
    $sanitized['cert_border_width'] = absint( $input['cert_border_width'] );
    
    $sanitized['cert_orientation'] = in_array( $input['cert_orientation'], ['landscape', 'portrait'] ) ? $input['cert_orientation'] : 'landscape';
    $sanitized['cert_font'] = sanitize_text_field( $input['cert_font'] );
    $sanitized['cert_instructor_name'] = sanitize_text_field( $input['cert_instructor_name'] );
    $sanitized['cert_signature'] = esc_url_raw( $input['cert_signature'] );

    $sanitized['email_welcome_subject'] = sanitize_text_field( $input['email_welcome_subject'] );
    $sanitized['email_welcome_body'] = sanitize_textarea_field( $input['email_welcome_body'] );
    $sanitized['email_complete_subject'] = sanitize_text_field( $input['email_complete_subject'] );
    $sanitized['email_complete_body'] = sanitize_textarea_field( $input['email_complete_body'] );
    $sanitized['email_quiz_passed_subject'] = sanitize_text_field( $input['email_quiz_passed_subject'] );
    $sanitized['email_quiz_passed_body'] = sanitize_textarea_field( $input['email_quiz_passed_body'] );

    $sanitized['grid_columns'] = absint( $input['grid_columns'] );
    $sanitized['lesson_list_style'] = in_array( $input['lesson_list_style'], ['simple', 'boxed', 'grid', 'z-pattern', 'timeline'] ) ? $input['lesson_list_style'] : 'simple';
    $sanitized['curriculum_style'] = in_array( $input['curriculum_style'], ['default', 'modern', 'clean', 'professional', 'glass', 'academic'] ) ? $input['curriculum_style'] : 'default';
    $sanitized['show_curriculum_duration'] = isset( $input['show_curriculum_duration'] ) ? 1 : 0;
    $sanitized['show_curriculum_icons'] = isset( $input['show_curriculum_icons'] ) ? 1 : 0;
    $sanitized['sidebar_position'] = in_array( $input['sidebar_position'], ['left', 'right'] ) ? $input['sidebar_position'] : 'right';
    $sanitized['video_position'] = in_array( $input['video_position'], ['top', 'bottom'] ) ? $input['video_position'] : 'top';
    $sanitized['enable_materials'] = isset( $input['enable_materials'] ) ? 1 : 0;
    $sanitized['show_breadcrumbs'] = isset( $input['show_breadcrumbs'] ) ? 1 : 0;
    $sanitized['show_lesson_index'] = isset( $input['show_lesson_index'] ) ? 1 : 0;
    $sanitized['show_lesson_author'] = isset( $input['show_lesson_author'] ) ? 1 : 0;
    $sanitized['show_lesson_date'] = isset( $input['show_lesson_date'] ) ? 1 : 0;
    $sanitized['show_sidebar'] = isset( $input['show_sidebar'] ) ? 1 : 0;
    $sanitized['show_nav_buttons'] = isset( $input['show_nav_buttons'] ) ? 1 : 0;
    $sanitized['show_sidebar_progress'] = isset( $input['show_sidebar_progress'] ) ? 1 : 0;
    $sanitized['show_lesson_instructor'] = isset( $input['show_lesson_instructor'] ) ? 1 : 0;
    $sanitized['enforce_sequential'] = isset( $input['enforce_sequential'] ) ? 1 : 0;
    $sanitized['enable_focus_mode'] = isset( $input['enable_focus_mode'] ) ? 1 : 0;
    $sanitized['focus_mode_position'] = in_array( $input['focus_mode_position'], ['header', 'breadcrumbs', 'floating'] ) ? $input['focus_mode_position'] : 'header';
    
    $sanitized['primary_color'] = sanitize_hex_color( $input['primary_color'] );
    $sanitized['accent_color'] = sanitize_hex_color( $input['accent_color'] );
    $sanitized['lesson_title_font_size'] = sanitize_text_field( $input['lesson_title_font_size'] );

    // Hero
    $sanitized['archive_hero_enable'] = isset( $input['archive_hero_enable'] ) ? 1 : 0;
    $sanitized['archive_hero_title'] = sanitize_text_field( $input['archive_hero_title'] );
    $sanitized['archive_hero_text'] = sanitize_textarea_field( $input['archive_hero_text'] );
    $sanitized['archive_hero_image'] = absint( $input['archive_hero_image'] );
    $sanitized['archive_hero_layout'] = in_array( $input['archive_hero_layout'], ['split', 'banner', 'minimal'] ) ? $input['archive_hero_layout'] : 'split';

    // Dashboard
    $sanitized['dashboard_hero_bg'] = absint( $input['dashboard_hero_bg'] );
    $sanitized['dashboard_hide_title'] = isset( $input['dashboard_hide_title'] ) ? 1 : 0;
    $sanitized['dashboard_show_avatar'] = isset( $input['dashboard_show_avatar'] ) ? 1 : 0;
    $sanitized['dashboard_welcome_text'] = sanitize_text_field( $input['dashboard_welcome_text'] );

    // Filtering
    $sanitized['enable_course_filters'] = isset( $input['enable_course_filters'] ) ? 1 : 0;
    $sanitized['filter_by_level'] = isset( $input['filter_by_level'] ) ? 1 : 0;
    $sanitized['filter_by_price'] = isset( $input['filter_by_price'] ) ? 1 : 0;
    $sanitized['filter_by_search'] = isset( $input['filter_by_search'] ) ? 1 : 0;
    $sanitized['hide_archive_title'] = isset( $input['hide_archive_title'] ) ? 1 : 0;

    // Slider
    $sanitized['slider_enable'] = isset( $input['slider_enable'] ) ? 1 : 0;
    $sanitized['slider_count'] = absint( $input['slider_count'] );
    $sanitized['slider_autoplay'] = isset( $input['slider_autoplay'] ) ? 1 : 0;
    $sanitized['slider_interval'] = absint( $input['slider_interval'] );
    $sanitized['slider_show_arrows'] = isset( $input['slider_show_arrows'] ) ? 1 : 0;
    $sanitized['slider_show_dots'] = isset( $input['slider_show_dots'] ) ? 1 : 0;
    $sanitized['slider_pause_hover'] = isset( $input['slider_pause_hover'] ) ? 1 : 0;
    
    $old_settings = get_option( 'wpa_course_settings' );
    if ( isset($old_settings['course_slug']) && $old_settings['course_slug'] !== $sanitized['course_slug'] || isset($old_settings['lesson_slug']) && $old_settings['lesson_slug'] !== $sanitized['lesson_slug'] ) {
        set_transient( 'wpa_flush_course_rules', true, 60 );
    }
    
    return $sanitized;
}

/**
 * Handle delayed flush
 */
function wpa_course_maybe_flush_rules() {
    if ( get_transient( 'wpa_flush_course_rules' ) ) {
        delete_transient( 'wpa_flush_course_rules' );
        flush_rewrite_rules();
    }
}
add_action( 'init', 'wpa_course_maybe_flush_rules', 20 );