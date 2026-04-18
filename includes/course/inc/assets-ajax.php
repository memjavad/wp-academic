<?php
/**
 * Assets Enqueue and AJAX Handlers for Courses.
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Enqueue Course Styles & Scripts
 */
function wpa_course_enqueue_assets() {
    if ( is_singular( 'wpa_course' ) || is_singular( 'wpa_lesson' ) || has_shortcode( get_the_content(), 'wpa_student_dashboard' ) || has_shortcode( get_the_content(), 'wpa_courses' ) ) {
        wp_enqueue_style( 'wpa-course-styles', plugin_dir_url( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'assets/css/course.css', [], '1.6' );
        
        // CSS Variables for custom colors
        $options = get_option( 'wpa_course_settings' );
        $primary = ! empty( $options['primary_color'] ) ? $options['primary_color'] : '#2563eb';
        $accent = ! empty( $options['accent_color'] ) ? $options['accent_color'] : '#10b981';
        $title_size = ! empty( $options['lesson_title_font_size'] ) ? $options['lesson_title_font_size'] : '3.5';
        
        $custom_css = ":root { --wpa-course-primary: {$primary}; --wpa-course-accent: {$accent}; --wpa-lesson-title-size: {$title_size}em; }";
        wp_add_inline_style( 'wpa-course-styles', $custom_css );

        if ( is_user_logged_in() ) {
            wp_enqueue_script( 'wpa-course-frontend', plugin_dir_url( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'assets/js/course-frontend.js', ['jquery'], '1.2', true );
            
            // Localize script with dynamic labels from settings
            $label_mark_complete = isset( $options['label_mark_complete'] ) ? $options['label_mark_complete'] : __( 'Mark Complete', 'wp-academic-post-enhanced' );
            $label_completed = isset( $options['label_completed'] ) ? $options['label_completed'] : __( 'Completed', 'wp-academic-post-enhanced' );

            wp_localize_script( 'wpa-course-frontend', 'wpa_course_vars', [
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'wpa_course_progress_nonce' ),
                'complete_text' => $label_mark_complete,
                'completed_text' => $label_completed,
                'enroll_nonce' => wp_create_nonce( 'wpa_course_enroll_nonce' ),
            ] );
        }
    }
}
add_action( 'wp_enqueue_scripts', 'wpa_course_enqueue_assets' );

/**
 * AJAX: Mark Lesson Complete
 */
function wpa_course_ajax_mark_complete() {
    check_ajax_referer( 'wpa_course_progress_nonce', 'nonce' );
    if ( ! is_user_logged_in() ) wp_send_json_error();

    $lesson_id = intval( $_POST['lesson_id'] );
    $user_id = get_current_user_id();
    $completed = get_user_meta( $user_id, '_wpa_completed_lessons', true );
    if ( ! is_array( $completed ) ) $completed = [];

    if ( ! in_array( $lesson_id, $completed ) ) {
        $completed[] = $lesson_id;
        update_user_meta( $user_id, '_wpa_completed_lessons', $completed );
        do_action( 'wpa_lesson_completed', $lesson_id, $user_id );
    }

    wp_send_json_success();
}
add_action( 'wp_ajax_wpa_course_mark_complete', 'wpa_course_ajax_mark_complete' );

/**
 * AJAX: Enroll in Course
 */
function wpa_course_ajax_enroll() {
    check_ajax_referer( 'wpa_course_enroll_nonce', 'nonce' );
    if ( ! is_user_logged_in() ) wp_send_json_error();

    $course_id = intval( $_POST['course_id'] );
    $user_id = get_current_user_id();
    
    // Check Prerequisite
    $prereq_id = get_post_meta( $course_id, '_wpa_course_prerequisite', true );
    if ( $prereq_id && ! wpa_course_is_course_completed( $prereq_id, $user_id ) ) {
        $prereq_title = get_the_title( $prereq_id );
        wp_send_json_error( sprintf( __( 'You must complete "%s" before enrolling in this course.', 'wp-academic-post-enhanced' ), $prereq_title ) );
    }

    $enrolled = get_user_meta( $user_id, '_wpa_enrolled_courses', true );
    if ( ! is_array( $enrolled ) ) $enrolled = [];

    if ( ! in_array( $course_id, $enrolled ) ) {
        $enrolled[] = $course_id;
        update_user_meta( $user_id, '_wpa_enrolled_courses', $enrolled );
        // Track enrollment date for Drip Content
        update_user_meta( $user_id, '_wpa_enrollment_date_' . $course_id, current_time( 'mysql' ) );
        do_action( 'wpa_course_user_enrolled', $course_id, $user_id );
    }

    wp_send_json_success();
}
add_action( 'wp_ajax_wpa_course_enroll', 'wpa_course_ajax_enroll' );
