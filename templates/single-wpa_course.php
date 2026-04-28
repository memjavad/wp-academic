<?php
/**
 * Template for Single Course
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( function_exists( 'wpa_get_header' ) ) {
    wpa_get_header();
} else {
    get_header();
}

$course_id = get_the_ID();

// Settings (Theme > Course)
$theme_opts = get_option( 'wpa_homepage_settings', [] );
$course_opts = get_option( 'wpa_course_settings', [] );

// Display Settings
$show_dur = wpa_get_setting( 'course_show_duration', 'show_course_duration', 1, $theme_opts, $course_opts );
$show_lvl = wpa_get_setting( 'course_show_level', 'show_course_level', 1, $theme_opts, $course_opts );
$curr_style = wpa_get_setting( 'course_curriculum_style', 'curriculum_style', 'modern', $theme_opts, $course_opts );
$show_curr_dur = wpa_get_setting( 'course_show_curriculum_duration', 'show_curriculum_duration', 1, $theme_opts, $course_opts );
$show_curr_icon = wpa_get_setting( 'course_show_curriculum_icons', 'show_curriculum_icons', 1, $theme_opts, $course_opts );

// Labels
$lbl_start = WPA_Theme_Labels::get( 'label_start_course' );
$lbl_login = WPA_Theme_Labels::get( 'label_login_enroll' );
$lbl_curr  = WPA_Theme_Labels::get( 'label_curriculum' );

// Meta
$duration = get_post_meta( $course_id, '_wpa_course_duration', true );
$level    = get_post_meta( $course_id, '_wpa_course_level', true );
$price    = get_post_meta( $course_id, '_wpa_course_price', true );
$is_enrolled = function_exists('wpa_course_is_user_enrolled') ? wpa_course_is_user_enrolled( $course_id ) : false;

?>

<main class="wpa-course-main-wrapper">
    <div class="wpa-container" style="margin-top: 90px; margin-bottom: 60px;">
        
        <!-- Hero Section -->
    <div class="wpa-course-hero-v2">
        <?php if ( has_post_thumbnail() ) : ?>
            <div class="wpa-hero-bg-blur"><?php the_post_thumbnail( 'large' ); ?></div>
            <div class="wpa-hero-thumb-wrap"><?php the_post_thumbnail( 'large' ); ?></div>
        <?php endif; ?>

        <div class="wpa-hero-info">
            <h1 class="wpa-hero-title"><?php the_title(); ?></h1>
            
            <div class="wpa-hero-meta-row">
                <?php if ( $show_dur && $duration ) : ?>
                    <span class="wpa-hero-meta-item"><?php echo WPA_Icons::get('clock'); ?> <?php echo esc_html( $duration ); ?></span>
                <?php endif; ?>
                <?php if ( $show_lvl && $level ) : ?>
                    <span class="wpa-hero-meta-item"><?php echo WPA_Icons::get('chart-bar'); ?> <?php echo esc_html( $level ); ?></span>
                <?php endif; ?>
                <?php if ( $price ) : ?>
                    <span class="wpa-hero-meta-item wpa-price-badge"><?php echo esc_html( $price ); ?></span>
                <?php endif; ?>
            </div>

            <div class="wpa-hero-action-area">
                <?php if ( is_user_logged_in() ) : ?>
                    <?php if ( $is_enrolled ) : 
                        $progress = function_exists('wpa_course_get_progress') ? wpa_course_get_progress( $course_id ) : 0;
                    ?>
                        <div class="wpa-hero-progress-label"><?php printf( WPA_Theme_Labels::get('label_your_progress'), $progress ); ?></div>
                        <div class="wpa-course-progress-bar" role="progressbar" aria-valuenow="<?php echo esc_attr( $progress ); ?>" aria-valuemin="0" aria-valuemax="100"><div class="wpa-progress-fill" style="width:<?php echo esc_attr( $progress ); ?>%"></div></div>
                        
                        <?php if ( $progress >= 100 ) : 
                            $cert_link = add_query_arg( [ 'wpa_download_certificate' => '1', 'course_id' => $course_id ], home_url() );
                        ?>
                            <div class="wpa-hero-cert-wrap">
                                <a href="<?php echo esc_url( $cert_link ); ?>" class="wpa-btn wpa-btn-outline wpa-btn-lg" target="_blank">
                                    <?php echo WPA_Icons::get('awards'); ?> <?php echo WPA_Theme_Labels::get('label_download') . ' ' . WPA_Theme_Labels::get('lesson_certificate'); ?>
                                </a>
                            </div>
                        <?php endif; ?>

                    <?php else : ?>
                        <?php 
                        $prereq_id = get_post_meta( $course_id, '_wpa_course_prerequisite', true );
                        $prereq_met = true;
                        if ( $prereq_id && function_exists('wpa_course_is_course_completed') && ! wpa_course_is_course_completed( $prereq_id ) ) {
                            $prereq_met = false;
                        }

                        if ( ! $prereq_met ) {
                            echo '<button class="wpa-btn wpa-btn-primary wpa-btn-lg" disabled style="opacity:0.7; cursor:not-allowed;">' . __( 'Prerequisite Required', 'wp-academic-post-enhanced' ) . '</button>';
                        } else {
                            echo '<button id="wpa-enroll-course" class="wpa-btn wpa-btn-primary wpa-btn-lg" data-course="' . esc_attr( $course_id ) . '">' . esc_html( $lbl_start ) . '</button>';
                        }
                        ?>
                    <?php endif; ?>
                <?php else : ?>
                    <a href="<?php echo wp_login_url( get_permalink() ); ?>" class="wpa-btn wpa-btn-primary wpa-btn-lg"><?php echo esc_html( $lbl_login ); ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Layout Wrapper -->
    <div class="wpa-course-layout-wrapper" style="margin-top: 40px;">
        
        <!-- Main Content -->
        <div class="wpa-course-main-content">
            
            <div class="wpa-card wpa-course-description" style="padding: 30px; margin-bottom: 30px;">
                <h3 style="margin-top:0;"><?php echo WPA_Theme_Labels::get('news_about_title'); ?></h3>
                <?php 
                $content = get_post_field( 'post_content', $course_id );
                echo apply_filters( 'the_content', $content ); 
                ?>
            </div>

            <!-- Curriculum -->
            <div class="wpa-course-curriculum style-<?php echo esc_attr( $curr_style ); ?>">
                <h3><?php echo esc_html( $lbl_curr ); ?></h3>
                <?php 
                $lessons = get_posts( [ 'post_type' => 'wpa_lesson', 'meta_key' => '_wpa_course_id', 'meta_value' => $course_id, 'posts_per_page' => -1, 'orderby' => 'menu_order', 'order' => 'ASC' ] );

                if ( ! empty( $lessons ) ) {
                    // Sections enabled? Usually yes for display.
                    $sections = [];
                    foreach ( $lessons as $lesson ) {
                        $sec = get_post_meta( $lesson->ID, '_wpa_lesson_section', true );
                        if ( ! $sec ) $sec = __( 'General', 'wp-academic-post-enhanced' );
                        $sections[$sec][] = $lesson;
                    }

                    foreach ( $sections as $sec_title => $sec_lessons ) {
                        if ( count($sections) > 1 || $sec_title !== __( 'General', 'wp-academic-post-enhanced' ) ) {
                            echo '<h4 class="wpa-section-title">' . esc_html( $sec_title ) . '</h4>';
                        }
                        echo '<ul class="wpa-lesson-list">';
                        foreach ( $sec_lessons as $l ) {
                            $is_done = function_exists('wpa_course_is_lesson_completed') ? wpa_course_is_lesson_completed( $l->ID ) : false;
                            $can_access = function_exists('wpa_course_user_can_access') ? wpa_course_user_can_access( $course_id ) : false;
                            
                            // Get Index
                            $lesson_index = array_search( $l, $lessons ) + 1;

                            $icon_html = '';
                            if ( $show_curr_icon ) {
                                $is_video = get_post_meta( $l->ID, '_wpa_lesson_video_url', true );
                                $ic = $is_video ? 'megaphone' : 'text-page'; // Using megaphone for video or another suitable icon if available
                                $icon_html = WPA_Icons::get($ic, 'wpa-lesson-icon') . ' ';
                            }
                            
                            $status_icon = $is_done ? WPA_Icons::get('yes', 'wpa-lesson-check') . ' ' : '';
                            if ( ! $can_access ) $status_icon = WPA_Icons::get('lock', 'wpa-lesson-lock') . ' ';
                            
                            echo '<li><a href="' . get_permalink($l->ID) . '">';
                            echo '<div class="wpa-list-left">';
                            echo '<span class="wpa-lesson-number">' . sprintf('%02d', $lesson_index) . '</span>';
                            echo '<div class="wpa-lesson-title-wrap">' . $icon_html . '<span class="wpa-lesson-title-text">' . esc_html( wpa_get_clean_title( $l->ID ) ) . '</span></div>';
                            echo '</div>';
                            
                            echo '<div class="wpa-list-right">';
                            if ( $show_curr_dur ) {
                                $ldur = get_post_meta( $l->ID, '_wpa_lesson_duration', true );
                                if ( $ldur ) echo '<span class="wpa-lesson-duration">' . esc_html( $ldur ) . '</span>';
                            }
                            echo $status_icon;
                            echo '</div>';
                            
                            echo '</a></li>';
                        }
                        echo '</ul>';
                    }
                } else {
                    echo '<p>' . WPA_Theme_Labels::get('label_no_lessons') . '</p>';
                }
                ?>
            </div>

        </div>

        <!-- Sidebar -->
        <div class="wpa-course-sidebar-area">
            <div class="wpa-instructor-card">
                <?php 
                $author_id = get_post_field( 'post_author', $course_id );
                $author_name = get_the_author_meta( 'display_name', $author_id );
                $author_bio = get_the_author_meta( 'description', $author_id );
                ?>
                <div class="wpa-instructor-header"><?php echo WPA_Theme_Labels::get('lesson_instructor'); ?></div>
                <div class="wpa-instructor-content">
                    <div class="wpa-instructor-avatar">
                        <?php 
                        $custom_avatar_id = get_user_meta( $author_id, 'wpa_user_custom_avatar', true );
                        if ( $custom_avatar_id ) {
                            echo wp_get_attachment_image( $custom_avatar_id, [120, 120], false, ['class' => 'wpa-custom-avatar'] );
                        } else {
                            echo get_avatar( $author_id, 120 ); 
                        }
                        ?>
                    </div>
                    <h4 class="wpa-instructor-name"><?php echo esc_html( $author_name ); ?></h4>
                    <div class="wpa-instructor-bio"><?php echo wp_trim_words( $author_bio, 20 ); ?></div>
                </div>
            </div>
        </div>

    </div>
</main>

<?php 
if ( function_exists( 'wpa_get_footer' ) ) {
    wpa_get_footer();
} else {
    get_footer();
}
?>