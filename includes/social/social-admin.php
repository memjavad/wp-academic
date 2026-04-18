<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Add the social submenu page.
 */
function wpa_add_social_admin_menu() {
    $enabled = get_option( 'wpa_social_enabled', false );
    if ( $enabled ) {
        add_submenu_page(
            'wp-academic-post-enhanced',
            __( 'Social Settings', 'wp-academic-post-enhanced' ),
            __( 'Social', 'wp-academic-post-enhanced' ),
            'manage_options',
            'wp-academic-post-enhanced-social',
            'wpa_social_page'
        );
    }
}
add_action( 'admin_menu', 'wpa_add_social_admin_menu' );

/**
 * Display the social settings page.
 */
function wpa_social_page() {
    ?>
    <div class="wrap wpa-settings-wrapper">
        <div class="wpa-header">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        </div>
        
        <div class="wpa-vertical-layout">
            <div class="wpa-vertical-nav">
                <ul>
                    <li><a href="#group-social-config" class="wpa-vtab active" data-target="group-social-config"><?php esc_html_e( 'Configuration', 'wp-academic-post-enhanced' ); ?></a></li>
                    <li><a href="#group-social-design" class="wpa-vtab" data-target="group-social-design"><?php esc_html_e( 'Style & Mobile', 'wp-academic-post-enhanced' ); ?></a></li>
                </ul>
            </div>

            <div class="wpa-vertical-content">
                <form action="options.php" method="post">
                    <?php settings_fields( 'wpa_social_options' ); ?>

                    <!-- Group 1: Configuration -->
                    <div id="group-social-config" class="wpa-group-content active">
                        <h2 class="nav-tab-wrapper">
                            <a href="#tab-social-gen" class="nav-tab nav-tab-active"><?php esc_html_e( 'General', 'wp-academic-post-enhanced' ); ?></a>
                            <a href="#tab-social-nets" class="nav-tab"><?php esc_html_e( 'Networks', 'wp-academic-post-enhanced' ); ?></a>
                        </h2>
                        
                        <div id="tab-social-gen" class="tab-content active">
                            <?php wpa_render_specific_section( 'wpa_social', 'wpa_social_section_general' ); ?>
                        </div>
                        <div id="tab-social-nets" class="tab-content" style="display:none;">
                            <?php wpa_render_specific_section( 'wpa_social', 'wpa_social_section_networks' ); ?>
                        </div>
                    </div>

                    <!-- Group 2: Style & Mobile -->
                    <div id="group-social-design" class="wpa-group-content">
                        <h2 class="nav-tab-wrapper">
                            <a href="#tab-social-style" class="nav-tab nav-tab-active"><?php esc_html_e( 'Design', 'wp-academic-post-enhanced' ); ?></a>
                            <a href="#tab-social-mobile" class="nav-tab"><?php esc_html_e( 'Floating & Mobile', 'wp-academic-post-enhanced' ); ?></a>
                        </h2>
                        
                        <div id="tab-social-style" class="tab-content active">
                            <?php wpa_render_specific_section( 'wpa_social', 'wpa_social_section_design' ); ?>
                        </div>
                        <div id="tab-social-mobile" class="tab-content" style="display:none;">
                            <?php wpa_render_specific_section( 'wpa_social', 'wpa_social_section_floating' ); ?>
                        </div>
                    </div>

                    <?php submit_button(); ?>
                </form>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Register social settings.
 */
function wpa_register_social_settings() {
    register_setting(
        'wpa_social_options',
        'wpa_social_settings',
        [
            'type' => 'array',
            'sanitize_callback' => 'wpa_sanitize_social_settings',
            'default' => [
                'services' => ['facebook', 'twitter', 'linkedin'],
                'mobile_services' => ['facebook', 'twitter', 'whatsapp', 'native-share'],
                'post_types' => ['post', 'wpa_course', 'wpa_lesson', 'wpa_news'],
                'style' => 'icons-text',
                'shape' => 'default',
                'size' => 'medium',
                'alignment' => 'left',
                'position' => 'none',
                'floating' => 'none',
                'mobile_sticky' => '0',
                'color_scheme' => 'color',
                'hover_effect' => '',
            ],
        ]
    );

    // Section: General
    add_settings_section(
        'wpa_social_section_general',
        __( 'General Settings', 'wp-academic-post-enhanced' ),
        'wpa_social_section_general_callback',
        'wpa_social'
    );

    add_settings_field('wpa_social_title_field', __('Title', 'wp-academic-post-enhanced'), 'wpa_social_title_field_callback', 'wpa_social', 'wpa_social_section_general');
    add_settings_field('wpa_social_post_types_field', __('Display on Post Types', 'wp-academic-post-enhanced'), 'wpa_social_post_types_field_callback', 'wpa_social', 'wpa_social_section_general');
    add_settings_field('wpa_social_position_field', __('Display Position', 'wp-academic-post-enhanced'), 'wpa_social_position_field_callback', 'wpa_social', 'wpa_social_section_general');

    // Section: Networks
    add_settings_section(
        'wpa_social_section_networks',
        __( 'Social Networks', 'wp-academic-post-enhanced' ),
        'wpa_social_section_networks_callback',
        'wpa_social'
    );

    add_settings_field('wpa_social_services_field', __('Desktop/Content Networks', 'wp-academic-post-enhanced'), 'wpa_social_services_field_callback', 'wpa_social', 'wpa_social_section_networks');
    add_settings_field('wpa_social_mobile_services_field', __('Mobile Sticky Bar Networks', 'wp-academic-post-enhanced'), 'wpa_social_mobile_services_field_callback', 'wpa_social', 'wpa_social_section_networks');

    // Section: Design
    add_settings_section(
        'wpa_social_section_design',
        __( 'Button Design', 'wp-academic-post-enhanced' ),
        'wpa_social_section_design_callback',
        'wpa_social'
    );

    add_settings_field('wpa_social_style_field', __('Button Style', 'wp-academic-post-enhanced'), 'wpa_social_style_field_callback', 'wpa_social', 'wpa_social_section_design');
    add_settings_field('wpa_social_shape_field', __('Button Shape', 'wp-academic-post-enhanced'), 'wpa_social_shape_field_callback', 'wpa_social', 'wpa_social_section_design');
    add_settings_field('wpa_social_size_field', __('Button Size', 'wp-academic-post-enhanced'), 'wpa_social_size_field_callback', 'wpa_social', 'wpa_social_section_design');
    add_settings_field('wpa_social_alignment_field', __('Alignment', 'wp-academic-post-enhanced'), 'wpa_social_alignment_field_callback', 'wpa_social', 'wpa_social_section_design');
    add_settings_field('wpa_social_color_scheme_field', __('Color Scheme', 'wp-academic-post-enhanced'), 'wpa_social_color_scheme_field_callback', 'wpa_social', 'wpa_social_section_design');
    add_settings_field('wpa_social_hover_effect_field', __('Hover Effect', 'wp-academic-post-enhanced'), 'wpa_social_hover_effect_field_callback', 'wpa_social', 'wpa_social_section_design');

    // Section: Floating/Mobile
    add_settings_section(
        'wpa_social_section_floating',
        __( 'Floating & Mobile', 'wp-academic-post-enhanced' ),
        'wpa_social_section_floating_callback',
        'wpa_social'
    );

    add_settings_field('wpa_social_floating_field', __('Floating Sidebar (Desktop)', 'wp-academic-post-enhanced'), 'wpa_social_floating_field_callback', 'wpa_social', 'wpa_social_section_floating');
    add_settings_field('wpa_social_mobile_sticky_field', __('Mobile Sticky Bar', 'wp-academic-post-enhanced'), 'wpa_social_mobile_sticky_field_callback', 'wpa_social', 'wpa_social_section_floating');
    add_settings_field('wpa_social_mobile_sticky_ad_code_field', __('Mobile Sticky Ad Code', 'wp-academic-post-enhanced'), 'wpa_social_mobile_sticky_ad_code_field_callback', 'wpa_social', 'wpa_social_section_floating');
}
add_action( 'admin_init', 'wpa_register_social_settings' );

/**
 * Sanitize social settings.
 *
 * @param array $input The input.
 * @return array The sanitized input.
 */
function wpa_sanitize_social_settings( $input ) {
    $sanitized = [];
    $defaults = [
        'title' => 'Share this article',
        'post_types' => ['post', 'wpa_course', 'wpa_lesson', 'wpa_news'],
        'services' => ['facebook', 'twitter', 'linkedin'],
        'style' => 'icons-text',
        'shape' => 'default',
        'size' => 'medium',
        'alignment' => 'left',
        'color_scheme' => 'color',
        'hover_effect' => '',
    ];

    $input = wp_parse_args( $input, $defaults );

    $sanitized['title'] = sanitize_text_field( $input['title'] );

    // Sanitize post types
    if ( isset($input['post_types']) && is_array( $input['post_types'] ) ) {
        $sanitized['post_types'] = array_map('sanitize_key', $input['post_types']);
    } else {
        $sanitized['post_types'] = ['post', 'wpa_course', 'wpa_lesson', 'wpa_news'];
    }

    // Sanitize services
    $available_services = ['facebook', 'twitter', 'linkedin', 'pinterest', 'reddit', 'telegram', 'whatsapp', 'email', 'copy-link', 'native-share', 'sms', 'print'];
    if ( isset($input['services']) && is_array( $input['services'] ) ) {
        $sanitized['services'] = array_intersect( $input['services'], $available_services );
    } else {
        $sanitized['services'] = $defaults['services'];
    }

    // Sanitize mobile services
    if ( isset($input['mobile_services']) && is_array( $input['mobile_services'] ) ) {
        $sanitized['mobile_services'] = array_intersect( $input['mobile_services'], $available_services );
    } else {
        $sanitized['mobile_services'] = ['facebook', 'twitter', 'whatsapp', 'native-share'];
    }

    // Sanitize style
    $available_styles = ['icons-text', 'icons', 'text', 'minimal', 'inline-text'];
    $sanitized['style'] = in_array( $input['style'], $available_styles ) ? $input['style'] : $defaults['style'];

    // Sanitize shape
    $available_shapes = ['default', 'rounded', 'circle'];
    $sanitized['shape'] = in_array( $input['shape'], $available_shapes ) ? $input['shape'] : $defaults['shape'];

    // Sanitize size
    $available_sizes = ['small', 'medium', 'large'];
    $sanitized['size'] = in_array( $input['size'], $available_sizes ) ? $input['size'] : $defaults['size'];

    // Sanitize alignment
    $available_alignments = ['left', 'center', 'right'];
    $sanitized['alignment'] = in_array( $input['alignment'], $available_alignments ) ? $input['alignment'] : $defaults['alignment'];

    // Sanitize position
    $available_positions = ['before', 'after', 'both', 'none'];
    $sanitized['position'] = in_array( $input['position'], $available_positions ) ? $input['position'] : 'none';

    // Sanitize floating (Desktop)
    $available_floating = ['left', 'right', 'none'];
    $sanitized['floating'] = in_array( $input['floating'], $available_floating ) ? $input['floating'] : 'none';

    // Sanitize mobile sticky
    $sanitized['mobile_sticky'] = isset( $input['mobile_sticky'] ) && $input['mobile_sticky'] == '1' ? '1' : '0';

    // Sanitize color scheme
    $available_color_schemes = ['color', 'mono', 'mono-hover', 'outline', 'gradient', 'dark', 'light', 'soft', 'glass', '3d'];
    $sanitized['color_scheme'] = in_array( $input['color_scheme'], $available_color_schemes ) ? $input['color_scheme'] : $defaults['color_scheme'];

    // Sanitize hover effect
    $sanitized['hover_effect'] = isset( $input['hover_effect'] ) && $input['hover_effect'] === 'glow' ? 'glow' : '';

    // Sanitize Ad Code (Allow HTML)
    $sanitized['mobile_sticky_ad_code'] = isset( $input['mobile_sticky_ad_code'] ) ? $input['mobile_sticky_ad_code'] : '';

    return $sanitized;
}

function wpa_social_mobile_sticky_ad_code_field_callback() {
    $options = get_option( 'wpa_social_settings' );
    $ad_code = isset( $options['mobile_sticky_ad_code'] ) ? $options['mobile_sticky_ad_code'] : '';
    echo '<textarea name="wpa_social_settings[mobile_sticky_ad_code]" rows="4" cols="50" class="large-text" placeholder="Paste your small ad code here (e.g. AdSense 320x50)">' . esc_textarea( $ad_code ) . '</textarea>';
    echo '<p class="description">' . esc_html__( 'This code will be injected at the very top of the mobile sticky bar.', 'wp-academic-post-enhanced' ) . '</p>';
}

function wpa_social_title_field_callback() {
    $options = get_option( 'wpa_social_settings' );
    $title = isset( $options['title'] ) ? $options['title'] : 'Share this article';
    echo '<input type="text" name="wpa_social_settings[title]" value="' . esc_attr( $title ) . '" class="regular-text" />';
}

function wpa_social_post_types_field_callback() {
    $options = get_option('wpa_social_settings');
    $post_types = get_post_types(['public' => true], 'objects');
    $selected_post_types = isset($options['post_types']) ? $options['post_types'] : ['post'];

    foreach ($post_types as $post_type) {
        echo '<label><input type="checkbox" name="wpa_social_settings[post_types][]" value="' . esc_attr($post_type->name) . '" ' . checked(in_array($post_type->name, $selected_post_types), true, false) . ' /> ' . esc_html($post_type->label) . '</label><br />';
    }
}

function wpa_social_section_general_callback() { echo '<p>' . esc_html__( 'Configure general settings.', 'wp-academic-post-enhanced' ) . '</p>'; }
function wpa_social_section_networks_callback() { echo '<p>' . esc_html__( 'Select which networks to display.', 'wp-academic-post-enhanced' ) . '</p>'; }
function wpa_social_section_design_callback() { echo '<p>' . esc_html__( 'Customize the button appearance.', 'wp-academic-post-enhanced' ) . '</p>'; }
function wpa_social_section_floating_callback() { echo '<p>' . esc_html__( 'Configure floating and sticky bars.', 'wp-academic-post-enhanced' ) . '</p>'; }

/**
 * Display the social services field.
 */
function wpa_social_services_field_callback() {
    $options = get_option( 'wpa_social_settings' );
    $services = isset( $options['services'] ) ? $options['services'] : [];
    $available_services = [
        'facebook' => 'Facebook',
        'twitter' => 'X (Twitter)',
        'linkedin' => 'LinkedIn',
        'pinterest' => 'Pinterest',
        'reddit' => 'Reddit',
        'telegram' => 'Telegram',
        'whatsapp' => 'WhatsApp',
        'email' => 'Email',
        'sms' => 'SMS',
        'print' => 'Print',
        'copy-link' => 'Copy Link',
        'native-share' => 'Native Share (Mobile)',
    ];

    foreach ( $available_services as $value => $label ) {
        echo '<label><input type="checkbox" name="wpa_social_settings[services][]" value="' . esc_attr( $value ) . '" ' . checked( in_array( $value, $services ), true, false ) . ' /> ' . esc_html( $label ) . '</label><br />';
    }
}

/**
 * Display the mobile social services field.
 */
function wpa_social_mobile_services_field_callback() {
    $options = get_option( 'wpa_social_settings' );
    $services = isset( $options['mobile_services'] ) ? $options['mobile_services'] : [];
    $available_services = [
        'facebook' => 'Facebook',
        'twitter' => 'X (Twitter)',
        'linkedin' => 'LinkedIn',
        'pinterest' => 'Pinterest',
        'reddit' => 'Reddit',
        'telegram' => 'Telegram',
        'whatsapp' => 'WhatsApp',
        'email' => 'Email',
        'sms' => 'SMS',
        'print' => 'Print',
        'copy-link' => 'Copy Link',
        'native-share' => 'Native Share (Mobile)',
    ];

    foreach ( $available_services as $value => $label ) {
        echo '<label><input type="checkbox" name="wpa_social_settings[mobile_services][]" value="' . esc_attr( $value ) . '" ' . checked( in_array( $value, $services ), true, false ) . ' /> ' . esc_html( $label ) . '</label><br />';
    }
}

/**
 * Display the social style field.
 */
function wpa_social_style_field_callback() {
    $options = get_option( 'wpa_social_settings' );
    $style = isset( $options['style'] ) ? $options['style'] : 'icons-text';
    $available_styles = [
        'icons-text' => 'Icons and Text',
        'icons' => 'Icons Only',
        'text' => 'Text Only',
        'minimal' => 'Minimal',
        'inline-text' => 'Inline Text Links',
    ];

    foreach ( $available_styles as $value => $label ) {
        echo '<label><input type="radio" name="wpa_social_settings[style]" value="' . esc_attr( $value ) . '" ' . checked( $style, $value, false ) . ' /> ' . esc_html( $label ) . '</label><br />';
    }
}

/**
 * Display the social shape field.
 */
function wpa_social_shape_field_callback() {
    $options = get_option( 'wpa_social_settings' );
    $shape = isset( $options['shape'] ) ? $options['shape'] : 'default';
    $available_shapes = ['default' => 'Default', 'rounded' => 'Rounded', 'circle' => 'Circle'];

    foreach ( $available_shapes as $value => $label ) {
        echo '<label><input type="radio" name="wpa_social_settings[shape]" value="' . esc_attr( $value ) . '" ' . checked( $shape, $value, false ) . ' /> ' . esc_html( $label ) . '</label><br />';
    }
}

/**
 * Display the social size field.
 */
function wpa_social_size_field_callback() {
    $options = get_option( 'wpa_social_settings' );
    $size = isset( $options['size'] ) ? $options['size'] : 'medium';
    $available_sizes = ['small' => 'Small', 'medium' => 'Medium', 'large' => 'Large'];

    foreach ( $available_sizes as $value => $label ) {
        echo '<label><input type="radio" name="wpa_social_settings[size]" value="' . esc_attr( $value ) . '" ' . checked( $size, $value, false ) . ' /> ' . esc_html( $label ) . '</label><br />';
    }
}

/**
 * Display the social alignment field.
 */
function wpa_social_alignment_field_callback() {
    $options = get_option( 'wpa_social_settings' );
    $alignment = isset( $options['alignment'] ) ? $options['alignment'] : 'left';
    $available_alignments = ['left' => 'Left', 'center' => 'Center', 'right' => 'Right'];

    foreach ( $available_alignments as $value => $label ) {
        echo '<label><input type="radio" name="wpa_social_settings[alignment]" value="' . esc_attr( $value ) . '" ' . checked( $alignment, $value, false ) . ' /> ' . esc_html( $label ) . '</label><br />';
    }
}

/**
 * Display the social position field.
 */
function wpa_social_position_field_callback() {
    $options = get_option( 'wpa_social_settings' );
    $position = isset( $options['position'] ) ? $options['position'] : 'none';
    $available_positions = [
        'before' => 'Before Content',
        'after' => 'After Content',
        'both' => 'Before & After Content',
        'none' => 'None (Shortcode Only or Citation Box)',
    ];

    foreach ( $available_positions as $value => $label ) {
        echo '<label><input type="radio" name="wpa_social_settings[position]" value="' . esc_attr( $value ) . '" ' . checked( $position, $value, false ) . ' /> ' . esc_html( $label ) . '</label><br />';
    }
}

/**
 * Display the social floating field.
 */
function wpa_social_floating_field_callback() {
    $options = get_option( 'wpa_social_settings' );
    $floating = isset( $options['floating'] ) ? $options['floating'] : 'none';
    $available_floating = [
        'none' => 'Disabled',
        'left' => 'Floating Left',
        'right' => 'Floating Right',
    ];

    foreach ( $available_floating as $value => $label ) {
        echo '<label><input type="radio" name="wpa_social_settings[floating]" value="' . esc_attr( $value ) . '" ' . checked( $floating, $value, false ) . ' /> ' . esc_html( $label ) . '</label><br />';
    }
}

/**
 * Display the social mobile sticky field.
 */
function wpa_social_mobile_sticky_field_callback() {
    $options = get_option( 'wpa_social_settings' );
    $mobile_sticky = isset( $options['mobile_sticky'] ) ? $options['mobile_sticky'] : '0';
    echo '<label><input type="checkbox" name="wpa_social_settings[mobile_sticky]" value="1" ' . checked( $mobile_sticky, '1', false ) . ' /> ' . esc_html__( 'Enable Sticky Bottom Bar on Mobile', 'wp-academic-post-enhanced' ) . '</label>';
}

/**
 * Display the social color scheme field.
 */
function wpa_social_color_scheme_field_callback() {
    $options = get_option( 'wpa_social_settings' );
    $color_scheme = isset( $options['color_scheme'] ) ? $options['color_scheme'] : 'color';
    $available_color_schemes = [
        'color' => 'Color (Default)',
        'mono' => 'Monochrome',
        'mono-hover' => 'Monochrome with Hover',
        'outline' => 'Outline',
        'gradient' => 'Gradient',
        'dark' => 'Dark',
        'light' => 'Light',
        'soft' => 'Soft (Pastel)',
        'glass' => 'Glassmorphism',
        '3d' => '3D Effect',
    ];

    foreach ( $available_color_schemes as $value => $label ) {
        echo '<label><input type="radio" name="wpa_social_settings[color_scheme]" value="' . esc_attr( $value ) . '" ' . checked( $color_scheme, $value, false ) . ' /> ' . esc_html( $label ) . '</label><br />';
    }
}

/**
 * Display the social hover effect field.
 */
function wpa_social_hover_effect_field_callback() {
    $options = get_option( 'wpa_social_settings' );
    $hover_effect = isset( $options['hover_effect'] ) ? $options['hover_effect'] : '';
    echo '<label><input type="checkbox" name="wpa_social_settings[hover_effect]" value="glow" ' . checked( $hover_effect, 'glow', false ) . ' /> ' . esc_html__( 'Glow Effect', 'wp-academic-post-enhanced' ) . '</label><br />';
}