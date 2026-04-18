<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Load all inc files
require_once plugin_dir_path( __FILE__ ) . 'inc/class-advanced-minifier.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-disable-features.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-performance-tweaks.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-astra-optimization.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-asset-manager.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-critical-css.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-server-optimization.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-database-optimizer.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-404-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-sitemap-manager.php';


// Load admin-only classes
if ( is_admin() ) {
    require_once plugin_dir_path( __FILE__ ) . 'inc/class-dashboard-widget.php';
    require_once plugin_dir_path( __FILE__ ) . 'inc/class-performance-reports.php';
}

class WP_Academic_Post_Enhanced_Advanced {

    private static $instance;

    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        // Instantiate classes
        WP_Academic_Post_Enhanced_Disable_Features::get_instance();
        WP_Academic_Post_Enhanced_Performance_Tweaks::get_instance();
        WP_Academic_Post_Enhanced_Astra_Optimization::get_instance();
        
        // Fix: Ensure these are loaded
        WP_Academic_Post_Enhanced_Critical_CSS::get_instance();
        WP_Academic_Post_Enhanced_Server_Optimization::get_instance();
        WP_Academic_Post_Enhanced_Database_Optimizer::get_instance();
        WP_Academic_Post_Enhanced_404_Handler::get_instance();
        WP_Academic_Post_Enhanced_Sitemap_Manager::get_instance();
        
        // Initial Flush for Sitemap URL
        if ( ! get_option( 'wpa_sitemap_flushed_v2' ) ) {
            flush_rewrite_rules();
            update_option( 'wpa_sitemap_flushed_v2', time() );
        }

        
        // Instantiate admin-only classes
        if ( is_admin() ) {
            WP_Academic_Post_Enhanced_Dashboard_Widget::get_instance();
            WP_Academic_Post_Enhanced_Performance_Reports::get_instance();
        }
    }
}

WP_Academic_Post_Enhanced_Advanced::get_instance();