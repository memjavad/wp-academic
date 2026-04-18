<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class WP_Academic_Post_Enhanced_Dashboard_Widget {

    private static $instance;

    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action( 'wp_dashboard_setup', [ $this, 'add_dashboard_widget' ] );
        add_action( 'shutdown', [ $this, 'track_performance_data' ] );
    }

    public function add_dashboard_widget() {
        wp_add_dashboard_widget(
            'wp_academic_post_enhanced_performance_widget',
            __( 'Site Performance', 'wp-academic-post-enhanced' ),
            [ $this, 'render_dashboard_widget' ]
        );
    }

    public function render_dashboard_widget() {
        $data = get_option( 'wp_academic_post_enhanced_performance_data', [] );
        $avg_load_time = ! empty( $data['load_times'] ) ? array_sum( $data['load_times'] ) / count( $data['load_times'] ) : 0;
        $avg_memory = ! empty( $data['memory_usage'] ) ? array_sum( $data['memory_usage'] ) / count( $data['memory_usage'] ) : 0;
        $avg_queries = ! empty( $data['query_counts'] ) ? array_sum( $data['query_counts'] ) / count( $data['query_counts'] ) : 0;

        echo '<p><strong>' . __('Server Performance (Avg. last 10 requests):', 'wp-academic-post-enhanced') . '</strong></p>';
        echo '<ul>';
        echo '<li>' . __('Page Load Time:', 'wp-academic-post-enhanced') . ' ' . esc_html( number_format( $avg_load_time, 2 ) ) . 's</li>';
        echo '<li>' . __('Memory Usage:', 'wp-academic-post-enhanced') . ' ' . esc_html( size_format( $avg_memory ) ) . '</li>';
        echo '<li>' . __('Database Queries:', 'wp-academic-post-enhanced') . ' ' . esc_html( number_format( $avg_queries, 0 ) ) . '</li>';
        echo '</ul>';
        
        // Reading Stats
        $posts_data = wp_count_posts();
        $published_posts = $posts_data->publish;
        
        // Approximation: 1000 words per post average if we don't query everything
        // Or we can do a quick sum query if the site isn't huge.
        global $wpdb;
        $total_words = $wpdb->get_var( "SELECT SUM(LENGTH(post_content) - LENGTH(REPLACE(post_content, ' ', ''))+1) FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'post'" );
        $avg_reading_speed = apply_filters( 'wpa_reading_speed', 200 );
        $total_reading_time = $total_words ? ceil( $total_words / $avg_reading_speed ) : 0;
        $hours = floor( $total_reading_time / 60 );
        $minutes = $total_reading_time % 60;
        
        echo '<hr>';
        echo '<p><strong>' . __('Content Stats:', 'wp-academic-post-enhanced') . '</strong></p>';
        echo '<ul>';
        echo '<li>' . __('Published Posts:', 'wp-academic-post-enhanced') . ' ' . esc_html( number_format( $published_posts ) ) . '</li>';
        echo '<li>' . __('Total Words:', 'wp-academic-post-enhanced') . ' ' . esc_html( number_format( $total_words ) ) . '</li>';
        echo '<li>' . __('Total Reading Time:', 'wp-academic-post-enhanced') . ' ' . sprintf( __( '%d hr %d min', 'wp-academic-post-enhanced' ), $hours, $minutes ) . '</li>';
        echo '</ul>';

        // Database Cleanup Opportunities
        $revisions = wp_count_posts('revision')->inherit;
        $spam_comments = wp_count_comments()->spam;
        $trashed_comments = wp_count_comments()->trash;
        
        echo '<hr>';
        echo '<p><strong>' . __('Database Cleanup Opportunities:', 'wp-academic-post-enhanced') . '</strong></p>';
        echo '<ul>';
        echo '<li>' . __('Revisions:', 'wp-academic-post-enhanced') . ' ' . intval($revisions) . ' ' . ($revisions > 100 ? '<a href="' . admin_url('admin.php?page=wp-academic-post-enhanced-performance#database-optimization') . '" style="color:#d63638;text-decoration:none;">(' . __('Clean', 'wp-academic-post-enhanced') . ')</a>' : '') . '</li>';
        echo '<li>' . __('Spam Comments:', 'wp-academic-post-enhanced') . ' ' . intval($spam_comments) . '</li>';
        echo '<li>' . __('Trashed Comments:', 'wp-academic-post-enhanced') . ' ' . intval($trashed_comments) . '</li>';
        echo '</ul>';
    }

    public function track_performance_data() {
        

        $data = get_option( 'wp_academic_post_enhanced_performance_data', [] );

        // Initialize arrays if they don't exist
        if ( ! isset( $data['load_times'] ) ) $data['load_times'] = [];
        if ( ! isset( $data['memory_usage'] ) ) $data['memory_usage'] = [];
        if ( ! isset( $data['query_counts'] ) ) $data['query_counts'] = [];

        // Load Time
        $load_time = timer_stop( 0, 3 );
        $data['load_times'][] = $load_time;

        // Memory Usage
        $memory_usage = memory_get_peak_usage();
        $data['memory_usage'][] = $memory_usage;

        // Query Count
        global $wpdb;
        $query_counts = $wpdb->num_queries;
        $data['query_counts'][] = $query_counts;

        // Keep only the last 10 entries
        $data['load_times'] = array_slice( $data['load_times'], -10 );
        $data['memory_usage'] = array_slice( $data['memory_usage'], -10 );
        $data['query_counts'] = array_slice( $data['query_counts'], -10 );

        update_option( 'wp_academic_post_enhanced_performance_data', $data );
    }
}
