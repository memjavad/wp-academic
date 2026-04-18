<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

require_once plugin_dir_path( __FILE__ ) . 'class-toc-engine.php';

/**
 * Add the table of contents to the beginning of the post content.
 *
 * @param string $content The post content.
 * @return string The modified post content.
 */
function wp_academic_post_enhanced_add_toc( $content ) {
    $options = get_option( 'wpa_toc_settings' );
    $defaults = [
        'enabled' => true,
        'title' => 'Table of Contents',
        'allowed_headings' => ['h1', 'h2', 'h3'],
        'collapsible' => false,
        'position' => 'before_first_heading',
        'min_headings' => 2,
        'post_types' => ['post', 'wpa_course', 'wpa_lesson', 'wpa_news'],
    ];
    $options = wp_parse_args( $options, $defaults );

    $current_post_type = get_post_type();
    // Removed is_main_query() check to support templates that call the_content() in specialized ways (like Lessons)
    if ( ! $options['enabled'] || ! is_singular() || ! in_array( $current_post_type, $options['post_types'] ) ) {
        return $content;
    }

    // Instantiate Engine
    $engine = new WPA_TOC_Engine( $content, $options );
    
    // Get Modified Content (with IDs)
    $content = $engine->get_content();
    
    // Get TOC HTML
    $toc_html = $engine->get_html();

    if ( empty( $toc_html ) ) {
        return $content;
    }

    // Suppress automatic injection for news pages as they use a sidebar widget
    if ( $current_post_type === 'wpa_news' ) {
        return $content;
    }

    // Apply positioning
    if ( 'before_first_heading' === $options['position'] ) {
        // Regex to find first heading (h1-h6) regardless of allowed list, so we position correctly
        if ( preg_match( '/<h[1-6][^>]*>/i', $content, $match, PREG_OFFSET_CAPTURE ) ) {
            $content = substr_replace( $content, $toc_html, $match[0][1], 0 );
        } else {
            $content = $toc_html . $content;
        }
    } elseif ( 'after_first_paragraph' === $options['position'] ) {
        if ( preg_match( '/<\/p>/i', $content, $match, PREG_OFFSET_CAPTURE ) ) {
            $content = substr_replace( $content, $toc_html, $match[0][1] + 4, 0 );
        } else {
            $content = $toc_html . $content;
        }
    } else { // 'top'
        $content = $toc_html . $content;
    }
    
    return $content;
}
add_filter( 'the_content', 'wp_academic_post_enhanced_add_toc', 5 );

/**
 * Enqueue TOC assets.
 */
function wp_academic_post_enhanced_enqueue_toc_assets() {
    $toc_options = get_option( 'wpa_toc_settings' );
    $defaults = [
        'enabled' => true,
        'post_types' => ['post', 'wpa_course', 'wpa_lesson', 'wpa_news'],
    ];
    $toc_options = wp_parse_args( $toc_options, $defaults );

    $current_post_type = get_post_type();
    if ( $toc_options['enabled'] && is_singular() && in_array( $current_post_type, $toc_options['post_types'] ) ) {
        
        // 1. Enqueue TOC Styles
        wp_enqueue_style(
            'wpa-toc-css',
            plugin_dir_url( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'assets/css/toc.css',
            [],
            WPA_VERSION
        );

        // 2. Enqueue TOC Logic (Collapsible + Scroll Spy)
        wp_enqueue_script(
            'wpa-toc-js',
            plugin_dir_url( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'assets/js/toc.js',
            [],
            WPA_VERSION,
            true
        );
    }
}
add_action( 'wp_enqueue_scripts', 'wp_academic_post_enhanced_enqueue_toc_assets' );

/**
 * SEO Fix: Strip TOC markup from excerpts and meta descriptions.
 *
 * The TOC engine injects reading-time badges (e.g. <span class="wpa-toc-time">1m</span>)
 * into the_content at priority 5. When WordPress or SEO plugins auto-generate
 * the excerpt/meta description from content, they strip HTML tags but keep the
 * inner text "1m", resulting in poisoned descriptions like:
 * "المحتويات: الفروق الفردية1m الجذور التاريخية1m..."
 *
 * This filter cleans the excerpt by:
 * 1. Removing the entire TOC container div
 * 2. Removing any leftover wpa-toc-time spans
 * 3. Cleaning up any orphaned "Nm" patterns (e.g. "1m", "12m")
 */
function wpa_strip_toc_from_excerpt( $excerpt ) {
    if ( empty( $excerpt ) ) {
        return $excerpt;
    }

    // 1. Remove the entire TOC container block (HTML)
    $excerpt = preg_replace( '/<div[^>]*class="[^"]*wpa-toc-container[^"]*"[^>]*>.*?<\/div>\s*(?:<\/div>)*/is', '', $excerpt );

    // 2. Remove any wpa-toc-time spans (in case they're outside the container)
    $excerpt = preg_replace( '/<span[^>]*class="[^"]*wpa-toc-time[^"]*"[^>]*>.*?<\/span>/is', '', $excerpt );

    // 3. Clean up orphaned reading-time text patterns (plain text "1m", "2m", etc.
    //    that remain after HTML stripping). Only match standalone Nm patterns.
    $excerpt = preg_replace( '/(?<=[^\d])\d{1,2}m(?=[^\w]|$)/u', '', $excerpt );

    // 4. Clean up extra whitespace
    $excerpt = preg_replace( '/\s{2,}/', ' ', trim( $excerpt ) );

    return $excerpt;
}
add_filter( 'get_the_excerpt', 'wpa_strip_toc_from_excerpt', 5 );
add_filter( 'wp_trim_excerpt', 'wpa_strip_toc_from_excerpt', 5 );
