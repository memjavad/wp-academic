<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Add the TOC submenu page.
 */
function wpa_add_toc_admin_menu() {
    add_submenu_page(
        'wp-academic-post-enhanced',
        __( 'TOC Settings', 'wp-academic-post-enhanced' ),
        __( 'TOC', 'wp-academic-post-enhanced' ),
        'manage_options',
        'wp-academic-post-enhanced-toc',
        'wpa_toc_page'
    );
}
add_action( 'admin_menu', 'wpa_add_toc_admin_menu' );

/**
 * Display the TOC settings page.
 */
function wpa_toc_page() {
    ?>
    <div class="wrap wpa-settings-wrapper">
        <div class="wpa-header">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        </div>
        
        <div class="wpa-vertical-layout">
            <div class="wpa-vertical-nav">
                <ul>
                    <li><a href="#group-toc" class="wpa-vtab active" data-target="group-toc"><?php esc_html_e( 'TOC Settings', 'wp-academic-post-enhanced' ); ?></a></li>
                </ul>
            </div>

            <div class="wpa-vertical-content">
                <form action="options.php" method="post">
                    <?php settings_fields( 'wpa_toc_options' ); ?>
                    
                    <div id="group-toc" class="wpa-group-content active">
                        <h2 class="nav-tab-wrapper">
                            <a href="#general" class="nav-tab nav-tab-active"><?php esc_html_e( 'General', 'wp-academic-post-enhanced' ); ?></a>
                            <a href="#appearance" class="nav-tab"><?php esc_html_e( 'Appearance', 'wp-academic-post-enhanced' ); ?></a>
                            <a href="#advanced" class="nav-tab"><?php esc_html_e( 'Advanced', 'wp-academic-post-enhanced' ); ?></a>
                        </h2>

                        <div class="tab-content active" id="general">
                            <?php wpa_render_specific_section( 'wpa_toc', 'wpa_toc_section_general' ); ?>
                        </div>

                        <div class="tab-content" id="appearance" style="display:none;">
                            <?php wpa_render_specific_section( 'wpa_toc', 'wpa_toc_section_appearance' ); ?>
                        </div>

                        <div class="tab-content" id="advanced" style="display:none;">
                            <?php wpa_render_specific_section( 'wpa_toc', 'wpa_toc_section_advanced' ); ?>
                        </div>
                    </div>

                    <div style="padding: 20px; border-top: 1px solid #f1f1f1;">
                        <?php submit_button(); ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Register TOC settings.
 */
function wpa_register_toc_settings() {
    register_setting(
        'wpa_toc_options',
        'wpa_toc_settings',
        [
            'type' => 'array',
            'sanitize_callback' => 'wpa_sanitize_toc_settings',
            'default' => [
                'enabled' => true,
                'title' => 'Table of Contents',
                'allowed_headings' => ['h1', 'h2', 'h3'],
                'collapsible' => false,
                'position' => 'before_first_heading',
                'min_headings' => 2,
                'line_spacing' => 1.5,
                'post_types' => ['post', 'wpa_course', 'wpa_lesson', 'wpa_news'],
                'title_top_margin' => 10,
            ],
        ]
    );

    // Section: General
    add_settings_section(
        'wpa_toc_section_general',
        __( 'General Settings', 'wp-academic-post-enhanced' ),
        'wpa_toc_section_general_callback',
        'wpa_toc'
    );

    add_settings_field('wpa_toc_enabled_field', __('Enable TOC', 'wp-academic-post-enhanced'), 'wpa_toc_enabled_field_callback', 'wpa_toc', 'wpa_toc_section_general');
    add_settings_field('wpa_toc_title_field', __('TOC Title', 'wp-academic-post-enhanced'), 'wpa_toc_title_field_callback', 'wpa_toc', 'wpa_toc_section_general');
    add_settings_field('wpa_toc_post_types_field', __('Display on Post Types', 'wp-academic-post-enhanced'), 'wpa_toc_post_types_field_callback', 'wpa_toc', 'wpa_toc_section_general');
    add_settings_field('wpa_toc_position_field', __('Position', 'wp-academic-post-enhanced'), 'wpa_toc_position_field_callback', 'wpa_toc', 'wpa_toc_section_general');

    // Section: Appearance
    add_settings_section(
        'wpa_toc_section_appearance',
        __( 'Appearance Settings', 'wp-academic-post-enhanced' ),
        'wpa_toc_section_appearance_callback',
        'wpa_toc'
    );

    add_settings_field('wpa_toc_collapsible_field', __('Collapsible', 'wp-academic-post-enhanced'), 'wpa_toc_collapsible_field_callback', 'wpa_toc', 'wpa_toc_section_appearance');
    add_settings_field('wpa_toc_start_collapsed_field', __('Start Collapsed', 'wp-academic-post-enhanced'), 'wpa_toc_start_collapsed_field_callback', 'wpa_toc', 'wpa_toc_section_appearance');
    add_settings_field('wpa_toc_line_spacing_field', __('Line Spacing (em)', 'wp-academic-post-enhanced'), 'wpa_toc_line_spacing_field_callback', 'wpa_toc', 'wpa_toc_section_appearance');
    add_settings_field('wpa_toc_font_size_field', __('Font Size (rem)', 'wp-academic-post-enhanced'), 'wpa_toc_font_size_field_callback', 'wpa_toc', 'wpa_toc_section_appearance');
    add_settings_field('wpa_toc_padding_field', __('Container Padding (px)', 'wp-academic-post-enhanced'), 'wpa_toc_padding_field_callback', 'wpa_toc', 'wpa_toc_section_appearance');
    add_settings_field('wpa_toc_title_top_margin_field', __('Title Top Margin (px)', 'wp-academic-post-enhanced'), 'wpa_toc_title_top_margin_field_callback', 'wpa_toc', 'wpa_toc_section_appearance');

    // Section: Advanced
    add_settings_section(
        'wpa_toc_section_advanced',
        __( 'Advanced Settings', 'wp-academic-post-enhanced' ),
        'wpa_toc_section_advanced_callback',
        'wpa_toc'
    );

    add_settings_field('wpa_toc_allowed_headings_field', __('Allowed Headings', 'wp-academic-post-enhanced'), 'wpa_toc_allowed_headings_field_callback', 'wpa_toc', 'wpa_toc_section_advanced');
    add_settings_field('wpa_toc_min_headings_field', __('Minimum Headings', 'wp-academic-post-enhanced'), 'wpa_toc_min_headings_field_callback', 'wpa_toc', 'wpa_toc_section_advanced');
}
add_action( 'admin_init', 'wpa_register_toc_settings' );

function wpa_sanitize_toc_settings( $input ) {
    $sanitized = [];
    $defaults = [
        'enabled' => true,
        'title' => 'Table of Contents',
        'allowed_headings' => ['h1', 'h2', 'h3'],
        'collapsible' => false,
        'position' => 'before_first_heading',
        'min_headings' => 2,
        'line_spacing' => 1.5,
        'post_types' => ['post', 'wpa_course', 'wpa_lesson', 'wpa_news'],
        'title_top_margin' => 10,
    ];
    $input = wp_parse_args( $input, $defaults );

    $sanitized['enabled'] = rest_sanitize_boolean( $input['enabled'] );
    $sanitized['title'] = sanitize_text_field( $input['title'] );
    $available_headings = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
    if ( is_array( $input['allowed_headings'] ) ) {
        $sanitized['allowed_headings'] = array_intersect( $input['allowed_headings'], $available_headings );
    } else {
        $sanitized['allowed_headings'] = $defaults['allowed_headings'];
    }
    $sanitized['collapsible'] = rest_sanitize_boolean( $input['collapsible'] );
    $sanitized['start_collapsed'] = rest_sanitize_boolean( $input['start_collapsed'] );
    $sanitized['position'] = in_array( $input['position'], ['before_first_heading', 'after_first_paragraph', 'top'] ) ? $input['position'] : 'before_first_heading';
    $sanitized['min_headings'] = absint( $input['min_headings'] );
    $sanitized['line_spacing'] = isset( $input['line_spacing'] ) ? floatval( $input['line_spacing'] ) : 1.5;
    $sanitized['font_size'] = isset( $input['font_size'] ) ? floatval( $input['font_size'] ) : 0.85;
    $sanitized['padding'] = isset( $input['padding'] ) ? absint( $input['padding'] ) : 15;
    $sanitized['title_top_margin'] = isset( $input['title_top_margin'] ) ? absint( $input['title_top_margin'] ) : 0;
    
    if ( is_array( $input['post_types'] ) ) {
        $sanitized['post_types'] = array_map('sanitize_key', $input['post_types']);
    } else {
        $sanitized['post_types'] = $defaults['post_types'];
    }

    return $sanitized;
}

function wpa_toc_section_general_callback() { echo '<p>' . esc_html__( 'Configure general Table of Contents settings.', 'wp-academic-post-enhanced' ) . '</p>'; }
function wpa_toc_section_appearance_callback() { echo '<p>' . esc_html__( 'Configure appearance related settings.', 'wp-academic-post-enhanced' ) . '</p>'; }
function wpa_toc_section_advanced_callback() { echo '<p>' . esc_html__( 'Configure advanced logic.', 'wp-academic-post-enhanced' ) . '</p>'; }

function wpa_toc_enabled_field_callback() { 
    $options = get_option('wpa_toc_settings'); 
    $enabled = isset($options['enabled']) ? $options['enabled'] : true; 
    echo '<label class="wpa-toggle-switch">';
    echo '<input type="checkbox" name="wpa_toc_settings[enabled]" value="1" ' . checked(1, $enabled, false) . ' />';
    echo '<span class="wpa-toggle-slider"></span>';
    echo '</label>';
}
function wpa_toc_title_field_callback() { $options = get_option('wpa_toc_settings'); $title = isset($options['title']) ? $options['title'] : 'Table of Contents'; echo '<input type="text" name="wpa_toc_settings[title]" value="' . esc_attr($title) . '" class="regular-text" />'; }
function wpa_toc_post_types_field_callback() { $options = get_option('wpa_toc_settings'); $post_types = get_post_types(['public' => true], 'objects'); $selected_post_types = isset($options['post_types']) ? $options['post_types'] : ['post']; foreach ($post_types as $post_type) { echo '<label><input type="checkbox" name="wpa_toc_settings[post_types][]" value="' . esc_attr($post_type->name) . '" ' . checked(in_array($post_type->name, $selected_post_types), true, false) . ' /> ' . esc_html($post_type->label) . '</label><br />'; } }
function wpa_toc_allowed_headings_field_callback() { $options = get_option('wpa_toc_settings'); $allowed_headings = isset($options['allowed_headings']) ? $options['allowed_headings'] : ['h1', 'h2', 'h3']; $available_headings = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']; foreach ($available_headings as $heading) { echo '<label><input type="checkbox" name="wpa_toc_settings[allowed_headings][]" value="' . esc_attr($heading) . '" ' . checked(in_array($heading, $allowed_headings), true, false) . ' /> ' . esc_html(strtoupper($heading)) . '</label><br />'; } }
function wpa_toc_collapsible_field_callback() { 
    $options = get_option('wpa_toc_settings'); 
    $collapsible = isset($options['collapsible']) ? $options['collapsible'] : false; 
    echo '<label class="wpa-toggle-switch">';
    echo '<input type="checkbox" id="wpa_toc_collapsible" name="wpa_toc_settings[collapsible]" value="1" ' . checked(1, $collapsible, false) . ' />';
    echo '<span class="wpa-toggle-slider"></span>';
    echo '</label>';
}

function wpa_toc_start_collapsed_field_callback() {
    $options = get_option('wpa_toc_settings');
    $start_collapsed = isset($options['start_collapsed']) ? $options['start_collapsed'] : false;
    echo '<label class="wpa-toggle-switch">';
    echo '<input type="checkbox" name="wpa_toc_settings[start_collapsed]" value="1" ' . checked(1, $start_collapsed, false) . ' />';
    echo '<span class="wpa-toggle-slider"></span>';
    echo '</label>';
    echo '<p class="description">' . esc_html__('If checked, the table of contents will be collapsed by default.', 'wp-academic-post-enhanced') . '</p>';
}
function wpa_toc_position_field_callback() { $options = get_option('wpa_toc_settings'); $position = isset($options['position']) ? $options['position'] : 'before_first_heading'; $positions = ['before_first_heading' => 'Before first heading', 'after_first_paragraph' => 'After first paragraph', 'top' => 'Top of post']; echo '<select name="wpa_toc_settings[position]">'; foreach ($positions as $value => $label) { echo '<option value="' . esc_attr($value) . '" ' . selected($position, $value, false) . '>' . esc_html($label) . '</option>'; } echo '</select>'; }
function wpa_toc_min_headings_field_callback() { $options = get_option('wpa_toc_settings'); $min_headings = isset($options['min_headings']) ? $options['min_headings'] : 2; echo '<input type="number" name="wpa_toc_settings[min_headings]" value="' . esc_attr($min_headings) . '" min="1" />'; }
function wpa_toc_line_spacing_field_callback() { $options = get_option('wpa_toc_settings'); $line_spacing = isset($options['line_spacing']) ? $options['line_spacing'] : 0.2; echo '<input type="number" name="wpa_toc_settings[line_spacing]" value="' . esc_attr($line_spacing) . '" step="0.1" min="0" /> em'; }
function wpa_toc_font_size_field_callback() { $options = get_option('wpa_toc_settings'); $font_size = isset($options['font_size']) ? $options['font_size'] : 0.85; echo '<input type="number" name="wpa_toc_settings[font_size]" value="' . esc_attr($font_size) . '" step="0.01" min="0" /> rem'; }
function wpa_toc_padding_field_callback() { $options = get_option('wpa_toc_settings'); $padding = isset($options['padding']) ? $options['padding'] : 15; echo '<input type="number" name="wpa_toc_settings[padding]" value="' . esc_attr($padding) . '" min="0" /> px'; }
function wpa_toc_title_top_margin_field_callback() { $options = get_option('wpa_toc_settings'); $title_top_margin = isset($options['title_top_margin']) ? $options['title_top_margin'] : 0; echo '<input type="number" name="wpa_toc_settings[title_top_margin]" value="' . esc_attr($title_top_margin) . '" min="0" />'; }