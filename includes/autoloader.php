<?php
/**
 * Autoloader for the WP Academic Post Enhanced plugin.
 *
 * @package WP_Academic_Post_Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * An array of feature slugs and their corresponding file paths.
 *
 * @var array
 */
$features = [
    'author' => 'author/author.php',
    'citation' => 'citation/citation.php',
    'schema' => 'schema/schema.php',
    'social' => 'social/social.php',
    'toc' => 'toc/toc.php',
    'smtp' => 'smtp/smtp.php',
    'advanced' => 'advanced/advanced.php',
    'reading' => 'reading/reading.php',
    'course' => 'course/course.php',
    'field_news' => 'field-news/field-news.php',
    'theme' => 'theme/theme.php',
    'glossary' => 'glossary/glossary.php',
];

/**
 * An array of admin-related feature slugs and their corresponding file paths.
 *
 * @var array
 */
$admin_features = [
    'author' => 'author/author-admin.php',
    'citation' => 'citation/citation-admin.php',
    'schema' => 'schema/schema-admin.php',
    'social' => 'social/social-admin.php',
    'toc' => 'toc/toc-admin.php',
    'smtp' => 'smtp/smtp-admin.php',
    'advanced' => 'advanced/advanced-admin.php', // Use unified performance page
    'reading' => 'reading/reading-admin.php',
    'course' => 'course/course-admin.php',
    'field_news' => 'field-news/field-news-admin.php',
    'theme' => 'theme/theme-admin.php',
    'glossary' => 'glossary/glossary-admin.php',
];

/**
 * An array of option keys for each feature.
 *
 * @var array
 */
$feature_options = [
    'author' => 'wp_academic_post_enhanced_author_enabled',
    'citation' => 'wp_academic_post_enhanced_citation_enabled',
    'schema' => 'wp_academic_post_enhanced_schema_enabled',
    'social' => 'wpa_social_enabled',
    'toc' => 'wp_academic_post_enhanced_toc_enabled',
    'smtp' => 'wp_academic_post_enhanced_smtp_enabled',
    'advanced' => 'wp_academic_post_enhanced_advanced_enabled',
    'reading' => 'wpa_reading_enabled',
    'course' => 'wpa_course_enabled',
    'field_news' => 'wpa_field_news_enabled',
    'theme' => 'wpa_homepage_enabled',
    'glossary' => 'wpa_glossary_enabled',
];

/**
 * Loads the plugin features based on whether they are enabled in the settings.
 */
function wp_academic_post_enhanced_autoloader() {
    global $features, $admin_features, $feature_options;

    $plugin_dir = plugin_dir_path( __FILE__ );

    // Core Classes Required by multiple features (Labels & Icons always needed for various templates)
    require_once $plugin_dir . 'theme/class-theme-labels.php';
    require_once $plugin_dir . 'theme/class-icons.php';

    // Load frontend features
    foreach ( $features as $key => $file ) {
        $option_name = isset( $feature_options[ $key ] ) ? $feature_options[ $key ] : '';
        if ( ! empty( $option_name ) && get_option( $option_name ) ) {
            require_once $plugin_dir . $file;
        } elseif ( empty( $option_name ) ) {
            // Load features that don't have an enable/disable option
            require_once $plugin_dir . $file;
        }
    }

    // Load admin features
    if ( is_admin() ) {
        foreach ( $admin_features as $key => $file ) {
            $option_name = isset( $feature_options[ $key ] ) ? $feature_options[ $key ] : '';
            if ( ! empty( $option_name ) && get_option( $option_name ) ) {
                require_once $plugin_dir . $file;
            } elseif ( empty( $option_name ) ) {
                // Load admin features that don't have an enable/disable option
                require_once $plugin_dir . $file;
            }
        }
    }
}
add_action( 'plugins_loaded', 'wp_academic_post_enhanced_autoloader' );
