<?php
/**
 * Admin Main Page View
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap wp-academic-post-enhanced-main-page">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <p><?php esc_html_e( 'Welcome to WP Academic Post Enhanced. Manage your features below.', 'wp-academic-post-enhanced' ); ?></p>
    
    <div class="feature-cards-container">
<?php
$features = [
    'citation' => [
        'name' => __( 'Citation', 'wp-academic-post-enhanced' ),
        'description' => __( 'Add academic citation styles to your posts.', 'wp-academic-post-enhanced' ),
        'slug' => 'wp-academic-post-enhanced-citation',
        'enabled_option' => 'wp_academic_post_enhanced_citation_enabled',
    ],
    'toc' => [
        'name' => __( 'Table of Contents', 'wp-academic-post-enhanced' ),
        'description' => __( 'Automatically generate a table of contents for your posts.', 'wp-academic-post-enhanced' ),
        'slug' => 'wp-academic-post-enhanced-toc',
        'enabled_option' => 'wp_academic_post_enhanced_toc_enabled',
    ],
    'schema' => [
        'name' => __( 'Schema Markup', 'wp-academic-post-enhanced' ),
        'description' => __( 'Add structured data (Schema.org) to your posts for better SEO.', 'wp-academic-post-enhanced' ),
        'slug' => 'wp-academic-post-enhanced-schema',
        'enabled_option' => 'wp_academic_post_enhanced_schema_enabled',
    ],
    'social' => [
        'name' => __( 'Social Sharing', 'wp-academic-post-enhanced' ),
        'description' => __( 'Add social sharing buttons to your posts.', 'wp-academic-post-enhanced' ),
        'slug' => 'wp-academic-post-enhanced-social',
        'enabled_option' => 'wpa_social_enabled',
    ],
    'advanced' => [
        'name' => __( 'Performance', 'wp-academic-post-enhanced' ),
        'description' => __( 'Manage performance related features like disabling comments, feeds, and more.', 'wp-academic-post-enhanced' ),
        'slug' => 'wp-academic-post-enhanced-performance',
        'enabled_option' => 'wp_academic_post_enhanced_advanced_enabled',
    ],
    'smtp' => [
        'name' => __( 'SMTP', 'wp-academic-post-enhanced' ),
        'description' => __( 'Configure SMTP for reliable email sending.', 'wp-academic-post-enhanced' ),
        'slug' => 'wp-academic-post-enhanced-smtp',
        'enabled_option' => 'wp_academic_post_enhanced_smtp_enabled',
    ],
    'reading' => [
        'name' => __( 'Reading Experience', 'wp-academic-post-enhanced' ),
        'description' => __( 'Display estimated reading time and a progress bar.', 'wp-academic-post-enhanced' ),
        'slug' => 'wp-academic-post-enhanced-reading',
        'enabled_option' => 'wpa_reading_enabled',
    ],
    'course' => [
        'name' => __( 'Course Management', 'wp-academic-post-enhanced' ),
        'description' => __( 'Create and manage courses and lessons.', 'wp-academic-post-enhanced' ),
        'slug' => 'wp-academic-post-enhanced-course',
        'enabled_option' => 'wpa_course_enabled',
    ],
    'field_news' => [
        'name' => __( 'Field News AI', 'wp-academic-post-enhanced' ),
        'description' => __( 'Generate news stories from latest studies using AI.', 'wp-academic-post-enhanced' ),
        'slug' => 'wp-academic-post-enhanced-field-news',
        'enabled_option' => 'wpa_field_news_enabled',
    ],
    'theme' => [
        'name' => __( 'Custom Theme', 'wp-academic-post-enhanced' ),
        'description' => __( 'Global theme settings and custom homepage builder.', 'wp-academic-post-enhanced' ),
        'slug' => 'wp-academic-post-enhanced-homepage',
        'enabled_option' => 'wpa_homepage_enabled',
    ],
    'glossary' => [
        'name' => __( 'Glossary', 'wp-academic-post-enhanced' ),
        'description' => __( 'Create and manage glossaries with auto-linking and tooltips.', 'wp-academic-post-enhanced' ),
        'slug' => 'wpa-glossary-settings',
        'enabled_option' => 'wpa_glossary_enabled',
    ],
    'sitemap' => [
        'name' => __( 'Academic XML Sitemap', 'wp-academic-post-enhanced' ),
        'description' => __( 'Standalone, high-performance sitemap for research and news indexing.', 'wp-academic-post-enhanced' ),
        'slug' => 'wp-academic-post-enhanced-performance#group-sitemap',
        'enabled_option' => 'wp_academic_post_enhanced_advanced_enabled',
    ],
];
?>
<?php foreach ( $features as $key => $feature ) : ?>
                <?php
                $is_enabled = (bool) get_option( $feature['enabled_option'], false );
                 
                $status_class = $is_enabled ? 'status-active' : 'status-inactive';
                $status_text = $is_enabled ? __( 'Active', 'wp-academic-post-enhanced' ) : __( 'Inactive', 'wp-academic-post-enhanced' );
                $toggle_text = $is_enabled ? __( 'Deactivate', 'wp-academic-post-enhanced' ) : __( 'Activate', 'wp-academic-post-enhanced' );
                ?>
                <div class="feature-card <?php echo esc_attr( $status_class ); ?>">
                    <h2><?php echo esc_html( $feature['name'] ); ?></h2>
                    <p class="description"><?php echo esc_html( $feature['description'] ); ?></p>
                    <div class="card-footer">
                        <span class="feature-status <?php echo esc_attr( $status_class ); ?>"><?php echo esc_html( $status_text ); ?></span>
                        <div class="card-actions">
                            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                                <input type="hidden" name="action" value="wp_academic_post_enhanced_toggle_feature">
                                <input type="hidden" name="feature_key" value="<?php echo esc_attr( $key ); ?>">
                                <input type="hidden" name="enabled_option" value="<?php echo esc_attr( $feature['enabled_option'] ); ?>">
                                <input type="hidden" name="current_status" value="<?php echo esc_attr( $is_enabled ? '1' : '0' ); ?>">
                                <?php wp_nonce_field( 'wp_academic_post_enhanced_toggle_feature_' . $key, '_wpnonce_wp_academic_post_enhanced_toggle_feature' ); ?>
                                <?php $aria_label_toggle = sprintf( $is_enabled ? __( 'Deactivate %s', 'wp-academic-post-enhanced' ) : __( 'Activate %s', 'wp-academic-post-enhanced' ), $feature['name'] ); ?>
                                <button type="submit" class="button <?php echo $is_enabled ? 'button-secondary' : 'button-primary'; ?>" aria-label="<?php echo esc_attr( $aria_label_toggle ); ?>">
                                    <?php echo esc_html( $toggle_text ); ?>
                                </button>
                            </form>
                            <?php $aria_label_settings = sprintf( __( 'Settings for %s', 'wp-academic-post-enhanced' ), $feature['name'] ); ?>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $feature['slug'] ) ); ?>" class="button" aria-label="<?php echo esc_attr( $aria_label_settings ); ?>">
                                <?php esc_html_e( 'Settings', 'wp-academic-post-enhanced' ); ?>
                            </a>
                        </div>
                    </div>
                </div>
        <?php endforeach; ?>
    </div>
</div>
