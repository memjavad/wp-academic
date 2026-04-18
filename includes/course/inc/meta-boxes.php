<?php
/**
 * Meta Boxes for Courses and Lessons.
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add Course Details Meta Box
 */
function wpa_course_add_details_meta_box() {
    add_meta_box(
        'wpa_course_details',
        __( 'Course Details', 'wp-academic-post-enhanced' ),
        'wpa_course_details_callback',
        'wpa_course',
        'side',
        'high'
    );
}
add_action( 'add_meta_boxes', 'wpa_course_add_details_meta_box' );

function wpa_course_details_callback( $post ) {
    $duration = get_post_meta( $post->ID, '_wpa_course_duration', true );
    $level = get_post_meta( $post->ID, '_wpa_course_level', true );
    $type = get_post_meta( $post->ID, '_wpa_course_type', true );
    $price = get_post_meta( $post->ID, '_wpa_course_price', true );
    $language = get_post_meta( $post->ID, '_wpa_course_language', true );
    $prerequisite = get_post_meta( $post->ID, '_wpa_course_prerequisite', true );
    $all_courses = wpa_get_all_courses_list();
    
    wp_nonce_field( 'wpa_course_details_save', 'wpa_course_details_nonce' );
    ?>
    <p>
        <label for="wpa_course_duration"><strong><?php esc_html_e( 'Duration', 'wp-academic-post-enhanced' ); ?></strong></label>
        <input type="text" name="wpa_course_duration" id="wpa_course_duration" value="<?php echo esc_attr( $duration ); ?>" class="widefat" placeholder="e.g. 2 Hours">
    </p>
    <p>
        <label for="wpa_course_level"><strong><?php esc_html_e( 'Difficulty Level', 'wp-academic-post-enhanced' ); ?></strong></label>
        <select name="wpa_course_level" id="wpa_course_level" class="widefat">
            <option value="All Levels" <?php selected( $level, 'All Levels' ); ?>><?php esc_html_e( 'All Levels', 'wp-academic-post-enhanced' ); ?></option>
            <option value="Beginner" <?php selected( $level, 'Beginner' ); ?>><?php esc_html_e( 'Beginner', 'wp-academic-post-enhanced' ); ?></option>
            <option value="Intermediate" <?php selected( $level, 'Intermediate' ); ?>><?php esc_html_e( 'Intermediate', 'wp-academic-post-enhanced' ); ?></option>
            <option value="Advanced" <?php selected( $level, 'Advanced' ); ?>><?php esc_html_e( 'Advanced', 'wp-academic-post-enhanced' ); ?></option>
        </select>
    </p>
    <p>
        <label for="wpa_course_type"><strong><?php esc_html_e( 'Course Type', 'wp-academic-post-enhanced' ); ?></strong></label>
        <select name="wpa_course_type" id="wpa_course_type" class="widefat">
            <option value="Self-Paced" <?php selected( $type, 'Self-Paced' ); ?>><?php esc_html_e( 'Self-Paced', 'wp-academic-post-enhanced' ); ?></option>
            <option value="Instructor-Led" <?php selected( $type, 'Instructor-Led' ); ?>><?php esc_html_e( 'Instructor-Led', 'wp-academic-post-enhanced' ); ?></option>
            <option value="Hybrid" <?php selected( $type, 'Hybrid' ); ?>><?php esc_html_e( 'Hybrid', 'wp-academic-post-enhanced' ); ?></option>
        </select>
    </p>
    <p>
        <label for="wpa_course_price"><strong><?php esc_html_e( 'Price', 'wp-academic-post-enhanced' ); ?></strong></label>
        <input type="text" name="wpa_course_price" id="wpa_course_price" value="<?php echo esc_attr( $price ); ?>" class="widefat" placeholder="<?php esc_html_e( 'e.g. Free or $99', 'wp-academic-post-enhanced' ); ?>">
    </p>
    <p>
        <label for="wpa_course_language"><strong><?php esc_html_e( 'Language', 'wp-academic-post-enhanced' ); ?></strong></label>
        <input type="text" name="wpa_course_language" id="wpa_course_language" value="<?php echo esc_attr( $language ); ?>" class="widefat" placeholder="<?php esc_html_e( 'e.g. English', 'wp-academic-post-enhanced' ); ?>">
    </p>
    <p>
        <label for="wpa_course_prerequisite"><strong><?php esc_html_e( 'Prerequisite Course', 'wp-academic-post-enhanced' ); ?></strong></label>
        <select name="wpa_course_prerequisite" id="wpa_course_prerequisite" class="widefat">
            <option value=""><?php esc_html_e( 'None', 'wp-academic-post-enhanced' ); ?></option>
            <?php foreach ( $all_courses as $c_id => $c_title ) : ?>
                <?php if ( $c_id === $post->ID ) continue; // Skip self ?>
                <option value="<?php echo esc_attr( $c_id ); ?>" <?php selected( $prerequisite, $c_id ); ?>><?php echo esc_html( $c_title ); ?></option>
            <?php endforeach; ?>
        </select>
        <p class="description"><?php esc_html_e( 'User must complete this course before enrolling.', 'wp-academic-post-enhanced' ); ?></p>
    </p>
    <?php
    do_action( 'wpa_course_details_meta_box_bottom', $post );
}

function wpa_course_save_details( $post_id ) {
    if ( ! isset( $_POST['wpa_course_details_nonce'] ) || ! wp_verify_nonce( $_POST['wpa_course_details_nonce'], 'wpa_course_details_save' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    if ( isset( $_POST['wpa_course_duration'] ) ) update_post_meta( $post_id, '_wpa_course_duration', sanitize_text_field( $_POST['wpa_course_duration'] ) );
    if ( isset( $_POST['wpa_course_level'] ) ) update_post_meta( $post_id, '_wpa_course_level', sanitize_text_field( $_POST['wpa_course_level'] ) );
    if ( isset( $_POST['wpa_course_type'] ) ) update_post_meta( $post_id, '_wpa_course_type', sanitize_text_field( $_POST['wpa_course_type'] ) );
    if ( isset( $_POST['wpa_course_price'] ) ) update_post_meta( $post_id, '_wpa_course_price', sanitize_text_field( $_POST['wpa_course_price'] ) );
    if ( isset( $_POST['wpa_course_language'] ) ) update_post_meta( $post_id, '_wpa_course_language', sanitize_text_field( $_POST['wpa_course_language'] ) );
    if ( isset( $_POST['wpa_course_prerequisite'] ) ) update_post_meta( $post_id, '_wpa_course_prerequisite', sanitize_text_field( $_POST['wpa_course_prerequisite'] ) );
}
add_action( 'save_post', 'wpa_course_save_details' );

/**
 * Add Access Settings to Course Meta Box
 */
function wpa_course_add_access_field( $post ) {
    $restriction = get_post_meta( $post->ID, '_wpa_course_restriction', true );
    ?>
    <p>
        <label for="wpa_course_restriction"><strong><?php esc_html_e( 'Access Control', 'wp-academic-post-enhanced' ); ?></strong></label>
        <select name="wpa_course_restriction" id="wpa_course_restriction" class="widefat">
            <option value="public" <?php selected( $restriction, 'public' ); ?>><?php esc_html_e( 'Public', 'wp-academic-post-enhanced' ); ?></option>
            <option value="logged_in" <?php selected( $restriction, 'logged_in' ); ?>><?php esc_html_e( 'Logged-in Users Only', 'wp-academic-post-enhanced' ); ?></option>
        </select>
    </p>
    <?php
}
add_action( 'wpa_course_details_meta_box_bottom', 'wpa_course_add_access_field' );

function wpa_course_save_access_field( $post_id ) {
    if ( isset( $_POST['wpa_course_restriction'] ) ) {
        update_post_meta( $post_id, '_wpa_course_restriction', sanitize_key( $_POST['wpa_course_restriction'] ) );
    }
}
add_action( 'save_post', 'wpa_course_save_access_field' );

/**
 * Add Lesson Materials & Video Meta Box
 */
function wpa_lesson_add_details_meta_box() {
    add_meta_box(
        'wpa_lesson_details',
        __( 'Lesson Details', 'wp-academic-post-enhanced' ),
        'wpa_lesson_details_callback',
        'wpa_lesson',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'wpa_lesson_add_details_meta_box' );

function wpa_lesson_details_callback( $post ) {
    $materials = get_post_meta( $post->ID, '_wpa_lesson_materials', true );
    $video_url = get_post_meta( $post->ID, '_wpa_lesson_video_url', true );
    $section = get_post_meta( $post->ID, '_wpa_lesson_section', true );
    $drip_days = get_post_meta( $post->ID, '_wpa_lesson_drip_days', true );
    
    wp_nonce_field( 'wpa_lesson_details_save', 'wpa_lesson_details_nonce' );
    ?>
    <p>
        <label for="wpa_lesson_section"><strong><?php esc_html_e( 'Section / Module Name', 'wp-academic-post-enhanced' ); ?></strong></label><br>
        <input type="text" name="wpa_lesson_section" id="wpa_lesson_section" value="<?php echo esc_attr( $section ); ?>" class="large-text" placeholder="e.g. Module 1: Introduction">
        <span class="description"><?php esc_html_e( 'Group this lesson under a section heading.', 'wp-academic-post-enhanced' ); ?></span>
    </p>
    <hr>
    <p>
        <label for="wpa_lesson_video_url"><strong><?php esc_html_e( 'Video URL (YouTube/Vimeo)', 'wp-academic-post-enhanced' ); ?></strong></label><br>
        <input type="url" name="wpa_lesson_video_url" id="wpa_lesson_video_url" value="<?php echo esc_url( $video_url ); ?>" class="large-text" placeholder="https://youtube.com/watch?v=...">
    </p>
    <hr>
    <p>
        <label for="wpa_lesson_materials"><strong><?php esc_html_e( 'Downloadable Materials', 'wp-academic-post-enhanced' ); ?></strong></label><br>
        <span class="description"><?php esc_html_e( 'Format: URL | Label (one per line).', 'wp-academic-post-enhanced' ); ?></span>
        <textarea name="wpa_lesson_materials" id="wpa_lesson_materials" rows="5" class="large-text" placeholder="https://example.com/file.pdf | Lecture Slides"><?php echo esc_textarea( $materials ); ?></textarea>
    </p>
    <hr>
    <p>
        <label for="wpa_lesson_drip_days"><strong><?php esc_html_e( 'Drip Content (Scheduled Release)', 'wp-academic-post-enhanced' ); ?></strong></label><br>
        <input type="number" name="wpa_lesson_drip_days" id="wpa_lesson_drip_days" value="<?php echo esc_attr( $drip_days ); ?>" class="small-text" min="0">
        <span class="description"><?php esc_html_e( 'Days after enrollment to unlock this lesson. Leave 0 for immediate access.', 'wp-academic-post-enhanced' ); ?></span>
    </p>
    <?php
}

function wpa_lesson_save_details( $post_id ) {
    if ( ! isset( $_POST['wpa_lesson_details_nonce'] ) || ! wp_verify_nonce( $_POST['wpa_lesson_details_nonce'], 'wpa_lesson_details_save' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    if ( isset( $_POST['wpa_lesson_materials'] ) ) {
        update_post_meta( $post_id, '_wpa_lesson_materials', sanitize_textarea_field( $_POST['wpa_lesson_materials'] ) );
    }
    if ( isset( $_POST['wpa_lesson_video_url'] ) ) {
        update_post_meta( $post_id, '_wpa_lesson_video_url', esc_url_raw( $_POST['wpa_lesson_video_url'] ) );
    }
    if ( isset( $_POST['wpa_lesson_section'] ) ) {
        update_post_meta( $post_id, '_wpa_lesson_section', sanitize_text_field( $_POST['wpa_lesson_section'] ) );
    }
    if ( isset( $_POST['wpa_lesson_drip_days'] ) ) {
        update_post_meta( $post_id, '_wpa_lesson_drip_days', absint( $_POST['wpa_lesson_drip_days'] ) );
    }
    if ( isset( $_POST['wpa_lesson_duration'] ) ) {
        update_post_meta( $post_id, '_wpa_lesson_duration', sanitize_text_field( $_POST['wpa_lesson_duration'] ) );
    }
}
add_action( 'save_post', 'wpa_lesson_save_details' );
