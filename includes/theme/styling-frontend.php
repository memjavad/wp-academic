<?php
/**
 * Frontend Styling Logic (Integrated into Theme Module)
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Apply heading styles to the front-end.
 */
function wpa_apply_heading_styles() {
    $options = get_option( 'wpa_heading_styling_settings' );

    if ( empty( $options ) || empty( $options['targeted_headings'] ) ) {
        return;
    }

    $color = isset( $options['color'] ) ? $options['color'] : '#000000';
    $color_dark = isset( $options['color_dark'] ) ? $options['color_dark'] : '#ffffff';
    $font_size = isset( $options['font_size'] ) ? $options['font_size'] : '24';
    $font_weight = isset( $options['font_weight'] ) ? $options['font_weight'] : 'bold';
    $text_decoration = isset( $options['text_decoration'] ) ? $options['text_decoration'] : 'none';
    $text_shadow_offset_x = isset( $options['text_shadow_offset_x'] ) ? $options['text_shadow_offset_x'] : 0;
    $text_shadow_offset_y = isset( $options['text_shadow_offset_y'] ) ? $options['text_shadow_offset_y'] : 0;
    $text_shadow_blur_radius = isset( $options['text_shadow_blur_radius'] ) ? $options['text_shadow_blur_radius'] : 0;
    $text_shadow_color = isset( $options['text_shadow_color'] ) ? $options['text_shadow_color'] : '#000000';
    $targeted_headings = $options['targeted_headings'];

    $selectors = [];
    $dark_selectors = [];
    
    foreach ( $targeted_headings as $tag ) {
        // Universal selectors
        $selectors[] = "body {$tag}";
        $selectors[] = ".entry-content {$tag}";
        $selectors[] = ".wpa-custom-template {$tag}";
        $selectors[] = ".wpa-article-content {$tag}";
        $selectors[] = ".wpa-course-main-content {$tag}";
        $selectors[] = ".wpa-lesson-body {$tag}";
        
        // Dark mode specific
        $dark_selectors[] = "body.wpa-dark-mode {$tag}";
        $dark_selectors[] = "body.wpa-dark-mode .entry-content {$tag}";
        $dark_selectors[] = "body.wpa-dark-mode .wpa-custom-template {$tag}";
    }

    $selector = implode( ', ', $selectors );
    $dark_selector = implode( ', ', $dark_selectors );

    $style_rules = [
        "color: {$color}",
        "font-size: {$font_size}px",
        "font-weight: {$font_weight}",
        "text-decoration: {$text_decoration}",
    ];

    // Construct text-shadow property
    if ( $text_shadow_offset_x !== 0 || $text_shadow_offset_y !== 0 || $text_shadow_blur_radius !== 0 || $text_shadow_color !== '#000000' ) {
        $style_rules[] = "text-shadow: {$text_shadow_offset_x}px {$text_shadow_offset_y}px {$text_shadow_blur_radius}px {$text_shadow_color}";
    }

    $css = "
        <style type='text/css'>
            {$selector} {
                " . implode( '; ', $style_rules ) . " !important;
            }
            {$dark_selector} {
                color: {$color_dark} !important;
            }
        </style>
    ";

    echo $css;
}
add_action( 'wp_head', 'wpa_apply_heading_styles' );

/**
 * Apply styles for frontend features (TOC, Citation, Social) to the front-end.
 */
function wpa_apply_frontend_feature_styles() {
    $css = '';
    $unified_shadow_options = get_option( 'wpa_unified_box_shadow_settings' );

    if ( ! empty( $unified_shadow_options ) ) {
        $css .= '.wpa-feature-box {';
        if ( ! empty( $unified_shadow_options['box_shadow_offset_x'] ) || ! empty( $unified_shadow_options['box_shadow_offset_y'] ) || ! empty( $unified_shadow_options['box_shadow_blur_radius'] ) || ! empty( $unified_shadow_options['box_shadow_color'] ) ) {
            $css .= 'box-shadow:' . esc_attr( $unified_shadow_options['box_shadow_offset_x'] ) . 'px ' . esc_attr( $unified_shadow_options['box_shadow_offset_y'] ) . 'px ' . esc_attr( $unified_shadow_options['box_shadow_blur_radius'] ) . 'px ' . esc_attr( $unified_shadow_options['box_shadow_color'] ) . ';';
        }
        $css .= '}';
    }

    // TOC Styles
    $toc_options = get_option( 'wpa_toc_styling_settings' );
    if ( ! empty( $toc_options ) ) {
        $css .= '.wpa-toc-container {';
        if ( ! empty( $toc_options['background_color'] ) ) {
            $css .= 'background-color:' . esc_attr( $toc_options['background_color'] ) . ';';
        }
        if ( ! empty( $toc_options['border_color'] ) ) {
            $css .= 'border-color:' . esc_attr( $toc_options['border_color'] ) . ';';
        }
        if ( ! empty( $toc_options['container_padding_top_bottom'] ) && ! empty( $toc_options['container_padding_left_right'] ) ) {
            $css .= 'padding:' . esc_attr( $toc_options['container_padding_top_bottom'] ) . 'px ' . esc_attr( $toc_options['container_padding_left_right'] ) . 'px;';
        }
        if ( ! empty( $toc_options['container_border_radius'] ) ) {
            $css .= 'border-radius:' . esc_attr( $toc_options['container_border_radius'] ) . 'px;';
        }
        $css .= '}';

        $css .= '.wpa-toc-container .wpa-feature-box-title {';
        if ( ! empty( $toc_options['title_color'] ) ) {
            $css .= 'color:' . esc_attr( $toc_options['title_color'] ) . ';';
        }
        if ( ! empty( $toc_options['title_font_size'] ) ) {
            $css .= 'font-size:' . esc_attr( $toc_options['title_font_size'] ) . 'px;';
        }
        $css .= '}';

        $css .= '.wpa-toc-container ul {';
        if ( ! empty( $toc_options['list_style_type'] ) ) {
            $css .= 'list-style-type:' . esc_attr( $toc_options['list_style_type'] ) . ';';
        }
        $css .= '}';

        $css .= '.wpa-toc-container ul li a {';
        if ( ! empty( $toc_options['link_color'] ) ) {
            $css .= 'color:' . esc_attr( $toc_options['link_color'] ) . ';';
        }
        if ( ! empty( $toc_options['list_font_size'] ) ) {
            $css .= 'font-size:' . esc_attr( $toc_options['list_font_size'] ) . 'px;';
        }
        $css .= '}';
    }

    // Citation Styles
    $citation_options = get_option( 'wpa_citation_styling_settings' );
    if ( ! empty( $citation_options ) ) {
        $css .= 'div.wpa-citation {';
        if ( ! empty( $citation_options['background_color'] ) ) {
            $css .= 'background-color:' . esc_attr( $citation_options['background_color'] ) . ';';
        }
        if ( ! empty( $citation_options['border_color'] ) ) {
            $css .= 'border-color:' . esc_attr( $citation_options['border_color'] ) . ';';
        }
        $css .= '}';
        $css .= 'div.wpa-citation .wpa-feature-box-title, .wpa-citation .wpa-citation-tab, .wpa-citation .wpa-citation-content {';
        if ( ! empty( $citation_options['text_color'] ) ) {
            $css .= 'color:' . esc_attr( $citation_options['text_color'] ) . ';';
        }
        $css .= '}';
    }

    // Social Styles
    $social_options = get_option( 'wpa_social_styling_settings' );
    if ( ! empty( $social_options ) ) {
        $css .= 'div.wpa-social-sharing {';
        if ( ! empty( $social_options['background_color'] ) ) {
            $css .= 'background-color:' . esc_attr( $social_options['background_color'] ) . ';';
        }
        if ( ! empty( $social_options['border_color'] ) ) {
            $css .= 'border-color:' . esc_attr( $social_options['border_color'] ) . ';';
        }
        $css .= '}';
        $css .= 'div.wpa-social-sharing .wpa-feature-box-title {';
        if ( ! empty( $social_options['text_color'] ) ) {
            $css .= 'color:' . esc_attr( $social_options['text_color'] ) . ';';
        }
        $css .= '}';
    }

    if ( ! empty( $css ) ) {
        echo "<style type='text/css'>{$css}</style>";
    }
}
add_action( 'wp_head', 'wpa_apply_frontend_feature_styles' );
