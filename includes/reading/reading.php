<?php
/**
 * Reading Experience feature (Reading Time + Reading Progress).
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Calculate reading time.
 *
 * @param string $content The post content.
 * @return int Reading time in minutes.
 */
function wpa_calculate_reading_time( $content ) {
    $clean_content = strip_tags( $content );
    // Multibyte-safe word count (supports Arabic, Cyrillic, etc.)
    $words = preg_split( '/[\s\p{P}]+/u', $clean_content, -1, PREG_SPLIT_NO_EMPTY );
    $word_count = is_array( $words ) ? count( $words ) : 0;

    // Add CJK character count (Chinese/Japanese/Korean)
    $cjk_count = preg_match_all( '/[\p{Han}\p{Hiragana}\p{Katakana}\p{Hangul}]/u', $clean_content );
    if ( $cjk_count > 0 ) {
        $word_count += $cjk_count;
    }

    $reading_speed = apply_filters( 'wpa_reading_speed', 200 ); // Words per minute
    $reading_time = ceil( $word_count / max( 1, $reading_speed ) );
    return max( 1, $reading_time );
}

/**
 * Add reading time to post content or meta.
 *
 * @param string $content The post content.
 * @return string The modified content.
 */
function wpa_add_reading_time( $content ) {
    $options = get_option( 'wpa_reading_settings' );
    $defaults = [
        'time_enabled' => true,
        'time_label' => 'min read',
        'time_position' => 'before_content',
        'post_types' => ['post', 'wpa_course', 'wpa_lesson', 'wpa_news'],
    ];
    $options = wp_parse_args( $options, $defaults );

    $current_post_type = get_post_type();
    if ( ! $options['time_enabled'] || ! is_singular() || ! is_main_query() || ! in_array( $current_post_type, $options['post_types'] ) ) {
        return $content;
    }

    $reading_time = wpa_calculate_reading_time( get_post_field( 'post_content', get_the_ID() ) );
    
    $html = '<span class="wpa-reading-time">';
    $html .= '<span class="wpa-reading-time-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg></span>';
    $html .= ' ' . esc_html( $reading_time ) . ' ' . esc_html( $options['time_label'] );
    $html .= '</span>';

    if ( $options['time_position'] === 'before_content' ) {
        return $html . $content;
    }

    return $content;
}

/**
 * Add Text Resizer controls.
 *
 * @param string $content The post content.
 * @return string The modified content.
 */
function wpa_add_text_resizer( $content ) {
    $options = get_option( 'wpa_reading_settings' );
    $defaults = [
        'resizer_enabled' => false,
        'resizer_position' => 'before_content',
        'resizer_content_selector' => '.entry-content',
        'post_types' => ['post', 'wpa_course', 'wpa_lesson', 'wpa_news'],
    ];
    $options = wp_parse_args( $options, $defaults );

    $current_post_type = get_post_type();
    if ( ! $options['resizer_enabled'] || ! is_singular() || ! is_main_query() || ! in_array( $current_post_type, $options['post_types'] ) ) {
        return $content;
    }

    $selector = ! empty( $options['resizer_content_selector'] ) ? esc_attr( $options['resizer_content_selector'] ) : '.entry-content';

    $controls = '<div class="wpa-text-resizer" data-content-selector="' . $selector . '">';
    $controls .= '<span class="wpa-text-resizer-label">' . esc_html__( 'Text Size:', 'wp-academic-post-enhanced' ) . '</span>';
    $controls .= '<button type="button" class="wpa-text-resizer-btn wpa-resizer-decrease" aria-label="' . esc_attr__( 'Decrease font size', 'wp-academic-post-enhanced' ) . '">A-</button>';
    $controls .= '<button type="button" class="wpa-text-resizer-btn wpa-resizer-reset" aria-label="' . esc_attr__( 'Reset font size', 'wp-academic-post-enhanced' ) . '">A</button>';
    $controls .= '<button type="button" class="wpa-text-resizer-btn wpa-resizer-increase" aria-label="' . esc_attr__( 'Increase font size', 'wp-academic-post-enhanced' ) . '">A+</button>';
    $controls .= '</div>';

    if ( $options['resizer_position'] === 'before_content' ) {
        return $controls . $content;
    }

    return $content;
}

/**
 * Add progress bar HTML to footer.
 */
function wpa_add_reading_progress_html() {
    $options = get_option( 'wpa_reading_settings' );
    $defaults = [
        'progress_enabled' => true,
        'progress_position' => 'top',
        'post_types' => ['post', 'wpa_course', 'wpa_lesson', 'wpa_news'],
    ];
    $options = wp_parse_args( $options, $defaults );

    $current_post_type = get_post_type();
    if ( is_singular() && $options['progress_enabled'] && in_array( $current_post_type, $options['post_types'] ) ) {
        $position = isset( $options['progress_position'] ) ? $options['progress_position'] : 'top';
        echo '<div id="wpa-reading-progress-container" class="wpa-progress-' . esc_attr( $position ) . '"><div id="wpa-reading-progress-bar"></div></div>';
    }
}

/**
 * Enqueue reading experience assets.
 */
function wpa_reading_assets() {
    $options = get_option( 'wpa_reading_settings' );
    $defaults = [
        'time_enabled' => true,
        'progress_enabled' => true,
        'progress_color' => '#2563eb',
        'progress_height' => '4',
        'resizer_enabled' => false,
        'resizer_btn_color' => '#1d2327',
        'resizer_btn_bg_color' => '#f0f0f1',
        'post_types' => ['post', 'wpa_course', 'wpa_lesson', 'wpa_news'],
    ];
    $options = wp_parse_args( $options, $defaults );

    $current_post_type = get_post_type();
    if ( ! is_singular() || ! in_array( $current_post_type, $options['post_types'] ) ) {
        return;
    }

    // Reading Time Styles
    if ( $options['time_enabled'] ) {
        wp_enqueue_style( 'wpa-reading-time', plugin_dir_url( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'assets/css/reading-time.css', [], '1.0' );
    }

    // Text Resizer Assets
    if ( $options['resizer_enabled'] ) {
        wp_enqueue_style( 'wpa-text-resizer', plugin_dir_url( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'assets/css/text-resizer.css', [], WPA_VERSION );
        
        $btn_color = $options['resizer_btn_color'];
        $btn_bg_color = $options['resizer_btn_bg_color'];
        $custom_resizer_css = ":root { --wpa-resizer-btn-color: {$btn_color}; --wpa-resizer-btn-bg: {$btn_bg_color}; }";
        wp_add_inline_style( 'wpa-text-resizer', $custom_resizer_css );

        wp_enqueue_script( 'wpa-text-resizer', plugin_dir_url( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'assets/js/text-resizer.js', [], WPA_VERSION, true );
    }

    // Reading Progress Styles & Scripts
    if ( $options['progress_enabled'] ) {
        wp_enqueue_style( 'wpa-reading-progress', plugin_dir_url( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'assets/css/reading-progress.css', [], '1.0' );
        wp_enqueue_script( 'wpa-reading-progress', plugin_dir_url( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'assets/js/reading-progress.js', [], '1.0', true );
        
        $color = isset( $options['progress_color'] ) ? $options['progress_color'] : '#2563eb';
        $height = isset( $options['progress_height'] ) ? $options['progress_height'] : '4';
        
        $custom_css = ":root { --wpa-progress-color: {$color}; --wpa-progress-height: {$height}px; }";
        wp_add_inline_style( 'wpa-reading-progress', $custom_css );
    }
}

// Only hook if the feature is enabled (Safety Check)
if ( get_option( 'wpa_reading_enabled' ) ) {
    add_filter( 'the_content', 'wpa_add_reading_time', 10 );
    add_filter( 'the_content', 'wpa_add_text_resizer', 15 );
    add_action( 'wp_footer', 'wpa_add_reading_progress_html' );
    add_action( 'wp_enqueue_scripts', 'wpa_reading_assets' );
}
