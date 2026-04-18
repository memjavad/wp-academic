<?php
/**
 * Author Subtitle feature.
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Display author subtitle.
 *
 * @param string $content The post content.
 * @return string
 */
function wp_academic_post_enhanced_display_author_subtitle( $content ) {
    $author_subtitle_enabled = get_option( 'wp_academic_post_enhanced_author_subtitle_enabled', true );
    
    if ( is_single() && in_the_loop() && is_main_query() ) {
        $subtitle_html = '';
        if ( $author_subtitle_enabled ) {
            $subtitle = get_post_meta( get_the_ID(), '_wp_academic_post_enhanced_author_subtitle', true );
            if ( ! empty( $subtitle ) ) {
                $subtitle_html = '<p class="wpa-author-subtitle">' . esc_html( $subtitle ) . '</p>';
            }
        }

        $content = $subtitle_html . $content;
    }
    return $content;
}
add_filter( 'the_content', 'wp_academic_post_enhanced_display_author_subtitle', 5 );
