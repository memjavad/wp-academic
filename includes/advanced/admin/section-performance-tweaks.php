<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

add_settings_section('wp_academic_post_enhanced_performance_section', __('Performance Tweaks', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_performance_tweaks_section_callback', 'wp_academic_post_enhanced_performance_tweaks');

add_settings_field('wp_academic_post_enhanced_heartbeat_field', __('Heartbeat Control', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_heartbeat_field_callback', 'wp_academic_post_enhanced_performance_tweaks', 'wp_academic_post_enhanced_performance_section');
add_settings_field('wp_academic_post_enhanced_post_revisions_field', __('Post Revisions Control', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_post_revisions_field_callback', 'wp_academic_post_enhanced_performance_tweaks', 'wp_academic_post_enhanced_performance_section');
add_settings_field('wp_academic_post_enhanced_disable_self_pings_field', __('Disable Self Pings', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_disable_self_pings_field_callback', 'wp_academic_post_enhanced_performance_tweaks', 'wp_academic_post_enhanced_performance_section');
add_settings_field('wp_academic_post_enhanced_remove_capital_p_dangit_field', __('Remove capital_P_dangit Filter', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_remove_capital_p_dangit_field_callback', 'wp_academic_post_enhanced_performance_tweaks', 'wp_academic_post_enhanced_performance_section');
add_settings_field('wp_academic_post_enhanced_remove_query_strings_field', __('Remove Query Strings', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_remove_query_strings_field_callback', 'wp_academic_post_enhanced_performance_tweaks', 'wp_academic_post_enhanced_performance_section');
add_settings_field('wp_academic_post_enhanced_disable_gravatars_field', __('Disable Gravatars', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_disable_gravatars_field_callback', 'wp_academic_post_enhanced_performance_tweaks', 'wp_academic_post_enhanced_performance_section');
add_settings_field('wp_academic_post_enhanced_defer_javascript_field', __('Defer Parsing of JavaScript', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_defer_javascript_field_callback', 'wp_academic_post_enhanced_performance_tweaks', 'wp_academic_post_enhanced_performance_section');
add_settings_field('wp_academic_post_enhanced_remove_x_pingback_field', __('Remove X-Pingback Header', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_remove_x_pingback_field_callback', 'wp_academic_post_enhanced_performance_tweaks', 'wp_academic_post_enhanced_performance_section');
add_settings_field('wp_academic_post_enhanced_disable_google_fonts_field', __('Disable Google Fonts', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_disable_google_fonts_field_callback', 'wp_academic_post_enhanced_performance_tweaks', 'wp_academic_post_enhanced_performance_section');
add_settings_field('wp_academic_post_enhanced_disable_font_awesome_field', __('Disable Font Awesome', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_disable_font_awesome_field_callback', 'wp_academic_post_enhanced_performance_tweaks', 'wp_academic_post_enhanced_performance_section');
add_settings_field('wp_academic_post_enhanced_clean_up_header_field', __('Clean Up Header', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_clean_up_header_field_callback', 'wp_academic_post_enhanced_performance_tweaks', 'wp_academic_post_enhanced_performance_section');
add_settings_field('wp_academic_post_enhanced_preload_requests_field', __('Preload Critical Requests', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_preload_requests_field_callback', 'wp_academic_post_enhanced_performance_tweaks', 'wp_academic_post_enhanced_performance_section');

add_settings_field('wp_academic_post_enhanced_dns_prefetch_field', __('DNS Prefetching', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_dns_prefetch_field_callback', 'wp_academic_post_enhanced_performance_tweaks', 'wp_academic_post_enhanced_performance_section');
add_settings_field('wp_academic_post_enhanced_disable_google_maps_field', __('Disable Google Maps', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_disable_google_maps_field_callback', 'wp_academic_post_enhanced_performance_tweaks', 'wp_academic_post_enhanced_performance_section');

add_settings_field('wp_academic_post_enhanced_image_compression_field', __('Image Compression Quality', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_image_compression_field_callback', 'wp_academic_post_enhanced_performance_tweaks', 'wp_academic_post_enhanced_performance_section');
add_settings_field('wp_academic_post_enhanced_disable_wc_cart_fragmentation_field', __('Disable WooCommerce Cart Fragmentation', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_disable_wc_cart_fragmentation_field_callback', 'wp_academic_post_enhanced_performance_tweaks', 'wp_academic_post_enhanced_performance_section');
add_settings_field('wp_academic_post_enhanced_404_parent_redirect_field', __('404 Parent Redirection', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_404_parent_redirect_field_callback', 'wp_academic_post_enhanced_performance_tweaks', 'wp_academic_post_enhanced_performance_section');

function wp_academic_post_enhanced_performance_tweaks_section_callback() {

    echo '<p>' . esc_html__( 'Fine-tune WordPress performance by disabling or modifying certain features.', 'wp-academic-post-enhanced' ) . '</p>';
}

function wp_academic_post_enhanced_heartbeat_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_heartbeat', 'default' );
    ?>
    <select name="wp_academic_post_enhanced_heartbeat">
        <option value="default" <?php selected( $option, 'default' ); ?>><?php esc_html_e( 'Default', 'wp-academic-post-enhanced' ); ?></option>
        <option value="disable" <?php selected( $option, 'disable' ); ?>><?php esc_html_e( 'Disable Everywhere', 'wp-academic-post-enhanced' ); ?></option>
        <option value="60" <?php selected( $option, '60' ); ?>><?php esc_html_e( '60 seconds', 'wp-academic-post-enhanced' ); ?></option>
    </select>
    <p class="description"><?php esc_html_e( 'Control the WordPress Heartbeat API. Disabling it may affect plugins that rely on it.', 'wp-academic-post-enhanced' ); ?></p>
    <?php
}

function wp_academic_post_enhanced_post_revisions_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_post_revisions', 'default' );
    ?>
    <select name="wp_academic_post_enhanced_post_revisions">
        <option value="default" <?php selected( $option, 'default' ); ?>><?php esc_html_e( 'Default (Enable)', 'wp-academic-post-enhanced' ); ?></option>
        <option value="disable" <?php selected( $option, 'disable' ); ?>><?php esc_html_e( 'Disable', 'wp-academic-post-enhanced' ); ?></option>
        <option value="3" <?php selected( $option, '3' ); ?>><?php esc_html_e( 'Limit to 3 Revisions', 'wp-academic-post-enhanced' ); ?></option>
        <option value="5" <?php selected( $option, '5' ); ?>><?php esc_html_e( 'Limit to 5 Revisions', 'wp-academic-post-enhanced' ); ?></option>
    </select>
    <p class="description"><?php esc_html_e( 'Control the number of post revisions stored in the database.', 'wp-academic-post-enhanced' ); ?></p>
    <?php
}

function wp_academic_post_enhanced_disable_self_pings_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_disable_self_pings', false );
    echo '<input type="checkbox" name="wp_academic_post_enhanced_disable_self_pings" value="1" ' . checked( 1, $option, false ) . ' />';
    echo '<p class="description">' . esc_html__( 'Disable self pings (pingbacks to your own site).', 'wp-academic-post-enhanced' ) . '</p>';
}

function wp_academic_post_enhanced_remove_capital_p_dangit_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_remove_capital_p_dangit', false );
    echo '<input type="checkbox" name="wp_academic_post_enhanced_remove_capital_p_dangit" value="1" ' . checked( 1, $option, false ) . ' />';
    echo '<p class="description">' . esc_html__( 'Remove the filter that corrects "Wordpress" to "WordPress".', 'wp-academic-post-enhanced' ) . '</p>';
}

function wp_academic_post_enhanced_remove_query_strings_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_remove_query_strings', false );
    echo '<input type="checkbox" name="wp_academic_post_enhanced_remove_query_strings" value="1" ' . checked( 1, $option, false ) . ' />';
    echo '<p class="description">' . esc_html__( 'Remove version query strings from static resources (e.g., ?ver=1.0) to improve caching.', 'wp-academic-post-enhanced' ) . '</p>';
}

function wp_academic_post_enhanced_disable_gravatars_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_disable_gravatars', false );
    echo '<input type="checkbox" name="wp_academic_post_enhanced_disable_gravatars" value="1" ' . checked( 1, $option, false ) . ' />';
    echo '<p class="description">' . esc_html__( 'Disables Gravatars and replaces them with a local default avatar.', 'wp-academic-post-enhanced' ) . '</p>';
}

function wp_academic_post_enhanced_defer_javascript_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_defer_javascript', false );
    echo '<input type="checkbox" name="wp_academic_post_enhanced_defer_javascript" value="1" ' . checked( 1, $option, false ) . ' />';
    echo '<p class="description">' . esc_html__( 'Adds the "defer" attribute to script tags to improve page load time. This may cause issues with some plugins.', 'wp-academic-post-enhanced' ) . '</p>';
}

function wp_academic_post_enhanced_remove_x_pingback_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_remove_x_pingback', false );
    echo '<input type="checkbox" name="wp_academic_post_enhanced_remove_x_pingback" value="1" ' . checked( 1, $option, false ) . ' />';
    echo '<p class="description">' . esc_html__( 'Remove the X-Pingback header from the HTTP headers.', 'wp-academic-post-enhanced' ) . '</p>';
}

function wp_academic_post_enhanced_disable_google_fonts_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_disable_google_fonts', false );
    echo '<input type="checkbox" name="wp_academic_post_enhanced_disable_google_fonts" value="1" ' . checked( 1, $option, false ) . ' />';
    echo '<p class="description">' . esc_html__( 'Prevents Google Fonts from loading on your site.', 'wp-academic-post-enhanced' ) . '</p>';
}

function wp_academic_post_enhanced_disable_font_awesome_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_disable_font_awesome', false );
    echo '<input type="checkbox" name="wp_academic_post_enhanced_disable_font_awesome" value="1" ' . checked( 1, $option, false ) . ' />';
    echo '<p class="description">' . esc_html__( 'Prevents Font Awesome from loading on your site.', 'wp-academic-post-enhanced' ) . '</p>';
}



function wp_academic_post_enhanced_clean_up_header_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_clean_up_header', false );
    echo '<input type="checkbox" name="wp_academic_post_enhanced_clean_up_header" value="1" ' . checked( 1, $option, false ) . ' />';
    echo '<p class="description">' . esc_html__( 'Removes unnecessary links from the WordPress header, such as the adjacent posts links.', 'wp-academic-post-enhanced' ) . '</p>';
}

function wp_academic_post_enhanced_sanitize_textarea( $input ) {
    return implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $input ) ) );
}

function wp_academic_post_enhanced_preload_requests_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_preload_requests', '' );
    echo '<textarea name="wp_academic_post_enhanced_preload_requests" rows="5" cols="50" class="large-text" placeholder="' . esc_attr__( 'Enter one URL per line', 'wp-academic-post-enhanced' ) . '">' . esc_textarea( $option ) . '</textarea>';
    echo '<p class="description">' . esc_html__( 'Specify URLs to be preloaded. This can speed up the loading of critical resources like fonts or CSS files.', 'wp-academic-post-enhanced' ) . '</p>';
}

function wp_academic_post_enhanced_dns_prefetch_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_dns_prefetch', '' );
    echo '<textarea name="wp_academic_post_enhanced_dns_prefetch" rows="5" cols="50" class="large-text" placeholder="' . esc_attr__( 'Enter one domain per line, without http(s)://', 'wp-academic-post-enhanced' ) . '">' . esc_textarea( $option ) . '</textarea>';
    echo '<p class="description">' . esc_html__( 'Specify external domains to prefetch. This can speed up loading of resources from those domains.', 'wp-academic-post-enhanced' ) . '</p>';
}

function wp_academic_post_enhanced_disable_google_maps_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_disable_google_maps', false );
    echo '<input type="checkbox" name="wp_academic_post_enhanced_disable_google_maps" value="1" ' . checked( 1, $option, false ) . ' />';
    echo '<p class="description">' . esc_html__( 'Prevents Google Maps API from loading on your site.', 'wp-academic-post-enhanced' ) . '</p>';
}

function wp_academic_post_enhanced_image_compression_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_image_compression', 90 ); // Default to 90
    ?>
    <input type="number" name="wp_academic_post_enhanced_image_compression" value="<?php echo esc_attr( $option ); ?>" min="0" max="100" step="5" />
    <p class="description"><?php esc_html_e( 'Set the JPEG image compression quality (0-100). Lower values result in smaller file sizes but lower quality.', 'wp-academic-post-enhanced' ); ?></p>
    <?php
}

function wp_academic_post_enhanced_disable_wc_cart_fragmentation_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_disable_wc_cart_fragmentation', false );
    echo '<input type="checkbox" name="wp_academic_post_enhanced_disable_wc_cart_fragmentation" value="1" ' . checked( 1, $option, false ) . ' />';
    echo '<p class="description">' . esc_html__( 'Disables WooCommerce cart fragmentation AJAX requests on non-WooCommerce pages.', 'wp-academic-post-enhanced' ) . '</p>';
}

function wp_academic_post_enhanced_remove_wp_version_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_remove_wp_version', false );
    echo '<input type="checkbox" name="wp_academic_post_enhanced_remove_wp_version" value="1" ' . checked( 1, $option, false ) . ' />';
    echo '<p class="description">' . esc_html__( 'Removes the WordPress version number from the head and RSS feeds.', 'wp-academic-post-enhanced' ) . '</p>';
}

function wp_academic_post_enhanced_404_parent_redirect_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_404_parent_redirect', false );
    echo '<input type="checkbox" name="wp_academic_post_enhanced_404_parent_redirect" value="1" ' . checked( 1, $option, false ) . ' />';
    echo '<p class="description">' . esc_html__( 'Automatically redirect 404 errors to the parent URL (301 Permanent Redirect).', 'wp-academic-post-enhanced' ) . '</p>';
}

