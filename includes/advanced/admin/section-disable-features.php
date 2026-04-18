<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

add_settings_section('wp_academic_post_enhanced_disable_features_section', __('Disable WordPress Features', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_disable_features_section_callback', 'wp_academic_post_enhanced_disable_features');

add_settings_field('wp_academic_post_enhanced_disable_comments_field', __('Disable Comments', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_disable_comments_field_callback', 'wp_academic_post_enhanced_disable_features', 'wp_academic_post_enhanced_disable_features_section');
add_settings_field('wp_academic_post_enhanced_disable_pages_field', __('Disable Pages', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_disable_pages_field_callback', 'wp_academic_post_enhanced_disable_features', 'wp_academic_post_enhanced_disable_features_section');
add_settings_field('wp_academic_post_enhanced_disable_xmlrpc_field', __('Disable XML-RPC', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_disable_xmlrpc_field_callback', 'wp_academic_post_enhanced_disable_features', 'wp_academic_post_enhanced_disable_features_section');
add_settings_field('wp_academic_post_enhanced_disable_rest_api_field', __('Disable REST API for non-logged-in users', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_disable_rest_api_field_callback', 'wp_academic_post_enhanced_disable_features', 'wp_academic_post_enhanced_disable_features_section');
add_settings_field('wp_academic_post_enhanced_disable_feeds_field', __('Disable Feeds', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_disable_feeds_field_callback', 'wp_academic_post_enhanced_disable_features', 'wp_academic_post_enhanced_disable_features_section');
add_settings_field('wp_academic_post_enhanced_disable_emoji_field', __('Disable Emoji', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_disable_emoji_field_callback', 'wp_academic_post_enhanced_disable_features', 'wp_academic_post_enhanced_disable_features_section');
add_settings_field('wp_academic_post_enhanced_disable_embeds_field', __('Disable Embeds', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_disable_embeds_field_callback', 'wp_academic_post_enhanced_disable_features', 'wp_academic_post_enhanced_disable_features_section');
add_settings_field('wp_academic_post_enhanced_disable_jquery_migrate_field', __('Disable jQuery Migrate', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_disable_jquery_migrate_field_callback', 'wp_academic_post_enhanced_disable_features', 'wp_academic_post_enhanced_disable_features_section');
add_settings_field('wp_academic_post_enhanced_disable_gutenberg_css_frontend_field', __('Disable Gutenberg CSS (Frontend)', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_disable_gutenberg_css_frontend_field_callback', 'wp_academic_post_enhanced_disable_features', 'wp_academic_post_enhanced_disable_features_section');
add_settings_field('wp_academic_post_enhanced_disable_search_field', __('Disable Search', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_disable_search_field_callback', 'wp_academic_post_enhanced_disable_features', 'wp_academic_post_enhanced_disable_features_section');

function wp_academic_post_enhanced_disable_features_section_callback() {
    echo '<p>' . esc_html__( 'Select the WordPress features you want to disable.', 'wp-academic-post-enhanced' ) . '</p>';
}

function wp_academic_post_enhanced_disable_comments_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_disable_comments', false );
    echo '<input type="checkbox" name="wp_academic_post_enhanced_disable_comments" value="1" ' . checked( 1, $option, false ) . ' />';
}

function wp_academic_post_enhanced_disable_pages_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_disable_pages', false );
    echo '<input type="checkbox" name="wp_academic_post_enhanced_disable_pages" value="1" ' . checked( 1, $option, false ) . ' />';
}

function wp_academic_post_enhanced_disable_xmlrpc_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_disable_xmlrpc', false );
    echo '<input type="checkbox" name="wp_academic_post_enhanced_disable_xmlrpc" value="1" ' . checked( 1, $option, false ) . ' />';
}

function wp_academic_post_enhanced_disable_rest_api_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_disable_rest_api', false );
    echo '<input type="checkbox" name="wp_academic_post_enhanced_disable_rest_api" value="1" ' . checked( 1, $option, false ) . ' />';
}

function wp_academic_post_enhanced_disable_feeds_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_disable_feeds', false );
    echo '<input type="checkbox" name="wp_academic_post_enhanced_disable_feeds" value="1" ' . checked( 1, $option, false ) . ' />';
}

function wp_academic_post_enhanced_disable_emoji_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_disable_emoji', false );
    echo '<input type="checkbox" name="wp_academic_post_enhanced_disable_emoji" value="1" ' . checked( 1, $option, false ) . ' />
';
}

function wp_academic_post_enhanced_disable_embeds_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_disable_embeds', false );
    echo '<input type="checkbox" name="wp_academic_post_enhanced_disable_embeds" value="1" ' . checked( 1, $option, false ) . ' />';
}

function wp_academic_post_enhanced_disable_jquery_migrate_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_disable_jquery_migrate', false );
    echo '<input type="checkbox" name="wp_academic_post_enhanced_disable_jquery_migrate" value="1" ' . checked( 1, $option, false ) . ' />';
}

function wp_academic_post_enhanced_disable_gutenberg_css_frontend_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_disable_gutenberg_css_frontend', false );
    echo '<input type="checkbox" name="wp_academic_post_enhanced_disable_gutenberg_css_frontend" value="1" ' . checked( 1, $option, false ) . ' />';
}

function wp_academic_post_enhanced_disable_search_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_disable_search', false );
    echo '<input type="checkbox" name="wp_academic_post_enhanced_disable_search" value="1" ' . checked( 1, $option, false ) . ' />';
    echo '<p class="description">' . esc_html__( 'Disables the WordPress search functionality. Search queries will be redirected to the homepage.', 'wp-academic-post-enhanced' ) . '</p>';
}
