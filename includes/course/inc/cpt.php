<?php
/**
 * Course and Lesson Custom Post Types Registration.
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register Course and Lesson Custom Post Types.
 */
function wpa_course_register_cpts() {
    $options = get_option( 'wpa_course_settings' );
    $course_slug = ! empty( $options['course_slug'] ) ? $options['course_slug'] : 'course';
    $lesson_slug = ! empty( $options['lesson_slug'] ) ? $options['lesson_slug'] : 'lesson';

    // 1. Course CPT
    $course_labels = [
        'name'               => _x( 'Courses', 'post type general name', 'wp-academic-post-enhanced' ),
        'singular_name'      => _x( 'Course', 'post type singular name', 'wp-academic-post-enhanced' ),
        'menu_name'          => _x( 'Courses', 'admin menu', 'wp-academic-post-enhanced' ),
        'name_admin_bar'     => _x( 'Course', 'add new on admin bar', 'wp-academic-post-enhanced' ),
        'add_new'            => _x( 'Add New', 'course', 'wp-academic-post-enhanced' ),
        'add_new_item'       => __( 'Add New Course', 'wp-academic-post-enhanced' ),
        'new_item'           => __( 'New Course', 'wp-academic-post-enhanced' ),
        'edit_item'          => __( 'Edit Course', 'wp-academic-post-enhanced' ),
        'view_item'          => __( 'View Course', 'wp-academic-post-enhanced' ),
        'all_items'          => __( 'All Courses', 'wp-academic-post-enhanced' ),
        'search_items'       => __( 'Search Courses', 'wp-academic-post-enhanced' ),
        'not_found'          => __( 'No courses found.', 'wp-academic-post-enhanced' ),
        'not_found_in_trash' => __( 'No courses found in Trash.', 'wp-academic-post-enhanced' ),
    ];

    $course_args = [
        'labels'             => $course_labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => [ 'slug' => $course_slug ],
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-welcome-learn-more',
        'supports'           => [ 'title', 'editor', 'thumbnail', 'excerpt', 'author' ],
        'show_in_rest'       => true,
    ];

    register_post_type( 'wpa_course', $course_args );

    // 2. Lesson CPT
    $lesson_labels = [
        'name'               => _x( 'Lessons', 'post type general name', 'wp-academic-post-enhanced' ),
        'singular_name'      => _x( 'Lesson', 'post type singular name', 'wp-academic-post-enhanced' ),
        'menu_name'          => _x( 'Lessons', 'admin menu', 'wp-academic-post-enhanced' ),
        'name_admin_bar'     => _x( 'Lesson', 'add new on admin bar', 'wp-academic-post-enhanced' ),
        'add_new'            => _x( 'Add New', 'lesson', 'wp-academic-post-enhanced' ),
        'add_new_item'       => __( 'Add New Lesson', 'wp-academic-post-enhanced' ),
        'new_item'           => __( 'New Lesson', 'wp-academic-post-enhanced' ),
        'edit_item'          => __( 'Edit Lesson', 'wp-academic-post-enhanced' ),
        'view_item'          => __( 'View Lesson', 'wp-academic-post-enhanced' ),
        'all_items'          => __( 'All Lessons', 'wp-academic-post-enhanced' ),
        'search_items'       => __( 'Search Lessons', 'wp-academic-post-enhanced' ),
        'not_found'          => __( 'No lessons found.', 'wp-academic-post-enhanced' ),
        'not_found_in_trash' => __( 'No lessons found in Trash.', 'wp-academic-post-enhanced' ),
    ];

    $lesson_args = [
        'labels'             => $lesson_labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => 'edit.php?post_type=wpa_course', // Show under Courses menu
        'query_var'          => true,
        'rewrite'            => [ 'slug' => $lesson_slug ],
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => [ 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ], // page-attributes for 'order'
        'show_in_rest'       => true,
    ];

    register_post_type( 'wpa_lesson', $lesson_args );

    // Flush rewrite rules if we haven't yet (Fixes 404s on new install)
    if ( ! get_option( 'wpa_course_permalinks_flushed' ) ) {
        flush_rewrite_rules();
        update_option( 'wpa_course_permalinks_flushed', true );
    }
}
add_action( 'init', 'wpa_course_register_cpts' );

/**
 * Add Course Column to Lesson List
 */
function wpa_lesson_columns( $columns ) {
    $columns['assigned_course'] = __( 'Assigned Course', 'wp-academic-post-enhanced' );
    return $columns;
}
add_filter( 'manage_wpa_lesson_posts_columns', 'wpa_lesson_columns' );

function wpa_lesson_custom_column( $column, $post_id ) {
    if ( 'assigned_course' === $column ) {
        $course_id = get_post_meta( $post_id, '_wpa_course_id', true );
        if ( $course_id ) {
            echo '<a href="' . get_edit_post_link( $course_id ) . '">' . get_the_title( $course_id ) . '</a>';
        } else {
            echo '<span style="color: #999;">' . __( 'Unassigned', 'wp-academic-post-enhanced' ) . '</span>';
        }
    }
}
add_action( 'manage_wpa_lesson_posts_custom_column', 'wpa_lesson_custom_column', 10, 2 );
