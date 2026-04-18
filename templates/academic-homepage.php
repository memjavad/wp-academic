<?php
/**
 * Custom Academic Homepage Template (Builder Version)
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Load Builder if not already loaded (Frontend check)
if ( ! class_exists( 'WPA_Theme_Builder' ) ) {
    $builder_path = plugin_dir_path( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'includes/theme/class-theme-builder.php';
    if ( file_exists( $builder_path ) ) {
        require_once $builder_path;
    }
}

// Ensure template functions are loaded if accessed directly via template include
if ( ! function_exists( 'wpa_get_header' ) ) {
    $funcs_path = plugin_dir_path( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'includes/theme/template-functions.php';
    if ( file_exists( $funcs_path ) ) {
        require_once $funcs_path;
    }
}

$options = get_option( 'wpa_homepage_settings' );
$use_custom_header = ! empty( $options['enable_custom_header'] );
$use_custom_footer = ! empty( $options['enable_custom_footer'] );

// Check for Global Override
if ( true ) { // Enforced consistency
    $use_custom_header = true;
    $use_custom_footer = true;
}

// --- HEADER ---
if ( $use_custom_header && function_exists( 'wpa_get_header' ) ) {
    wpa_get_header();
} else {
    get_header();
}
?>

<div class="wpa-homepage-wrapper">
    <?php 
    if ( class_exists( 'WPA_Theme_Builder' ) ) {
        $layout = get_option( 'wpa_homepage_layout', [] );
        if ( is_string( $layout ) ) $layout = json_decode( $layout, true );
        if ( ! is_array( $layout ) ) $layout = [];
        
        // Debug
        if ( current_user_can('manage_options') ) {
            echo '<!-- WPA Builder Debug: Layout Count: ' . count($layout) . ' -->';
        }
        
        WPA_Theme_Builder::get_instance()->render_layout(); 
    } else {
        echo '<div class="wpa-container"><p>' . esc_html__( 'Theme Builder class not found.', 'wp-academic-post-enhanced' ) . '</p></div>';
    }
    ?>
</div>

<?php 
// --- FOOTER ---
if ( $use_custom_footer && function_exists( 'wpa_get_footer' ) ) {
    wpa_get_footer();
} else {
    get_footer();
}
?>