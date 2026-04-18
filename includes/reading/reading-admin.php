<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Add the reading experience submenu page.
 */
function wpa_add_reading_admin_menu() {
    // Only show if the main feature is enabled
    $enabled = get_option( 'wpa_reading_enabled', false );
    if ( $enabled ) {
        add_submenu_page(
            'wp-academic-post-enhanced',
            __( 'Reading Experience', 'wp-academic-post-enhanced' ),
            __( 'Reading Experience', 'wp-academic-post-enhanced' ),
            'manage_options',
            'wp-academic-post-enhanced-reading',
            'wpa_reading_page'
        );
    }
}
add_action( 'admin_menu', 'wpa_add_reading_admin_menu' );

/**
 * Display the reading settings page.
 */
function wpa_reading_page() {
    ?>
    <div class="wrap wpa-settings-wrapper">
        <div class="wpa-header">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        </div>
        
        <div class="wpa-vertical-layout">
            <div class="wpa-vertical-nav">
                <ul>
                    <li><a href="#group-reading" class="wpa-vtab active" data-target="group-reading"><?php esc_html_e( 'Reading Tools', 'wp-academic-post-enhanced' ); ?></a></li>
                </ul>
            </div>

            <div class="wpa-vertical-content">
                <div id="group-reading" class="wpa-group-content active">
                    <h2 class="nav-tab-wrapper">
                        <a href="#time" class="nav-tab nav-tab-active"><?php esc_html_e( 'Reading Time', 'wp-academic-post-enhanced' ); ?></a>
                        <a href="#resizer" class="nav-tab"><?php esc_html_e( 'Text Resizer', 'wp-academic-post-enhanced' ); ?></a>
                        <a href="#progress" class="nav-tab"><?php esc_html_e( 'Progress Bar', 'wp-academic-post-enhanced' ); ?></a>
                    </h2>

                    <div class="tab-content active" id="time">
                        <form action="options.php" method="post">
                            <?php settings_fields( 'wpa_reading_options' ); ?>
                            <?php wpa_render_specific_section( 'wpa_reading', 'wpa_reading_time_section' ); ?>
                            <?php submit_button(); ?>
                        </form>
                    </div>

                    <div class="tab-content" id="resizer" style="display:none;">
                        <form action="options.php" method="post">
                            <?php settings_fields( 'wpa_reading_options' ); ?>
                            <?php wpa_render_specific_section( 'wpa_reading', 'wpa_reading_resizer_section' ); ?>
                            <?php submit_button(); ?>
                        </form>
                    </div>

                    <div class="tab-content" id="progress" style="display:none;">
                        <form action="options.php" method="post">
                            <?php settings_fields( 'wpa_reading_options' ); ?>
                            <?php wpa_render_specific_section( 'wpa_reading', 'wpa_reading_progress_section' ); ?>
                            <?php submit_button(); ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Register reading settings.
 */
function wpa_register_reading_settings() {
    register_setting(
        'wpa_reading_options',
        'wpa_reading_settings',
        [
            'type' => 'array',
            'sanitize_callback' => 'wpa_sanitize_reading_settings',
            'default' => [
                'time_enabled' => true,
                'time_label' => 'min read',
                'time_position' => 'before_content',
                'progress_enabled' => true,
                'progress_color' => '#2563eb',
                'progress_height' => '4',
                'progress_position' => 'top',
            ],
        ]
    );

    // Section: Reading Time
    add_settings_section(
        'wpa_reading_time_section',
        __( 'Estimated Reading Time', 'wp-academic-post-enhanced' ),
        'wpa_reading_time_section_callback',
        'wpa_reading'
    );

    add_settings_field(
        'time_enabled',
        __( 'Enable Reading Time', 'wp-academic-post-enhanced' ),
        'wpa_reading_field_checkbox_callback',
        'wpa_reading',
        'wpa_reading_time_section',
        ['key' => 'time_enabled']
    );

    add_settings_field(
        'time_label',
        __( 'Label', 'wp-academic-post-enhanced' ),
        'wpa_reading_field_text_callback',
        'wpa_reading',
        'wpa_reading_time_section',
        ['key' => 'time_label']
    );

    add_settings_field(
        'time_position',
        __( 'Position', 'wp-academic-post-enhanced' ),
        'wpa_reading_time_position_callback',
        'wpa_reading',
        'wpa_reading_time_section'
    );

    // Section: Text Resizer
    add_settings_section(
        'wpa_reading_resizer_section',
        __( 'Text Resizer', 'wp-academic-post-enhanced' ),
        'wpa_reading_resizer_section_callback',
        'wpa_reading'
    );

    add_settings_field(
        'resizer_enabled',
        __( 'Enable Text Resizer', 'wp-academic-post-enhanced' ),
        'wpa_reading_field_checkbox_callback',
        'wpa_reading',
        'wpa_reading_resizer_section',
        ['key' => 'resizer_enabled']
    );

    add_settings_field(
        'resizer_position',
        __( 'Position', 'wp-academic-post-enhanced' ),
        'wpa_reading_resizer_position_callback',
        'wpa_reading',
        'wpa_reading_resizer_section'
    );

    add_settings_field(
        'resizer_content_selector',
        __( 'Content Selector', 'wp-academic-post-enhanced' ),
        'wpa_reading_field_text_callback',
        'wpa_reading',
        'wpa_reading_resizer_section',
        ['key' => 'resizer_content_selector', 'description' => __( 'CSS selector for the main content area (e.g., .entry-content). Leave empty for auto-detection.', 'wp-academic-post-enhanced' )]
    );

    add_settings_field(
        'resizer_btn_color',
        __( 'Button Text Color', 'wp-academic-post-enhanced' ),
        'wpa_reading_resizer_color_callback',
        'wpa_reading',
        'wpa_reading_resizer_section',
        ['key' => 'resizer_btn_color']
    );

    add_settings_field(
        'resizer_btn_bg_color',
        __( 'Button Background Color', 'wp-academic-post-enhanced' ),
        'wpa_reading_resizer_color_callback',
        'wpa_reading',
        'wpa_reading_resizer_section',
        ['key' => 'resizer_btn_bg_color']
    );

    // Section: Reading Progress
    add_settings_section(
        'wpa_reading_progress_section',
        __( 'Reading Progress Bar', 'wp-academic-post-enhanced' ),
        'wpa_reading_progress_section_callback',
        'wpa_reading'
    );

    add_settings_field(
        'progress_enabled',
        __( 'Enable Progress Bar', 'wp-academic-post-enhanced' ),
        'wpa_reading_field_checkbox_callback',
        'wpa_reading',
        'wpa_reading_progress_section',
        ['key' => 'progress_enabled']
    );

    add_settings_field(
        'progress_color',
        __( 'Color', 'wp-academic-post-enhanced' ),
        'wpa_reading_progress_color_callback',
        'wpa_reading',
        'wpa_reading_progress_section'
    );

    add_settings_field(
        'progress_height',
        __( 'Height (px)', 'wp-academic-post-enhanced' ),
        'wpa_reading_progress_height_callback',
        'wpa_reading',
        'wpa_reading_progress_section'
    );

    add_settings_field(
        'progress_position',
        __( 'Position', 'wp-academic-post-enhanced' ),
        'wpa_reading_progress_position_callback',
        'wpa_reading',
        'wpa_reading_progress_section'
    );
}
add_action( 'admin_init', 'wpa_register_reading_settings' );

function wpa_sanitize_reading_settings( $input ) {
    $sanitized = [];
    $sanitized['time_enabled'] = isset( $input['time_enabled'] );
    $sanitized['time_label'] = sanitize_text_field( $input['time_label'] );
    $sanitized['time_position'] = sanitize_key( $input['time_position'] );
    
    $sanitized['resizer_enabled'] = isset( $input['resizer_enabled'] );
    $sanitized['resizer_position'] = sanitize_key( $input['resizer_position'] );
    $sanitized['resizer_content_selector'] = sanitize_text_field( $input['resizer_content_selector'] );
    $sanitized['resizer_btn_color'] = sanitize_hex_color( $input['resizer_btn_color'] );
    $sanitized['resizer_btn_bg_color'] = sanitize_hex_color( $input['resizer_btn_bg_color'] );

    $sanitized['progress_enabled'] = isset( $input['progress_enabled'] );
    $sanitized['progress_color'] = sanitize_hex_color( $input['progress_color'] );
    $sanitized['progress_height'] = absint( $input['progress_height'] );
    $sanitized['progress_position'] = sanitize_key( $input['progress_position'] );
    
    return $sanitized;
}

function wpa_reading_time_section_callback() {
    echo '<p>' . esc_html__( 'Configure the estimated reading time display.', 'wp-academic-post-enhanced' ) . '</p>';
}

function wpa_reading_resizer_section_callback() {
    echo '<p>' . esc_html__( 'Configure the text resizer tool.', 'wp-academic-post-enhanced' ) . '</p>';
}

function wpa_reading_progress_section_callback() {
    echo '<p>' . esc_html__( 'Configure the reading progress bar display.', 'wp-academic-post-enhanced' ) . '</p>';
}

// Generic Callbacks
function wpa_reading_field_checkbox_callback( $args ) {
    $options = get_option( 'wpa_reading_settings' );
    $key = $args['key'];
    $checked = isset( $options[ $key ] ) && $options[ $key ];
    echo '<input type="checkbox" name="wpa_reading_settings[' . esc_attr( $key ) . ']" value="1" ' . checked( $checked, true, false ) . ' />';
}

function wpa_reading_field_text_callback( $args ) {
    $options = get_option( 'wpa_reading_settings' );
    $key = $args['key'];
    $value = isset( $options[ $key ] ) ? $options[ $key ] : '';
    echo '<input type="text" name="wpa_reading_settings[' . esc_attr( $key ) . ']" value="' . esc_attr( $value ) . '" class="regular-text" />';
    if ( ! empty( $args['description'] ) ) {
        echo '<p class="description">' . esc_html( $args['description'] ) . '</p>';
    }
}

// Specific Callbacks
function wpa_reading_time_position_callback() {
    $options = get_option( 'wpa_reading_settings' );
    $position = isset( $options['time_position'] ) ? $options['time_position'] : 'before_content';
    ?>
    <select name="wpa_reading_settings[time_position]">
        <option value="before_content" <?php selected( $position, 'before_content' ); ?>><?php esc_html_e( 'Before Content', 'wp-academic-post-enhanced' ); ?></option>
        <option value="manual" <?php selected( $position, 'manual' ); ?>><?php esc_html_e( 'Manual (Shortcode)', 'wp-academic-post-enhanced' ); ?></option>
    </select>
    <p class="description"><?php esc_html_e( 'Use [wpa_reading_time] for manual placement.', 'wp-academic-post-enhanced' ); ?></p>
    <?php
}

function wpa_reading_resizer_position_callback() {
    $options = get_option( 'wpa_reading_settings' );
    $position = isset( $options['resizer_position'] ) ? $options['resizer_position'] : 'before_content';
    ?>
    <select name="wpa_reading_settings[resizer_position]">
        <option value="before_content" <?php selected( $position, 'before_content' ); ?>><?php esc_html_e( 'Before Content', 'wp-academic-post-enhanced' ); ?></option>
        <option value="manual" <?php selected( $position, 'manual' ); ?>><?php esc_html_e( 'Manual (Shortcode)', 'wp-academic-post-enhanced' ); ?></option>
    </select>
    <p class="description"><?php esc_html_e( 'Use [wpa_text_resizer] for manual placement.', 'wp-academic-post-enhanced' ); ?></p>
    <?php
}

function wpa_reading_resizer_color_callback( $args ) {
    $options = get_option( 'wpa_reading_settings' );
    $key = $args['key'];
    $default = ($key === 'resizer_btn_color') ? '#1d2327' : '#f0f0f1';
    $color = isset( $options[ $key ] ) ? $options[ $key ] : $default;
    echo '<input type="text" name="wpa_reading_settings[' . esc_attr( $key ) . ']" value="' . esc_attr( $color ) . '" class="wpa-color-picker" />';
}

function wpa_reading_progress_color_callback() {
    $options = get_option( 'wpa_reading_settings' );
    $color = isset( $options['progress_color'] ) ? $options['progress_color'] : '#2563eb';
    echo '<input type="text" name="wpa_reading_settings[progress_color]" value="' . esc_attr( $color ) . '" class="wpa-color-picker" />';
}

function wpa_reading_progress_height_callback() {
    $options = get_option( 'wpa_reading_settings' );
    $height = isset( $options['progress_height'] ) ? $options['progress_height'] : '4';
    echo '<input type="number" name="wpa_reading_settings[progress_height]" value="' . esc_attr( $height ) . '" min="1" max="20" class="small-text" /> px';
}

function wpa_reading_progress_position_callback() {
    $options = get_option( 'wpa_reading_settings' );
    $position = isset( $options['progress_position'] ) ? $options['progress_position'] : 'top';
    ?>
    <select name="wpa_reading_settings[progress_position]">
        <option value="top" <?php selected( $position, 'top' ); ?>><?php esc_html_e( 'Top', 'wp-academic-post-enhanced' ); ?></option>
        <option value="bottom" <?php selected( $position, 'bottom' ); ?>><?php esc_html_e( 'Bottom', 'wp-academic-post-enhanced' ); ?></option>
    </select>
    <?php
}
