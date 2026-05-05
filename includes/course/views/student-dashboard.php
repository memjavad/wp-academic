<?php
/**
 * View template for the student dashboard shortcode.
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

// Variables extracted from the shortcode function are available here:
// $current_user, $bg_url, $header_class, $header_style, $show_avatar, $user_id, $welcome_text, $completed_count, $enrolled_ids, $active_courses, $completed_courses, $all_courses_html

?>
<div class="wpa-dash-header <?php echo esc_attr( $header_class ); ?>">
    <?php if ( $bg_url ) : ?>
        <div class="wpa-dash-bg" <?php echo $header_style; ?>></div>
    <?php endif; ?>

    <div class="wpa-dash-header-inner">
        <?php if ( $show_avatar ) : ?>
            <div class="wpa-dash-avatar">
                <?php
                $custom_avatar_id = get_user_meta( $user_id, 'wpa_user_custom_avatar', true );
                if ( $custom_avatar_id ) {
                    echo wp_get_attachment_image( $custom_avatar_id, [100, 100], false, ['class' => 'wpa-custom-avatar'] );
                } else {
                    echo get_avatar( $user_id, 100 );
                }
                ?>
            </div>
        <?php endif; ?>

        <div class="wpa-dash-info">
            <h2><?php echo esc_html( $welcome_text ); ?></h2>
            <div class="wpa-dash-stats">
                <?php
                echo date_i18n( get_option( 'date_format' ) );
                ?>
            </div>
        </div>
    </div>
    <?php if ( $bg_url ) : ?><div class="wpa-dash-overlay"></div><?php endif; ?>
</div>

<?php if ( empty( $enrolled_ids ) || ! is_array( $enrolled_ids ) ) : ?>
    <p><?php echo WPA_Theme_Labels::get( 'dash_not_enrolled' ); ?></p>
<?php else : ?>
    <div class="wpa-dashboard-wrapper">
        <div class="wpa-dashboard-tabs">
            <button class="wpa-dash-tab active" data-tab="active"><?php echo WPA_Theme_Labels::get('status_active'); ?> <span class="count"><?php echo count($active_courses); ?></span></button>
            <button class="wpa-dash-tab" data-tab="completed"><?php echo WPA_Theme_Labels::get('status_completed'); ?> <span class="count"><?php echo count($completed_courses); ?></span></button>
            <button class="wpa-dash-tab" data-tab="all"><?php echo WPA_Theme_Labels::get('status_all_courses'); ?></button>
        </div>

        <div id="wpa-dash-active" class="wpa-dash-content active">
            <div class="wpa-dashboard-grid">
                <?php echo !empty($active_courses) ? implode('', $active_courses) : '<p>' . WPA_Theme_Labels::get('msg_no_active') . '</p>'; ?>
            </div>
        </div>

        <div id="wpa-dash-completed" class="wpa-dash-content" style="display:none;">
            <div class="wpa-dashboard-grid">
                <?php echo !empty($completed_courses) ? implode('', $completed_courses) : '<p>' . WPA_Theme_Labels::get('msg_no_completed') . '</p>'; ?>
            </div>
        </div>

        <div id="wpa-dash-all" class="wpa-dash-content" style="display:none;">
            <div class="wpa-dashboard-grid">
                <?php echo $all_courses_html; ?>
            </div>
        </div>
    </div>
<?php endif; ?>
