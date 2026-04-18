<?php
/**
 * Plugin Name: WP Academic Post Enhanced
 * Description: A plugin to enhance blog posts for the academic sector with a focus on SEO and LLMs.
 * Version: 3.9.2
 * Author: Mohammed Looti
 * Author URI: Your Website
 * License: GPL2
 * Text Domain: wp-academic-post-enhanced
 */



if ( ! defined( 'WP_ACADEMIC_POST_ENHANCED_FILE' ) ) {
    define( 'WP_ACADEMIC_POST_ENHANCED_FILE', __FILE__ );
}

if ( ! defined( 'WPA_VERSION' ) ) {
    define( 'WPA_VERSION', '3.9.2' );
}

// Load the plugin features.
require_once plugin_dir_path( __FILE__ ) . 'includes/class-activator.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/autoloader.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/pdf/pdf-download.php'; // PDF Download Handler

// Activation Hook
register_activation_hook( __FILE__, [ 'WPA_Activator', 'activate' ] );

// Update Check (Runs on Admin Init)
add_action( 'admin_init', [ 'WPA_Activator', 'check_update' ] );

// Temporary Sitemap Fix Trigger
add_action( 'init', function() {
    if ( ! is_admin() && ! get_option( 'wpa_sitemap_fix_v7' ) ) {
        global $wpdb;
        $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_wpa_sitemap_%'" );
        flush_rewrite_rules();
        update_option( 'wpa_sitemap_fix_v7', time() );
    }
});

// Register Widgets
function wpa_register_widgets() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-fixed-content-widget.php';
    register_widget( 'WPA_Fixed_Content_Widget' );
}
add_action( 'widgets_init', 'wpa_register_widgets' );


/**
 * Load plugin textdomain.
 */
function wp_academic_post_enhanced_load_textdomain() {
	load_plugin_textdomain( 'wp-academic-post-enhanced', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'wp_academic_post_enhanced_load_textdomain' );

/**
 * Add the main menu page.
 */
function wp_academic_post_enhanced_add_admin_menu() {
    add_menu_page(
        __( 'WP Academic Post Enhanced', 'wp-academic-post-enhanced' ),
        __( 'Academic Post', 'wp-academic-post-enhanced' ),
        'manage_options',
        'wp-academic-post-enhanced',
        'wp_academic_post_enhanced_main_page',
        'dashicons-welcome-learn-more',
        20
    );
}
add_action( 'admin_menu', 'wp_academic_post_enhanced_add_admin_menu' );

/**
 * Enqueue admin styles.
 */
function wp_academic_post_enhanced_enqueue_admin_styles( $hook_suffix ) {
    wp_enqueue_style(
        'wp-academic-post-enhanced-admin-settings',
        plugin_dir_url( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'assets/css/admin-settings.css',
        [],
        '1.100'
    );

    if ( is_rtl() ) {
        wp_enqueue_style(
            'wpa-rtl',
            plugin_dir_url( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'assets/css/wpa-rtl.css',
            [],
            '1.0'
        );
    }

    // Enqueue Tabs Script for all plugin pages
    if ( strpos( $hook_suffix, 'academic-post' ) !== false || strpos( $hook_suffix, 'wpa_' ) !== false ) {
        wp_enqueue_script(
            'wp-academic-post-enhanced-tabs',
            plugin_dir_url( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'assets/js/performance-tabs.js',
            ['jquery'],
            '1.3',
            true
        );

        // Unified Color Picker Enqueue
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script(
            'wpa-color-picker-init',
            plugin_dir_url( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'assets/js/wpa-color-picker-init.js',
            ['wp-color-picker', 'jquery'],
            '1.0',
            true
        );
    }

    // Enqueue Homepage Builder Assets only on the Custom Theme page
    if ( strpos( $hook_suffix, 'wp-academic-post-enhanced-homepage' ) !== false ) {
        wp_enqueue_style(
            'wpa-homepage-builder-css',
            plugin_dir_url( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'assets/css/homepage-builder.css',
            [],
            '1.0'
        );

        wp_enqueue_script(
            'wpa-homepage-builder-js',
            plugin_dir_url( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'assets/js/homepage-builder.js',
            ['jquery', 'jquery-ui-sortable'],
            '1.0',
            true
        );
    }
}
add_action( 'admin_enqueue_scripts', 'wp_academic_post_enhanced_enqueue_admin_styles' );



/**
 * Display the main page.
 */
function wp_academic_post_enhanced_main_page() {
    require_once plugin_dir_path( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'includes/views/admin-main-page.php';
}

/**
 * Handle feature toggle action.
 */
function wp_academic_post_enhanced_toggle_feature() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'You do not have sufficient permissions to access this page.', 'wp-academic-post-enhanced' ) );
    }

    $feature_key = isset( $_POST['feature_key'] ) ? sanitize_key( $_POST['feature_key'] ) : '';
    $enabled_option = isset( $_POST['enabled_option'] ) ? sanitize_key( $_POST['enabled_option'] ) : '';
    $current_status = isset( $_POST['current_status'] ) ? (bool) $_POST['current_status'] : false;

    if ( ! wp_verify_nonce( $_POST['_wpnonce_wp_academic_post_enhanced_toggle_feature'], 'wp_academic_post_enhanced_toggle_feature_' . $feature_key ) ) {
        wp_die( __( 'Nonce verification failed.', 'wp-academic-post-enhanced' ) );
    }

    if ( ! empty( $enabled_option ) ) {
        $new_status = ! $current_status;
        update_option( $enabled_option, $new_status );

        $settings_map = [
            'citation' => 'wpa_citation_settings',
            'toc' => 'wpa_toc_settings',
            'social' => 'wpa_social_settings',
            'author' => 'wpa_author_settings',
            'schema' => 'wpa_schema_settings',
            'smtp' => 'wpa_smtp_settings',
            'advanced' => 'wpa_advanced_settings',
            'reading' => 'wpa_reading_settings',
            'theme' => 'wpa_homepage_settings',
            'glossary' => 'wpa_glossary_settings',
        ];

        if ( array_key_exists( $feature_key, $settings_map ) ) {
            $settings_option_name = $settings_map[ $feature_key ];
            $options = get_option( $settings_option_name, [] );
            $options['enabled'] = $new_status;
            update_option( $settings_option_name, $options );
        }
    }

    // Redirect back to the main plugin page
    wp_safe_redirect( admin_url( 'admin.php?page=wp-academic-post-enhanced&msg=settings-saved' ) );
    exit;
}
add_action( 'admin_post_wp_academic_post_enhanced_toggle_feature', 'wp_academic_post_enhanced_toggle_feature' );

/**
 * Custom Settings Renderer for Grid Layout.
 * Wraps sections in cards.
 */
function wpa_render_settings_sections_as_grid( $page ) {
    global $wp_settings_sections, $wp_settings_fields;

    if ( ! isset( $wp_settings_sections[ $page ] ) ) {
        return;
    }

    echo '<div class="wpa-settings-grid">';

    foreach ( (array) $wp_settings_sections[ $page ] as $section ) {
        echo '<div class="wpa-section-card">';
        
        if ( $section['title'] ) {
            echo "<h3>{$section['title']}</h3>\n";
        }

        if ( $section['callback'] ) {
            echo '<div class="wpa-section-desc">';
            call_user_func( $section['callback'], $section );
            echo '</div>';
        }

        if ( ! isset( $wp_settings_fields ) || ! isset( $wp_settings_fields[ $page ] ) || ! isset( $wp_settings_fields[ $page ][ $section['id'] ] ) ) {
            echo '</div>'; // Close card even if empty fields
            continue;
        }

        echo '<table class="form-table" role="presentation">';
        do_settings_fields( $page, $section['id'] );
        echo '</table>';
        
        echo '</div>'; // .wpa-section-card
    }

    echo '</div>'; // .wpa-settings-grid
}

/**
 * Custom Settings Renderer for a Specific Section.
 * Renders fields of a specific section without the section title wrapper (or with).
 * Useful for custom tab layouts.
 */
function wpa_render_specific_section( $page, $section_id ) {
    global $wp_settings_sections, $wp_settings_fields;

    if ( ! isset( $wp_settings_sections[ $page ][ $section_id ] ) ) {
        return;
    }

    $section = $wp_settings_sections[ $page ][ $section_id ];

    if ( $section['callback'] ) {
        echo '<div class="wpa-section-desc">';
        call_user_func( $section['callback'], $section );
        echo '</div>';
    }

    if ( ! isset( $wp_settings_fields ) || ! isset( $wp_settings_fields[ $page ] ) || ! isset( $wp_settings_fields[ $page ][ $section['id'] ] ) ) {
        return;
    }

    echo '<table class="form-table" role="presentation">';
    do_settings_fields( $page, $section['id'] );
    echo '</table>';
}
