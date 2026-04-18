<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

add_settings_section('wp_academic_post_enhanced_server_optimization_section', __('Lightspeed Optimization', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_server_optimization_section_callback', 'wp_academic_post_enhanced_server_optimization');

add_settings_field('wp_academic_post_enhanced_esi_enabled_field', __('Enable ESI', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_esi_enabled_field_callback', 'wp_academic_post_enhanced_server_optimization', 'wp_academic_post_enhanced_server_optimization_section');
add_settings_field('wp_academic_post_enhanced_image_optimization_enabled_field', __('Enable Image Optimization', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_image_optimization_enabled_field_callback', 'wp_academic_post_enhanced_server_optimization', 'wp_academic_post_enhanced_server_optimization_section');
add_settings_field('wp_academic_post_enhanced_gzip_compression_enabled_field', __('Enable Gzip Compression', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_gzip_compression_enabled_field_callback', 'wp_academic_post_enhanced_server_optimization', 'wp_academic_post_enhanced_server_optimization_section');
add_settings_field('wp_academic_post_enhanced_http2_push_enabled_field', __('Enable HTTP/2 Push', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_http2_push_enabled_field_callback', 'wp_academic_post_enhanced_server_optimization', 'wp_academic_post_enhanced_server_optimization_section');
add_settings_field('wp_academic_post_enhanced_keep_alive_enabled_field', __('Enable Keep-Alive', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_keep_alive_enabled_field_callback', 'wp_academic_post_enhanced_server_optimization', 'wp_academic_post_enhanced_server_optimization_section');
add_settings_field('wp_academic_post_enhanced_hotlink_protection_enabled_field', __('Enable Hotlink Protection', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_hotlink_protection_enabled_field_callback', 'wp_academic_post_enhanced_server_optimization', 'wp_academic_post_enhanced_server_optimization_section');
add_settings_field('wp_academic_post_enhanced_vary_accept_encoding_enabled_field', __('Enable Vary: Accept-Encoding Header', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_vary_accept_encoding_enabled_field_callback', 'wp_academic_post_enhanced_server_optimization', 'wp_academic_post_enhanced_server_optimization_section');

function wp_academic_post_enhanced_server_optimization_section_callback() {
    echo '<p>' . esc_html__( 'Configure Lightspeed server optimizations. ', 'wp-academic-post-enhanced' ) . '<strong>' . esc_html__( 'Please make a backup of your .htaccess file before enabling these features.', 'wp-academic-post-enhanced' ) . '</strong></p>';
}

function wp_academic_post_enhanced_esi_enabled_field_callback() {
    $options = get_option( 'wp_academic_post_enhanced_server_optimization_options', [] );
    $option = isset( $options['esi_enabled'] ) ? $options['esi_enabled'] : false;
    echo '<input type="checkbox" name="wp_academic_post_enhanced_server_optimization_options[esi_enabled]" value="1" ' . checked( 1, $option, false ) . ' />';
}

function wp_academic_post_enhanced_image_optimization_enabled_field_callback() {
    $options = get_option( 'wp_academic_post_enhanced_server_optimization_options', [] );
    $option = isset( $options['image_optimization_enabled'] ) ? $options['image_optimization_enabled'] : false;
    echo '<input type="checkbox" name="wp_academic_post_enhanced_server_optimization_options[image_optimization_enabled]" value="1" ' . checked( 1, $option, false ) . ' />';
}

function wp_academic_post_enhanced_gzip_compression_enabled_field_callback() {
    $options = get_option( 'wp_academic_post_enhanced_server_optimization_options', [] );
    $option = isset( $options['gzip_compression_enabled'] ) ? $options['gzip_compression_enabled'] : false;
    echo '<input type="checkbox" name="wp_academic_post_enhanced_server_optimization_options[gzip_compression_enabled]" value="1" ' . checked( 1, $option, false ) . ' />';
}

function wp_academic_post_enhanced_http2_push_enabled_field_callback() {
    $options = get_option( 'wp_academic_post_enhanced_server_optimization_options', [] );
    $option = isset( $options['http2_push_enabled'] ) ? $options['http2_push_enabled'] : false;
    echo '<input type="checkbox" name="wp_academic_post_enhanced_server_optimization_options[http2_push_enabled]" value="1" ' . checked( 1, $option, false ) . ' />';
}

function wp_academic_post_enhanced_keep_alive_enabled_field_callback() {
    $options = get_option( 'wp_academic_post_enhanced_server_optimization_options', [] );
    $option = isset( $options['keep_alive_enabled'] ) ? $options['keep_alive_enabled'] : false;
    echo '<input type="checkbox" name="wp_academic_post_enhanced_server_optimization_options[keep_alive_enabled]" value="1" ' . checked( 1, $option, false ) . ' />';
}

function wp_academic_post_enhanced_hotlink_protection_enabled_field_callback() {
    $options = get_option( 'wp_academic_post_enhanced_server_optimization_options', [] );
    $option = isset( $options['hotlink_protection_enabled'] ) ? $options['hotlink_protection_enabled'] : false;
    echo '<input type="checkbox" name="wp_academic_post_enhanced_server_optimization_options[hotlink_protection_enabled]" value="1" ' . checked( 1, $option, false ) . ' />';
}

function wp_academic_post_enhanced_vary_accept_encoding_enabled_field_callback() {
    $options = get_option( 'wp_academic_post_enhanced_server_optimization_options', [] );
    $option = isset( $options['vary_accept_encoding_enabled'] ) ? $options['vary_accept_encoding_enabled'] : false;
    echo '<input type="checkbox" name="wp_academic_post_enhanced_server_optimization_options[vary_accept_encoding_enabled]" value="1" ' . checked( 1, $option, false ) . ' />';
}