<?php
/**
 * View template for a course card in the student dashboard.
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

// Variables available: $course_id, $progress, $status_class, $status_text, $thumb
?>
<div class="wpa-dash-card <?php echo esc_attr( $status_class ); ?>">
    <a href="<?php echo get_permalink( $course_id ); ?>" class="wpa-dash-thumb" style="background-image: url(<?php echo esc_url( $thumb ); ?>);">
        <span class="wpa-status-badge"><?php echo esc_html( $status_text ); ?></span>
    </a>
    <div class="wpa-dash-body">
        <h4><a href="<?php echo get_permalink( $course_id ); ?>"><?php echo get_the_title( $course_id ); ?></a></h4>

        <div class="wpa-dash-progress-wrapper">
            <div class="wpa-course-progress-bar" role="progressbar" aria-valuenow="<?php echo esc_attr( $progress ); ?>" aria-valuemin="0" aria-valuemax="100">
                <div class="wpa-progress-fill" style="width:<?php echo esc_attr( $progress ); ?>%"></div>
            </div>
            <span class="wpa-dash-percent"><?php echo esc_html( $progress ); ?>%</span>
        </div>

        <div class="wpa-dash-footer">
            <?php if ( $progress >= 100 ) :
                $cert_link = add_query_arg( [ 'wpa_download_certificate' => '1', 'course_id' => $course_id ], home_url() ); ?>
                <a href="<?php echo esc_url( $cert_link ); ?>" class="wpa-btn wpa-btn-outline" target="_blank"><span class="dashicons dashicons-awards"></span> <?php echo WPA_Theme_Labels::get('lesson_certificate'); ?></a>
            <?php else : ?>
                <a href="<?php echo get_permalink( $course_id ); ?>" class="wpa-btn wpa-btn-primary"><?php echo WPA_Theme_Labels::get('lesson_continue'); ?> <span class="dashicons dashicons-arrow-right-alt2"></span></a>
            <?php endif; ?>
        </div>
    </div>
</div>
