<?php
/**
 * Helper Functions for Courses.
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Access Control Check
 */
function wpa_course_user_can_access( $course_id ) {
    $restriction = get_post_meta( $course_id, '_wpa_course_restriction', true );
    if ( $restriction === 'logged_in' && ! is_user_logged_in() ) {
        return false;
    }
    return true;
}

/**
 * Helper: Detect if string is RTL
 */
function wpa_is_rtl( $string ) {
    $rtl_chars_pattern = '/[\x{0590}-\x{05ff}\x{0600}-\x{06ff}\x{0750}-\x{077f}\x{08a0}-\x{08ff}\x{fb50}-\x{fdff}\x{fe70}-\x{feff}]/u';
    return preg_match( $rtl_chars_pattern, $string ) === 1;
}

/**
 * Helper: Check if user is enrolled
 */
function wpa_course_is_user_enrolled( $course_id, $user_id = 0 ) {
    if ( ! $user_id ) $user_id = get_current_user_id();
    if ( ! $user_id ) return false;
    
    $enrolled = get_user_meta( $user_id, '_wpa_enrolled_courses', true );
    return ( is_array( $enrolled ) && in_array( $course_id, $enrolled ) );
}

/**
 * Helper: Check if lesson is completed
 */
function wpa_course_is_lesson_completed( $lesson_id, $user_id = 0 ) {
    if ( ! $user_id ) $user_id = get_current_user_id();
    if ( ! $user_id ) return false;
    
    $completed = get_user_meta( $user_id, '_wpa_completed_lessons', true );
    return ( is_array( $completed ) && in_array( $lesson_id, $completed ) );
}

/**
 * Helper: Get Course Progress
 */
function wpa_course_get_progress( $course_id, $user_id = 0 ) {
    if ( ! $user_id ) $user_id = get_current_user_id();
    if ( ! $user_id ) return 0;

    $lessons = get_posts( [
        'post_type' => 'wpa_lesson',
        'meta_key' => '_wpa_course_id',
        'meta_value' => $course_id,
        'posts_per_page' => -1,
        'fields' => 'ids',
    ] );

    if ( empty( $lessons ) ) return 0;

    $completed = get_user_meta( $user_id, '_wpa_completed_lessons', true );
    if ( ! is_array( $completed ) ) $completed = [];

    $count_completed = count( array_intersect( $lessons, $completed ) );
    
    return round( ( $count_completed / count( $lessons ) ) * 100 );
}

/**
 * Helper: Check if course is completed
 */
function wpa_course_is_course_completed( $course_id, $user_id = 0 ) {
    return wpa_course_get_progress( $course_id, $user_id ) >= 100;
}

/**
 * Helper: Get All Courses List (ID => Title)
 */
function wpa_get_all_courses_list() {
    $courses = get_posts( [
        'post_type' => 'wpa_course',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'title',
        'order' => 'ASC',
    ] );

    $list = [];
    foreach ( $courses as $course ) {
        $list[ $course->ID ] = $course->post_title;
    }
    return $list;
}

/**
 * Centrally get course settings with language-aware defaults.
 */
function wpa_get_course_settings() {
    $is_arabic = ( get_locale() === 'ar' || is_rtl() );
    
    $defaults = [
        'course_slug' => 'course',
        'lesson_slug' => 'lesson',
        'enable_sections' => 1,
        'enable_certificates' => 1,
        'enable_drip_content' => 1,
        'enable_emails' => 1,
        'enable_quizzes' => 1,
        'quiz_label' => $is_arabic ? 'اختبار الدرس' : 'Lesson Quiz',
        'quiz_success_msg' => $is_arabic ? 'صحيح! يمكنك الآن إكمال الدرس.' : 'Correct! You can now complete the lesson.',
        'quiz_error_msg' => $is_arabic ? 'غير صحيح. حاول مرة أخرى.' : 'Incorrect. Please try again.',
        'quiz_show_correct' => 0,
        'show_course_meta_header' => 1,
        'show_course_duration' => 1,
        'show_course_level' => 1,
        'course_header_bg_color' => '#f3f4f6',
        'course_header_text_color' => '#1f2937',
        'cert_title' => $is_arabic ? 'شهادة إتمام' : 'Certificate of Completion',
        'cert_subtitle' => $is_arabic ? 'تشهد بأن' : 'This is to certify that',
        'cert_completion_text' => $is_arabic ? 'أكمل بنجاح الدورة' : 'Has successfully completed the course',
        'email_welcome_subject' => $is_arabic ? 'مرحبًا بك في {course_title}' : 'Welcome to {course_title}',
        'email_welcome_body' => $is_arabic ? "مرحبًا {student_name}، لقد قمت بالتسجيل بنجاح في {course_title}. ابدأ التعلم الآن: {course_link}" : "Hi {student_name}, You have successfully enrolled in {course_title}. Start learning now: {course_link}",
        'email_complete_subject' => $is_arabic ? 'تهانينا! لقد أكملت {course_title}' : 'Congratulations! You completed {course_title}',
        'email_complete_body' => $is_arabic ? "مرحبًا {student_name}، لقد أكملت بنجاح الدورة التدريبية {course_title}. قم بتنزيل شهادتك هنا: {certificate_link}" : "Hi {student_name}, You have successfully completed the course {course_title}. Download your certificate here: {certificate_link}",
        'email_quiz_passed_subject' => $is_arabic ? 'لقد اجتزت اختبار {lesson_title}' : 'You passed the quiz for {lesson_title}',
        'email_quiz_passed_body' => $is_arabic ? "مرحبًا {student_name}، تهانينا! لقد اجتزت بنجاح اختبار {lesson_title}." : "Hi {student_name}, Congratulations! You have successfully passed the quiz for {lesson_title}.",
        'label_start_course' => $is_arabic ? 'ابدأ الدورة' : 'Start Course',
        'label_login_enroll' => $is_arabic ? 'تسجيل الدخول للتسجيل' : 'Login to Enroll',
        'label_curriculum' => $is_arabic ? 'منهاج الدورة' : 'Course Curriculum',
        'completed_message' => $is_arabic ? 'تهانينا! لقد أكملت هذه الدورة.' : 'Congratulations! You have completed this course.',
        'label_mark_complete' => $is_arabic ? 'تحديد كمكتمل' : 'Mark Complete',
        'label_completed' => $is_arabic ? 'مكتمل' : 'Completed',
        'label_prev_lesson' => $is_arabic ? 'الدرس السابق' : 'Previous Lesson',
        'label_next_lesson' => $is_arabic ? 'الدرس التالي' : 'Next Lesson',
        'label_course_home' => $is_arabic ? 'رئيسية الدورة' : 'Course Home',
        'label_materials' => $is_arabic ? 'المواد والمصادر' : 'Downloads & Resources',
        'label_download' => $is_arabic ? 'تحميل' : 'Download',
        'label_view_course' => $is_arabic ? 'عرض الدورة' : 'View Course',
        'label_filter_all_levels' => $is_arabic ? 'جميع المستويات' : 'All Levels',
        'label_filter_all_prices' => $is_arabic ? 'جميع الأسعار' : 'All Prices',
        'label_search_placeholder' => $is_arabic ? 'بحث في الدورات...' : 'Search courses...',
        'label_filter_button' => $is_arabic ? 'تصفية' : 'Filter',
        'label_reset_button' => $is_arabic ? 'إعادة تعيين' : 'Reset',
        'label_no_results' => $is_arabic ? 'لا توجد دورات مطابقة.' : 'No courses found matching your criteria.',
        'locked_message' => $is_arabic ? 'يجب عليك تسجيل الدخول لعرض هذا الدرس.' : 'You must be logged in to view this lesson.',
        'sequential_message' => $is_arabic ? 'يرجى إكمال الدرس السابق لفتح هذا المحتوى.' : 'Please complete the previous lesson to unlock this content.',
        'label_lesson_suffix' => $is_arabic ? ' في مجال علم النفس' : ' in the field of psychology',
        'archive_hero_title' => $is_arabic ? 'دوراتنا' : 'Our Courses',
        'archive_hero_text' => $is_arabic ? 'استكشف مجموعتنا الواسعة من الدورات الأكاديمية.' : 'Explore our wide range of academic courses.',
        'dashboard_welcome_text' => $is_arabic ? 'مرحبًا بعودتك، {name}' : 'Welcome back, {name}',
        'enable_materials' => 1,
        'show_breadcrumbs' => 1,
        'show_sidebar' => 1,
        'show_nav_buttons' => 1,
        'show_sidebar_progress' => 1,
        'show_lesson_author' => 1,
        'show_lesson_date' => 1,
        'show_lesson_index' => 1,
        'show_lesson_instructor' => 1,
        'lesson_list_style' => 'simple',
        'grid_columns' => 3,
        'archive_hero_enable' => 0,
        'archive_hero_image' => '',
        'archive_hero_layout' => 'split',
        'dashboard_hero_bg' => '',
        'dashboard_hide_title' => 1,
        'dashboard_show_avatar' => 1,
        'enable_course_filters' => 1,
        'filter_by_level' => 1,
        'filter_by_price' => 1,
        'filter_by_search' => 1,
        'hide_archive_title' => 0,
        'slider_enable' => 0,
        'slider_count' => 5,
        'slider_autoplay' => 1,
        'slider_interval' => 5000,
        'slider_show_arrows' => 1,
        'slider_show_dots' => 1,
        'slider_pause_hover' => 1,
        'enforce_sequential' => 0,
        'enable_focus_mode' => 1,
        'focus_mode_position' => 'header',
        'curriculum_style' => 'modern',
    ];

    $options = get_option( 'wpa_course_settings', [] );
    if ( ! is_array( $options ) ) {
        $options = [];
    }
    
    return array_merge( $defaults, $options );
}