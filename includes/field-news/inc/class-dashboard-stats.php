<?php
/**
 * Field News Dashboard Widget
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPA_Field_News_Dashboard_Widget {

    public function __construct() {
        add_action( 'wp_dashboard_setup', [ $this, 'add_dashboard_widgets' ] );
    }

    public function add_dashboard_widgets() {
        wp_add_dashboard_widget(
            'wpa_field_news_stats',
            __( 'Field News Automation', 'wp-academic-post-enhanced' ),
            [ $this, 'render_widget' ]
        );
    }

    public function render_widget() {
        // Count Pending Studies
        $pending = new WP_Query([
            'post_type' => 'wpa_study',
            'meta_key' => '_wpa_status',
            'meta_value' => 'pending',
            'post_status' => 'publish',
            'fields' => 'ids'
        ]);
        $pending_count = $pending->found_posts;

        // Count Published News
        $news = wp_count_posts( 'wpa_news' );
        $published_count = isset($news->publish) ? $news->publish : 0;

        $last_fetch = get_option( 'wpa_field_news_last_fetch_time', 'Never' );
        if ( $last_fetch !== 'Never' ) {
            $last_fetch = human_time_diff( $last_fetch, current_time( 'timestamp' ) ) . ' ago';
        }

        $last_post = get_option( 'wpa_field_news_last_post_time', 'Never' );
        if ( $last_post !== 'Never' ) {
            $last_post = human_time_diff( $last_post, current_time( 'timestamp' ) ) . ' ago';
        }

        // Calculate Next Runs
        $next_fetch_ts = wp_next_scheduled( 'wpa_field_news_fetch_cron_event' );
        $next_fetch = $next_fetch_ts ? 'in ' . human_time_diff( current_time( 'timestamp' ), $next_fetch_ts ) : 'Not Scheduled';

        $next_post_ts = wp_next_scheduled( 'wpa_field_news_cron_event' );
        $next_post = $next_post_ts ? 'in ' . human_time_diff( current_time( 'timestamp' ), $next_post_ts ) : 'Not Scheduled';

        echo '<div class="wpa-dash-stats" style="display:flex; justify-content:space-around; text-align:center; margin-bottom:20px;">';
        
        echo '<div>';
        echo '<span class="dashicons dashicons-database" style="font-size:30px; height:30px; width:30px; color:#64748b;"></span>';
        echo '<h4 style="margin:5px 0; font-size:1.5em; color:#2271b1;">' . intval( $pending_count ) . '</h4>';
        echo '<span style="color:#64748b; font-size:0.9em;">Pending Studies</span>';
        echo '</div>';

        echo '<div>';
        echo '<span class="dashicons dashicons-megaphone" style="font-size:30px; height:30px; width:30px; color:#64748b;"></span>';
        echo '<h4 style="margin:5px 0; font-size:1.5em; color:#166534;">' . intval( $published_count ) . '</h4>';
        echo '<span style="color:#64748b; font-size:0.9em;">Published News</span>';
        echo '</div>';

        echo '</div>';

        echo '<div style="background:#f0f6fc; padding:10px; border-radius:4px; margin-bottom:15px; font-size:0.9em; color:#4b5563;">';
        echo '<div style="margin-bottom:5px; display:flex; justify-content:space-between;"><span><strong>Last Fetch:</strong> ' . esc_html( $last_fetch ) . '</span> <span style="color:#2271b1;">(Next: ' . esc_html( $next_fetch ) . ')</span></div>';
        echo '<div style="display:flex; justify-content:space-between;"><span><strong>Last Post:</strong> ' . esc_html( $last_post ) . '</span> <span style="color:#166534;">(Next: ' . esc_html( $next_post ) . ')</span></div>';
        echo '</div>';

        echo '<div style="display:flex; gap:10px;">';
        echo '<a href="' . admin_url( 'edit.php?post_type=wpa_news&page=wpa-field-news-repo' ) . '" class="button button-primary" style="flex:1; text-align:center;">Manage Repo</a>';
        // We could add a quick fetch button here too if we replicated the form
        echo '</div>';
    }
}

new WPA_Field_News_Dashboard_Widget();
