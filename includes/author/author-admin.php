<?php
/**
 * Author Subtitle admin feature.
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Add the meta box for the author subtitle.
 */
function wp_academic_post_enhanced_add_author_subtitle_meta_box() {
    $author_subtitle_enabled = get_option( 'wp_academic_post_enhanced_author_subtitle_enabled', true );
    if ( ! $author_subtitle_enabled ) {
        return;
    }

    add_meta_box(
        'wp_academic_post_enhanced_author_subtitle',
        __( 'Author Subtitle', 'wp-academic-post-enhanced' ),
        'wp_academic_post_enhanced_author_subtitle_meta_box_html',
        'post', // Assuming this should only apply to posts.
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'wp_academic_post_enhanced_add_author_subtitle_meta_box' );

/**
 * Render the meta box HTML.
 *
 * @param WP_Post $post The post object.
 */
function wp_academic_post_enhanced_author_subtitle_meta_box_html( $post ) {
    $subtitle = get_post_meta( $post->ID, '_wp_academic_post_enhanced_author_subtitle', true );
    wp_nonce_field( 'wp_academic_post_enhanced_author_subtitle_save', 'wp_academic_post_enhanced_author_subtitle_nonce' );
    ?>
    <p>
        <label for="wpa_author_subtitle"><?php esc_html_e( 'Enter the author subtitle below:', 'wp-academic-post-enhanced' ); ?></label>
        <input type="text" id="wpa_author_subtitle" name="wpa_author_subtitle" class="widefat" value="<?php echo esc_attr( $subtitle ); ?>">
    </p>
    <?php
}

/**
 * Save the meta box data.
 *
 * @param int $post_id The post ID.
 */
function wp_academic_post_enhanced_save_author_subtitle_meta_box_data( $post_id ) {
    if ( ! isset( $_POST['wp_academic_post_enhanced_author_subtitle_nonce'] ) || ! wp_verify_nonce( $_POST['wp_academic_post_enhanced_author_subtitle_nonce'], 'wp_academic_post_enhanced_author_subtitle_save' ) ) {
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    if ( isset( $_POST['wpa_author_subtitle'] ) ) {
        update_post_meta( $post_id, '_wp_academic_post_enhanced_author_subtitle', sanitize_text_field( $_POST['wpa_author_subtitle'] ) );
    }
}
add_action( 'save_post', 'wp_academic_post_enhanced_save_author_subtitle_meta_box_data' );
