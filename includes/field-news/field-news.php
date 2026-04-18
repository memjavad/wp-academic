<?php
/**
 * Field News Main Loader (Refactored)
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Load CPT
require_once plugin_dir_path( __FILE__ ) . 'cpt.php';

// Load Classes
require_once plugin_dir_path( __FILE__ ) . 'inc/class-scopus-api.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-google-ai.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-unsplash-api.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-news-generator.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-news-widget.php';

if ( is_admin() ) {
    require_once plugin_dir_path( __FILE__ ) . 'inc/class-dashboard-stats.php';
}

// Load Legacy Wrapper (for Admin/AJAX Hooks)
require_once plugin_dir_path( __FILE__ ) . 'class-field-news-engine.php';

// Load Repo Admin
if ( is_admin() ) {
    require_once plugin_dir_path( __FILE__ ) . 'repo-admin.php';
}

// Load Frontend
require_once plugin_dir_path( __FILE__ ) . 'frontend.php';

/**
 * Add custom cron intervals.
 */
function wpa_field_news_cron_schedules( $schedules ) {
    $schedules['1min'] = [ 'interval' => 60, 'display' => __( 'Every Minute', 'wp-academic-post-enhanced' ) ];
    $schedules['5min'] = [ 'interval' => 300, 'display' => __( 'Every 5 Minutes', 'wp-academic-post-enhanced' ) ];
    $schedules['15min'] = [ 'interval' => 900, 'display' => __( 'Every 15 Minutes', 'wp-academic-post-enhanced' ) ];
    $schedules['30min'] = [ 'interval' => 1800, 'display' => __( 'Every 30 Minutes', 'wp-academic-post-enhanced' ) ];
    $schedules['weekly'] = [
        'interval' => 604800,
        'display'  => __( 'Weekly', 'wp-academic-post-enhanced' )
    ];
    return $schedules;
}
add_filter( 'cron_schedules', 'wpa_field_news_cron_schedules' );

/**
 * Initialize the module and handle scheduling.
 */
function wpa_field_news_init() {
    $options = get_option( 'wpa_field_news_settings' );
    
    // --- Schedule 1: Auto-Posting ---
    $post_enabled = isset( $options['auto_post_enable'] ) ? $options['auto_post_enable'] : 0;
    $post_interval = isset( $options['auto_post_interval'] ) ? $options['auto_post_interval'] : 'daily';
    $post_hook = 'wpa_field_news_cron_event';

    if ( $post_enabled ) {
        if ( ! wp_next_scheduled( $post_hook ) ) {
            wp_schedule_event( time(), $post_interval, $post_hook );
        } elseif ( wp_get_schedule( $post_hook ) !== $post_interval ) {
            wp_clear_scheduled_hook( $post_hook );
            wp_schedule_event( time(), $post_interval, $post_hook );
        }
    } else {
        wp_clear_scheduled_hook( $post_hook );
    }

    // --- Schedule 2: Auto-Fetching ---
    $fetch_enabled = isset( $options['repo_auto_fetch'] ) ? $options['repo_auto_fetch'] : 0;
    $fetch_interval = isset( $options['repo_fetch_interval'] ) ? $options['repo_fetch_interval'] : 'daily';
    $fetch_hook = 'wpa_field_news_fetch_cron_event';

    if ( $fetch_enabled ) {
        if ( ! wp_next_scheduled( $fetch_hook ) ) {
            wp_schedule_event( time(), $fetch_interval, $fetch_hook );
        } elseif ( wp_get_schedule( $fetch_hook ) !== $fetch_interval ) {
            wp_clear_scheduled_hook( $fetch_hook );
            wp_schedule_event( time(), $fetch_interval, $fetch_hook );
        }
    } else {
        wp_clear_scheduled_hook( $fetch_hook );
    }

    // --- Schedule 3: Auto-Screening ---
    $screen_enabled = isset( $options['auto_screen_enable'] ) ? $options['auto_screen_enable'] : 0;
    $screen_interval = isset( $options['auto_screen_interval'] ) ? $options['auto_screen_interval'] : 'hourly';
    $screen_hook = 'wpa_field_news_screen_cron_event';

    if ( $screen_enabled ) {
        if ( ! wp_next_scheduled( $screen_hook ) ) {
            wp_schedule_event( time(), $screen_interval, $screen_hook );
        } elseif ( wp_get_schedule( $screen_hook ) !== $screen_interval ) {
            wp_clear_scheduled_hook( $screen_hook );
            wp_schedule_event( time(), $screen_interval, $screen_hook );
        }
    } else {
        wp_clear_scheduled_hook( $screen_hook );
    }

    // Register Sidebar
    register_sidebar( [
        'name'          => __( 'Field News Sidebar', 'wp-academic-post-enhanced' ),
        'id'            => 'wpa-field-news-sidebar',
        'description'   => __( 'Sidebar for the custom Field News single post template.', 'wp-academic-post-enhanced' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s wpa-widget">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ] );

    // Register Widget
    register_widget( 'WPA_Field_News_Widget' );
}
add_action( 'init', 'wpa_field_news_init' );

/**
 * Cron Event Handler: Posting
 */
function wpa_field_news_run_cron() {
    try {
        $gen = new WPA_News_Generator();
        $id = $gen->generate_post();
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) error_log( 'Field News Auto-Post Success: Post ID ' . $id );
    } catch ( Exception $e ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) error_log( 'Field News Auto-Post Failed: ' . $e->getMessage() );
    }
}
add_action( 'wpa_field_news_cron_event', 'wpa_field_news_run_cron' );

/**
 * Cron Event Handler: Fetching
 */
function wpa_field_news_run_fetch_cron() {
    try {
        $gen = new WPA_News_Generator();
        $count = $gen->fetch_and_store_candidates();
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) error_log( 'Field News Auto-Fetch Success: Stored ' . $count . ' candidates.' );
    } catch ( Exception $e ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) error_log( 'Field News Auto-Fetch Failed: ' . $e->getMessage() );
    }
}

add_action( 'wpa_field_news_fetch_cron_event', 'wpa_field_news_run_fetch_cron' );

/**
 * Cron Event Handler: Auto-Screening
 */
function wpa_field_news_run_screen_cron() {
    try {
        // Get Pending Studies
        $args = [
            'post_type'      => 'wpa_study',
            'posts_per_page' => 20, // Screen in batches
            'post_status'    => 'publish',
            'meta_key'       => '_wpa_status',
            'meta_value'     => 'pending',
            'orderby'        => 'date',
            'order'          => 'DESC'
        ];
        $query = new WP_Query( $args );

        if ( ! $query->have_posts() ) return;

        $studies = [];
        while ( $query->have_posts() ) {
            $query->the_post();
            $id = get_the_ID();
            $data = get_post_meta( $id, '_wpa_study_data', true );
            $studies[] = [
                'id' => $id,
                'title' => get_the_title(),
                'abstract' => isset($data['abstract']) ? $data['abstract'] : ''
            ];
        }
        wp_reset_postdata();

        $ai = new WPA_Google_AI();
        $results = $ai->bulk_screen_studies( $studies );

        $count = 0;
        if ( ! empty( $results['ignored'] ) ) {
            foreach ( $results['ignored'] as $id ) {
                update_post_meta( $id, '_wpa_status', 'ignored' );
                $count++;
            }
        }
        if ( ! empty( $results['selected'] ) ) {
            foreach ( $results['selected'] as $id ) {
                update_post_meta( $id, '_wpa_status', 'selected' );
                $count++;
            }
        }

        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) error_log( 'Field News Auto-Screen Success: Processed ' . $count . ' studies.' );

    } catch ( Exception $e ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) error_log( 'Field News Auto-Screen Failed: ' . $e->getMessage() );
    }
}
add_action( 'wpa_field_news_screen_cron_event', 'wpa_field_news_run_screen_cron' );



/**
 * Check for missed schedules on Init (Catch-Up Logic).
 * This ensures that if the site was offline (local dev), tasks run upon return.
 */
function wpa_field_news_check_missed_schedule() {
    // Check transient lock (prevent flooding if error occurs)
    if ( get_transient( 'wpa_field_news_catchup_lock' ) ) return;

    // Avoid running on AJAX requests
    if ( defined('DOING_AJAX') && DOING_AJAX ) return;

    $options = get_option( 'wpa_field_news_settings' );
    $post_enabled = isset( $options['auto_post_enable'] ) ? $options['auto_post_enable'] : 0;
    
    if ( ! $post_enabled ) return;

    $last_run = get_option( 'wpa_field_news_last_post_time', 0 );
    $interval_key = isset( $options['auto_post_interval'] ) ? $options['auto_post_interval'] : 'daily';
    
    // Determine interval in seconds
    $schedules = wp_get_schedules();
    $interval = isset( $schedules[ $interval_key ] ) ? $schedules[ $interval_key ]['interval'] : 86400;

    // If never run, set last run to now (don't run immediately on first enable)
    if ( $last_run == 0 ) {
        update_option( 'wpa_field_news_last_post_time', current_time( 'timestamp' ) );
        return;
    }

    $now = current_time( 'timestamp' );
    $time_since = $now - $last_run;

    // If time elapsed is greater than interval + buffer (5 mins)
    if ( $time_since > ( $interval + 300 ) ) {
        // Set lock for 5 minutes
        set_transient( 'wpa_field_news_catchup_lock', true, 300 );
        
        // Trigger Generation
        wpa_field_news_run_cron();
        
        // Show Admin Notice if in Admin
        if ( is_admin() ) {
            add_action( 'admin_notices', function() {
                echo '<div class="notice notice-info is-dismissible"><p>' . esc_html__( 'Field News Generator: Missed schedule detected. Automatically generated a news story now.', 'wp-academic-post-enhanced' ) . '</p></div>';
            });
        }
    }
}
add_action( 'init', 'wpa_field_news_check_missed_schedule' );
