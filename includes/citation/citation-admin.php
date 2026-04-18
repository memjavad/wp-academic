<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Add the citation submenu page.
 */
function wp_academic_post_enhanced_add_citation_admin_menu() {
    $options = get_option( 'wpa_citation_settings' );
    if ( ! empty( $options['enabled'] ) ) {
        add_submenu_page(
            'wp-academic-post-enhanced',
            __( 'Citation Settings', 'wp-academic-post-enhanced' ),
            __( 'Citation', 'wp-academic-post-enhanced' ),
            'manage_options',
            'wp-academic-post-enhanced-citation',
            'wp_academic_post_enhanced_citation_page'
        );
    }
}
add_action( 'admin_menu', 'wp_academic_post_enhanced_add_citation_admin_menu' );

/**
 * Display the citation settings page.
 */
function wp_academic_post_enhanced_citation_page() {
    ?>
    <div class="wrap wpa-settings-wrapper">
        <div class="wpa-header">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        </div>
        
        <div class="wpa-vertical-layout">
            <div class="wpa-vertical-nav">
                <ul>
                    <li><a href="#group-citation" class="wpa-vtab active" data-target="group-citation"><?php esc_html_e( 'Citation Box', 'wp-academic-post-enhanced' ); ?></a></li>
                    <li><a href="#group-pdf" class="wpa-vtab" data-target="group-pdf"><?php esc_html_e( 'PDF Settings', 'wp-academic-post-enhanced' ); ?></a></li>
                </ul>
            </div>

            <div class="wpa-vertical-content">
                <form action="options.php" method="post">
                    <?php settings_fields( 'wpa_citation_options' ); ?>

                    <!-- Group 1: Citation -->
                    <div id="group-citation" class="wpa-group-content active">
                        <h2 class="nav-tab-wrapper">
                            <a href="#tab-general" class="nav-tab nav-tab-active"><?php esc_html_e( 'General', 'wp-academic-post-enhanced' ); ?></a>
                            <a href="#tab-appearance" class="nav-tab"><?php esc_html_e( 'Appearance', 'wp-academic-post-enhanced' ); ?></a>
                        </h2>
                        
                        <div id="tab-general" class="tab-content active">
                            <?php wpa_render_specific_section( 'wpa_citation', 'wpa_citation_section' ); ?>
                        </div>
                        <div id="tab-appearance" class="tab-content" style="display:none;">
                            <?php wpa_render_specific_section( 'wpa_citation', 'wpa_citation_appearance_section' ); ?>
                        </div>
                    </div>

                    <!-- Group 2: PDF -->
                    <div id="group-pdf" class="wpa-group-content">
                        <h2 class="nav-tab-wrapper">
                            <a href="#tab-pdf-gen" class="nav-tab nav-tab-active"><?php esc_html_e( 'PDF Download', 'wp-academic-post-enhanced' ); ?></a>
                            <a href="#tab-pdf-style" class="nav-tab"><?php esc_html_e( 'PDF Styling', 'wp-academic-post-enhanced' ); ?></a>
                        </h2>
                        
                        <div id="tab-pdf-gen" class="tab-content active">
                            <?php wpa_render_specific_section( 'wpa_citation', 'wpa_citation_pdf_section' ); ?>
                        </div>
                        <div id="tab-pdf-style" class="tab-content" style="display:none;">
                            <?php wpa_render_specific_section( 'wpa_citation', 'wpa_citation_pdf_appearance_section' ); ?>
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
 * Register citation settings.
 */
function wp_academic_post_enhanced_register_citation_settings() {
    register_setting(
        'wpa_citation_options',
        'wpa_citation_settings',
        [
            'type' => 'array',
            'sanitize_callback' => 'wpa_sanitize_citation_settings',
            'default' => [
                'enabled' => true,
                'text' => 'Please cite this article as:',
                'styles' => ['apa', 'mla'],
                'position' => 'after_content',
                'default_style' => 'apa',
                'background_color' => '#f9f9f9',
                'border_color' => '#ddd',
                'text_color' => '#333',
                'post_types' => ['post', 'wpa_course', 'wpa_lesson', 'wpa_news'],
            ],
        ]
    );

    add_settings_section(
        'wpa_citation_section',
        __( 'General Settings', 'wp-academic-post-enhanced' ),
        'wpa_citation_section_callback',
        'wpa_citation'
    );

    add_settings_field('wpa_citation_enabled_field', __('Enable Citation Feature', 'wp-academic-post-enhanced'), 'wpa_citation_enabled_field_callback', 'wpa_citation', 'wpa_citation_section');
    
    // PDF Download Section (New)
    add_settings_section(
        'wpa_citation_pdf_section',
        __( 'PDF Download Settings', 'wp-academic-post-enhanced' ),
        'wpa_citation_pdf_section_callback',
        'wpa_citation'
    );

    add_settings_field('wpa_citation_pdf_download_field', __('Enable PDF Download', 'wp-academic-post-enhanced'), 'wpa_citation_pdf_download_field_callback', 'wpa_citation', 'wpa_citation_pdf_section');
    add_settings_field('wpa_citation_pdf_post_types_field', __('PDF Download Post Types', 'wp-academic-post-enhanced'), 'wpa_citation_pdf_post_types_field_callback', 'wpa_citation', 'wpa_citation_pdf_section');
    add_settings_field('wpa_citation_pdf_elements_field', __('PDF Elements', 'wp-academic-post-enhanced'), 'wpa_citation_pdf_elements_field_callback', 'wpa_citation', 'wpa_citation_pdf_section');
    add_settings_field('wpa_citation_pdf_font_field', __('PDF Font', 'wp-academic-post-enhanced'), 'wpa_citation_pdf_font_field_callback', 'wpa_citation', 'wpa_citation_pdf_section');

    // PDF Appearance Section
    add_settings_section(
        'wpa_citation_pdf_appearance_section',
        __( 'PDF Styling & Appearance', 'wp-academic-post-enhanced' ),
        'wpa_citation_pdf_appearance_section_callback',
        'wpa_citation'
    );

    add_settings_field('wpa_citation_pdf_text_color_field', __('Text Color', 'wp-academic-post-enhanced'), 'wpa_citation_pdf_text_color_field_callback', 'wpa_citation', 'wpa_citation_pdf_appearance_section');
    add_settings_field('wpa_citation_pdf_heading_color_field', __('Heading Color', 'wp-academic-post-enhanced'), 'wpa_citation_pdf_heading_color_field_callback', 'wpa_citation', 'wpa_citation_pdf_appearance_section');
    add_settings_field('wpa_citation_pdf_link_color_field', __('Link Color', 'wp-academic-post-enhanced'), 'wpa_citation_pdf_link_color_field_callback', 'wpa_citation', 'wpa_citation_pdf_appearance_section');
    add_settings_field('wpa_citation_pdf_citation_bg_color_field', __('Citation Box Background', 'wp-academic-post-enhanced'), 'wpa_citation_pdf_citation_bg_color_field_callback', 'wpa_citation', 'wpa_citation_pdf_appearance_section');
    add_settings_field('wpa_citation_pdf_watermark_text_field', __('Watermark Text', 'wp-academic-post-enhanced'), 'wpa_citation_pdf_watermark_text_field_callback', 'wpa_citation', 'wpa_citation_pdf_appearance_section');

    add_settings_field('wpa_citation_title_field', __('Title', 'wp-academic-post-enhanced'), 'wpa_citation_title_field_callback', 'wpa_citation', 'wpa_citation_section');
    add_settings_field('wpa_citation_post_types_field', __('Display on Post Types', 'wp-academic-post-enhanced'), 'wpa_citation_post_types_field_callback', 'wpa_citation', 'wpa_citation_section');
    add_settings_field('wpa_citation_styles_field', __('Available Citation Styles', 'wp-academic-post-enhanced'), 'wpa_citation_styles_field_callback', 'wpa_citation', 'wpa_citation_section');
    add_settings_field('wpa_citation_position_field', __('Position', 'wp-academic-post-enhanced'), 'wpa_citation_position_field_callback', 'wpa_citation', 'wpa_citation_section');
    add_settings_field('wpa_citation_default_style_field', __('Default Style', 'wp-academic-post-enhanced'), 'wpa_citation_default_style_field_callback', 'wpa_citation', 'wpa_citation_section');

    // Appearance Section
    add_settings_section(
        'wpa_citation_appearance_section',
        __( 'Appearance', 'wp-academic-post-enhanced' ),
        'wpa_citation_appearance_section_callback',
        'wpa_citation'
    );

    add_settings_field('wpa_citation_background_color_field', __('Background Color', 'wp-academic-post-enhanced'), 'wpa_citation_background_color_field_callback', 'wpa_citation', 'wpa_citation_appearance_section');
    add_settings_field('wpa_citation_border_color_field', __('Border Color', 'wp-academic-post-enhanced'), 'wpa_citation_border_color_field_callback', 'wpa_citation', 'wpa_citation_appearance_section');
    add_settings_field('wpa_citation_text_color_field', __('Text Color', 'wp-academic-post-enhanced'), 'wpa_citation_text_color_field_callback', 'wpa_citation', 'wpa_citation_appearance_section');
}
add_action( 'admin_init', 'wp_academic_post_enhanced_register_citation_settings' );

function wpa_sanitize_citation_settings( $input ) {
    $sanitized = [];
    $defaults = [
        'enabled' => true,
        'title' => 'Cite this article',
        'styles' => ['apa', 'mla'],
        'position' => 'after_content',
        'default_style' => 'apa',
        'background_color' => '#f9f9f9',
        'border_color' => '#ddd',
        'text_color' => '#333',
        'post_types' => ['post'],
    ];
    $input = wp_parse_args( $input, $defaults );

    $sanitized['enabled'] = rest_sanitize_boolean( $input['enabled'] );
    $sanitized['pdf_download_enabled'] = isset( $input['pdf_download_enabled'] ) ? rest_sanitize_boolean( $input['pdf_download_enabled'] ) : false;
    $sanitized['pdf_font'] = isset( $input['pdf_font'] ) && in_array( $input['pdf_font'], ['lateef', 'notosans', 'notoserif', 'amiri', 'notoarabic'] ) ? $input['pdf_font'] : 'lateef';
    
    // PDF Post Types
    if ( isset($input['pdf_post_types']) && is_array( $input['pdf_post_types'] ) ) {
        $sanitized['pdf_post_types'] = array_map('sanitize_key', $input['pdf_post_types']);
    } else {
        $sanitized['pdf_post_types'] = ['post', 'wpa_course', 'wpa_lesson', 'wpa_news'];
    }

    // PDF Elements
    $default_pdf_elements = ['header', 'footer', 'cover_title', 'cover_meta', 'cover_citation'];
    if ( isset( $input['pdf_elements'] ) && is_array( $input['pdf_elements'] ) ) {
        $sanitized['pdf_elements'] = array_intersect( $input['pdf_elements'], $default_pdf_elements );
    } else {
        // Fallback to default if not set (or empty, matching existing pattern)
        $sanitized['pdf_elements'] = $default_pdf_elements; 
    }

    $sanitized['pdf_text_color'] = isset( $input['pdf_text_color'] ) ? sanitize_hex_color( $input['pdf_text_color'] ) : '#111111';
    $sanitized['pdf_heading_color'] = isset( $input['pdf_heading_color'] ) ? sanitize_hex_color( $input['pdf_heading_color'] ) : '#000000';
    $sanitized['pdf_link_color'] = isset( $input['pdf_link_color'] ) ? sanitize_hex_color( $input['pdf_link_color'] ) : '#0000EE';
    $sanitized['pdf_citation_bg_color'] = isset( $input['pdf_citation_bg_color'] ) ? sanitize_hex_color( $input['pdf_citation_bg_color'] ) : '#fcfcfc';
    $sanitized['pdf_watermark_text'] = isset( $input['pdf_watermark_text'] ) ? sanitize_text_field( $input['pdf_watermark_text'] ) : '';

    $sanitized['title'] = sanitize_text_field( $input['title'] );

    $available_styles = ['apa', 'mla', 'chicago', 'harvard', 'ieee', 'ama'];
    if ( is_array( $input['styles'] ) ) {
        $sanitized['styles'] = array_intersect( $input['styles'], $available_styles );
    } else {
        $sanitized['styles'] = $defaults['styles'];
    }

    $sanitized['position'] = in_array( $input['position'], ['before_content', 'after_content'] ) ? $input['position'] : 'after_content';
    $sanitized['default_style'] = in_array( $input['default_style'], $sanitized['styles'] ) ? $input['default_style'] : $sanitized['styles'][0];
    $sanitized['background_color'] = sanitize_hex_color( $input['background_color'] );
    $sanitized['border_color'] = sanitize_hex_color( $input['border_color'] );
    $sanitized['text_color'] = sanitize_hex_color( $input['text_color'] );

    if ( is_array( $input['post_types'] ) ) {
        $sanitized['post_types'] = array_map('sanitize_key', $input['post_types']);
    } else {
        $sanitized['post_types'] = $defaults['post_types'];
    }

    return $sanitized;
}

function wpa_citation_section_callback() {
    echo '<p>' . esc_html__( 'Customize the citation settings.', 'wp-academic-post-enhanced' ) . '</p>';
}

function wpa_citation_pdf_section_callback() {
    echo '<p>' . esc_html__( 'Configure the PDF download functionality.', 'wp-academic-post-enhanced' ) . '</p>';
}

function wpa_citation_enabled_field_callback() {
    $options = get_option( 'wpa_citation_settings' );
    $enabled = isset($options['enabled']) ? $options['enabled'] : true;
    echo '<input type="checkbox" name="wpa_citation_settings[enabled]" value="1" ' . checked( 1, $enabled, false ) . ' />';
}

function wpa_citation_pdf_download_field_callback() {
    $options = get_option( 'wpa_citation_settings' );
    $enabled = isset($options['pdf_download_enabled']) ? $options['pdf_download_enabled'] : false;
    echo '<input type="checkbox" name="wpa_citation_settings[pdf_download_enabled]" value="1" ' . checked( 1, $enabled, false ) . ' /> ' . __( 'Enable "Download PDF" button (Opens Print-Ready View)', 'wp-academic-post-enhanced' );
}

function wpa_citation_pdf_post_types_field_callback() {
    $options = get_option('wpa_citation_settings');
    $post_types = get_post_types(['public' => true], 'objects');
    $selected_post_types = isset($options['pdf_post_types']) ? $options['pdf_post_types'] : ['post'];

    foreach ($post_types as $post_type) {
        echo '<label><input type="checkbox" name="wpa_citation_settings[pdf_post_types][]" value="' . esc_attr($post_type->name) . '" ' . checked(in_array($post_type->name, $selected_post_types), true, false) . ' /> ' . esc_html($post_type->label) . '</label><br />';
    }
}

function wpa_citation_pdf_elements_field_callback() {
    $options = get_option( 'wpa_citation_settings' );
    $defaults = ['header', 'footer', 'cover_title', 'cover_meta', 'cover_citation'];
    $selected = isset($options['pdf_elements']) ? $options['pdf_elements'] : $defaults;
    
    $elements = [
        'header' => __( 'Header (Title & Page Num)', 'wp-academic-post-enhanced' ),
        'footer' => __( 'Footer (Site Info)', 'wp-academic-post-enhanced' ),
        'cover_title' => __( 'First Page: Title', 'wp-academic-post-enhanced' ),
        'cover_meta' => __( 'First Page: Author & Date', 'wp-academic-post-enhanced' ),
        'cover_citation' => __( 'First Page: Citation Box', 'wp-academic-post-enhanced' ),
    ];

    foreach ( $elements as $key => $label ) {
        echo '<label style="margin-right: 15px;"><input type="checkbox" name="wpa_citation_settings[pdf_elements][]" value="' . esc_attr( $key ) . '" ' . checked( in_array( $key, $selected ), true, false ) . ' /> ' . esc_html( $label ) . '</label><br>';
    }
}

function wpa_citation_pdf_font_field_callback() {
    $options = get_option( 'wpa_citation_settings' );
    $font = isset($options['pdf_font']) ? $options['pdf_font'] : 'lateef';
    ?>
    <select name="wpa_citation_settings[pdf_font]">
        <option value="lateef" <?php selected( $font, 'lateef' ); ?>>Lateef (Classic Arabic)</option>
        <option value="amiri" <?php selected( $font, 'amiri' ); ?>>Amiri (Academic Arabic/Latin)</option>
        <option value="notoarabic" <?php selected( $font, 'notoarabic' ); ?>>Noto Naskh Arabic</option>
        <option value="notosans" <?php selected( $font, 'notosans' ); ?>>Noto Sans (Universal)</option>
        <option value="notoserif" <?php selected( $font, 'notoserif' ); ?>>Noto Serif (Universal)</option>
    </select>
    <p class="description"><?php esc_html_e( 'Select the font for the PDF. Noto and Amiri provide the best multilingual support. "Amiri" is recommended for academic papers.', 'wp-academic-post-enhanced' ); ?></p>
    <?php
}

function wpa_citation_pdf_appearance_section_callback() {
    echo '<p>' . esc_html__( 'Customize the look of the generated PDF.', 'wp-academic-post-enhanced' ) . '</p>';
}

function wpa_citation_pdf_text_color_field_callback() {
    $options = get_option( 'wpa_citation_settings' );
    $val = isset($options['pdf_text_color']) ? $options['pdf_text_color'] : '#111111';
    echo '<input type="text" name="wpa_citation_settings[pdf_text_color]" value="' . esc_attr( $val ) . '" class="wpa-color-picker" />';
}

function wpa_citation_pdf_heading_color_field_callback() {
    $options = get_option( 'wpa_citation_settings' );
    $val = isset($options['pdf_heading_color']) ? $options['pdf_heading_color'] : '#000000';
    echo '<input type="text" name="wpa_citation_settings[pdf_heading_color]" value="' . esc_attr( $val ) . '" class="wpa-color-picker" />';
}

function wpa_citation_pdf_link_color_field_callback() {
    $options = get_option( 'wpa_citation_settings' );
    $val = isset($options['pdf_link_color']) ? $options['pdf_link_color'] : '#0000EE';
    echo '<input type="text" name="wpa_citation_settings[pdf_link_color]" value="' . esc_attr( $val ) . '" class="wpa-color-picker" />';
}

function wpa_citation_pdf_citation_bg_color_field_callback() {
    $options = get_option( 'wpa_citation_settings' );
    $val = isset($options['pdf_citation_bg_color']) ? $options['pdf_citation_bg_color'] : '#fcfcfc';
    echo '<input type="text" name="wpa_citation_settings[pdf_citation_bg_color]" value="' . esc_attr( $val ) . '" class="wpa-color-picker" />';
}

function wpa_citation_pdf_watermark_text_field_callback() {
    $options = get_option( 'wpa_citation_settings' );
    $val = isset($options['pdf_watermark_text']) ? $options['pdf_watermark_text'] : '';
    echo '<input type="text" name="wpa_citation_settings[pdf_watermark_text]" value="' . esc_attr( $val ) . '" class="regular-text" placeholder="' . esc_attr__( 'e.g. DRAFT or CONFIDENTIAL', 'wp-academic-post-enhanced' ) . '" />';
    echo '<p class="description">' . esc_html__( 'Leave empty to disable.', 'wp-academic-post-enhanced' ) . '</p>';
}

function wpa_citation_title_field_callback() {
    $options = get_option( 'wpa_citation_settings' );
    $title = isset($options['title']) ? $options['title'] : 'Cite this article';
    echo '<input type="text" name="wpa_citation_settings[title]" value="' . esc_attr( $title ) . '" class="regular-text" />';
}

function wpa_citation_post_types_field_callback() {
    $options = get_option('wpa_citation_settings');
    $post_types = get_post_types(['public' => true], 'objects');
    $selected_post_types = isset($options['post_types']) ? $options['post_types'] : ['post'];

    foreach ($post_types as $post_type) {
        echo '<label><input type="checkbox" name="wpa_citation_settings[post_types][]" value="' . esc_attr($post_type->name) . '" ' . checked(in_array($post_type->name, $selected_post_types), true, false) . ' /> ' . esc_html($post_type->label) . '</label><br />';
    }
}

function wpa_citation_styles_field_callback() {
    $options = get_option( 'wpa_citation_settings' );
    $selected_styles = isset($options['styles']) ? $options['styles'] : ['apa', 'mla'];
    $styles = [
        'apa' => 'APA',
        'mla' => 'MLA',
        'chicago' => 'Chicago',
        'harvard' => 'Harvard',
        'ieee' => 'IEEE',
        'ama' => 'AMA',
    ];
    foreach ( $styles as $key => $label ) {
        echo '<label><input type="checkbox" name="wpa_citation_settings[styles][]" value="' . esc_attr( $key ) . '" ' . checked( in_array( $key, $selected_styles ), true, false ) . ' /> ' . esc_html( $label ) . '</label><br />';
    }
}

function wpa_citation_position_field_callback() {
    $options = get_option( 'wpa_citation_settings' );
    $position = isset($options['position']) ? $options['position'] : 'after_content';
    ?>
    <select name="wpa_citation_settings[position]">
        <option value="after_content" <?php selected( $position, 'after_content' ); ?>><?php esc_html_e( 'After Content', 'wp-academic-post-enhanced' ); ?></option>
        <option value="before_content" <?php selected( $position, 'before_content' ); ?>><?php esc_html_e( 'Before Content', 'wp-academic-post-enhanced' ); ?></option>
    </select>
    <?php
}

function wpa_citation_default_style_field_callback() {
    $options = get_option( 'wpa_citation_settings' );
    $default_style = isset($options['default_style']) ? $options['default_style'] : 'apa';
    $styles = isset($options['styles']) ? $options['styles'] : ['apa', 'mla'];
    ?>
    <select name="wpa_citation_settings[default_style]">
        <?php foreach ( $styles as $style ) : ?>
            <option value="<?php echo esc_attr( $style ); ?>" <?php selected( $default_style, $style ); ?>><?php echo esc_html( strtoupper( $style ) ); ?></option>
        <?php endforeach; ?>
    </select>
    <?php
}

function wpa_citation_appearance_section_callback() {
    echo '<p>' . esc_html__( 'Customize the appearance of the citation block.', 'wp-academic-post-enhanced' ) . '</p>';
}

function wpa_citation_background_color_field_callback() {
    $options = get_option( 'wpa_citation_settings' );
    $background_color = isset($options['background_color']) ? $options['background_color'] : '#f9f9f9';
    echo '<input type="text" name="wpa_citation_settings[background_color]" value="' . esc_attr( $background_color ) . '" class="wpa-color-picker" />';
}

function wpa_citation_border_color_field_callback() {
    $options = get_option( 'wpa_citation_settings' );
    $border_color = isset($options['border_color']) ? $options['border_color'] : '#ddd';
    echo '<input type="text" name="wpa_citation_settings[border_color]" value="' . esc_attr( $border_color ) . '" class="wpa-color-picker" />';
}

function wpa_citation_text_color_field_callback() {
    $options = get_option( 'wpa_citation_settings' );
    $text_color = isset($options['text_color']) ? $options['text_color'] : '#333';
    echo '<input type="text" name="wpa_citation_settings[text_color]" value="' . esc_attr( $text_color ) . '" class="wpa-color-picker" />';
}