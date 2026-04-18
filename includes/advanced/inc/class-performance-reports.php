<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class WP_Academic_Post_Enhanced_Performance_Reports {

    private static $instance;

    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action( 'update_option_wp_academic_post_enhanced_performance_reports_options', [ $this, 'schedule_or_unschedule_report' ], 10, 2 );
        add_action( 'wpa_performance_report_cron', [ $this, 'send_performance_report' ] );
    }

    public function schedule_or_unschedule_report( $old_value, $new_value ) {
        $was_enabled = isset( $old_value['enabled'] ) ? $old_value['enabled'] : false;
        $is_enabled = isset( $new_value['enabled'] ) ? $new_value['enabled'] : false;

        if ( $is_enabled && ! $was_enabled ) {
            $frequency = isset( $new_value['frequency'] ) ? $new_value['frequency'] : 'weekly';
            wp_schedule_event( time(), $frequency, 'wpa_performance_report_cron' );
        } elseif ( ! $is_enabled && $was_enabled ) {
            wp_clear_scheduled_hook( 'wpa_performance_report_cron' );
        }
    }

    public function send_performance_report() {
        $options = get_option( 'wp_academic_post_enhanced_performance_reports_options', [] );
        $recipient = isset( $options['recipient'] ) ? $options['recipient'] : get_option('admin_email');

        $data = get_option( 'wp_academic_post_enhanced_performance_data', [] );
        $avg_load_time = ! empty( $data['load_times'] ) ? array_sum( $data['load_times'] ) / count( $data['load_times'] ) : 0;
        $avg_memory = ! empty( $data['memory_usage'] ) ? array_sum( $data['memory_usage'] ) / count( $data['memory_usage'] ) : 0;
        $avg_queries = ! empty( $data['query_counts'] ) ? array_sum( $data['query_counts'] ) / count( $data['query_counts'] ) : 0;

        $subject = __('Your Site Performance Report', 'wp-academic-post-enhanced');
        $body = __('Here is your site performance report:', 'wp-academic-post-enhanced') . "\r\n\r\n";
        $body .= __('Average Page Load Time:', 'wp-academic-post-enhanced') . ' ' . number_format( $avg_load_time, 2 ) . "s\r\n";
        $body .= __('Average Memory Usage:', 'wp-academic-post-enhanced') . ' ' . size_format( $avg_memory ) . "\r\n";
        $body .= __('Average Database Queries:', 'wp-academic-post-enhanced') . ' ' . number_format( $avg_queries, 0 ) . "\r\n";

        wp_mail( $recipient, $subject, $body );
    }
}
