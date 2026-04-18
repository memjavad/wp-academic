<?php
/**
 * User Profile Custom Fields for Course Instructors.
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add Custom Fields to User Profile
 */
function wpa_add_user_custom_fields( $user ) {
    $image_id = get_user_meta( $user->ID, 'wpa_user_custom_avatar', true );
    $image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '';
    ?>
    <h3><?php esc_html_e( 'Academic Post: Instructor Details', 'wp-academic-post-enhanced' ); ?></h3>
    <table class="form-table">
        <tr>
            <th><label for="wpa_academic_title"><?php esc_html_e( 'Academic Title', 'wp-academic-post-enhanced' ); ?></label></th>
            <td>
                <input type="text" name="wpa_academic_title" id="wpa_academic_title" value="<?php echo esc_attr( get_user_meta( $user->ID, 'wpa_academic_title', true ) ); ?>" class="regular-text" />
                <p class="description"><?php esc_html_e( 'e.g., Professor, PhD, Researcher', 'wp-academic-post-enhanced' ); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="wpa_academic_affiliation"><?php esc_html_e( 'Affiliation', 'wp-academic-post-enhanced' ); ?></label></th>
            <td>
                <input type="text" name="wpa_academic_affiliation" id="wpa_academic_affiliation" value="<?php echo esc_attr( get_user_meta( $user->ID, 'wpa_academic_affiliation', true ) ); ?>" class="regular-text" />
                <p class="description"><?php esc_html_e( 'University or Institution Name', 'wp-academic-post-enhanced' ); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="wpa_academic_orcid"><?php esc_html_e( 'ORCID URL', 'wp-academic-post-enhanced' ); ?></label></th>
            <td>
                <input type="url" name="wpa_academic_orcid" id="wpa_academic_orcid" value="<?php echo esc_attr( get_user_meta( $user->ID, 'wpa_academic_orcid', true ) ); ?>" class="regular-text" />
                <p class="description"><?php esc_html_e( 'e.g., https://orcid.org/0000-0000-0000-0000', 'wp-academic-post-enhanced' ); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="wpa_academic_linkedin"><?php esc_html_e( 'LinkedIn URL', 'wp-academic-post-enhanced' ); ?></label></th>
            <td>
                <input type="url" name="wpa_academic_linkedin" id="wpa_academic_linkedin" value="<?php echo esc_attr( get_user_meta( $user->ID, 'wpa_academic_linkedin', true ) ); ?>" class="regular-text" />
                <p class="description"><?php esc_html_e( 'e.g., https://www.linkedin.com/in/yourprofile/', 'wp-academic-post-enhanced' ); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="wpa_user_custom_avatar"><?php esc_html_e( 'Custom Profile Image', 'wp-academic-post-enhanced' ); ?></label></th>
            <td>
                <div class="wpa-media-picker-container">
                    <div class="wpa-preview-wrapper" style="margin-bottom: 10px;">
                        <img src="<?php echo esc_url( $image_url ); ?>" class="wpa-user-avatar-preview" style="max-width: 100px; height: auto; border-radius: 50%; display: <?php echo $image_url ? 'block' : 'none'; ?>;">
                    </div>
                    <input type="hidden" name="wpa_user_custom_avatar" id="wpa_user_custom_avatar" value="<?php echo esc_attr( $image_id ); ?>">
                    <button type="button" class="button wpa-media-upload-btn"><?php esc_html_e( 'Select Image', 'wp-academic-post-enhanced' ); ?></button>
                    <button type="button" class="button wpa-media-remove-btn" style="<?php echo ! $image_id ? 'display:none;' : ''; ?>"><?php esc_html_e( 'Remove', 'wp-academic-post-enhanced' ); ?></button>
                    <p class="description"><?php esc_html_e( 'Used in the Course Instructor sidebar. If empty, Gravatar will be used.', 'wp-academic-post-enhanced' ); ?></p>
                </div>
            </td>
        </tr>
    </table>
    <?php
}
add_action( 'show_user_profile', 'wpa_add_user_custom_fields' );
add_action( 'edit_user_profile', 'wpa_add_user_custom_fields' );

/**
 * Save Custom Fields
 */
function wpa_save_user_custom_fields( $user_id ) {
    if ( ! current_user_can( 'edit_user', $user_id ) ) {
        return;
    }
    if ( isset( $_POST['wpa_user_custom_avatar'] ) ) {
        update_user_meta( $user_id, 'wpa_user_custom_avatar', sanitize_text_field( $_POST['wpa_user_custom_avatar'] ) );
    }
    if ( isset( $_POST['wpa_academic_title'] ) ) {
        update_user_meta( $user_id, 'wpa_academic_title', sanitize_text_field( $_POST['wpa_academic_title'] ) );
    }
    if ( isset( $_POST['wpa_academic_affiliation'] ) ) {
        update_user_meta( $user_id, 'wpa_academic_affiliation', sanitize_text_field( $_POST['wpa_academic_affiliation'] ) );
    }
    if ( isset( $_POST['wpa_academic_orcid'] ) ) {
        update_user_meta( $user_id, 'wpa_academic_orcid', esc_url_raw( $_POST['wpa_academic_orcid'] ) );
    }
    if ( isset( $_POST['wpa_academic_linkedin'] ) ) {
        update_user_meta( $user_id, 'wpa_academic_linkedin', esc_url_raw( $_POST['wpa_academic_linkedin'] ) );
    }
}
add_action( 'personal_options_update', 'wpa_save_user_custom_fields' );
add_action( 'edit_user_profile_update', 'wpa_save_user_custom_fields' );
