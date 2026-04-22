<?php
/**
 * Field News Custom Post Type.
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register the News Custom Post Type.
 */
function wpa_field_news_register_cpt() {
    $labels = [
        'name'               => _x( 'Field News', 'post type general name', 'wp-academic-post-enhanced' ),
        'singular_name'      => _x( 'News', 'post type singular name', 'wp-academic-post-enhanced' ),
        'menu_name'          => _x( 'Field News', 'admin menu', 'wp-academic-post-enhanced' ),
        'name_admin_bar'     => _x( 'News', 'add new on admin bar', 'wp-academic-post-enhanced' ),
        'add_new'            => _x( 'Add New', 'news', 'wp-academic-post-enhanced' ),
        'add_new_item'       => __( 'Add New News Story', 'wp-academic-post-enhanced' ),
        'new_item'           => __( 'New News Story', 'wp-academic-post-enhanced' ),
        'edit_item'          => __( 'Edit News Story', 'wp-academic-post-enhanced' ),
        'view_item'          => __( 'View News Story', 'wp-academic-post-enhanced' ),
        'all_items'          => __( 'All News', 'wp-academic-post-enhanced' ),
        'search_items'       => __( 'Search News', 'wp-academic-post-enhanced' ),
        'not_found'          => __( 'No news stories found.', 'wp-academic-post-enhanced' ),
        'not_found_in_trash' => __( 'No news stories found in Trash.', 'wp-academic-post-enhanced' ),
    ];

    $args = [
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => [ 'slug' => 'field-news' ],
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-megaphone',
        'supports'           => [ 'title', 'editor', 'thumbnail', 'excerpt', 'author', 'comments' ],
        'taxonomies'         => [ 'category', 'post_tag' ],
        'show_in_rest'       => true,
    ];

    register_post_type( 'wpa_news', $args );

    // Flush rules once to prevent 404 errors
    if ( ! get_option( 'wpa_news_cpt_flushed' ) ) {
        flush_rewrite_rules();
        update_option( 'wpa_news_cpt_flushed', true );
    }

    // Register Study Repository CPT (Hidden storage)
    $study_labels = [
        'name'          => __( 'Studies', 'wp-academic-post-enhanced' ),
        'singular_name' => __( 'Study', 'wp-academic-post-enhanced' ),
    ];
    register_post_type( 'wpa_study', [
        'labels'          => $study_labels,
        'public'          => false,
        'show_ui'         => true,
        'show_in_menu'    => false, // Hidden, managed via custom page
        'capability_type' => 'post',
        'supports'        => [ 'title', 'custom-fields' ]
    ]);
}
add_action( 'init', 'wpa_field_news_register_cpt' );
