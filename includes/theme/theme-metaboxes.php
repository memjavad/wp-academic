<?php
/**
 * Theme Metaboxes
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register Meta Boxes.
 */
function wpa_theme_register_meta_boxes() {
    $screens = [ 'post', 'page', 'wpa_course', 'wpa_lesson', 'wpa_news' ];
    foreach ( $screens as $screen ) {
        add_meta_box(
            'wpa_page_layout_options',
            __( 'Page Layout & Width', 'wp-academic-post-enhanced' ),
            'wpa_theme_layout_meta_box_callback',
            $screen,
            'side',
            'low'
        );
    }
}
add_action( 'add_meta_boxes', 'wpa_theme_register_meta_boxes' );

/**
 * Meta Box Callback.
 */
function wpa_theme_layout_meta_box_callback( $post ) {
    wp_nonce_field( 'wpa_theme_save_layout_meta', 'wpa_theme_layout_meta_nonce' );
    
    $width = get_post_meta( $post->ID, '_wpa_container_width', true );
    if ( ! $width ) $width = 'default';
    
    $custom_width = get_post_meta( $post->ID, '_wpa_container_width_custom', true );
    
    $sidebar = get_post_meta( $post->ID, '_wpa_sidebar_position', true );
    if ( ! $sidebar ) $sidebar = 'default';

    $hide_title = get_post_meta( $post->ID, '_wpa_hide_title', true );
    $hide_featured = get_post_meta( $post->ID, '_wpa_hide_featured', true );

    ?>
    <p><strong><?php esc_html_e( 'Container Width', 'wp-academic-post-enhanced' ); ?></strong></p>
    <label style="display:block; margin-bottom:5px;">
        <input type="radio" name="wpa_container_width" value="default" <?php checked( $width, 'default' ); ?>>
        <?php esc_html_e( 'Default (Global Setting)', 'wp-academic-post-enhanced' ); ?>
    </label>
    <label style="display:block; margin-bottom:5px;">
        <input type="radio" name="wpa_container_width" value="standard" <?php checked( $width, 'standard' ); ?>>
        <?php esc_html_e( 'Standard (1200px)', 'wp-academic-post-enhanced' ); ?>
    </label>
    <label style="display:block; margin-bottom:5px;">
        <input type="radio" name="wpa_container_width" value="narrow" <?php checked( $width, 'narrow' ); ?>>
        <?php esc_html_e( 'Narrow (800px)', 'wp-academic-post-enhanced' ); ?>
    </label>
    <label style="display:block; margin-bottom:5px;">
        <input type="radio" name="wpa_container_width" value="wide" <?php checked( $width, 'wide' ); ?>>
        <?php esc_html_e( 'Wide (90%)', 'wp-academic-post-enhanced' ); ?>
    </label>
    <label style="display:block; margin-bottom:5px;">
        <input type="radio" name="wpa_container_width" value="full" <?php checked( $width, 'full' ); ?>>
        <?php esc_html_e( 'Full Width (100%)', 'wp-academic-post-enhanced' ); ?>
    </label>
    <label style="display:block; margin-bottom:10px;">
        <input type="radio" name="wpa_container_width" value="custom" <?php checked( $width, 'custom' ); ?>>
        <?php esc_html_e( 'Custom', 'wp-academic-post-enhanced' ); ?>
    </label>
    
    <div id="wpa-custom-width-input" style="display: <?php echo ( $width === 'custom' ) ? 'block' : 'none'; ?>; margin-left: 20px; margin-bottom: 20px;">
        <label for="wpa_container_width_custom"><?php esc_html_e( 'Enter Width (e.g. 1400px):', 'wp-academic-post-enhanced' ); ?></label>
        <input type="text" id="wpa_container_width_custom" name="wpa_container_width_custom" value="<?php echo esc_attr( $custom_width ); ?>" style="width:100%; margin-top:5px;">
    </div>

    <hr>

    <p><strong><?php esc_html_e( 'Sidebar Position', 'wp-academic-post-enhanced' ); ?></strong></p>
    <select name="wpa_sidebar_position" style="width:100%; margin-bottom: 20px;">
        <option value="default" <?php selected( $sidebar, 'default' ); ?>><?php esc_html_e( 'Default (Global)', 'wp-academic-post-enhanced' ); ?></option>
        <option value="none" <?php selected( $sidebar, 'none' ); ?>><?php esc_html_e( 'No Sidebar', 'wp-academic-post-enhanced' ); ?></option>
        <option value="right" <?php selected( $sidebar, 'right' ); ?>><?php esc_html_e( 'Right Sidebar', 'wp-academic-post-enhanced' ); ?></option>
        <option value="left" <?php selected( $sidebar, 'left' ); ?>><?php esc_html_e( 'Left Sidebar', 'wp-academic-post-enhanced' ); ?></option>
    </select>

    <hr>

    <p><strong><?php esc_html_e( 'Visibility Overrides', 'wp-academic-post-enhanced' ); ?></strong></p>
    <label style="display:block; margin-bottom:5px;">
        <input type="checkbox" name="wpa_hide_title" value="1" <?php checked( $hide_title, '1' ); ?>>
        <?php esc_html_e( 'Hide Page Title', 'wp-academic-post-enhanced' ); ?>
    </label>
    <label style="display:block; margin-bottom:5px;">
        <input type="checkbox" name="wpa_hide_featured" value="1" <?php checked( $hide_featured, '1' ); ?>>
        <?php esc_html_e( 'Hide Featured Image', 'wp-academic-post-enhanced' ); ?>
    </label>

    <script>
    jQuery(document).ready(function($){
        $('input[name="wpa_container_width"]').change(function(){
            if($(this).val() === 'custom') {
                $('#wpa-custom-width-input').slideDown();
            } else {
                $('#wpa-custom-width-input').slideUp();
            }
        });
    });
    </script>
    <?php
}

/**
 * Save Meta Box Data.
 */
function wpa_theme_save_layout_meta( $post_id ) {
    if ( ! isset( $_POST['wpa_theme_layout_meta_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['wpa_theme_layout_meta_nonce'], 'wpa_theme_save_layout_meta' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    if ( isset( $_POST['wpa_container_width'] ) ) {
        update_post_meta( $post_id, '_wpa_container_width', sanitize_key( $_POST['wpa_container_width'] ) );
    }
    
    if ( isset( $_POST['wpa_container_width_custom'] ) ) {
        update_post_meta( $post_id, '_wpa_container_width_custom', sanitize_text_field( $_POST['wpa_container_width_custom'] ) );
    }

    if ( isset( $_POST['wpa_sidebar_position'] ) ) {
        update_post_meta( $post_id, '_wpa_sidebar_position', sanitize_key( $_POST['wpa_sidebar_position'] ) );
    }

    update_post_meta( $post_id, '_wpa_hide_title', isset( $_POST['wpa_hide_title'] ) ? '1' : '0' );
    update_post_meta( $post_id, '_wpa_hide_featured', isset( $_POST['wpa_hide_featured'] ) ? '1' : '0' );
}
add_action( 'save_post', 'wpa_theme_save_layout_meta' );
