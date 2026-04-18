<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Add the Performance submenu page.
 */
function wp_academic_post_enhanced_add_performance_admin_menu() {
    add_submenu_page(
        'wp-academic-post-enhanced',
        __( 'Performance Settings', 'wp-academic-post-enhanced' ),
        __( 'Performance', 'wp-academic-post-enhanced' ),
        'manage_options',
        'wp-academic-post-enhanced-performance',
        'wp_academic_post_enhanced_performance_page'
    );
}
add_action( 'admin_menu', 'wp_academic_post_enhanced_add_performance_admin_menu' );

/**
 * Display the Performance settings page.
 */
function wp_academic_post_enhanced_performance_page() {
    if ( isset( $_GET['optimized'] ) && 'true' === $_GET['optimized'] ) {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e( 'Database optimization completed!', 'wp-academic-post-enhanced' ); ?></p>
        </div>
        <?php
    }
    ?>
    <div class="wrap wpa-settings-wrapper">
        <div class="wpa-header">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        </div>
        
        <div class="wpa-vertical-layout">
            <div class="wpa-vertical-nav">
                <ul>
                    <li><a href="#group-optimization" class="wpa-vtab active" data-target="group-optimization"><?php esc_html_e( 'Site Optimization', 'wp-academic-post-enhanced' ); ?></a></li>
                    <li><a href="#group-server" class="wpa-vtab" data-target="group-server"><?php esc_html_e( 'Server & Assets', 'wp-academic-post-enhanced' ); ?></a></li>
                    <li><a href="#group-database" class="wpa-vtab" data-target="group-database"><?php esc_html_e( 'Database', 'wp-academic-post-enhanced' ); ?></a></li>
                    <li><a href="#group-sitemap" class="wpa-vtab" data-target="group-sitemap"><?php esc_html_e( 'Sitemap', 'wp-academic-post-enhanced' ); ?></a></li>
                </ul>
            </div>

            <div class="wpa-vertical-content">
                
                <!-- Group 1: Optimization -->
                <div id="group-optimization" class="wpa-group-content active">
                    <h2 class="nav-tab-wrapper">
                        <a href="#tab-disable" class="nav-tab nav-tab-active"><?php esc_html_e( 'Disable Features', 'wp-academic-post-enhanced' ); ?></a>
                        <a href="#tab-tweaks" class="nav-tab"><?php esc_html_e( 'Performance Tweaks', 'wp-academic-post-enhanced' ); ?></a>
                    </h2>
                    
                    <div id="tab-disable" class="tab-content active">
                        <form action="options.php" method="post">
                            <?php
                            settings_fields( 'wp_academic_post_enhanced_disable_features_options' );
                            wpa_render_specific_section( 'wp_academic_post_enhanced_disable_features', 'wp_academic_post_enhanced_disable_features_section' );
                            submit_button();
                            ?>
                        </form>
                    </div>
                    <div id="tab-tweaks" class="tab-content" style="display:none;">
                        <form action="options.php" method="post">
                            <?php
                            settings_fields( 'wp_academic_post_enhanced_performance_tweaks_options' );
                            wpa_render_specific_section( 'wp_academic_post_enhanced_performance_tweaks', 'wp_academic_post_enhanced_performance_section' );
                            submit_button();
                            ?>
                        </form>
                    </div>
                </div>

                <!-- Group 2: Server & Assets -->
                <div id="group-server" class="wpa-group-content">
                    <h2 class="nav-tab-wrapper">
                        <a href="#tab-server" class="nav-tab nav-tab-active"><?php esc_html_e( 'Server Optimization', 'wp-academic-post-enhanced' ); ?></a>
                        <a href="#tab-css" class="nav-tab"><?php esc_html_e( 'Critical CSS', 'wp-academic-post-enhanced' ); ?></a>
                    </h2>
                    
                    <div id="tab-server" class="tab-content active">
                        <form action="options.php" method="post">
                            <?php
                            settings_fields( 'wp_academic_post_enhanced_server_optimization_options' );
                            wpa_render_specific_section( 'wp_academic_post_enhanced_server_optimization', 'wp_academic_post_enhanced_server_optimization_section' );
                            submit_button();
                            ?>
                        </form>
                    </div>
                    <div id="tab-css" class="tab-content" style="display:none;">
                        <?php wp_academic_post_enhanced_render_critical_css_section(); ?>
                    </div>
                </div>

                <!-- Group 3: Database -->
                <div id="group-database" class="wpa-group-content">
                    <h2 class="nav-tab-wrapper">
                        <a href="#tab-db" class="nav-tab nav-tab-active"><?php esc_html_e( 'Database Cleanup', 'wp-academic-post-enhanced' ); ?></a>
                    </h2>
                    <div id="tab-db" class="tab-content active">
                        <?php wp_academic_post_enhanced_render_database_optimization_section(); ?>
                    </div>
                </div>

                <!-- Group 4: Sitemap -->
                <div id="group-sitemap" class="wpa-group-content">
                    <h2 class="nav-tab-wrapper">
                        <a href="#tab-sitemap" class="nav-tab nav-tab-active"><?php esc_html_e( 'Sitemap Management', 'wp-academic-post-enhanced' ); ?></a>
                    </h2>
                    <div id="tab-sitemap" class="tab-content active">
                        <?php wp_academic_post_enhanced_render_sitemap_section(); ?>
                    </div>
                </div>


            </div>
        </div>
    </div>
    <?php
}

/**
 * Register Performance settings.
 */
function wp_academic_post_enhanced_register_performance_settings() {
    // Disable Features Settings
    register_setting('wp_academic_post_enhanced_disable_features_options', 'wp_academic_post_enhanced_disable_comments', ['type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => false]);
    register_setting('wp_academic_post_enhanced_disable_features_options', 'wp_academic_post_enhanced_disable_pages', ['type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => false]);
    register_setting('wp_academic_post_enhanced_disable_features_options', 'wp_academic_post_enhanced_disable_xmlrpc', ['type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => false]);
    register_setting('wp_academic_post_enhanced_disable_features_options', 'wp_academic_post_enhanced_disable_rest_api', ['type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => false]);
    register_setting('wp_academic_post_enhanced_disable_features_options', 'wp_academic_post_enhanced_disable_feeds', ['type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => false]);
    register_setting('wp_academic_post_enhanced_disable_features_options', 'wp_academic_post_enhanced_disable_emoji', ['type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => false]);
    register_setting('wp_academic_post_enhanced_disable_features_options', 'wp_academic_post_enhanced_disable_embeds', ['type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => false]);
    register_setting('wp_academic_post_enhanced_disable_features_options', 'wp_academic_post_enhanced_disable_jquery_migrate', ['type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => false]);
    register_setting('wp_academic_post_enhanced_disable_features_options', 'wp_academic_post_enhanced_disable_gutenberg_css_frontend', ['type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => false]);
    register_setting('wp_academic_post_enhanced_disable_features_options', 'wp_academic_post_enhanced_disable_search', ['type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => false]);

    // Performance Tweaks Settings
    register_setting('wp_academic_post_enhanced_performance_tweaks_options', 'wp_academic_post_enhanced_heartbeat', ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => 'default']);
    register_setting('wp_academic_post_enhanced_performance_tweaks_options', 'wp_academic_post_enhanced_post_revisions', ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => 'default']);
    register_setting('wp_academic_post_enhanced_performance_tweaks_options', 'wp_academic_post_enhanced_disable_self_pings', ['type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => false]);
    register_setting('wp_academic_post_enhanced_performance_tweaks_options', 'wp_academic_post_enhanced_remove_capital_p_dangit', ['type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => false]);
    register_setting('wp_academic_post_enhanced_performance_tweaks_options', 'wp_academic_post_enhanced_remove_query_strings', ['type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => false]);
    register_setting('wp_academic_post_enhanced_performance_tweaks_options', 'wp_academic_post_enhanced_disable_gravatars', ['type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => false]);
    register_setting('wp_academic_post_enhanced_performance_tweaks_options', 'wp_academic_post_enhanced_defer_javascript', ['type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => false]);
    register_setting('wp_academic_post_enhanced_performance_tweaks_options', 'wp_academic_post_enhanced_remove_x_pingback', ['type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => false]);
    register_setting('wp_academic_post_enhanced_performance_tweaks_options', 'wp_academic_post_enhanced_disable_google_fonts', ['type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => false]);
    register_setting('wp_academic_post_enhanced_performance_tweaks_options', 'wp_academic_post_enhanced_disable_font_awesome', ['type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => false]);
    register_setting('wp_academic_post_enhanced_performance_tweaks_options', 'wp_academic_post_enhanced_clean_up_header', ['type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => false]);
    register_setting('wp_academic_post_enhanced_performance_tweaks_options', 'wp_academic_post_enhanced_preload_requests', ['type' => 'string', 'sanitize_callback' => 'wp_academic_post_enhanced_sanitize_textarea']);
    register_setting('wp_academic_post_enhanced_performance_tweaks_options', 'wp_academic_post_enhanced_dns_prefetch', ['type' => 'string', 'sanitize_callback' => 'wp_academic_post_enhanced_sanitize_textarea']);
    register_setting('wp_academic_post_enhanced_performance_tweaks_options', 'wp_academic_post_enhanced_disable_google_maps', ['type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => false]);
    register_setting('wp_academic_post_enhanced_performance_tweaks_options', 'wp_academic_post_enhanced_image_compression', ['type' => 'integer', 'sanitize_callback' => 'absint', 'default' => 90]);
    register_setting('wp_academic_post_enhanced_performance_tweaks_options', 'wp_academic_post_enhanced_disable_wc_cart_fragmentation', ['type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => false]);
    register_setting('wp_academic_post_enhanced_performance_tweaks_options', 'wp_academic_post_enhanced_remove_wp_version', ['type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => false]);
    register_setting('wp_academic_post_enhanced_performance_tweaks_options', 'wp_academic_post_enhanced_404_parent_redirect', ['type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => false]);


    // Server Optimization Settings
    register_setting(
        'wp_academic_post_enhanced_server_optimization_options',
        'wp_academic_post_enhanced_server_optimization_options',
        [
            'type' => 'array',
            'sanitize_callback' => 'wp_academic_post_enhanced_sanitize_server_optimization_options',
        ]
    );

    // Critical CSS Settings
    register_setting( 
        'wp_academic_post_enhanced_critical_css_options', 
        'wp_academic_post_enhanced_page_optimization_options', 
        'wp_academic_post_enhanced_sanitize_page_optimization_options' 
    );

    require_once plugin_dir_path( __FILE__ ) . 'admin/section-disable-features.php';
    require_once plugin_dir_path( __FILE__ ) . 'admin/section-performance-tweaks.php';
    require_once plugin_dir_path( __FILE__ ) . 'admin/section-server-optimization.php';
    require_once plugin_dir_path( __FILE__ ) . 'admin/section-critical-css.php';
    require_once plugin_dir_path( __FILE__ ) . 'admin/section-database-optimization.php';
    require_once plugin_dir_path( __FILE__ ) . 'admin/section-sitemap.php';

    // Sitemap Settings
    register_setting('wp_academic_post_enhanced_sitemap_options', 'wpa_sitemap_post_types', ['type' => 'array', 'default' => ['post', 'page', 'wpa_news', 'wpa_course', 'wpa_glossary']]);
    register_setting('wp_academic_post_enhanced_sitemap_options', 'wpa_sitemap_limit', ['type' => 'integer', 'sanitize_callback' => 'absint', 'default' => 1000]);
}
add_action( 'admin_init', 'wp_academic_post_enhanced_register_performance_settings' );

/**
 * Sanitize the Server Optimization options array.
 */
function wp_academic_post_enhanced_sanitize_server_optimization_options( $input ) {
    $sanitized_input = [];
    $sanitized_input['esi_enabled'] = isset( $input['esi_enabled'] ) ? rest_sanitize_boolean( $input['esi_enabled'] ) : false;
    $sanitized_input['image_optimization_enabled'] = isset( $input['image_optimization_enabled'] ) ? rest_sanitize_boolean( $input['image_optimization_enabled'] ) : false;
    $sanitized_input['gzip_compression_enabled'] = isset( $input['gzip_compression_enabled'] ) ? rest_sanitize_boolean( $input['gzip_compression_enabled'] ) : false;
    $sanitized_input['http2_push_enabled'] = isset( $input['http2_push_enabled'] ) ? rest_sanitize_boolean( $input['http2_push_enabled'] ) : false;
    $sanitized_input['keep_alive_enabled'] = isset( $input['keep_alive_enabled'] ) ? rest_sanitize_boolean( $input['keep_alive_enabled'] ) : false;
    $sanitized_input['hotlink_protection_enabled'] = isset( $input['hotlink_protection_enabled'] ) ? rest_sanitize_boolean( $input['hotlink_protection_enabled'] ) : false;
    $sanitized_input['vary_accept_encoding_enabled'] = isset( $input['vary_accept_encoding_enabled'] ) ? rest_sanitize_boolean( $input['vary_accept_encoding_enabled'] ) : false;
    return $sanitized_input;
}

/**
 * Enqueue scripts for the performance page.
 */
function wp_academic_post_enhanced_enqueue_performance_scripts( $hook_suffix ) {
    if ( ! isset( $_GET['page'] ) || 'wp-academic-post-enhanced-performance' !== $_GET['page'] ) {
        return;
    }
    wp_enqueue_script( 'wp-academic-post-enhanced-performance-tabs', plugin_dir_url( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'assets/js/performance-tabs.js', [ 'jquery' ], '1.0', true );
}
add_action( 'admin_enqueue_scripts', 'wp_academic_post_enhanced_enqueue_performance_scripts' );



