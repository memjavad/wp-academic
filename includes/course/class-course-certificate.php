<?php
/**
 * Course Completion Certificates.
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPA_Course_Certificate {

    public function __construct() {
        $options = get_option( 'wpa_course_settings' );
        $enabled = isset( $options['enable_certificates'] ) ? $options['enable_certificates'] : 1;
        
        if ( ! $enabled ) {
            return;
        }

        add_action( 'template_redirect', [ $this, 'handle_certificate_download' ] );
    }

    /**
     * Handle Download Request
     */
    public function handle_certificate_download() {
        if ( isset( $_GET['wpa_download_certificate'] ) && '1' === $_GET['wpa_download_certificate'] && isset( $_GET['course_id'] ) ) {
            
            if ( ! is_user_logged_in() ) {
                wp_die( __( 'Please log in.', 'wp-academic-post-enhanced' ) );
            }

            $course_id = intval( $_GET['course_id'] );
            $user_id = get_current_user_id();

            // Verify Completion
            $progress = wpa_course_get_progress( $course_id, $user_id );
            if ( $progress < 100 ) {
                wp_die( __( 'You must complete the course first.', 'wp-academic-post-enhanced' ) );
            }

            $this->generate_pdf( $course_id, $user_id );
        }
    }

    /**
     * Generate PDF using mPDF
     */
    private function generate_pdf( $course_id, $user_id ) {
        // Load mPDF
        if ( ! class_exists( '\Mpdf\Mpdf' ) ) {
            $autoload_path = plugin_dir_path( dirname( __FILE__ ) ) . 'pdf/vendor/autoload.php';
            if ( file_exists( $autoload_path ) ) {
                require_once $autoload_path;
            } else {
                wp_die( __( 'PDF Generator library missing.', 'wp-academic-post-enhanced' ) );
            }
        }

        $options = get_option( 'wpa_course_settings' );
        $title_text = ! empty( $options['cert_title'] ) ? $options['cert_title'] : __( 'Certificate of Completion', 'wp-academic-post-enhanced' );
        $subtitle_text = ! empty( $options['cert_subtitle'] ) ? $options['cert_subtitle'] : __( 'This is to certify that', 'wp-academic-post-enhanced' );
        $completion_text = ! empty( $options['cert_completion_text'] ) ? $options['cert_completion_text'] : __( 'Has successfully completed the course', 'wp-academic-post-enhanced' );
        $border_color = ! empty( $options['cert_border_color'] ) ? $options['cert_border_color'] : '#444444';
        $logo_url = ! empty( $options['cert_logo'] ) ? $options['cert_logo'] : '';
        
        // New Settings
        $orientation = ! empty( $options['cert_orientation'] ) ? $options['cert_orientation'] : 'landscape';
        $font = ! empty( $options['cert_font'] ) ? $options['cert_font'] : 'helvetica';
        $bg_image = ! empty( $options['cert_background_image'] ) ? $options['cert_background_image'] : '';
        $instructor_name = ! empty( $options['cert_instructor_name'] ) ? $options['cert_instructor_name'] : '';
        $signature_url = ! empty( $options['cert_signature'] ) ? $options['cert_signature'] : '';

        // Design Settings
        $style_type = ! empty( $options['cert_style'] ) ? $options['cert_style'] : 'classic';
        $text_color = ! empty( $options['cert_text_color'] ) ? $options['cert_text_color'] : '#111111';
        $bg_color = ! empty( $options['cert_bg_color'] ) ? $options['cert_bg_color'] : '#ffffff';
        $border_width = ! empty( $options['cert_border_width'] ) ? $options['cert_border_width'] . 'px' : '10px';

        // Map orientation
        $format = ( $orientation === 'portrait' ) ? 'A4-P' : 'A4-L';

        $user = get_userdata( $user_id );
        $course_title = get_the_title( $course_id );
        $date = date_i18n( get_option( 'date_format' ) );
        $site_name = get_bloginfo( 'name' );

        // Background Logic
        $bg_css = 'background-color: ' . esc_attr( $bg_color ) . ';';
        if ( ! empty( $bg_image ) ) {
            $bg_css .= ' background-image: url(\'' . esc_url( $bg_image ) . '\'); background-size: cover; background-position: center;';
        }

        // Border Logic based on Style
        $border_css = '';
        if ( $style_type === 'minimal' ) {
            $border_css = 'border: none; padding: 40px;';
        } elseif ( $style_type === 'fancy' ) {
            $border_css = 'border: ' . esc_attr( $border_width ) . ' double ' . esc_attr( $border_color ) . '; padding: 50px;';
        } else { // Classic
            $border_css = 'border: ' . esc_attr( $border_width ) . ' solid ' . esc_attr( $border_color ) . '; padding: 50px;';
        }

        $html = '
        <style>
            body { 
                font-family: ' . esc_attr( $font ) . ', sans-serif; 
                text-align: center; 
                color: ' . esc_attr( $text_color ) . ';
                ' . $border_css . '
                ' . $bg_css . '
            }
            .header { font-size: 30pt; font-weight: bold; margin-bottom: 20px; text-transform: uppercase; color: ' . esc_attr( $text_color ) . '; }
            .sub-header { font-size: 18pt; margin-bottom: 40px; opacity: 0.8; }
            .name { font-size: 36pt; font-weight: bold; margin-bottom: 20px; border-bottom: 2px solid #ccc; display: inline-block; padding-bottom: 10px; color: ' . esc_attr( $text_color ) . '; }
            .course { font-size: 24pt; margin-bottom: 40px; font-weight: bold; }
            .date { font-size: 14pt; opacity: 0.7; margin-top: 50px; }
            .logo { margin-bottom: 30px; font-size: 20pt; font-weight: bold; }
            .logo img { max-height: 100px; width: auto; }
            
            .footer { margin-top: 60px; width: 100%; }
            .signature-block { width: 40%; float: right; text-align: center; }
            .date-block { width: 40%; float: left; text-align: center; }
            .line { border-bottom: 1px solid #999; margin-bottom: 10px; height: 1px; width: 80%; margin-left: auto; margin-right: auto; }
            .signature-img { max-height: 60px; width: auto; margin-bottom: 5px; }
            .meta-label { font-size: 12pt; opacity: 0.7; }
        </style>';
        
        if ( ! empty( $logo_url ) ) {
            $html .= '<div class="logo"><img src="' . esc_url( $logo_url ) . '"></div>';
        } else {
            $html .= '<div class="logo">' . esc_html( $site_name ) . '</div>';
        }
        
        $html .= '
        <div class="header">' . esc_html( $title_text ) . '</div>
        
        <div class="sub-header">' . esc_html( $subtitle_text ) . '</div>
        
        <div class="name">' . esc_html( $user->display_name ) . '</div>
        
        <div class="sub-header" style="margin-top: 30px;">' . esc_html( $completion_text ) . '</div>
        
        <div class="course">' . esc_html( $course_title ) . '</div>
        
        <div class="footer">
            <div class="date-block">
                <div style="height: 65px; padding-top: 40px;"><div class="line"></div></div>
                <div class="meta-label">' . sprintf( __( 'Awarded on %s', 'wp-academic-post-enhanced' ), $date ) . '</div>
            </div>
            
            <div class="signature-block">';
            
            if ( ! empty( $signature_url ) ) {
                $html .= '<img src="' . esc_url( $signature_url ) . '" class="signature-img">';
            } else {
                $html .= '<div style="height: 65px;"></div>';
            }
            
            $html .= '<div class="line"></div>
                <div class="meta-label">' . ( ! empty( $instructor_name ) ? esc_html( $instructor_name ) : __( 'Instructor', 'wp-academic-post-enhanced' ) ) . '</div>
            </div>
        </div>
        ';

        try {
            $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => $format]); 
            $mpdf->WriteHTML( $html );
            $mpdf->Output( 'Certificate-' . $course_id . '.pdf', 'D' ); // Download
            exit;
        } catch ( \Mpdf\MpdfException $e ) {
            wp_die( $e->getMessage() );
        }
    }
}

new WPA_Course_Certificate();
