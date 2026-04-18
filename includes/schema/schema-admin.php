<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Add the Schema submenu page.
 */
function wp_academic_post_enhanced_add_schema_admin_menu() {
    $enabled = get_option( 'wp_academic_post_enhanced_schema_enabled', true );
    if ( $enabled ) {
        add_submenu_page(
            'wp-academic-post-enhanced',
            __( 'Schema Settings', 'wp-academic-post-enhanced' ),
            __( 'Schema', 'wp-academic-post-enhanced' ),
            'manage_options',
            'wp-academic-post-enhanced-schema',
            'wp_academic_post_enhanced_schema_page'
        );
    }
}
add_action( 'admin_menu', 'wp_academic_post_enhanced_add_schema_admin_menu' );

/**
 * Display the Schema settings page.
 */
function wp_academic_post_enhanced_schema_page() {
    ?>
    <div class="wrap wpa-settings-wrapper">
        <div class="wpa-header">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        </div>
        
        <h2 class="nav-tab-wrapper">
            <a href="#general" class="nav-tab nav-tab-active"><?php esc_html_e( 'General', 'wp-academic-post-enhanced' ); ?></a>
            <a href="#post-type-settings" class="nav-tab"><?php esc_html_e( 'Post Types', 'wp-academic-post-enhanced' ); ?></a>
            <a href="#person-settings" class="nav-tab"><?php esc_html_e( 'Person', 'wp-academic-post-enhanced' ); ?></a>
            <a href="#scholarly-settings" class="nav-tab"><?php esc_html_e( 'Scholarly', 'wp-academic-post-enhanced' ); ?></a>
            <a href="#toc-settings" class="nav-tab"><?php esc_html_e( 'TOC', 'wp-academic-post-enhanced' ); ?></a>
            <a href="#rating-settings" class="nav-tab"><?php esc_html_e( 'Rating', 'wp-academic-post-enhanced' ); ?></a>
        </h2>

        <div class="wpa-card">
            <form action="options.php" method="post">
                <?php
                settings_fields( 'wp_academic_post_enhanced_schema_options' );
                ?>

                <div id="general" class="tab-content active">
                    <?php 
                    wpa_render_specific_section( 'wp_academic_post_enhanced_schema_enable', 'wp_academic_post_enhanced_schema_enable_section' );
                    wpa_render_specific_section( 'wp_academic_post_enhanced_schema_sitewide', 'wp_academic_post_enhanced_schema_sitewide_section' ); 
                    ?>
                </div>

                <div id="post-type-settings" class="tab-content">
                    <?php wpa_render_specific_section( 'wp_academic_post_enhanced_schema_post_types', 'wp_academic_post_enhanced_schema_post_types_section' ); ?>
                </div>

                <div id="person-settings" class="tab-content">
                    <?php wpa_render_specific_section( 'wp_academic_post_enhanced_schema_person', 'wp_academic_post_enhanced_schema_person_section' ); ?>
                </div>

                <div id="scholarly-settings" class="tab-content">
                    <?php wpa_render_specific_section( 'wp_academic_post_enhanced_schema_scholarly', 'wp_academic_post_enhanced_schema_scholarly_section' ); ?>
                </div>

                <div id="toc-settings" class="tab-content">
                    <?php wpa_render_specific_section( 'wp_academic_post_enhanced_schema_toc', 'wp_academic_post_enhanced_schema_toc_section' ); ?>
                </div>

                <div id="rating-settings" class="tab-content">
                    <?php wpa_render_specific_section( 'wp_academic_post_enhanced_schema_rating', 'wp_academic_post_enhanced_schema_rating_section' ); ?>
                </div>

                <?php submit_button(); ?>
            </form>
        </div>
    </div>
    <?php
}

/**
 * Enqueue scripts for the schema page.
 */
function wp_academic_post_enhanced_enqueue_schema_scripts( $hook_suffix ) {
    if ( 'academic-post_page_wp-academic-post-enhanced-schema' !== $hook_suffix ) {
        return;
    }
    wp_enqueue_script( 'wp-academic-post-enhanced-performance-tabs', plugin_dir_url( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'assets/js/performance-tabs.js', [ 'jquery' ], '1.0', true );
}
add_action( 'admin_enqueue_scripts', 'wp_academic_post_enhanced_enqueue_schema_scripts' );

/**
 * Register Schema settings.
 */
function wp_academic_post_enhanced_register_schema_settings() {
    // Option group
    $option_group = 'wp_academic_post_enhanced_schema_options';

    // General Settings
    register_setting($option_group, 'wp_academic_post_enhanced_schema_enabled', ['type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => true]);

    // Enable/Disable Section
    add_settings_section('wp_academic_post_enhanced_schema_enable_section', __('Enable/Disable Schema', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_schema_enable_section_callback', 'wp_academic_post_enhanced_schema_enable');
    add_settings_field('wp_academic_post_enhanced_schema_enabled_field', __('Enable Schema', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_schema_enabled_field_callback', 'wp_academic_post_enhanced_schema_enable', 'wp_academic_post_enhanced_schema_enable_section');

    // Sitewide Settings
    $sitewide_page = 'wp_academic_post_enhanced_schema_sitewide';
    register_setting($option_group, 'wp_academic_post_enhanced_schema_sitewide_type', ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => 'organization']);
    register_setting($option_group, 'wp_academic_post_enhanced_schema_sitewide_organization_name', ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => get_bloginfo('name')]);
    register_setting($option_group, 'wp_academic_post_enhanced_schema_sitewide_organization_logo', ['type' => 'string', 'sanitize_callback' => 'esc_url_raw', 'default' => '']);
    register_setting($option_group, 'wp_academic_post_enhanced_schema_sitewide_person_name', ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '']);
    register_setting($option_group, 'wp_academic_post_enhanced_schema_sitelinks_searchbox_enabled', ['type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => true]);
    register_setting($option_group, 'wp_academic_post_enhanced_schema_breadcrumbs_enabled', ['type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => true]);
    register_setting($option_group, 'wp_academic_post_enhanced_schema_sitenavigation_enabled', ['type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => true]);
    register_setting($option_group, 'wp_academic_post_enhanced_schema_contactpoint_enabled', ['type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => true]);
    register_setting($option_group, 'wp_academic_post_enhanced_schema_contactpoint_telephone', ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '']);
    register_setting($option_group, 'wp_academic_post_enhanced_schema_contactpoint_email', ['type' => 'string', 'sanitize_callback' => 'sanitize_email', 'default' => '']);
    register_setting($option_group, 'wp_academic_post_enhanced_schema_social_profiles', ['type' => 'string', 'sanitize_callback' => 'wp_academic_post_enhanced_sanitize_social_profiles', 'default' => '']);

    add_settings_section('wp_academic_post_enhanced_schema_sitewide_section', __('Sitewide Schema Settings', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_schema_sitewide_section_callback', $sitewide_page);
    add_settings_field('wp_academic_post_enhanced_schema_sitewide_type_field', __('This Website Represents', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_schema_sitewide_type_field_callback', $sitewide_page, 'wp_academic_post_enhanced_schema_sitewide_section');
    add_settings_field('wp_academic_post_enhanced_schema_sitewide_organization_name_field', __('Organization Name', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_schema_sitewide_organization_name_field_callback', $sitewide_page, 'wp_academic_post_enhanced_schema_sitewide_section');
    add_settings_field('wp_academic_post_enhanced_schema_sitewide_organization_logo_field', __('Organization Logo', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_schema_sitewide_organization_logo_field_callback', $sitewide_page, 'wp_academic_post_enhanced_schema_sitewide_section');
    add_settings_field('wp_academic_post_enhanced_schema_sitewide_person_name_field', __('Person Name', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_schema_sitewide_person_name_field_callback', $sitewide_page, 'wp_academic_post_enhanced_schema_sitewide_section');
    add_settings_field('wp_academic_post_enhanced_schema_sitelinks_searchbox_enabled_field', __('Enable Sitelinks Search Box', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_schema_sitelinks_searchbox_enabled_field_callback', $sitewide_page, 'wp_academic_post_enhanced_schema_sitewide_section');
    add_settings_field('wp_academic_post_enhanced_schema_breadcrumbs_enabled_field', __('Enable Breadcrumbs Schema', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_schema_breadcrumbs_enabled_field_callback', $sitewide_page, 'wp_academic_post_enhanced_schema_sitewide_section');
    add_settings_field('wp_academic_post_enhanced_schema_sitenavigation_enabled_field', __('Enable Site Navigation Schema', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_schema_sitenavigation_enabled_field_callback', $sitewide_page, 'wp_academic_post_enhanced_schema_sitewide_section');
    add_settings_field('wp_academic_post_enhanced_schema_contactpoint_enabled_field', __('Enable Contact Point Schema', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_schema_contactpoint_enabled_field_callback', $sitewide_page, 'wp_academic_post_enhanced_schema_sitewide_section');
    add_settings_field('wp_academic_post_enhanced_schema_contactpoint_telephone_field', __('Telephone', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_schema_contactpoint_telephone_field_callback', $sitewide_page, 'wp_academic_post_enhanced_schema_sitewide_section');
    add_settings_field('wp_academic_post_enhanced_schema_contactpoint_email_field', __('Email', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_schema_contactpoint_email_field_callback', $sitewide_page, 'wp_academic_post_enhanced_schema_sitewide_section');
    add_settings_field('wp_academic_post_enhanced_schema_social_profiles_field', __('Social Profiles', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_schema_social_profiles_field_callback', $sitewide_page, 'wp_academic_post_enhanced_schema_sitewide_section');

    // Post Type Settings
    $post_types_page = 'wp_academic_post_enhanced_schema_post_types';
    register_setting($option_group, 'wp_academic_post_enhanced_schema_post_type_mapping', ['type' => 'array', 'sanitize_callback' => 'wp_academic_post_enhanced_sanitize_schema_post_type_mapping', 'default' => [
        'wpa_course' => 'Course',
        'wpa_news' => 'ScholarlyArticle',
        'wpa_lesson' => 'ScholarlyArticle'
    ]]);
    register_setting($option_group, 'wp_academic_post_enhanced_schema_scholarly_settings', ['type' => 'array', 'sanitize_callback' => 'wp_academic_post_enhanced_sanitize_scholarly_settings', 'default' => []]);
    add_settings_section('wp_academic_post_enhanced_schema_post_types_section', __('Post Type Schema Settings', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_schema_section_callback', $post_types_page);
    add_settings_field('wp_academic_post_enhanced_schema_post_type_mapping_field', __('Post Type Schema Mapping', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_schema_post_type_mapping_field_callback', $post_types_page, 'wp_academic_post_enhanced_schema_post_types_section');

    // Person Settings
    $person_page = 'wp_academic_post_enhanced_schema_person';
    register_setting($option_group, 'wp_academic_post_enhanced_person_name', ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '']);
    register_setting($option_group, 'wp_academic_post_enhanced_person_affiliation_name', ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '']);
    register_setting($option_group, 'wp_academic_post_enhanced_person_sameas', ['type' => 'string', 'sanitize_callback' => 'wp_academic_post_enhanced_sanitize_social_profiles', 'default' => '']);
    register_setting($option_group, 'wp_academic_post_enhanced_person_jobtitle', ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '']);
    register_setting($option_group, 'wp_academic_post_enhanced_person_alumniof', ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '']);
    add_settings_section('wp_academic_post_enhanced_schema_person_section', __('Person Schema Settings', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_schema_person_section_callback', $person_page);
    add_settings_field('wp_academic_post_enhanced_person_name_field', __('Name', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_person_name_field_callback', $person_page, 'wp_academic_post_enhanced_schema_person_section');
    add_settings_field('wp_academic_post_enhanced_person_affiliation_name_field', __('Affiliation', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_person_affiliation_name_field_callback', $person_page, 'wp_academic_post_enhanced_schema_person_section');
    add_settings_field('wp_academic_post_enhanced_person_sameas_field', __('SameAs (Profiles)', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_person_sameas_field_callback', $person_page, 'wp_academic_post_enhanced_schema_person_section');
    add_settings_field('wp_academic_post_enhanced_person_jobtitle_field', __('Job Title', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_person_jobtitle_field_callback', $person_page, 'wp_academic_post_enhanced_schema_person_section');
    add_settings_field('wp_academic_post_enhanced_person_alumniof_field', __('Alumni Of', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_person_alumniof_field_callback', $person_page, 'wp_academic_post_enhanced_schema_person_section');

    // Scholarly Settings
    $scholarly_page = 'wp_academic_post_enhanced_schema_scholarly';
    register_setting($option_group, 'wp_academic_post_enhanced_schema_in_language', ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => 'en']);
    register_setting($option_group, 'wp_academic_post_enhanced_scholarly_issn', ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '']);
    register_setting($option_group, 'wp_academic_post_enhanced_scholarly_is_part_of', ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => get_bloginfo('name')]);
    register_setting($option_group, 'wp_academic_post_enhanced_scholarly_citations', ['type' => 'string', 'sanitize_callback' => 'sanitize_textarea_field', 'default' => '']);
    add_settings_section('wp_academic_post_enhanced_schema_scholarly_section', __('Scholarly Article Settings', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_schema_scholarly_section_callback', $scholarly_page);
    add_settings_field('wp_academic_post_enhanced_schema_in_language_field', __('Article Language', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_schema_in_language_field_callback', $scholarly_page, 'wp_academic_post_enhanced_schema_scholarly_section');
    add_settings_field('wp_academic_post_enhanced_scholarly_issn_field', __('Default ISSN', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_scholarly_issn_field_callback', $scholarly_page, 'wp_academic_post_enhanced_schema_scholarly_section');
    add_settings_field('wp_academic_post_enhanced_scholarly_is_part_of_field', __('Default "Is Part Of"', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_scholarly_is_part_of_field_callback', $scholarly_page, 'wp_academic_post_enhanced_schema_scholarly_section');
    add_settings_field('wp_academic_post_enhanced_scholarly_citations_field', __('Default Citations', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_scholarly_citations_field_callback', $scholarly_page, 'wp_academic_post_enhanced_schema_scholarly_section');

    // TOC Settings
    $toc_page = 'wp_academic_post_enhanced_schema_toc';
    register_setting($option_group, 'wp_academic_post_enhanced_schema_toc_enabled', ['type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => true]);
    register_setting($option_group, 'wp_academic_post_enhanced_schema_toc_headings', ['type' => 'array', 'sanitize_callback' => 'wp_academic_post_enhanced_sanitize_toc_headings', 'default' => ['h1', 'h2', 'h3']]);
    register_setting($option_group, 'wp_academic_post_enhanced_schema_toc_min_headings', ['type' => 'number', 'sanitize_callback' => 'absint', 'default' => 2]);
    add_settings_section('wp_academic_post_enhanced_schema_toc_section', __('TOC Schema Settings', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_schema_toc_section_callback', $toc_page);
    add_settings_field('wp_academic_post_enhanced_schema_toc_enabled_field', __('Enable TOC Schema', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_schema_toc_enabled_field_callback', $toc_page, 'wp_academic_post_enhanced_schema_toc_section');
    add_settings_field('wp_academic_post_enhanced_schema_toc_headings_field', __('Include Headings in TOC Schema', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_schema_toc_headings_field_callback', $toc_page, 'wp_academic_post_enhanced_schema_toc_section');
    add_settings_field('wp_academic_post_enhanced_schema_toc_min_headings_field', __('Minimum Headings for TOC Schema', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_schema_toc_min_headings_field_callback', $toc_page, 'wp_academic_post_enhanced_schema_toc_section');

    // Rating Settings
    $rating_page = 'wp_academic_post_enhanced_schema_rating';
    register_setting($option_group, 'wp_academic_post_enhanced_schema_rating_enabled', ['type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => false]);
    register_setting($option_group, 'wp_academic_post_enhanced_schema_rating_value', ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '5']);
    register_setting($option_group, 'wp_academic_post_enhanced_schema_rating_review_count', ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '1']);
    register_setting($option_group, 'wp_academic_post_enhanced_schema_rating_item_reviewed_type', ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '']);
    add_settings_section('wp_academic_post_enhanced_schema_rating_section', __('Aggregate Rating Settings', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_schema_rating_section_callback', $rating_page);
    add_settings_field('wp_academic_post_enhanced_schema_rating_enabled_field', __('Enable Aggregate Rating', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_schema_rating_enabled_field_callback', $rating_page, 'wp_academic_post_enhanced_schema_rating_section');
    add_settings_field('wp_academic_post_enhanced_schema_rating_value_field', __('Rating Value', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_schema_rating_value_field_callback', $rating_page, 'wp_academic_post_enhanced_schema_rating_section');
    add_settings_field('wp_academic_post_enhanced_schema_rating_review_count_field', __('Review Count', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_schema_rating_review_count_field_callback', $rating_page, 'wp_academic_post_enhanced_schema_rating_section');
    add_settings_field('wp_academic_post_enhanced_schema_rating_item_reviewed_type_field', __('Custom Item Reviewed Type', 'wp-academic-post-enhanced'), 'wp_academic_post_enhanced_schema_rating_item_reviewed_type_field_callback', $rating_page, 'wp_academic_post_enhanced_schema_rating_section');
}
add_action('admin_init', 'wp_academic_post_enhanced_register_schema_settings');

function wp_academic_post_enhanced_schema_enable_section_callback() {
    echo '<p>' . esc_html__( 'Use this setting to turn the Schema feature on or off.', 'wp-academic-post-enhanced' ) . '</p>';
}

function wp_academic_post_enhanced_schema_enabled_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_schema_enabled', true );
    echo '<input type="checkbox" name="wp_academic_post_enhanced_schema_enabled" value="1" ' . checked( 1, $option, false ) . ' />';
}

function wp_academic_post_enhanced_sanitize_social_profiles( $input ) {
    $urls = explode( "\n", $input );
    $sanitized_urls = [];
    foreach ( $urls as $url ) {
        $sanitized_urls[] = esc_url_raw( trim( $url ) );
    }
    return implode( "\n", $sanitized_urls );
}


function wp_academic_post_enhanced_schema_sitewide_section_callback() {
    echo '<p>' . esc_html__( 'Configure sitewide schema settings.', 'wp-academic-post-enhanced' ) . '</p>';
}

function wp_academic_post_enhanced_schema_sitewide_type_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_schema_sitewide_type', 'organization' );
    ?>
    <select id="sitewide-schema-type" name="wp_academic_post_enhanced_schema_sitewide_type">
        <option value="organization" <?php selected( $option, 'organization' ); ?>><?php esc_html_e( 'Organization', 'wp-academic-post-enhanced' ); ?></option>
        <option value="person" <?php selected( $option, 'person' ); ?>><?php esc_html_e( 'Person', 'wp-academic-post-enhanced' ); ?></option>
    </select>
    <?php
}

function wp_academic_post_enhanced_schema_sitewide_organization_name_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_schema_sitewide_organization_name', get_bloginfo( 'name' ) );
    echo '<input type="text" name="wp_academic_post_enhanced_schema_sitewide_organization_name" value="' . esc_attr( $option ) . '" />';
}

function wp_academic_post_enhanced_schema_sitewide_organization_logo_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_schema_sitewide_organization_logo', '' );
    echo '<input type="text" name="wp_academic_post_enhanced_schema_sitewide_organization_logo" value="' . esc_attr( $option ) . '" placeholder="https://example.com/logo.png" />';
}

function wp_academic_post_enhanced_schema_sitewide_person_name_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_schema_sitewide_person_name', '' );
    echo '<input type="text" name="wp_academic_post_enhanced_schema_sitewide_person_name" value="' . esc_attr( $option ) . '" />';
}

function wp_academic_post_enhanced_schema_sitelinks_searchbox_enabled_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_schema_sitelinks_searchbox_enabled', true );
    echo '<input type="checkbox" name="wp_academic_post_enhanced_schema_sitelinks_searchbox_enabled" value="1" ' . checked( 1, $option, false ) . ' />';
}

function wp_academic_post_enhanced_schema_breadcrumbs_enabled_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_schema_breadcrumbs_enabled', true );
    echo '<input type="checkbox" name="wp_academic_post_enhanced_schema_breadcrumbs_enabled" value="1" ' . checked( 1, $option, false ) . ' />';
}

function wp_academic_post_enhanced_schema_sitenavigation_enabled_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_schema_sitenavigation_enabled', true );
    echo '<input type="checkbox" name="wp_academic_post_enhanced_schema_sitenavigation_enabled" value="1" ' . checked( 1, $option, false ) . ' />';
}

function wp_academic_post_enhanced_schema_contactpoint_enabled_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_schema_contactpoint_enabled', true );
    echo '<input type="checkbox" name="wp_academic_post_enhanced_schema_contactpoint_enabled" value="1" ' . checked( 1, $option, false ) . ' />';
}

function wp_academic_post_enhanced_schema_contactpoint_telephone_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_schema_contactpoint_telephone', '' );
    echo '<input type="text" name="wp_academic_post_enhanced_schema_contactpoint_telephone" value="' . esc_attr( $option ) . '" />';
}

function wp_academic_post_enhanced_schema_contactpoint_email_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_schema_contactpoint_email', '' );
    echo '<input type="email" name="wp_academic_post_enhanced_schema_contactpoint_email" value="' . esc_attr( $option ) . '" />';
}

function wp_academic_post_enhanced_schema_social_profiles_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_schema_social_profiles', '' );
    echo '<textarea name="wp_academic_post_enhanced_schema_social_profiles" rows="5" cols="50" placeholder="' . esc_attr__( 'Enter one URL per line', 'wp-academic-post-enhanced' ) . '">' . esc_textarea( $option ) . '</textarea>';
}

function wp_academic_post_enhanced_schema_section_callback() {
    echo '<p>' . esc_html__( 'Configure schema settings for different post types.', 'wp-academic-post-enhanced' ) . '</p>';
}


function wp_academic_post_enhanced_schema_scholarly_section_callback() {
    echo '<p>' . esc_html__( 'These settings apply to post types mapped to the ScholarlyArticle schema.', 'wp-academic-post-enhanced' ) . '</p>';
}

function wp_academic_post_enhanced_schema_person_section_callback() {
    echo '<p>' . esc_html__( 'Configure the global Person schema for the author of the site.', 'wp-academic-post-enhanced' ) . '</p>';
}

function wp_academic_post_enhanced_person_name_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_person_name', '' );
    echo '<input type="text" name="wp_academic_post_enhanced_person_name" value="' . esc_attr( $option ) . '" />';
}

function wp_academic_post_enhanced_person_affiliation_name_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_person_affiliation_name', '' );
    echo '<input type="text" name="wp_academic_post_enhanced_person_affiliation_name" value="' . esc_attr( $option ) . '" />';
}

function wp_academic_post_enhanced_person_sameas_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_person_sameas', '' );
    echo '<textarea name="wp_academic_post_enhanced_person_sameas" rows="5" cols="50" placeholder="' . esc_attr__( 'Enter one URL per line', 'wp-academic-post-enhanced' ) . '">' . esc_textarea( $option ) . '</textarea>';
}

function wp_academic_post_enhanced_person_jobtitle_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_person_jobtitle', '' );
    echo '<input type="text" name="wp_academic_post_enhanced_person_jobtitle" value="' . esc_attr( $option ) . '" />';
}

function wp_academic_post_enhanced_person_alumniof_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_person_alumniof', '' );
    echo '<input type="text" name="wp_academic_post_enhanced_person_alumniof" value="' . esc_attr( $option ) . '" />';
}

function wp_academic_post_enhanced_schema_in_language_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_schema_in_language', 'en' );
    echo '<input type="text" name="wp_academic_post_enhanced_schema_in_language" value="' . esc_attr( $option ) . '" />';
    echo '<p class="description">' . esc_html__( 'Enter the language code for the article (e.g., en, es, fr).', 'wp-academic-post-enhanced' ) . '</p>';
}

function wp_academic_post_enhanced_schema_post_type_mapping_field_callback() {
    $mapping_option = get_option( 'wp_academic_post_enhanced_schema_post_type_mapping', [] );
    $scholarly_option = get_option( 'wp_academic_post_enhanced_schema_scholarly_settings', [] );
    $post_types = get_post_types( ["public" => true], 'objects' );
    $schema_types = ['Article', 'BlogPosting', 'NewsArticle', 'ScholarlyArticle', 'TechArticle', 'Thesis', 'Course', 'Book'];

    foreach ( $post_types as $post_type ) {
        if ( $post_type->name === 'attachment' ) {
            continue;
        }

        $current_schema = isset( $mapping_option[ $post_type->name ] ) ? $mapping_option[ $post_type->name ] : '';
        $scholarly_settings = isset( $scholarly_option[ $post_type->name ] ) ? $scholarly_option[ $post_type->name ] : [];
        $display_scholarly = ( $current_schema === 'ScholarlyArticle' ) ? '' : 'style="display:none;"';
        ?>
        <div class="post-type-schema-mapping">
            <h4><?php echo esc_html( $post_type->label ); ?></h4>
            <label for="schema-type-<?php echo esc_attr( $post_type->name ); ?>"><?php esc_html_e( 'Schema Type', 'wp-academic-post-enhanced' ); ?></label>
            <select name="wp_academic_post_enhanced_schema_post_type_mapping[<?php echo esc_attr( $post_type->name ); ?>]" id="schema-type-<?php echo esc_attr( $post_type->name ); ?>" class="schema-type-select" data-post-type="<?php echo esc_attr( $post_type->name ); ?>">
                <option value=""><?php esc_html_e( 'None', 'wp-academic-post-enhanced' ); ?></option>
                <?php foreach ( $schema_types as $schema_type ) : ?>
                    <option value="<?php echo esc_attr( $schema_type ); ?>" <?php selected( $current_schema, $schema_type ); ?>><?php echo esc_html( $schema_type ); ?></option>
                <?php endforeach; ?>
            </select>

            <div id="schema-settings-<?php echo esc_attr( $post_type->name ); ?>" class="scholarly-settings-wrapper" <?php echo $display_scholarly; ?> >
                <h5><?php esc_html_e( 'Scholarly Settings', 'wp-academic-post-enhanced' ); ?></h5>
                <table class="form-table">
                    <!-- General Scholarly Settings -->
                    <tr class="schema-settings-group schema-settings-ScholarlyArticle schema-settings-Book schema-settings-TechArticle schema-settings-Thesis schema-settings-MedicalScholarlyArticle schema-settings-NewsArticle">
                        <th scope="row"><label for="scholarly-wordCount-<?php echo esc_attr( $post_type->name ); ?>"><?php esc_html_e( 'Word Count', 'wp-academic-post-enhanced' ); ?></label></th>
                        <td>
                            <?php
                            $current_word_count_setting = isset( $scholarly_settings['wordCount'] ) ? $scholarly_settings['wordCount'] : 'automatic';
                            $manual_word_count_value = '';
                            if ( is_numeric( $current_word_count_setting ) ) {
                                $manual_word_count_value = $current_word_count_setting;
                                $current_word_count_setting = 'manual';
                            }
                            ?>
                            <select name="wp_academic_post_enhanced_schema_scholarly_settings[<?php echo esc_attr( $post_type->name ); ?>][wordCountSource]" id="scholarly-wordCountSource-<?php echo esc_attr( $post_type->name ); ?>" class="word-count-source-select">
                                <option value="automatic" <?php selected( $current_word_count_setting, 'automatic' ); ?>><?php esc_html_e( 'Automatic', 'wp-academic-post-enhanced' ); ?></option>
                                <option value="manual" <?php selected( $current_word_count_setting, 'manual' ); ?>><?php esc_html_e( 'Manual', 'wp-academic-post-enhanced' ); ?></option>
                            </select>
                            <input type="number" id="scholarly-wordCount-<?php echo esc_attr( $post_type->name ); ?>" name="wp_academic_post_enhanced_schema_scholarly_settings[<?php echo esc_attr( $post_type->name ); ?>][wordCount]" value="<?php echo esc_attr( $manual_word_count_value ); ?>" class="word-count-manual-input" style="<?php echo ( $current_word_count_setting === 'manual' ) ? '' : 'display:none;'; ?>" min="0" />
                        </td>
                    </tr>
                    <!-- Thesis Specific -->
                    <tr class="schema-settings-group schema-settings-Thesis" style="display:none;">
                        <th scope="row"><label for="scholarly-inSupportOf-<?php echo esc_attr( $post_type->name ); ?>"><?php esc_html_e( 'Degree (inSupportOf)', 'wp-academic-post-enhanced' ); ?></label></th>
                        <td>
                            <input type="text" id="scholarly-inSupportOf-<?php echo esc_attr( $post_type->name ); ?>" name="wp_academic_post_enhanced_schema_scholarly_settings[<?php echo esc_attr( $post_type->name ); ?>][inSupportOf]" value="<?php echo esc_attr( isset( $scholarly_settings['inSupportOf'] ) ? $scholarly_settings['inSupportOf'] : '' ); ?>" placeholder="e.g., PhD in Computer Science"/>
                        </td>
                    </tr>
                    <!-- TechArticle Specific -->
                    <tr class="schema-settings-group schema-settings-TechArticle" style="display:none;">
                        <th scope="row"><label for="scholarly-proficiencyLevel-<?php echo esc_attr( $post_type->name ); ?>"><?php esc_html_e( 'Proficiency Level', 'wp-academic-post-enhanced' ); ?></label></th>
                        <td>
                            <select name="wp_academic_post_enhanced_schema_scholarly_settings[<?php echo esc_attr( $post_type->name ); ?>][proficiencyLevel]" id="scholarly-proficiencyLevel-<?php echo esc_attr( $post_type->name ); ?>">
                                <option value="Beginner" <?php selected( isset($scholarly_settings['proficiencyLevel']) ? $scholarly_settings['proficiencyLevel'] : '', 'Beginner' ); ?>><?php esc_html_e( 'Beginner', 'wp-academic-post-enhanced' ); ?></option>
                                <option value="Intermediate" <?php selected( isset($scholarly_settings['proficiencyLevel']) ? $scholarly_settings['proficiencyLevel'] : '', 'Intermediate' ); ?>><?php esc_html_e( 'Intermediate', 'wp-academic-post-enhanced' ); ?></option>
                                <option value="Expert" <?php selected( isset($scholarly_settings['proficiencyLevel']) ? $scholarly_settings['proficiencyLevel'] : '', 'Expert' ); ?>><?php esc_html_e( 'Expert', 'wp-academic-post-enhanced' ); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr class="schema-settings-group schema-settings-TechArticle" style="display:none;">
                        <th scope="row"><label for="scholarly-dependencies-<?php echo esc_attr( $post_type->name ); ?>"><?php esc_html_e( 'Dependencies', 'wp-academic-post-enhanced' ); ?></label></th>
                        <td>
                            <input type="text" id="scholarly-dependencies-<?php echo esc_attr( $post_type->name ); ?>" name="wp_academic_post_enhanced_schema_scholarly_settings[<?php echo esc_attr( $post_type->name ); ?>][dependencies]" value="<?php echo esc_attr( isset( $scholarly_settings['dependencies'] ) ? $scholarly_settings['dependencies'] : '' ); ?>" placeholder="e.g., Python 3.8, NumPy"/>
                        </td>
                    </tr>
                    <!-- Course Specific -->
                    <tr class="schema-settings-group schema-settings-Course" style="display:none;">
                        <th scope="row"><label for="scholarly-courseCode-<?php echo esc_attr( $post_type->name ); ?>"><?php esc_html_e( 'Course Code', 'wp-academic-post-enhanced' ); ?></label></th>
                        <td>
                            <input type="text" id="scholarly-courseCode-<?php echo esc_attr( $post_type->name ); ?>" name="wp_academic_post_enhanced_schema_scholarly_settings[<?php echo esc_attr( $post_type->name ); ?>][courseCode]" value="<?php echo esc_attr( isset( $scholarly_settings['courseCode'] ) ? $scholarly_settings['courseCode'] : '' ); ?>" placeholder="e.g., CS101"/>
                        </td>
                    </tr>
                    <tr class="schema-settings-group schema-settings-Course" style="display:none;">
                        <th scope="row"><label for="scholarly-courseMode-<?php echo esc_attr( $post_type->name ); ?>"><?php esc_html_e( 'Course Mode', 'wp-academic-post-enhanced' ); ?></label></th>
                        <td>
                            <select name="wp_academic_post_enhanced_schema_scholarly_settings[<?php echo esc_attr( $post_type->name ); ?>][courseMode]" id="scholarly-courseMode-<?php echo esc_attr( $post_type->name ); ?>">
                                <option value="online" <?php selected( isset($scholarly_settings['courseMode']) ? $scholarly_settings['courseMode'] : '', 'online' ); ?>><?php esc_html_e( 'Online', 'wp-academic-post-enhanced' ); ?></option>
                                <option value="offline" <?php selected( isset($scholarly_settings['courseMode']) ? $scholarly_settings['courseMode'] : '', 'offline' ); ?>><?php esc_html_e( 'Offline', 'wp-academic-post-enhanced' ); ?></option>
                                <option value="blended" <?php selected( isset($scholarly_settings['courseMode']) ? $scholarly_settings['courseMode'] : '', 'blended' ); ?>><?php esc_html_e( 'Blended', 'wp-academic-post-enhanced' ); ?></option>
                            </select>
                        </td>
                    </tr>

                    <tr class="schema-settings-group schema-settings-ScholarlyArticle schema-settings-Book schema-settings-TechArticle schema-settings-Thesis schema-settings-MedicalScholarlyArticle schema-settings-NewsArticle">
                        <th scope="row"><label for="scholarly-timeRequired-<?php echo esc_attr( $post_type->name ); ?>"><?php esc_html_e( 'Time Required', 'wp-academic-post-enhanced' ); ?></label></th>
                        <td>
                            <input type="text" id="scholarly-timeRequired-<?php echo esc_attr( $post_type->name ); ?>" name="wp_academic_post_enhanced_schema_scholarly_settings[<?php echo esc_attr( $post_type->name ); ?>][timeRequired]" value="<?php echo esc_attr( isset( $scholarly_settings['timeRequired'] ) ? $scholarly_settings['timeRequired'] : '' ); ?>" placeholder="e.g., PT1H30M"/>
                            <p class="description"><?php esc_html_e( 'Enter duration in ISO 8601 format (e.g., PT1H30M for 1 hour 30 minutes).', 'wp-academic-post-enhanced' ); ?></p>
                        </td>
                    </tr>
                    <tr class="schema-settings-group schema-settings-ScholarlyArticle schema-settings-Book schema-settings-TechArticle schema-settings-Thesis schema-settings-MedicalScholarlyArticle schema-settings-Course schema-settings-NewsArticle">
                        <th scope="row"><label for="scholarly-educationalUse-<?php echo esc_attr( $post_type->name ); ?>"><?php esc_html_e( 'Educational Use', 'wp-academic-post-enhanced' ); ?></label></th>
                        <td>
                            <?php
                            $educational_uses = [
                                'Assessment', 'Curriculum', 'Lesson Plan', 'Homework', 'Lab Experiment',
                                'Lecture', 'Problem Set', 'Quiz', 'Syllabus', 'Textbook', 'Workbook'
                            ];
                            $current_educational_use = isset( $scholarly_settings['educationalUse'] ) ? (array) $scholarly_settings['educationalUse'] : [];
                            ?>
                            <select name="wp_academic_post_enhanced_schema_scholarly_settings[<?php echo esc_attr( $post_type->name ); ?>][educationalUse][]" id="scholarly-educationalUse-<?php echo esc_attr( $post_type->name ); ?>" multiple="multiple" style="width: 100%;">
                                <?php foreach ( $educational_uses as $use ) : ?>
                                    <option value="<?php echo esc_attr( $use ); ?>" <?php selected( in_array( $use, $current_educational_use ), true ); ?>><?php echo esc_html( $use ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr class="schema-settings-group schema-settings-ScholarlyArticle schema-settings-Book schema-settings-TechArticle schema-settings-Thesis schema-settings-MedicalScholarlyArticle schema-settings-NewsArticle">
                        <th scope="row"><label for="scholarly-interactivityType-<?php echo esc_attr( $post_type->name ); ?>"><?php esc_html_e( 'Interactivity Type', 'wp-academic-post-enhanced' ); ?></label></th>
                        <td>
                            <?php
                            $interactivity_types = [
                                'Active', 'Expositive', 'Mixed', 'Passive', 'Semi-interactive'
                            ];
                            $current_interactivity_type = isset( $scholarly_settings['interactivityType'] ) ? (array) $scholarly_settings['interactivityType'] : [];
                            ?>
                            <select name="wp_academic_post_enhanced_schema_scholarly_settings[<?php echo esc_attr( $post_type->name ); ?>][interactivityType][]" id="scholarly-interactivityType-<?php echo esc_attr( $post_type->name ); ?>" multiple="multiple" style="width: 100%;">
                                <?php foreach ( $interactivity_types as $type ) : ?>
                                    <option value="<?php echo esc_attr( $type ); ?>" <?php selected( in_array( $type, $current_interactivity_type ), true ); ?>><?php echo esc_html( $type ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr class="schema-settings-group schema-settings-ScholarlyArticle schema-settings-Book schema-settings-TechArticle schema-settings-Thesis schema-settings-MedicalScholarlyArticle schema-settings-NewsArticle">
                        <th scope="row"><label for="scholarly-isAccessibleForFree-<?php echo esc_attr( $post_type->name ); ?>"><?php esc_html_e( 'Is Accessible For Free', 'wp-academic-post-enhanced' ); ?></label></th>
                        <td><input type="checkbox" id="scholarly-isAccessibleForFree-<?php echo esc_attr( $post_type->name ); ?>" name="wp_academic_post_enhanced_schema_scholarly_settings[<?php echo esc_attr( $post_type->name ); ?>][isAccessibleForFree]" value="1" <?php checked( isset( $scholarly_settings['isAccessibleForFree'] ) ? $scholarly_settings['isAccessibleForFree'] : 0, 1 ); ?> /></td>
                    </tr>
                    <tr class="schema-settings-group schema-settings-ScholarlyArticle">
                        <th scope="row"><label for="scholarly-add-book-type-<?php echo esc_attr( $post_type->name ); ?>"><?php esc_html_e( 'Add "Book" as Secondary Type', 'wp-academic-post-enhanced' ); ?></label></th>
                        <td>
                            <input type="checkbox" id="scholarly-add-book-type-<?php echo esc_attr( $post_type->name ); ?>" name="wp_academic_post_enhanced_schema_scholarly_settings[<?php echo esc_attr( $post_type->name ); ?>][add_book_type]" value="1" <?php checked( isset( $scholarly_settings['add_book_type'] ) ? $scholarly_settings['add_book_type'] : 0, 1 ); ?> />
                            <p class="description"><?php esc_html_e( 'Enables "Book" type alongside "ScholarlyArticle" to improve Rich Results compatibility (e.g. for Reviews/Ratings).', 'wp-academic-post-enhanced' ); ?></p>
                        </td>
                    </tr>
                    <tr class="schema-settings-group schema-settings-ScholarlyArticle schema-settings-Book schema-settings-TechArticle schema-settings-Thesis schema-settings-MedicalScholarlyArticle schema-settings-Course schema-settings-NewsArticle">
                        <th scope="row"><label for="scholarly-learningResourceType-<?php echo esc_attr( $post_type->name ); ?>"><?php esc_html_e( 'Learning Resource Type', 'wp-academic-post-enhanced' ); ?></label></th>
                        <td>
                            <?php
                            $learning_resource_types = [
                                'Course', 'Lecture', 'Problem Set', 'Quiz', 'Syllabus', 'Textbook', 'Workbook',
                                'Assessment', 'Curriculum', 'Lesson Plan', 'Lab Experiment'
                            ];
                            $current_learning_resource_type = isset( $scholarly_settings['learningResourceType'] ) ? (array) $scholarly_settings['learningResourceType'] : [];
                            ?>
                            <select name="wp_academic_post_enhanced_schema_scholarly_settings[<?php echo esc_attr( $post_type->name ); ?>][learningResourceType][]" id="scholarly-learningResourceType-<?php echo esc_attr( $post_type->name ); ?>" multiple="multiple" style="width: 100%;">
                                <?php foreach ( $learning_resource_types as $type ) : ?>
                                    <option value="<?php echo esc_attr( $type ); ?>" <?php selected( in_array( $type, $current_learning_resource_type ), true ); ?>><?php echo esc_html( $type ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr class="schema-settings-group schema-settings-ScholarlyArticle schema-settings-Book schema-settings-TechArticle schema-settings-Thesis schema-settings-MedicalScholarlyArticle schema-settings-NewsArticle">
                        <th scope="row"><label for="scholarly-citation-<?php echo esc_attr( $post_type->name ); ?>"><?php esc_html_e( 'Citation', 'wp-academic-post-enhanced' ); ?></label></th>
                        <td><textarea id="scholarly-citation-<?php echo esc_attr( $post_type->name ); ?>" name="wp_academic_post_enhanced_schema_scholarly_settings[<?php echo esc_attr( $post_type->name ); ?>][citation]" rows="5" cols="50"><?php echo esc_textarea( isset( $scholarly_settings['citation'] ) ? $scholarly_settings['citation'] : '' ); ?></textarea></td>
                    </tr>
                    <tr class="schema-settings-group schema-settings-ScholarlyArticle schema-settings-Book schema-settings-TechArticle schema-settings-Thesis schema-settings-MedicalScholarlyArticle schema-settings-Course schema-settings-NewsArticle">
                        <th scope="row"><label for="scholarly-keywordsSource-<?php echo esc_attr( $post_type->name ); ?>"><?php esc_html_e( 'Keywords Source', 'wp-academic-post-enhanced' ); ?></label></th>
                        <td>
                            <select id="scholarly-keywordsSource-<?php echo esc_attr( $post_type->name ); ?>" name="wp_academic_post_enhanced_schema_scholarly_settings[<?php echo esc_attr( $post_type->name ); ?>][keywordsSource]" class="keywords-source-select" data-post-type="<?php echo esc_attr( $post_type->name ); ?>">
                                <option value="manual" <?php selected( isset( $scholarly_settings['keywordsSource'] ) ? $scholarly_settings['keywordsSource'] : '', 'manual' ); ?>><?php esc_html_e( 'Manual', 'wp-academic-post-enhanced' ); ?></option>
                                <option value="tags" <?php selected( isset( $scholarly_settings['keywordsSource'] ) ? $scholarly_settings['keywordsSource'] : '', 'tags' ); ?>><?php esc_html_e( 'Tags', 'wp-academic-post-enhanced' ); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr class="manual-keywords-row schema-settings-group schema-settings-ScholarlyArticle schema-settings-Book schema-settings-TechArticle schema-settings-Thesis schema-settings-MedicalScholarlyArticle schema-settings-Course schema-settings-NewsArticle" data-post-type="<?php echo esc_attr( $post_type->name ); ?>">
                        <th scope="row"><label for="scholarly-keywords-<?php echo esc_attr( $post_type->name ); ?>"><?php esc_html_e( 'Manual Keywords', 'wp-academic-post-enhanced' ); ?></label></th>
                        <td><input type="text" id="scholarly-keywords-<?php echo esc_attr( $post_type->name ); ?>" name="wp_academic_post_enhanced_schema_scholarly_settings[<?php echo esc_attr( $post_type->name ); ?>][keywords]" value="<?php echo esc_attr( isset( $scholarly_settings['keywords'] ) ? $scholarly_settings['keywords'] : '' ); ?>" /></td>
                    </tr>
                </table>
            </div>
        </div>
        <script>
        (function($) {
            function toggleSchemaSettings() {
                $('.schema-type-select').each(function() {
                    var postType = $(this).data('post-type');
                    var selectedType = $(this).val();
                    var wrapper = $('#schema-settings-' + postType);
                    
                    if ( selectedType && selectedType !== '' ) {
                        wrapper.show();
                        wrapper.find('.schema-settings-group').hide();
                        wrapper.find('.schema-settings-' + selectedType).show();
                    } else {
                        wrapper.hide();
                    }
                });
            }
            $(document).ready(toggleSchemaSettings);
            $(document).on('change', '.schema-type-select', toggleSchemaSettings);
        })(jQuery);
        </script>
        <?php
    }
}

function wp_academic_post_enhanced_sanitize_schema_post_type_mapping( $input ) {
    $sanitized = [];
    $post_types = get_post_types( ["public" => true] );
    $schema_types = ['Article', 'BlogPosting', 'NewsArticle', 'ScholarlyArticle', 'TechArticle', 'Thesis', 'Course', 'Book'];

    if ( is_array( $input ) ) {
        foreach ( $input as $post_type => $schema_type ) {
            if ( in_array( $post_type, $post_types, true ) && in_array( $schema_type, $schema_types, true ) ) {
                $sanitized[ $post_type ] = $schema_type;
            }
        }
    }
    return $sanitized;
}

function wp_academic_post_enhanced_sanitize_scholarly_settings( $input ) {
    $sanitized_input = [];

    if ( ! is_array( $input ) ) {
        return $sanitized_input;
    }

    foreach ( $input as $post_type => $settings ) {
        $sanitized_settings = [];

        // Sanitize wordCount
        if ( isset( $settings['wordCountSource'] ) ) {
            if ( $settings['wordCountSource'] === 'manual' && isset( $settings['wordCount'] ) ) {
                $sanitized_settings['wordCount'] = absint( $settings['wordCount'] );
            } else {
                $sanitized_settings['wordCount'] = 'automatic';
            }
        }

        // Sanitize timeRequired
        if ( isset( $settings['timeRequired'] ) ) {
            $sanitized_settings['timeRequired'] = sanitize_text_field( $settings['timeRequired'] );
        }

        // Sanitize educationalUse
        if ( isset( $settings['educationalUse'] ) && is_array( $settings['educationalUse'] ) ) {
            $sanitized_settings['educationalUse'] = array_map( 'sanitize_text_field', $settings['educationalUse'] );
        }

        // Sanitize interactivityType
        if ( isset( $settings['interactivityType'] ) && is_array( $settings['interactivityType'] ) ) {
            $sanitized_settings['interactivityType'] = array_map( 'sanitize_text_field', $settings['interactivityType'] );
        }

        // Sanitize isAccessibleForFree
        if ( isset( $settings['isAccessibleForFree'] ) ) {
            $sanitized_settings['isAccessibleForFree'] = rest_sanitize_boolean( $settings['isAccessibleForFree'] );
        }

        // Sanitize add_book_type
        if ( isset( $settings['add_book_type'] ) ) {
            $sanitized_settings['add_book_type'] = rest_sanitize_boolean( $settings['add_book_type'] );
        }

        // Sanitize learningResourceType
        if ( isset( $settings['learningResourceType'] ) && is_array( $settings['learningResourceType'] ) ) {
            $sanitized_settings['learningResourceType'] = array_map( 'sanitize_text_field', $settings['learningResourceType'] );
        }

        // Sanitize citation
        if ( isset( $settings['citation'] ) ) {
            $sanitized_settings['citation'] = sanitize_textarea_field( $settings['citation'] );
        }

        // Sanitize keywordsSource
        if ( isset( $settings['keywordsSource'] ) ) {
            $sanitized_settings['keywordsSource'] = sanitize_text_field( $settings['keywordsSource'] );
        }

        // Sanitize keywords
        if ( isset( $settings['keywords'] ) ) {
            $sanitized_settings['keywords'] = sanitize_text_field( $settings['keywords'] );
        }

        // --- New Fields ---
        // Thesis: inSupportOf
        if ( isset( $settings['inSupportOf'] ) ) {
            $sanitized_settings['inSupportOf'] = sanitize_text_field( $settings['inSupportOf'] );
        }

        // TechArticle: proficiencyLevel
        if ( isset( $settings['proficiencyLevel'] ) ) {
            $sanitized_settings['proficiencyLevel'] = sanitize_text_field( $settings['proficiencyLevel'] );
        }

        // TechArticle: dependencies
        if ( isset( $settings['dependencies'] ) ) {
            $sanitized_settings['dependencies'] = sanitize_text_field( $settings['dependencies'] );
        }

        // Course: courseCode
        if ( isset( $settings['courseCode'] ) ) {
            $sanitized_settings['courseCode'] = sanitize_text_field( $settings['courseCode'] );
        }

        // Course: courseMode
        if ( isset( $settings['courseMode'] ) ) {
            $sanitized_settings['courseMode'] = sanitize_text_field( $settings['courseMode'] );
        }

        $sanitized_input[ sanitize_key( $post_type ) ] = $sanitized_settings;
    }

    return $sanitized_input;
}

function wp_academic_post_enhanced_schema_toc_section_callback() {
    echo '<p>' . esc_html__( 'Configure schema settings for the Table of Contents.', 'wp-academic-post-enhanced' ) . '</p>';
}

function wp_academic_post_enhanced_schema_toc_enabled_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_schema_toc_enabled', true );
    echo '<input type="checkbox" name="wp_academic_post_enhanced_schema_toc_enabled" value="1" ' . checked( 1, $option, false ) . ' />';
}

function wp_academic_post_enhanced_schema_toc_headings_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_schema_toc_headings', ['h1', 'h2', 'h3'] );
    $headings = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
    foreach ( $headings as $heading ) {
        echo '<label><input type="checkbox" name="wp_academic_post_enhanced_schema_toc_headings[]" value="' . esc_attr( $heading ) . '" ' . checked( in_array( $heading, $option ), true, false ) . ' /> ' . esc_html( strtoupper( $heading ) ) . '</label><br />
';
    }
}

function wp_academic_post_enhanced_sanitize_toc_headings( $input ) {
    $sanitized = [];
    $available_headings = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
    if ( is_array( $input ) ) {
        foreach ( $input as $value ) {
            if ( in_array( $value, $available_headings, true ) ) {
                $sanitized[] = $value;
            }
        }
    }
    return $sanitized;
}

function wp_academic_post_enhanced_schema_toc_min_headings_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_schema_toc_min_headings', 2 );
    echo '<input type="number" name="wp_academic_post_enhanced_schema_toc_min_headings" value="' . esc_attr( $option ) . '" min="1" />';
}

function wp_academic_post_enhanced_schema_rating_section_callback() {
    echo '<p>' . esc_html__( 'Enable and configure a global AggregateRating schema for all posts.', 'wp-academic-post-enhanced' ) . '</p>';
}

function wp_academic_post_enhanced_schema_rating_enabled_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_schema_rating_enabled', false );
    echo '<input type="checkbox" name="wp_academic_post_enhanced_schema_rating_enabled" value="1" ' . checked( 1, $option, false ) . ' />';
}

function wp_academic_post_enhanced_schema_rating_value_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_schema_rating_value', '5' );
    echo '<input type="number" name="wp_academic_post_enhanced_schema_rating_value" value="' . esc_attr( $option ) . '" step="0.1" min="0" max="5" />';
}

function wp_academic_post_enhanced_schema_rating_review_count_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_schema_rating_review_count', '1' );
    echo '<input type="number" name="wp_academic_post_enhanced_schema_rating_review_count" value="' . esc_attr( $option ) . '" step="1" min="0" />';
}

function wp_academic_post_enhanced_schema_rating_item_reviewed_type_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_schema_rating_item_reviewed_type', '' );
    echo '<input type="text" name="wp_academic_post_enhanced_schema_rating_item_reviewed_type" value="' . esc_attr( $option ) . '" placeholder="e.g., Thesis" />';
    echo '<p class="description">' . esc_html__( 'Leave blank to use the default schema type for the post.', 'wp-academic-post-enhanced' ) . '</p>';
}

function wp_academic_post_enhanced_scholarly_issn_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_scholarly_issn', '' );
    echo '<input type="text" name="wp_academic_post_enhanced_scholarly_issn" value="' . esc_attr( $option ) . '" />';
}

function wp_academic_post_enhanced_scholarly_is_part_of_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_scholarly_is_part_of', get_bloginfo('name') );
    echo '<input type="text" name="wp_academic_post_enhanced_scholarly_is_part_of" value="' . esc_attr( $option ) . '" />';
}

function wp_academic_post_enhanced_scholarly_citations_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_scholarly_citations', '' );
    echo '<textarea name="wp_academic_post_enhanced_scholarly_citations" rows="5" cols="50" placeholder="' . esc_attr__( 'Enter one citation per line', 'wp-academic-post-enhanced' ) . '">' . esc_textarea( $option ) . '</textarea>';
}