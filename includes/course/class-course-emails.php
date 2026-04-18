<?php
/**
 * Course Email Notifications.
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPA_Course_Emails {

    public function __construct() {
        $options = get_option( 'wpa_course_settings' );
        $enabled = isset( $options['enabled'] ) ? $options['enabled'] : 1;
        
        // Use global course enabled check
        if ( ! get_option( 'wpa_course_enabled' ) ) return;

        $email_enabled = isset( $options['enable_emails'] ) ? $options['enable_emails'] : 1;
        if ( ! $email_enabled ) return;

        add_action( 'wpa_course_user_enrolled', [ $this, 'send_welcome_email' ], 10, 2 );
        add_action( 'wpa_lesson_completed', [ $this, 'check_course_completion' ], 10, 2 );
        add_action( 'wpa_course_quiz_passed', [ $this, 'send_quiz_passed_email' ], 10, 2 );
    }

    /**
     * Send Welcome Email on Enrollment
     */
    public function send_welcome_email( $course_id, $user_id ) {
        $user = get_userdata( $user_id );
        if ( ! $user ) return;

        $options = get_option( 'wpa_course_settings' );
        $subject_tpl = ! empty( $options['email_welcome_subject'] ) ? $options['email_welcome_subject'] : __( 'Welcome to {course_title}', 'wp-academic-post-enhanced' );
        $body_tpl = ! empty( $options['email_welcome_body'] ) ? $options['email_welcome_body'] : __( "Hi {student_name},\n\nYou have successfully enrolled in \"{course_title}\".\nStart learning now: {course_link}\n\nHappy Learning!", 'wp-academic-post-enhanced' );

        $replacements = [
            '{student_name}' => $user->display_name,
            '{course_title}' => get_the_title( $course_id ),
            '{lesson_title}' => '',
            '{course_link}'  => get_permalink( $course_id ),
            '{certificate_link}' => '',
        ];

        $subject = $this->replace_placeholders( $subject_tpl, $replacements );
        $message = $this->replace_placeholders( $body_tpl, $replacements );

        wp_mail( $user->user_email, $subject, $message );
    }

    /**
     * Check Completion on Lesson Complete
     */
    public function check_course_completion( $lesson_id, $user_id ) {
        $course_id = get_post_meta( $lesson_id, '_wpa_course_id', true );
        if ( ! $course_id ) return;

        // Recalculate progress
        $progress = wpa_course_get_progress( $course_id, $user_id );

        if ( $progress >= 100 ) {
            // Check if already sent
            $sent = get_user_meta( $user_id, '_wpa_completion_email_sent_' . $course_id, true );
            if ( ! $sent ) {
                $this->send_completion_email( $course_id, $user_id );
                update_user_meta( $user_id, '_wpa_completion_email_sent_' . $course_id, true );
            }
        }
    }

    /**
     * Send Completion Email
     */
    private function send_completion_email( $course_id, $user_id ) {
        $user = get_userdata( $user_id );
        $course_title = get_the_title( $course_id );
        
        $options = get_option( 'wpa_course_settings' );
        $subject_tpl = ! empty( $options['email_complete_subject'] ) ? $options['email_complete_subject'] : __( 'Congratulations! You completed {course_title}', 'wp-academic-post-enhanced' );
        $body_tpl = ! empty( $options['email_complete_body'] ) ? $options['email_complete_body'] : __( "Hi {student_name},\n\nYou have successfully completed the course \"{course_title}\".\n\nDownload your certificate here: {certificate_link}\n\nGreat job!", 'wp-academic-post-enhanced' );

        $cert_link = add_query_arg( [ 'wpa_download_certificate' => '1', 'course_id' => $course_id ], home_url() );

        $replacements = [
            '{student_name}' => $user->display_name,
            '{course_title}' => $course_title,
            '{lesson_title}' => '',
            '{course_link}'  => get_permalink( $course_id ),
            '{certificate_link}' => $cert_link,
        ];

        $subject = $this->replace_placeholders( $subject_tpl, $replacements );
        $message = $this->replace_placeholders( $body_tpl, $replacements );

        wp_mail( $user->user_email, $subject, $message );
    }

    /**
     * Send Quiz Passed Email
     */
    public function send_quiz_passed_email( $lesson_id, $user_id ) {
        $user = get_userdata( $user_id );
        if ( ! $user ) return;

        $course_id = get_post_meta( $lesson_id, '_wpa_course_id', true );
        $options = get_option( 'wpa_course_settings' );
        
        $subject_tpl = ! empty( $options['email_quiz_passed_subject'] ) ? $options['email_quiz_passed_subject'] : __( 'You passed the quiz for {lesson_title}', 'wp-academic-post-enhanced' );
        $body_tpl = ! empty( $options['email_quiz_passed_body'] ) ? $options['email_quiz_passed_body'] : '';

        if ( empty( $body_tpl ) ) return;

        $replacements = [
            '{student_name}' => $user->display_name,
            '{course_title}' => get_the_title( $course_id ),
            '{lesson_title}' => get_the_title( $lesson_id ),
            '{course_link}'  => get_permalink( $course_id ),
            '{certificate_link}' => '',
        ];

        $subject = $this->replace_placeholders( $subject_tpl, $replacements );
        $message = $this->replace_placeholders( $body_tpl, $replacements );

        wp_mail( $user->user_email, $subject, $message );
    }

    /**
     * Helper: Replace placeholders
     */
    private function replace_placeholders( $content, $replacements ) {
        return str_replace( array_keys( $replacements ), array_values( $replacements ), $content );
    }
}

new WPA_Course_Emails();
