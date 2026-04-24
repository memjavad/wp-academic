<?php
/**
 * Theme Labels Manager
 * Centralizes all frontend text labels and handles translations.
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPA_Theme_Labels {

    public function __construct() {
        add_action( 'admin_init', [ $this, 'register_settings' ] );
    }

    public static function get_all_labels() {
        return [
            // Course General
            'label_start_course' => [ 'default' => 'Start Course', 'ar' => 'ابدأ الدورة' ],
            'label_login_enroll' => [ 'default' => 'Login to Enroll', 'ar' => 'سجل الدخول للتسجيل' ],
            'label_curriculum'   => [ 'default' => 'Curriculum', 'ar' => 'المنهج الدراسي' ],
            'completed_message'  => [ 'default' => 'You have successfully completed this course!', 'ar' => 'لقد أكملت هذه الدورة بنجاح!' ],
            'locked_message'     => [ 'default' => 'This content is locked. Please enroll to access.', 'ar' => 'هذا المحتوى مغلق. يرجى التسجيل للوصول إليه.' ],
            'sequential_message' => [ 'default' => 'Please complete the previous lesson first.', 'ar' => 'يرجى إكمال الدرس السابق أولاً.' ],
            
            // Lesson
            'label_mark_complete' => [ 'default' => 'Mark Complete', 'ar' => 'حدد كمكتمل' ],
            'label_completed'     => [ 'default' => 'Completed', 'ar' => 'مكتمل' ],
            'label_prev_lesson'   => [ 'default' => 'Previous Lesson', 'ar' => 'الدرس السابق' ],
            'label_next_lesson'   => [ 'default' => 'Next Lesson', 'ar' => 'الدرس التالي' ],
            'label_course_home'   => [ 'default' => 'Course Home', 'ar' => 'رئيسية الدورة' ],
            'label_materials'     => [ 'default' => 'Materials', 'ar' => 'المواد التعليمية' ],
            'label_download'      => [ 'default' => 'Download', 'ar' => 'تحميل' ],
            'label_view_course'   => [ 'default' => 'View Course', 'ar' => 'عرض الدورة' ],
            'label_lesson_suffix' => [ 'default' => '', 'ar' => '' ],

            // Quiz
            'quiz_label'          => [ 'default' => 'Quiz', 'ar' => 'اختبار' ],
            'quiz_success_msg'    => [ 'default' => 'Congratulations! You passed the quiz.', 'ar' => 'تهانينا! لقد اجتزت الاختبار.' ],
            'quiz_error_msg'      => [ 'default' => 'Sorry, you did not pass. Please try again.', 'ar' => 'عذراً، لم تجتز الاختبار. يرجى المحاولة مرة أخرى.' ],

            // Filters
            'label_filter_all_levels' => [ 'default' => 'All Levels', 'ar' => 'جميع المستويات' ],
            'label_filter_all_prices' => [ 'default' => 'All Prices', 'ar' => 'جميع الأسعار' ],
            'label_search_placeholder' => [ 'default' => 'Search courses...', 'ar' => 'بحث عن دورات...' ],
            'label_filter_button'     => [ 'default' => 'Filter', 'ar' => 'تصفية' ],
            'label_reset_button'      => [ 'default' => 'Reset', 'ar' => 'إعادة تعيين' ],
            'label_no_results'        => [ 'default' => 'No courses found.', 'ar' => 'لم يتم العثور على دورات.' ],
            'label_no_lessons'        => [ 'default' => 'No lessons found for this course.', 'ar' => 'لم يتم العثور على دروس لهذه الدورة.' ],

            // Field News
            'news_journal'       => [ 'default' => 'Journal:', 'ar' => 'المجلة:' ],
            'news_published'     => [ 'default' => 'Published:', 'ar' => 'تاريخ النشر:' ],
            'news_authors'       => [ 'default' => 'Authors:', 'ar' => 'المؤلفون:' ],
            'news_citations'     => [ 'default' => 'Citations:', 'ar' => 'الاقتباسات:' ],
            'news_type'          => [ 'default' => 'Study Type:', 'ar' => 'نوع الدراسة:' ],
            'news_view_full'     => [ 'default' => 'View Full Study', 'ar' => 'عرض الدراسة الكاملة' ],
            'news_download_pdf'  => [ 'default' => 'Download PDF', 'ar' => 'تحميل PDF' ],
            'news_about_title'   => [ 'default' => 'About the Study', 'ar' => 'تفاصيل الدراسة' ],
            'news_share_title'   => [ 'default' => 'Share', 'ar' => 'مشاركة' ],
            'news_read_time'     => [ 'default' => 'Read Time', 'ar' => 'وقت القراءة' ],
            'news_latest'        => [ 'default' => 'Latest News', 'ar' => 'آخر الأخبار' ],
            'news_follow_google' => [ 'default' => 'Follow on Google News', 'ar' => 'تابعنا على أخبار جوجل' ],
            'news_year'          => [ 'default' => 'Year:', 'ar' => 'السنة:' ],
            'news_back_to_home'  => [ 'default' => 'Back to Home', 'ar' => 'العودة للرئيسية' ],
            'news_toc_title'     => [ 'default' => 'In this Article', 'ar' => 'محتويات المقال' ],
            'news_facts_title'   => [ 'default' => 'Study Facts', 'ar' => 'حقائق عن الدراسة' ],
            'news_author_title_sidebar' => [ 'default' => 'Lead Author', 'ar' => 'عن المؤلف' ],
            'news_read_original' => [ 'default' => 'Read Original Scientific Abstract', 'ar' => 'اقرأ الملخص العلمي الأصلي' ],
            'news_unknown_author' => [ 'default' => 'Unknown Author', 'ar' => 'مؤلف غير معروف' ],
            'news_read_more'     => [ 'default' => 'Read More', 'ar' => 'اقرأ المزيد' ],
            'news_highlights_title' => [ 'default' => 'Key Highlights', 'ar' => 'أبرز النقاط' ],
            'news_discussion_title' => [ 'default' => 'Discussion & Critical Thinking', 'ar' => 'المناقشة والتفكير النقدي' ],
            'news_related_courses_title' => [ 'default' => 'Deepen Your Knowledge', 'ar' => 'عمق معرفتك' ],
            'news_academic_course' => [ 'default' => 'Academic Course', 'ar' => 'دورة أكاديمية' ],

            // Citation Box
            'cite_retrieved_from' => [ 'default' => 'Retrieved from', 'ar' => 'تم الاسترجاع من' ],
            'cite_available_at'   => [ 'default' => 'Available at', 'ar' => 'متاح في' ],
            'cite_vol'            => [ 'default' => 'vol.', 'ar' => 'مجلد' ],
            'cite_no'             => [ 'default' => 'no.', 'ar' => 'عدد' ],
            'cite_pp'             => [ 'default' => 'ص', 'ar' => 'ص' ], // Arabic usually uses ص
            'cite_et_al'          => [ 'default' => 'et al.', 'ar' => 'وآخرون' ],
            'cite_box_title'      => [ 'default' => 'Cite this article', 'ar' => 'اقتبس من هذا المقال' ],
            'cite_btn_copy'       => [ 'default' => 'Copy Citation', 'ar' => 'نسخ الاقتباس' ],
            'cite_btn_ris'        => [ 'default' => 'Download Citation (.RIS)', 'ar' => 'تحميل الاقتباس (.RIS)' ],
            'cite_btn_bib'        => [ 'default' => 'Download BibTeX (.BIB)', 'ar' => 'تحميل الاقتباس (.BIB)' ],
            'cite_btn_pdf'        => [ 'default' => 'Download Post (.PDF)', 'ar' => 'تحميل المقال (.PDF)' ],

            // Lesson Page Extra
            'lesson_instructor'  => [ 'default' => 'Instructor', 'ar' => 'المعلم' ],
            'lesson_notes'       => [ 'default' => 'My Notes', 'ar' => 'ملاحظاتي' ],
            'lesson_notes_placeholder' => [ 'default' => 'Private notes...', 'ar' => 'ملاحظات خاصة...' ],
            'lesson_notes_saved' => [ 'default' => 'Saved locally', 'ar' => 'تم الحفظ محلياً' ],
            'lesson_focus_mode'  => [ 'default' => 'Focus Mode', 'ar' => 'وضع التركيز' ],
            'lesson_index_text'  => [ 'default' => 'Lesson %d of %d', 'ar' => 'الدرس %d من %d' ],
            'lesson_prereq_msg'  => [ 'default' => 'Prerequisite: %s', 'ar' => 'المتطلب السابق: %s' ],
            'lesson_drip_msg'    => [ 'default' => 'This content will become available on %s.', 'ar' => 'سيكون هذا المحتوى متاحاً في %s.' ],
            'lesson_log_in'      => [ 'default' => 'Log In', 'ar' => 'تسجيل الدخول' ],
            'lesson_quiz_passed_score' => [ 'default' => 'Passed! (Score: %d%%)', 'ar' => 'تم الاجتياز! (النتيجة: %d%%)' ],
            'lesson_quiz_failed_msg'   => [ 'default' => 'You scored %d%%. Minimum required is %d%%. Please try again.', 'ar' => 'لقد حصلت على %d%%. الحد الأدنى المطلوب هو %d%%. يرجى المحاولة مرة أخرى.' ],
            'lesson_submit_quiz' => [ 'default' => 'Submit Quiz', 'ar' => 'إرسال الاختبار' ],
            'lesson_certificate' => [ 'default' => 'Certificate', 'ar' => 'شهادة' ],
            'lesson_continue'    => [ 'default' => 'Continue', 'ar' => 'استمرار' ],

            // Dashboard & Status
            'status_enrolled'    => [ 'default' => 'Enrolled', 'ar' => 'مسجل' ],
            'status_in_progress' => [ 'default' => 'In Progress', 'ar' => 'قيد التقدم' ],
            'status_completed'   => [ 'default' => 'Completed', 'ar' => 'مكتمل' ],
            'status_active'      => [ 'default' => 'Active', 'ar' => 'نشط' ],
            'status_all_courses' => [ 'default' => 'All Courses', 'ar' => 'جميع الدورات' ],
            'msg_no_active'      => [ 'default' => 'No active courses.', 'ar' => 'لا توجد دورات نشطة.' ],
            'msg_no_completed'   => [ 'default' => 'No completed courses.', 'ar' => 'لا توجد دورات مكتملة.' ],
            'label_your_progress' => [ 'default' => 'Your Progress: %d%%', 'ar' => 'تقدمك: %d%%' ],
            'course_instructor_heading' => [ 'default' => 'Course Instructor', 'ar' => 'مدرس الدورة' ],

            // Glossary
            'glossary_term_details'    => [ 'default' => 'Term Details', 'ar' => 'تفاصيل المصطلح' ],
            'glossary_category'        => [ 'default' => 'Category:', 'ar' => 'التصنيف:' ],
            'glossary_tags'            => [ 'default' => 'Tags:', 'ar' => 'الوسوم:' ],
            'glossary_last_updated'    => [ 'default' => 'Last Updated:', 'ar' => 'آخر تحديث:' ],
            'glossary_course_label'    => [ 'default' => 'Course', 'ar' => 'دورة' ],
            'glossary_no_terms_letter' => [ 'default' => 'No terms found for this letter.', 'ar' => 'لم يتم العثور على مصطلحات لهذا الحرف.' ],
            'glossary_no_terms'        => [ 'default' => 'No terms found.', 'ar' => 'لم يتم العثور على مصطلحات.' ],
            'glossary_all'             => [ 'default' => 'All', 'ar' => 'الكل' ],
            'glossary_back_index'      => [ 'default' => 'Back to Glossary Index Page', 'ar' => 'العودة إلى صفحة الفهرس' ],
        ];
    }

    public function register_settings() {
        // We use the existing 'wpa_homepage_settings' option group from theme-admin.php
        
        add_settings_section(
            'wpa_theme_labels_section', 
            __( 'Frontend Labels & Translations', 'wp-academic-post-enhanced' ), 
            [ $this, 'section_callback' ], 
            'wpa_homepage'
        );

        $labels = self::get_all_labels();

        foreach ( $labels as $key => $data ) {
            // Register English Field
            add_settings_field(
                $key, 
                $data['default'] . ' (Default)', 
                [ $this, 'text_field_callback' ], 
                'wpa_homepage', 
                'wpa_theme_labels_section', 
                [ 'label_for' => $key, 'default' => $data['default'] ]
            );

            // Register Arabic Field
            add_settings_field(
                $key . '_ar', 
                $data['default'] . ' (Arabic)', 
                [ $this, 'text_field_callback' ], 
                'wpa_homepage', 
                'wpa_theme_labels_section', 
                [ 'label_for' => $key . '_ar', 'default' => $data['ar'] ]
            );
        }
    }

    public function section_callback() {
        echo '<p>' . __( 'Manage all frontend text labels here. If the site language is English, the Default label is used. If the site language is Arabic (or RTL), the Arabic label is used.', 'wp-academic-post-enhanced' ) . '</p>';
    }

    public function text_field_callback( $args ) {
        $options = get_option( 'wpa_homepage_settings' );
        $key = $args['label_for'];
        $val = isset( $options[$key] ) ? $options[$key] : '';
        
        // If empty, show placeholder from default
        $placeholder = isset($args['default']) ? $args['default'] : '';

        echo '<input type="text" name="wpa_homepage_settings[' . esc_attr( $key ) . ']" value="' . esc_attr( $val ) . '" class="regular-text" placeholder="' . esc_attr( $placeholder ) . '">';
    }

    /**
     * Helper to retrieve a label based on current locale.
     * 
     * @param string $key The label key (e.g., 'label_start_course').
     * @return string The translated label.
     */
    public static function get( $key ) {
        $options = get_option( 'wpa_homepage_settings', [] );
        
        // More robust Arabic detection
        $is_arabic = ( is_rtl() || strpos( get_locale(), 'ar' ) === 0 );

        if ( $is_arabic ) {
            // Check for Arabic override
            if ( ! empty( $options[ $key . '_ar' ] ) ) {
                return $options[ $key . '_ar' ];
            }
            // Fallback to default array 'ar' value if setting is empty
            $defaults = self::get_all_labels();
            if ( isset( $defaults[$key]['ar'] ) ) {
                return $defaults[$key]['ar'];
            }
        } else {
            // English / Default
            if ( ! empty( $options[ $key ] ) ) {
                return $options[ $key ];
            }
            // Fallback
            $defaults = self::get_all_labels();
            if ( isset( $defaults[$key]['default'] ) ) {
                return $defaults[$key]['default'];
            }
        }

        return '';
    }
}

new WPA_Theme_Labels();