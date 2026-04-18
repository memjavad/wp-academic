<?php
/**
 * Field News Admin Settings
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register the admin menu for Field News.
 */
function wpa_field_news_add_admin_menu() {
    add_submenu_page(
        'edit.php?post_type=wpa_news',
        __( 'Settings', 'wp-academic-post-enhanced' ),
        __( 'Settings', 'wp-academic-post-enhanced' ),
        'manage_options',
        'wp-academic-post-enhanced-field-news',
        'wpa_field_news_settings_page'
    );
}
add_action( 'admin_menu', 'wpa_field_news_add_admin_menu' );

/**
 * Register Settings.
 */
function wpa_field_news_register_settings() {
    register_setting( 'wpa_field_news_options', 'wpa_field_news_settings' );

    // 1. General & API Section
    add_settings_section(
        'wpa_field_news_general_section',
        __( 'General Configuration & APIs', 'wp-academic-post-enhanced' ),
        'wpa_field_news_general_section_callback',
        'wpa_field_news'
    );
    // APIs
    add_settings_field( 'scopus_api_key', __( 'Scopus API Key', 'wp-academic-post-enhanced' ), 'wpa_field_news_text_field', 'wpa_field_news', 'wpa_field_news_general_section', ['label_for' => 'scopus_api_key'] );
    add_settings_field( 'scopus_inst_token', __( 'Scopus Institutional Token', 'wp-academic-post-enhanced' ), 'wpa_field_news_text_field', 'wpa_field_news', 'wpa_field_news_general_section', ['label_for' => 'scopus_inst_token', 'desc' => 'Optional. Required by some university-linked API keys.'] );
    add_settings_field( 'google_api_key', __( 'Google AI Studio API Key', 'wp-academic-post-enhanced' ), 'wpa_field_news_text_field', 'wpa_field_news', 'wpa_field_news_general_section', ['label_for' => 'google_api_key'] );
    add_settings_field( 'unsplash_api_key', __( 'Unsplash Access Key', 'wp-academic-post-enhanced' ), 'wpa_field_news_text_field', 'wpa_field_news', 'wpa_field_news_general_section', ['label_for' => 'unsplash_api_key'] );
    add_settings_field( 'unsplash_secret_key', __( 'Unsplash Secret Key', 'wp-academic-post-enhanced' ), 'wpa_field_news_text_field', 'wpa_field_news', 'wpa_field_news_general_section', ['label_for' => 'unsplash_secret_key'] );
    // Global Settings
    add_settings_field( 'target_language', __( 'Target Language', 'wp-academic-post-enhanced' ), 'wpa_field_news_select_language_field', 'wpa_field_news', 'wpa_field_news_general_section', ['label_for' => 'target_language'] );
    add_settings_field( 'post_status', __( 'Default Post Status', 'wp-academic-post-enhanced' ), 'wpa_field_news_select_status_field', 'wpa_field_news', 'wpa_field_news_general_section', ['label_for' => 'post_status'] );
    add_settings_field( 'default_author', __( 'Default Post Author', 'wp-academic-post-enhanced' ), 'wpa_field_news_select_author_field', 'wpa_field_news', 'wpa_field_news_general_section', ['label_for' => 'default_author'] );

    // 2. Content Sources Section
    add_settings_section(
        'wpa_field_news_source_section',
        __( 'Content Sources & Filtering', 'wp-academic-post-enhanced' ),
        'wpa_field_news_source_section_callback',
        'wpa_field_news'
    );
    add_settings_field( 'topic_groups', __( 'Search Terms & Categories', 'wp-academic-post-enhanced' ), 'wpa_field_news_topic_repeater_field', 'wpa_field_news', 'wpa_field_news_source_section', ['label_for' => 'topic_groups'] );
    // Filters
    add_settings_field( 'scopus_date_range', __( 'Publication Date Range', 'wp-academic-post-enhanced' ), 'wpa_field_news_select_date_field', 'wpa_field_news', 'wpa_field_news_source_section', ['label_for' => 'scopus_date_range'] );
    add_settings_field( 'scopus_doc_type', __( 'Document Type', 'wp-academic-post-enhanced' ), 'wpa_field_news_select_doctype_field', 'wpa_field_news', 'wpa_field_news_source_section', ['label_for' => 'scopus_doc_type'] );
    add_settings_field( 'scopus_min_citations', __( 'Minimum Citations', 'wp-academic-post-enhanced' ), 'wpa_field_news_number_field', 'wpa_field_news', 'wpa_field_news_source_section', ['label_for' => 'scopus_min_citations', 'desc' => 'Filter for impactful studies.'] );
    add_settings_field( 'scopus_open_access', __( 'Open Access Only', 'wp-academic-post-enhanced' ), 'wpa_field_news_checkbox_field', 'wpa_field_news', 'wpa_field_news_source_section', ['label_for' => 'scopus_open_access'] );
    // Selection Logic
    add_settings_field( 'selection_strategy', __( 'Selection Strategy', 'wp-academic-post-enhanced' ), 'wpa_field_news_select_strategy_field', 'wpa_field_news', 'wpa_field_news_source_section', ['label_for' => 'selection_strategy'] );
    add_settings_field( 'selection_batch_size', __( 'Candidates per Batch', 'wp-academic-post-enhanced' ), 'wpa_field_news_number_field', 'wpa_field_news', 'wpa_field_news_source_section', ['label_for' => 'selection_batch_size', 'desc' => 'Studies sent to AI for evaluation (1-20).'] );
    add_settings_field( 'selection_criteria', __( 'Custom Selection Rules', 'wp-academic-post-enhanced' ), 'wpa_field_news_textarea_field', 'wpa_field_news', 'wpa_field_news_source_section', ['label_for' => 'selection_criteria', 'desc' => 'E.g., "Prioritize human trials".'] );
    add_settings_field( 'google_model_selector', __( 'Selector Model', 'wp-academic-post-enhanced' ), 'wpa_field_news_select_model_field', 'wpa_field_news', 'wpa_field_news_source_section', ['label_for' => 'google_model_selector', 'desc' => 'AI used to pick the best study.'] );

    // 3. AI Writer Section
    add_settings_section(
        'wpa_field_news_ai_section',
        __( 'AI Writer & Editor', 'wp-academic-post-enhanced' ),
        'wpa_field_news_ai_section_callback',
        'wpa_field_news'
    );
    // Models
    add_settings_field( 'google_model_title', __( 'Model for Title', 'wp-academic-post-enhanced' ), 'wpa_field_news_select_model_field', 'wpa_field_news', 'wpa_field_news_ai_section', ['label_for' => 'google_model_title'] );
    add_settings_field( 'google_model_body', __( 'Model for Body', 'wp-academic-post-enhanced' ), 'wpa_field_news_select_model_field', 'wpa_field_news', 'wpa_field_news_ai_section', ['label_for' => 'google_model_body'] );
    add_settings_field( 'google_model_tags', __( 'Model for Tags', 'wp-academic-post-enhanced' ), 'wpa_field_news_select_model_field', 'wpa_field_news', 'wpa_field_news_ai_section', ['label_for' => 'google_model_tags'] );
    // Style
    add_settings_field( 'ai_tone', __( 'Tone of Voice', 'wp-academic-post-enhanced' ), 'wpa_field_news_select_tone_field', 'wpa_field_news', 'wpa_field_news_ai_section', ['label_for' => 'ai_tone'] );
    add_settings_field( 'ai_length', __( 'Article Length', 'wp-academic-post-enhanced' ), 'wpa_field_news_select_length_field', 'wpa_field_news', 'wpa_field_news_ai_section', ['label_for' => 'ai_length'] );
    add_settings_field( 'ai_audience', __( 'Target Audience', 'wp-academic-post-enhanced' ), 'wpa_field_news_select_audience_field', 'wpa_field_news', 'wpa_field_news_ai_section', ['label_for' => 'ai_audience'] );
    add_settings_field( 'ai_structure', __( 'Article Structure', 'wp-academic-post-enhanced' ), 'wpa_field_news_select_structure_field', 'wpa_field_news', 'wpa_field_news_ai_section', ['label_for' => 'ai_structure'] );
    add_settings_field( 'ai_custom_instructions', __( 'Custom Instructions', 'wp-academic-post-enhanced' ), 'wpa_field_news_textarea_field', 'wpa_field_news', 'wpa_field_news_ai_section', ['label_for' => 'ai_custom_instructions'] );
    // Review
    add_settings_field( 'enable_ai_review', __( 'Enable AI Peer Review', 'wp-academic-post-enhanced' ), 'wpa_field_news_checkbox_field', 'wpa_field_news', 'wpa_field_news_ai_section', ['label_for' => 'enable_ai_review'] );
    add_settings_field( 'enable_ai_fact_check', __( 'Enable Rigorous Fact-Checking', 'wp-academic-post-enhanced' ), 'wpa_field_news_checkbox_field', 'wpa_field_news', 'wpa_field_news_ai_section', ['label_for' => 'enable_ai_fact_check', 'desc' => 'Cross-references generated text with the original study abstract.'] );
    add_settings_field( 'google_model_review', __( 'Reviewer Model', 'wp-academic-post-enhanced' ), 'wpa_field_news_select_model_field', 'wpa_field_news', 'wpa_field_news_ai_section', ['label_for' => 'google_model_review'] );
    add_settings_field( 'review_strictness', __( 'Review Strictness', 'wp-academic-post-enhanced' ), 'wpa_field_news_select_strictness_field', 'wpa_field_news', 'wpa_field_news_ai_section', ['label_for' => 'review_strictness'] );

    // 4. Automation Section
    add_settings_section(
        'wpa_field_news_auto_section',
        __( 'Automation Schedule', 'wp-academic-post-enhanced' ),
        'wpa_field_news_auto_section_callback',
        'wpa_field_news'
    );
    add_settings_field( 'auto_post_enable', __( 'Enable Auto-Posting', 'wp-academic-post-enhanced' ), 'wpa_field_news_checkbox_field', 'wpa_field_news', 'wpa_field_news_auto_section', ['label_for' => 'auto_post_enable', 'desc' => 'Automatically generate and publish news.'] );
    add_settings_field( 'auto_post_interval', __( 'Posting Interval', 'wp-academic-post-enhanced' ), 'wpa_field_news_select_interval_field', 'wpa_field_news', 'wpa_field_news_auto_section', ['label_for' => 'auto_post_interval'] );
    add_settings_field( 'repo_auto_fetch', __( 'Auto-Populate Repository', 'wp-academic-post-enhanced' ), 'wpa_field_news_checkbox_field', 'wpa_field_news', 'wpa_field_news_auto_section', ['label_for' => 'repo_auto_fetch', 'desc' => 'Automatically fetch and store new studies.'] );
    add_settings_field( 'repo_fetch_interval', __( 'Fetch Interval', 'wp-academic-post-enhanced' ), 'wpa_field_news_select_interval_field', 'wpa_field_news', 'wpa_field_news_auto_section', ['label_for' => 'repo_fetch_interval'] );
    
    // Auto Screening
    add_settings_field( 'auto_screen_enable', __( 'Enable Auto-Screening', 'wp-academic-post-enhanced' ), 'wpa_field_news_checkbox_field', 'wpa_field_news', 'wpa_field_news_auto_section', ['label_for' => 'auto_screen_enable', 'desc' => 'Automatically use AI to screen pending studies into "Selected" or "Ignored" lists.'] );
    add_settings_field( 'auto_screen_interval', __( 'Screening Interval', 'wp-academic-post-enhanced' ), 'wpa_field_news_select_interval_field', 'wpa_field_news', 'wpa_field_news_auto_section', ['label_for' => 'auto_screen_interval'] );
}
add_action( 'admin_init', 'wpa_field_news_register_settings' );

/**
 * Settings Page Callback.
 */
function wpa_field_news_settings_page() {
    ?>
    <div class="wrap wpa-settings-wrapper wpa-field-news-wrap">
        <h1><?php esc_html_e( 'Field News Generator', 'wp-academic-post-enhanced' ); ?></h1>
        <p><?php esc_html_e( 'Automatically generate news stories from the latest scientific studies using AI.', 'wp-academic-post-enhanced' ); ?></p>
        
        <style>
            .wpa-vertical-layout { display: flex; gap: 20px; margin-top: 20px; align-items: flex-start; }
            .wpa-vertical-nav { width: 220px; flex-shrink: 0; background: #fff; border: 1px solid #c3c4c7; border-radius: 4px; overflow: hidden; position: sticky; top: 50px; }
            .wpa-vertical-nav ul { margin: 0; padding: 0; list-style: none; }
            .wpa-vertical-nav li { margin: 0; border-bottom: 1px solid #f0f0f1; }
            .wpa-vertical-nav li:last-child { border-bottom: none; }
            .wpa-vertical-nav a { display: block; padding: 12px 15px; text-decoration: none; color: #1d2327; font-weight: 500; transition: background 0.2s, color 0.2s; border-left: 3px solid transparent; outline: none; box-shadow: none; }
            .wpa-vertical-nav a:hover { background: #f0f6fc; color: #2271b1; }
            .wpa-vertical-nav a.active { background: #f0f6fc; color: #2271b1; border-left-color: #2271b1; font-weight: 600; }
            
            .wpa-vertical-content { flex-grow: 1; min-width: 0; min-height: 600px; }
            .wpa-group-content { display: none; opacity: 0; }
            .wpa-group-content.active { display: block; animation: wpaFadeIn 0.3s forwards; }
            
            @keyframes wpaFadeIn {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .wpa-group-content .nav-tab-wrapper { margin-bottom: 15px; border-bottom: 1px solid #c3c4c7; }
            .wpa-group-content .tab-content { background: #fff; padding: 20px; border: 1px solid #c3c4c7; border-top: none; margin-top: -1px; }
        </style>

        <form method="post" action="options.php">
            <?php settings_fields( 'wpa_field_news_options' ); ?>
            
            <div class="wpa-vertical-layout">
                <div class="wpa-vertical-nav">
                    <ul>
                        <li><a href="#group-core" class="wpa-vtab active" data-target="group-core"><?php esc_html_e( 'Core Engine', 'wp-academic-post-enhanced' ); ?></a></li>
                        <li><a href="#group-ai" class="wpa-vtab" data-target="group-ai"><?php esc_html_e( 'AI & Automation', 'wp-academic-post-enhanced' ); ?></a></li>
                    </ul>
                </div>

                <div class="wpa-vertical-content">
                    
                    <!-- Group 1: Core Engine -->
                    <div id="group-core" class="wpa-group-content active">
                        <h2 class="nav-tab-wrapper">
                            <a href="#tab-general" class="nav-tab nav-tab-active"><?php esc_html_e( 'General & APIs', 'wp-academic-post-enhanced' ); ?></a>
                            <a href="#tab-source" class="nav-tab"><?php esc_html_e( 'Content Sources', 'wp-academic-post-enhanced' ); ?></a>
                        </h2>
                        
                        <div id="tab-general" class="tab-content active" style="display:block;">
                            <?php wpa_render_specific_section( 'wpa_field_news', 'wpa_field_news_general_section' ); ?>
                        </div>
                        <div id="tab-source" class="tab-content" style="display:none;">
                            <?php wpa_render_specific_section( 'wpa_field_news', 'wpa_field_news_source_section' ); ?>
                        </div>
                    </div>

                    <!-- Group 2: AI & Automation -->
                    <div id="group-ai" class="wpa-group-content">
                        <h2 class="nav-tab-wrapper">
                            <a href="#tab-ai" class="nav-tab nav-tab-active"><?php esc_html_e( 'AI Writer', 'wp-academic-post-enhanced' ); ?></a>
                            <a href="#tab-auto" class="nav-tab"><?php esc_html_e( 'Automation', 'wp-academic-post-enhanced' ); ?></a>
                        </h2>
                        
                        <div id="tab-ai" class="tab-content active" style="display:block;">
                            <?php wpa_render_specific_section( 'wpa_field_news', 'wpa_field_news_ai_section' ); ?>
                        </div>
                        <div id="tab-auto" class="tab-content" style="display:none;">
                            <?php wpa_render_specific_section( 'wpa_field_news', 'wpa_field_news_auto_section' ); ?>
                        </div>
                    </div>

                </div>
            </div>

            <?php submit_button(); ?>
        </form>

        <hr>
        
        <div class="wpa-manual-trigger-card">
            <h2><?php esc_html_e( 'Manual Trigger', 'wp-academic-post-enhanced' ); ?></h2>
            <p><?php esc_html_e( 'Test the generator by fetching the latest study and creating a post now.', 'wp-academic-post-enhanced' ); ?></p>
            <button id="wpa-field-news-run-btn" class="button button-large button-primary">
                <?php esc_html_e( 'Fetch & Generate News Story', 'wp-academic-post-enhanced' ); ?>
            </button>
            <div id="wpa-field-news-results" style="margin-top: 20px; padding: 15px; background: #fff; border: 1px solid #ccd0d4; display: none;"></div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('.wpa-vtab').on('click', function(e) {
                e.preventDefault();
                var target = $(this).data('target');
                var $targetGroup = $('#' + target);
                $('.wpa-vtab').removeClass('active');
                $(this).addClass('active');
                $('.wpa-group-content').removeClass('active');
                setTimeout(function() {
                    $targetGroup.addClass('active');
                    var $firstTab = $targetGroup.find('.nav-tab-wrapper a.nav-tab').first();
                    if ($firstTab.length) { $firstTab.trigger('click'); }
                }, 10);
            });

            $('.nav-tab-wrapper a.nav-tab').on('click', function(e) {
                e.preventDefault();
                var target = $(this).attr('href').replace('#', '');
                var $group = $(this).closest('.wpa-group-content');
                $group.find('.nav-tab').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');
                $group.find('.tab-content').hide();
                $group.find('#' + target).show();
            });
        });
        </script>
    </div>
    <?php
}

// Section Callbacks
function wpa_field_news_general_section_callback() { echo '<p>' . __( 'Configure API keys and global settings.', 'wp-academic-post-enhanced' ) . '</p>'; }
function wpa_field_news_source_section_callback() { echo '<p>' . __( 'Define where content comes from and how studies are selected.', 'wp-academic-post-enhanced' ) . '</p>'; }
function wpa_field_news_ai_section_callback() { echo '<p>' . __( 'Customize the personality, structure, and intelligence of the writer.', 'wp-academic-post-enhanced' ) . '</p>'; }
function wpa_field_news_auto_section_callback() { 
    echo '<p>' . __( 'Configure the schedule for automatic news generation.', 'wp-academic-post-enhanced' ) . '</p>';
    
    // Status Indicator
    $last_run = get_option( 'wpa_field_news_last_post_time' );
    $last_run_text = $last_run ? human_time_diff( $last_run, current_time( 'timestamp' ) ) . ' ago' : 'Never';
    echo '<div class="wpa-cron-status" style="background:#f0f6fc; padding:10px; border-radius:4px; border:1px solid #cce5ff; margin-bottom:15px;">';
    echo '<strong>' . __( 'Last Run:', 'wp-academic-post-enhanced' ) . '</strong> ' . esc_html( $last_run_text ) . '<br>';
    
    if ( wp_next_scheduled( 'wpa_field_news_cron_event' ) ) {
        $next = wp_next_scheduled( 'wpa_field_news_cron_event' );
        echo '<strong>' . __( 'Next Run:', 'wp-academic-post-enhanced' ) . '</strong> ' . human_time_diff( current_time('timestamp'), $next ) . ' from now';
    } else {
        echo '<strong>' . __( 'Next Run:', 'wp-academic-post-enhanced' ) . '</strong> Not Scheduled';
    }
    echo '</div>';

    // Heartbeat & Info
    echo '<p class="description" style="color:#d63638;">' . __( 'Note for Local Environments:', 'wp-academic-post-enhanced' ) . ' ' . 
         __( 'If you close your browser, automation will stop. However, when you return to the admin dashboard, the plugin will automatically "Catch Up" and generate a post if one was missed.', 'wp-academic-post-enhanced' ) . '</p>';
    
    echo '<p class="description"><strong>' . __( 'Browser Heartbeat:', 'wp-academic-post-enhanced' ) . '</strong> ' . 
         __( 'Keep this tab open! This page will automatically ping the server every minute to ensure scheduled tasks run even if there are no visitors.', 'wp-academic-post-enhanced' ) . '</p>';
    
    // System Cron
    echo '<details><summary>' . __( 'Advanced: System Cron Command', 'wp-academic-post-enhanced' ) . '</summary>';
    echo '<p>' . __( 'For production servers, use this crontab command:', 'wp-academic-post-enhanced' ) . '</p>';
    echo '<code style="display:block; padding:10px; background:#f0f0f1;">wget -q -O - ' . esc_url( site_url( 'wp-cron.php?doing_wp_cron' ) ) . ' >/dev/null 2>&1</code>';
    echo '</details>';
}
function wpa_field_news_display_section_callback() { echo '<p>' . __( 'Customize the appearance of the generated news posts.', 'wp-academic-post-enhanced' ) . '</p>'; }
function wpa_field_news_global_style_section_callback() { echo '<p>' . __( 'Extend the clean, academic styling of Field News to the rest of your website.', 'wp-academic-post-enhanced' ) . '</p>'; }

// Field Callbacks
function wpa_field_news_select_strictness_field( $args ) {
    $options = get_option( 'wpa_field_news_settings' );
    $key = $args['label_for'];
    $val = isset( $options[ $key ] ) ? $options[ $key ] : 'moderate';
    
    $levels = [
        'lenient' => 'Lenient (Check for major errors only)',
        'moderate' => 'Moderate (Standard Check)',
        'strict' => 'Strict (High Standards)'
    ];

    echo '<select name="wpa_field_news_settings[' . esc_attr( $key ) . ']">';
    foreach ( $levels as $k => $v ) {
        echo '<option value="' . esc_attr( $k ) . '" ' . selected( $val, $k, false ) . '>' . esc_html( $v ) . '</option>';
    }
    echo '</select>';
}

// Field Callbacks
function wpa_field_news_color_field( $args ) {
    $options = get_option( 'wpa_field_news_settings' );
    $key = $args['label_for'];
    
    // Determine default based on key name
    $default = '#ffffff';
    if ( strpos( $key, 'text' ) !== false ) {
        $default = '#333333';
    } elseif ( strpos( $key, 'heading' ) !== false ) {
        $default = '#111827';
    }
    
    $val = isset( $options[ $key ] ) ? $options[ $key ] : $default;
    echo '<input type="text" name="wpa_field_news_settings[' . esc_attr( $key ) . ']" value="' . esc_attr( $val ) . '" class="wpa-color-picker" data-default-color="' . esc_attr( $default ) . '">';
}

function wpa_field_news_select_img_style_field( $args ) {
    $options = get_option( 'wpa_field_news_settings' );
    $key = $args['label_for'];
    $val = isset( $options[ $key ] ) ? $options[ $key ] : 'standard';
    
    $styles = [
        'standard' => 'Standard (Contained)',
        'wide'     => 'Wide (Full Width)',
        'hidden'   => 'Hidden'
    ];

    echo '<select name="wpa_field_news_settings[' . esc_attr( $key ) . ']">';
    foreach ( $styles as $k => $v ) {
        echo '<option value="' . esc_attr( $k ) . '" ' . selected( $val, $k, false ) . '>' . esc_html( $v ) . '</option>';
    }
    echo '</select>';
}

function wpa_field_news_select_font_field( $args ) {
    $options = get_option( 'wpa_field_news_settings' );
    $key = $args['label_for'];
    $val = isset( $options[ $key ] ) ? $options[ $key ] : 'sans';
    
    $fonts = [
        'sans' => 'System Sans-Serif (Clean)',
        'serif' => 'System Serif (Academic)',
        'mono' => 'Monospace',
        'inter' => 'Inter (Modern)',
        'merriweather' => 'Merriweather (Classic)'
    ];

    echo '<select name="wpa_field_news_settings[' . esc_attr( $key ) . ']">';
    foreach ( $fonts as $k => $v ) {
        echo '<option value="' . esc_attr( $k ) . '" ' . selected( $val, $k, false ) . '>' . esc_html( $v ) . '</option>';
    }
    echo '</select>';
}

function wpa_field_news_text_field( $args ) {
    $options = get_option( 'wpa_field_news_settings' );
    $key = $args['label_for'];
    $val = isset( $options[ $key ] ) ? $options[ $key ] : '';
    $desc = isset( $args['desc'] ) ? '<p class="description">' . esc_html( $args['desc'] ) . '</p>' : '';
    
    // Security: Use password field for sensitive keys
    $type = 'text';
    if ( strpos( $key, 'api_key' ) !== false || strpos( $key, 'secret' ) !== false || strpos( $key, 'token' ) !== false ) {
        $type = 'password';
    }
    
    echo '<input type="' . esc_attr( $type ) . '" id="' . esc_attr( $key ) . '" name="wpa_field_news_settings[' . esc_attr( $key ) . ']" value="' . esc_attr( $val ) . '" class="regular-text">';
    
    // Add Test Button for API keys
    if ( strpos( $key, 'api_key' ) !== false ) {
        $api_type = str_replace( '_api_key', '', $key );
        echo ' <button type="button" class="button wpa-test-api-btn" data-api="' . esc_attr( $api_type ) . '">' . __( 'Test', 'wp-academic-post-enhanced' ) . '</button>';
        echo '<span class="wpa-api-test-result" id="result-' . esc_attr( $api_type ) . '" style="margin-left: 10px; font-weight: bold;"></span>';
    }
    
    echo $desc;
}

function wpa_field_news_select_position_field( $args ) {
    $options = get_option( 'wpa_field_news_settings' );
    $key = $args['label_for'];
    $val = isset( $options[ $key ] ) ? $options[ $key ] : 'after';
    
    $positions = [
        'before' => 'Before Content',
        'after'  => 'After Content',
        'bottom' => 'Bottom of Post'
    ];

    echo '<select name="wpa_field_news_settings[' . esc_attr( $key ) . ']">';
    foreach ( $positions as $k => $v ) {
        echo '<option value="' . esc_attr( $k ) . '" ' . selected( $val, $k, false ) . '>' . esc_html( $v ) . '</option>';
    }
    echo '</select>';
}

function wpa_field_news_checkbox_field( $args ) {
    $options = get_option( 'wpa_field_news_settings' );
    $key = $args['label_for'];
    $val = isset( $options[ $key ] ) ? $options[ $key ] : 0;
    $desc = isset( $args['desc'] ) ? '<p class="description">' . esc_html( $args['desc'] ) . '</p>' : '';
    
    // Hidden input ensures '0' is sent if unchecked
    echo '<input type="hidden" name="wpa_field_news_settings[' . esc_attr( $key ) . ']" value="0">';
    echo '<label><input type="checkbox" name="wpa_field_news_settings[' . esc_attr( $key ) . ']" value="1" ' . checked( 1, $val, false ) . '> ' . $desc . '</label>';
}

function wpa_field_news_number_field( $args ) {
    $options = get_option( 'wpa_field_news_settings' );
    $key = $args['label_for'];
    $val = isset( $options[ $key ] ) ? $options[ $key ] : '';
    $desc = isset( $args['desc'] ) ? '<p class="description">' . esc_html( $args['desc'] ) . '</p>' : '';
    
    echo '<input type="number" name="wpa_field_news_settings[' . esc_attr( $key ) . ']" value="' . esc_attr( $val ) . '" class="small-text" min="0"> ' . $desc;
}

function wpa_field_news_select_interval_field( $args ) {
    $options = get_option( 'wpa_field_news_settings' );
    $key = $args['label_for'];
    $val = isset( $options[ $key ] ) ? $options[ $key ] : 'daily';
    
    $intervals = [
        '1min' => 'Every Minute (Testing)',
        '5min' => 'Every 5 Minutes',
        '15min' => 'Every 15 Minutes',
        '30min' => 'Every 30 Minutes',
        'hourly' => 'Hourly',
        'twicedaily' => 'Twice Daily',
        'daily' => 'Daily',
        'weekly' => 'Weekly'
    ];

    echo '<select name="wpa_field_news_settings[' . esc_attr( $key ) . ']">';
    foreach ( $intervals as $k => $v ) {
        echo '<option value="' . esc_attr( $k ) . '" ' . selected( $val, $k, false ) . '>' . esc_html( $v ) . '</option>';
    }
    echo '</select>';
}

function wpa_field_news_select_language_field( $args ) {
    $options = get_option( 'wpa_field_news_settings' );
    $key = $args['label_for'];
    $val = isset( $options[ $key ] ) ? $options[ $key ] : 'en';
    
    $langs = [
        'en' => 'English (Default)',
        'ar' => 'Arabic',
        'es' => 'Spanish',
        'fr' => 'French',
        'de' => 'German',
        'zh' => 'Chinese (Simplified)',
        'ja' => 'Japanese',
        'ru' => 'Russian',
        'pt' => 'Portuguese',
        'it' => 'Portuguese'
    ];

    echo '<select name="wpa_field_news_settings[' . esc_attr( $key ) . ']">';
    foreach ( $langs as $k => $v ) {
        echo '<option value="' . esc_attr( $k ) . '" ' . selected( $val, $k, false ) . '>' . esc_html( $v ) . '</option>';
    }
    echo '</select>';
    echo '<p class="description">' . __( 'The AI will generate content directly in this language.', 'wp-academic-post-enhanced' ) . '</p>';
}

function wpa_field_news_select_date_field( $args ) {
    $options = get_option( 'wpa_field_news_settings' );
    $key = $args['label_for'];
    $val = isset( $options[ $key ] ) ? $options[ $key ] : 'all';
    
    $dates = [
        'all' => 'Any Time',
        'last_7_days' => 'Last 7 Days',
        'last_30_days' => 'Last 30 Days',
        'last_90_days' => 'Last 3 Months',
        'last_180_days' => 'Last 6 Months',
        'last_365_days' => 'Last Year',
        '2026' => '2026',
        '2025' => '2025',
        '2024' => '2024'
    ];

    echo '<select name="wpa_field_news_settings[' . esc_attr( $key ) . ']">';
    foreach ( $dates as $k => $v ) {
        echo '<option value="' . esc_attr( $k ) . '" ' . selected( $val, $k, false ) . '>' . esc_html( $v ) . '</option>';
    }
    echo '</select>';
}

function wpa_field_news_select_doctype_field( $args ) {
    $options = get_option( 'wpa_field_news_settings' );
    $key = $args['label_for'];
    $val = isset( $options[ $key ] ) ? $options[ $key ] : 'ar';
    
    $types = [
        'all' => 'All Types',
        'ar' => 'Article',
        're' => 'Review',
        'cp' => 'Conference Paper',
        'bk' => 'Book'
    ];

    echo '<select name="wpa_field_news_settings[' . esc_attr( $key ) . ']">';
    foreach ( $types as $k => $v ) {
        echo '<option value="' . esc_attr( $k ) . '" ' . selected( $val, $k, false ) . '>' . esc_html( $v ) . '</option>';
    }
    echo '</select>';
}

function wpa_field_news_topic_repeater_field( $args ) {
    $options = get_option( 'wpa_field_news_settings' );
    $groups = isset( $options['topic_groups'] ) ? $options['topic_groups'] : [];
    
    // Fallback migration: If old single fields exist, create first group
    if ( empty( $groups ) && ! empty( $options['search_query'] ) ) {
        $groups[] = [
            'query' => $options['search_query'],
            'cat'   => isset( $options['post_category'] ) ? $options['post_category'] : ''
        ];
    }
    
    // Ensure at least one empty row if absolutely nothing exists
    if ( empty( $groups ) ) {
        $groups[] = [ 'query' => '', 'cat' => '' ];
    }

    $categories = get_categories( [ 'hide_empty' => false ] );
    ?>
    <div id="wpa-topic-repeater">
        <table class="widefat striped" style="border: 1px solid #c3c4c7; box-shadow: none;">
            <thead>
                <tr>
                    <th style="width: 60%;"><?php esc_html_e( 'Search Query (Scopus)', 'wp-academic-post-enhanced' ); ?></th>
                    <th style="width: 30%;"><?php esc_html_e( 'Post Category', 'wp-academic-post-enhanced' ); ?></th>
                    <th style="width: 10%;"></th>
                </tr>
            </thead>
            <tbody id="wpa-topic-list">
                <?php foreach ( $groups as $idx => $group ) : 
                    $q = isset( $group['query'] ) ? $group['query'] : '';
                    $c = isset( $group['cat'] ) ? $group['cat'] : '';
                ?>
                <tr class="wpa-topic-row">
                    <td>
                        <input type="text" name="wpa_field_news_settings[topic_groups][<?php echo $idx; ?>][query]" value="<?php echo esc_attr( $q ); ?>" style="width: 100%;" placeholder="e.g. Cognitive Therapy">
                    </td>
                    <td>
                        <select name="wpa_field_news_settings[topic_groups][<?php echo $idx; ?>][cat]" style="width: 100%;">
                            <option value=""><?php esc_html_e( '-- Select Category --', 'wp-academic-post-enhanced' ); ?></option>
                            <?php foreach ( $categories as $cat ) : ?>
                                <option value="<?php echo esc_attr( $cat->term_id ); ?>" <?php selected( $c, $cat->term_id ); ?>><?php echo esc_html( $cat->name ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <button type="button" class="button wpa-remove-row" style="color: #b32d2e; border-color: #b32d2e;">&times;</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p>
            <button type="button" class="button button-secondary" id="wpa-add-topic-row"><?php esc_html_e( 'Add Another Topic', 'wp-academic-post-enhanced' ); ?></button>
        </p>
        <p class="description">
            <?php esc_html_e( 'The automation will cycle through these groups one by one for each post.', 'wp-academic-post-enhanced' ); ?>
        </p>
    </div>
    
    <!-- Template for JS -->
    <script type="text/template" id="wpa-topic-row-tmpl">
        <tr class="wpa-topic-row">
            <td>
                <input type="text" name="wpa_field_news_settings[topic_groups][INDEX][query]" value="" style="width: 100%;" placeholder="e.g. Neuroscience">
            </td>
            <td>
                <select name="wpa_field_news_settings[topic_groups][INDEX][cat]" style="width: 100%;">
                    <option value=""><?php esc_html_e( '-- Select Category --', 'wp-academic-post-enhanced' ); ?></option>
                    <?php foreach ( $categories as $cat ) : ?>
                        <option value="<?php echo esc_attr( $cat->term_id ); ?>"><?php echo esc_html( $cat->name ); ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td>
                <button type="button" class="button wpa-remove-row" style="color: #b32d2e; border-color: #b32d2e;">&times;</button>
            </td>
        </tr>
    </script>
    <?php
}

function wpa_field_news_select_status_field( $args ) {
    $options = get_option( 'wpa_field_news_settings' );
    $key = $args['label_for'];
    $val = isset( $options[ $key ] ) ? $options[ $key ] : 'draft';
    echo '<select name="wpa_field_news_settings[' . esc_attr( $key ) . ']">';
    echo '<option value="draft" ' . selected( $val, 'draft', false ) . '>Draft</option>';
    echo '<option value="publish" ' . selected( $val, 'publish', false ) . '>Publish</option>';
    echo '<option value="private" ' . selected( $val, 'private', false ) . '>Private</option>';
    echo '</select>';
}

function wpa_field_news_select_author_field( $args ) {
    $options = get_option( 'wpa_field_news_settings' );
    $key = $args['label_for'];
    $val = isset( $options[ $key ] ) ? $options[ $key ] : get_current_user_id();
    
    $user_args = [
        'name'     => 'wpa_field_news_settings[' . esc_attr( $key ) . ']',
        'selected' => $val,
        'echo'     => 1,
    ];
    wp_dropdown_users( $user_args );
    echo '<p class="description">' . __( 'Select the user that will be assigned as the author of generated posts.', 'wp-academic-post-enhanced' ) . '</p>';
}

function wpa_field_news_select_model_field( $args ) {
    $options = get_option( 'wpa_field_news_settings' );
    $key = $args['label_for'];
    $val = isset( $options[ $key ] ) ? $options[ $key ] : 'gemini-2.0-flash';
    
    $models = [
        'gemini-3-pro-preview'        => 'Gemini 3 Pro Preview',
        'gemini-3-flash-preview'      => 'Gemini 3 Flash Preview',
        'gemini-2.5-pro'              => 'Gemini 2.5 Pro (Stable)',
        'gemini-2.5-flash'            => 'Gemini 2.5 Flash (Stable)',
        'gemini-2.5-flash-lite'       => 'Gemini 2.5 Flash-Lite',
        'gemini-2.0-flash'            => 'Gemini 2.0 Flash (Stable)',
        'gemini-2.0-flash-lite'       => 'Gemini 2.0 Flash-Lite',
        'gemini-pro-latest'           => 'Gemini Pro (Latest)',
        'gemini-flash-latest'         => 'Gemini Flash (Latest)',
        'deep-research-pro-preview-12-2025' => 'Deep Research Pro Preview',
        'gemma-3-27b-it'              => 'Gemma 3 27B (Lightweight)',
        'gemma-3-12b-it'              => 'Gemma 3 12B',
        'gemma-3-4b-it'               => 'Gemma 3 4B',
        'gemma-3-1b-it'               => 'Gemma 3 1B',
        'gemini-1.5-pro'              => 'Gemini 1.5 Pro (Legacy)',
        'gemini-1.5-flash'            => 'Gemini 1.5 Flash (Legacy)',
    ];

    echo '<select name="wpa_field_news_settings[' . esc_attr( $key ) . ']">';
    foreach ( $models as $id => $label ) {
        echo '<option value="' . esc_attr( $id ) . '" ' . selected( $val, $id, false ) . '>' . esc_html( $label ) . '</option>';
    }
    echo '</select>';
    
    $desc = isset( $args['desc'] ) ? $args['desc'] : __( 'Select the Google AI model to use for this task.', 'wp-academic-post-enhanced' );
    echo '<p class="description">' . esc_html( $desc ) . '</p>';
}

function wpa_field_news_select_strategy_field( $args ) {
    $options = get_option( 'wpa_field_news_settings' );
    $key = $args['label_for'];
    $val = isset( $options[ $key ] ) ? $options[ $key ] : 'impact';
    
    $strategies = [
        'impact'      => 'Most Impactful / Newsworthy',
        'practical'   => 'Most Practical / Applicable',
        'controversial' => 'Most Controversial / Surprising',
        'recent'      => 'Most Recent (Date)',
        'random'      => 'Random Selection'
    ];

    echo '<select name="wpa_field_news_settings[' . esc_attr( $key ) . ']">';
    foreach ( $strategies as $k => $v ) {
        echo '<option value="' . esc_attr( $k ) . '" ' . selected( $val, $k, false ) . '>' . esc_html( $v ) . '</option>';
    }
    echo '</select>';
}

function wpa_field_news_select_tone_field( $args ) {
    $options = get_option( 'wpa_field_news_settings' );
    $key = $args['label_for'];
    $val = isset( $options[ $key ] ) ? $options[ $key ] : 'professional';
    
    $tones = [
        'professional' => 'Professional (Default)',
        'academic'     => 'Academic / Formal',
        'casual'       => 'Casual / Blog',
        'enthusiastic' => 'Enthusiastic',
        'skeptical'    => 'Critical / Skeptical',
        'storytelling' => 'Narrative / Storytelling'
    ];

    echo '<select name="wpa_field_news_settings[' . esc_attr( $key ) . ']">';
    foreach ( $tones as $k => $v ) {
        echo '<option value="' . esc_attr( $k ) . '" ' . selected( $val, $k, false ) . '>' . esc_html( $v ) . '</option>';
    }
    echo '</select>';
}

function wpa_field_news_select_length_field( $args ) {
    $options = get_option( 'wpa_field_news_settings' );
    $key = $args['label_for'];
    $val = isset( $options[ $key ] ) ? $options[ $key ] : 'medium';
    
    $lengths = [
        'short'  => 'Short (< 400 words)',
        'medium' => 'Medium (~800 words)',
        'long'   => 'Long / Deep Dive (> 1200 words)'
    ];

    echo '<select name="wpa_field_news_settings[' . esc_attr( $key ) . ']">';
    foreach ( $lengths as $k => $v ) {
        echo '<option value="' . esc_attr( $k ) . '" ' . selected( $val, $k, false ) . '>' . esc_html( $v ) . '</option>';
    }
    echo '</select>';
}

function wpa_field_news_select_audience_field( $args ) {
    $options = get_option( 'wpa_field_news_settings' );
    $key = $args['label_for'];
    $val = isset( $options[ $key ] ) ? $options[ $key ] : 'general';
    
    $audiences = [
        'general' => 'General Public (Simple)',
        'student' => 'University Students',
        'expert'  => 'Subject Matter Experts',
        'kids'    => 'Explained for Kids (EL15)'
    ];

    echo '<select name="wpa_field_news_settings[' . esc_attr( $key ) . ']">';
    foreach ( $audiences as $k => $v ) {
        echo '<option value="' . esc_attr( $k ) . '" ' . selected( $val, $k, false ) . '>' . esc_html( $v ) . '</option>';
    }
    echo '</select>';
}

function wpa_field_news_select_structure_field( $args ) {
    $options = get_option( 'wpa_field_news_settings' );
    $key = $args['label_for'];
    $val = isset( $options[ $key ] ) ? $options[ $key ] : 'news';
    
    $structures = [
        'news'      => 'Standard News Article',
        'listicle'  => 'Listicle (Top 5 Findings...)' ,
        'qa'        => 'Q&A Format',
        'essay'     => 'Opinion / Essay',
        'eli5'      => 'ELI5 (Explain Like I\'m 5)',
        'debate'    => 'Critical Review / Debate',
        'case'      => 'Case Study / Scenario',
        'bullets'   => 'Executive Summary (Bullets)',
        'interview' => 'Simulated Interview'
    ];

    echo '<select name="wpa_field_news_settings[' . esc_attr( $key ) . ']">';
    foreach ( $structures as $k => $v ) {
        echo '<option value="' . esc_attr( $k ) . '" ' . selected( $val, $k, false ) . '>' . esc_html( $v ) . '</option>';
    }
    echo '</select>';
}

function wpa_field_news_textarea_field( $args ) {
    $options = get_option( 'wpa_field_news_settings' );
    $key = $args['label_for'];
    $val = isset( $options[ $key ] ) ? $options[ $key ] : '';
    $desc = isset( $args['desc'] ) ? '<p class="description">' . esc_html( $args['desc'] ) . '</p>' : '';
    
    echo '<textarea name="wpa_field_news_settings[' . esc_attr( $key ) . ']" rows="4" cols="50" class="large-text">' . esc_textarea( $val ) . '</textarea>' . $desc;
}

function wpa_field_news_select_category_field( $args ) {
    $options = get_option( 'wpa_field_news_settings' );
    $key = $args['label_for'];
    $val = isset( $options[ $key ] ) ? $options[ $key ] : '';
    
    $args = [ 'name' => 'wpa_field_news_settings[' . esc_attr( $key ) . ']', 'selected' => $val, 'show_option_none' => __( 'Select Category', 'wp-academic-post-enhanced' ), 'class' => 'regular-text' ];
    wp_dropdown_categories( $args );
}

/**
 * Enqueue Assets for Admin
 */
function wpa_field_news_admin_assets( $hook ) {
    if ( strpos( $hook, 'field-news' ) === false ) return;

    wp_enqueue_style( 'wpa-field-news-css', plugin_dir_url( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'assets/css/field-news.css', [], '1.0' );
        wp_enqueue_script( 'wpa-field-news-js', plugin_dir_url( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'assets/js/field-news.js', ['jquery'], '1.0', true );
        
        wp_localize_script( 'wpa-field-news-js', 'wpaFieldNews', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'wpa_field_news_nonce' ),
            'heartbeat_enabled' => true
        ]);
    }
    add_action( 'admin_enqueue_scripts', 'wpa_field_news_admin_assets' );
    