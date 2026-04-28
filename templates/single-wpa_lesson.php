<?php
/**
 * Template for Single Lesson
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

$lesson_id = get_the_ID();
$course_id = get_post_meta( $lesson_id, '_wpa_course_id', true );

// Settings (Theme > Course)
$theme_opts = get_option( 'wpa_homepage_settings', [] );
$course_opts = get_option( 'wpa_course_settings', [] );


// Access Control
$can_access = function_exists('wpa_course_user_can_access') ? wpa_course_user_can_access( $course_id ) : true;
$is_enrolled = function_exists('wpa_course_is_user_enrolled') ? wpa_course_is_user_enrolled( $course_id ) : false;

// Display Logic
$show_sidebar = wpa_get_setting( 'lesson_show_sidebar', 'show_sidebar', 1, $theme_opts, $course_opts );
$sidebar_pos  = wpa_get_setting( 'lesson_sidebar_pos', 'sidebar_position', 'right', $theme_opts, $course_opts );
$video_pos    = wpa_get_setting( 'lesson_video_pos', 'video_position', 'top', $theme_opts, $course_opts );
$enable_focus = wpa_get_setting( 'lesson_enable_focus_mode', 'enable_focus_mode', 1, $theme_opts, $course_opts );
$show_bread   = wpa_get_setting( 'lesson_show_breadcrumbs', 'show_breadcrumbs', 1, $theme_opts, $course_opts );
$show_materials = wpa_get_setting( 'lesson_show_materials', 'enable_materials', 1, $theme_opts, $course_opts );
$show_nav     = wpa_get_setting( 'lesson_show_nav', 'show_nav_buttons', 1, $theme_opts, $course_opts );

// Layout Class
$layout_class = 'wpa-lesson-layout sidebar-' . ( $show_sidebar ? esc_attr( $sidebar_pos ) : 'none' );

// Instructor Logic
$show_instructor = wpa_get_setting('lesson_show_instructor', 'show_instructor_card', 1, $theme_opts, $course_opts);
$author_id = get_post_field( 'post_author', $course_id );
$author_name = get_the_author_meta( 'display_name', $author_id );
$author_bio = get_the_author_meta( 'description', $author_id );
?>

<main class="wpa-lesson-main-wrapper">
    <div class="wpa-container" style="margin-top: 90px; margin-bottom: 60px;">
        
        <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

        <?php if ( ! $course_id ) : ?>
            <div class="wpa-lesson-content-area">
                <div class="wpa-lesson-main-header">
                    <h1 class="wpa-lesson-title-main"><?php the_title(); ?></h1>
                </div>
                
                <?php 
                $video_url = get_post_meta( $lesson_id, '_wpa_lesson_video_url', true );
                if ( ! empty( $video_url ) ) {
                    echo '<div class="wpa-video-container" style="margin-bottom:30px;">' . wp_oembed_get( $video_url ) . '</div>';
                }
                ?>

                <div class="wpa-lesson-body">
                    <?php 
                    the_content(); 
                    ?>
                </div>
            </div>
        <?php else : ?>

            <div class="<?php echo esc_attr( $layout_class ); ?>">
                
                <!-- Main Content Area -->
                <div class="wpa-lesson-content-area">
                    
                    <div class="wpa-lesson-main-header">
                        <h1 class="wpa-lesson-title-main"><?php the_title(); ?></h1>
                        <?php if ( $enable_focus ) : ?>
                            <button id="wpa-focus-mode-toggle" class="button wpa-btn wpa-btn-text wpa-focus-btn" title="<?php echo WPA_Theme_Labels::get('lesson_focus_mode'); ?>">
                                <?php echo WPA_Icons::get('fullscreen-alt'); ?>
                                <?php echo WPA_Theme_Labels::get('lesson_focus_mode'); ?>
                            </button>
                        <?php endif; ?>
                    </div>

                    <?php if ( $show_bread ) : ?>
                    <nav class="wpa-breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'wp-academic-post-enhanced' ); ?>" itemscope itemtype="https://schema.org/BreadcrumbList">
                        <span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                            <a itemprop="item" href="<?php echo esc_url( home_url( '/' ) ); ?>"><span itemprop="name"><?php echo WPA_Theme_Labels::get('label_home'); ?></span></a>
                            <meta itemprop="position" content="1" />
                        </span>
                        <span class="wpa-breadcrumb-sep">›</span>
                        <span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                            <a itemprop="item" href="<?php echo esc_url( get_permalink( $course_id ) ); ?>"><span itemprop="name"><?php echo esc_html( get_the_title( $course_id ) ); ?></span></a>
                            <meta itemprop="position" content="2" />
                        </span>
                        <span class="wpa-breadcrumb-sep">›</span>
                        <span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                            <span itemprop="name" class="wpa-breadcrumb-current"><?php the_title(); ?></span>
                            <meta itemprop="position" content="3" />
                        </span>
                    </nav>
                    <?php endif; ?>

                    <?php if ( ! $can_access ) : ?>
                        <div class="wpa-lesson-locked">
                            <p><?php echo WPA_Icons::get('lock'); ?> <?php echo WPA_Theme_Labels::get('locked_message'); ?></p>
                            <a href="<?php echo get_permalink( $course_id ); ?>" class="wpa-btn wpa-btn-primary"><?php echo WPA_Theme_Labels::get('label_course_home'); ?></a>
                        </div>
                    <?php else : ?>
                        
                        <?php 
                        $video_url = get_post_meta( $lesson_id, '_wpa_lesson_video_url', true );
                        if ( $video_pos === 'top' && ! empty( $video_url ) ) {
                            echo '<div class="wpa-video-container">' . wp_oembed_get( $video_url ) . '</div>';
                        }
                        ?>

                        <div class="wpa-lesson-body">
                            <?php 
                            the_content(); 
                            ?>
                        </div>

                        <?php 
                        if ( $video_pos !== 'top' && ! empty( $video_url ) ) {
                            echo '<div class="wpa-video-container" style="margin-top:30px;">' . wp_oembed_get( $video_url ) . '</div>';
                        }
                        ?>

                        <?php 
                        $materials_raw = get_post_meta( $lesson_id, '_wpa_lesson_materials', true );
                        if ( $show_materials && ! empty( $materials_raw ) ) : ?>
                            <div class="wpa-lesson-materials">
                                <h5><?php echo WPA_Icons::get('pdf'); ?> <?php echo WPA_Theme_Labels::get('label_materials'); ?></h5>
                                <ul>
                                    <?php 
                                    $lines = explode( "\n", $materials_raw );
                                    foreach ( $lines as $line ) {
                                        $parts = explode( '|', $line );
                                        $url = trim( $parts[0] );
                                        $label = isset( $parts[1] ) ? trim( $parts[1] ) : WPA_Theme_Labels::get('label_download');
                                        if ( $url ) {
                                            echo '<li><a href="' . esc_url( $url ) . '" target="_blank">' . esc_html( $label ) . ' ' . WPA_Icons::get('download') . '</a></li>';
                                        }
                                    }
                                    ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if ( $show_nav ) : ?>
                        <div class="wpa-course-navigation">
                            <?php
                            $all_lessons = get_posts( [ 'post_type' => 'wpa_lesson', 'meta_key' => '_wpa_course_id', 'meta_value' => $course_id, 'posts_per_page' => -1, 'orderby' => 'menu_order', 'order' => 'ASC', 'fields' => 'ids' ] );
                            $current_index = array_search( $lesson_id, $all_lessons );
                            $prev_id = ( $current_index > 0 ) ? $all_lessons[ $current_index - 1 ] : false;
                            $next_id = ( $current_index < count( $all_lessons ) - 1 ) ? $all_lessons[ $current_index + 1 ] : false;
                            
                            if ( $prev_id ) {
                                echo '<a href="' . get_permalink( $prev_id ) . '" class="wpa-btn wpa-btn-outline">&larr; ' . WPA_Theme_Labels::get('label_prev_lesson') . '</a>';
                            } else {
                                echo '<span></span>';
                            }

                            $is_completed = function_exists('wpa_course_is_lesson_completed') ? wpa_course_is_lesson_completed( $lesson_id ) : false;
                            $btn_text = $is_completed ? WPA_Theme_Labels::get('label_completed') : WPA_Theme_Labels::get('label_mark_complete');
                            $btn_class = $is_completed ? 'wpa-btn wpa-btn-success' : 'wpa-btn wpa-btn-outline';
                            $disabled = $is_completed ? 'disabled' : '';
                            
                            echo '<button id="wpa-mark-complete" class="' . esc_attr( $btn_class ) . '" data-lesson="' . esc_attr( $lesson_id ) . '" ' . $disabled . '>' . esc_html( $btn_text ) . '</button>';

                            if ( $next_id ) {
                                echo '<a href="' . get_permalink( $next_id ) . '" class="wpa-btn wpa-btn-primary">' . WPA_Theme_Labels::get('label_next_lesson') . ' &rarr;</a>';
                            } else {
                                echo '<span></span>';
                            }
                            ?>
                        </div>
                        <?php endif; ?>

                    <?php endif; ?>

                </div> <!-- .wpa-lesson-content-area -->

                <!-- Sidebar Area -->
                <?php if ( $show_sidebar ) : ?>
                    <div class="wpa-lesson-sidebar">
                        <h4><a href="<?php echo get_permalink( $course_id ); ?>"><?php echo get_the_title( $course_id ); ?></a></h4>
                        
                        <?php 
                        $show_sb_prog = wpa_get_setting('lesson_show_sidebar_progress', 'show_sidebar_progress', 1, $theme_opts, $course_opts);
                        if ( $is_enrolled && $show_sb_prog ) : 
                            $progress = function_exists('wpa_course_get_progress') ? wpa_course_get_progress( $course_id ) : 0;
                        ?>
                            <div class="wpa-sidebar-progress" role="progressbar" aria-valuenow="<?php echo esc_attr( $progress ); ?>" aria-valuemin="0" aria-valuemax="100"><div class="wpa-progress-fill" style="width:<?php echo esc_attr( $progress ); ?>%"></div></div>
                        <?php endif; ?>

                        <?php if ( $show_instructor ) : ?>
                            <div class="wpa-instructor-card wpa-lesson-teacher-card" style="margin-top: 25px; margin-bottom: 25px;">
                                <div class="wpa-instructor-header" style="text-align: left; padding: 12px 20px; font-size: 0.75rem;"><?php echo WPA_Theme_Labels::get('lesson_instructor'); ?></div>
                                <div class="wpa-instructor-content" style="padding: 15px 20px; text-align: left; display: flex; align-items: center; gap: 15px;">
                                    <div class="wpa-instructor-avatar" style="margin-bottom: 0; flex-shrink: 0;">
                                        <?php 
                                        $custom_avatar_id = get_user_meta( $author_id, 'wpa_user_custom_avatar', true );
                                        if ( $custom_avatar_id ) {
                                            echo wp_get_attachment_image( $custom_avatar_id, [50, 50], false, ['class' => 'wpa-custom-avatar', 'style' => 'width:50px; height:50px; border-width: 2px;'] );
                                        } else {
                                            echo get_avatar( $author_id, 50, '', '', ['class' => 'photo', 'extra_attr' => 'style="width:50px; height:50px; border-radius:50%; border: 2px solid var(--wpa-bg-light);"'] ); 
                                        }
                                        ?>
                                    </div>
                                    <div class="wpa-instructor-info">
                                        <h4 class="wpa-instructor-name" style="font-size: 0.95rem; margin: 0; color: var(--wpa-text-main); font-weight: 700;"><?php echo esc_html( $author_name ); ?></h4>
                                        <div class="wpa-instructor-bio" style="font-size: 0.75rem; opacity: 0.7; margin-top: 3px; line-height: 1.4;"><?php echo wp_trim_words( $author_bio, 8 ); ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php 
                        $sidebar_lessons = get_posts( [ 'post_type' => 'wpa_lesson', 'meta_key' => '_wpa_course_id', 'meta_value' => $course_id, 'posts_per_page' => -1, 'orderby' => 'menu_order', 'order' => 'ASC' ] );
                        
                        if ( ! empty( $sidebar_lessons ) ) {
                            $sections = [];
                            foreach ( $sidebar_lessons as $lesson ) {
                                $sec = get_post_meta( $lesson->ID, '_wpa_lesson_section', true );
                                if ( ! $sec ) $sec = __( 'General', 'wp-academic-post-enhanced' );
                                $sections[$sec][] = $lesson;
                            }

                            echo '<div class="wpa-sidebar-curriculum">';
                            foreach ( $sections as $sec_title => $sec_lessons ) {
                                if ( count($sections) > 1 || $sec_title !== __( 'General', 'wp-academic-post-enhanced' ) ) {
                                    echo '<h5 class="wpa-sidebar-section-title">' . esc_html( $sec_title ) . '</h5>';
                                }
                                echo '<ul class="wpa-sidebar-list">';
                                foreach ( $sec_lessons as $l ) {
                                    $is_current = ( $l->ID === $lesson_id );
                                    $l_done = function_exists('wpa_course_is_lesson_completed') ? wpa_course_is_lesson_completed( $l->ID ) : false;
                                    $class = $is_current ? 'class="current-lesson"' : '';
                                    
                                    echo '<li ' . $class . '>';
                                    echo '<a href="' . get_permalink( $l->ID ) . '">';
                                    echo esc_html( wpa_get_clean_title( $l->ID ) );
                                    if ( $l_done ) {
                                        echo ' ' . WPA_Icons::get('yes', 'wpa-check');
                                    }
                                    echo '</a>';
                                    echo '</li>';
                                }
                                echo '</ul>';
                            }
                            echo '</div>';
                        }
                        ?>
                    </div> <!-- .wpa-lesson-sidebar -->
                <?php endif; ?>

            </div> <!-- .wpa-lesson-layout -->
        <?php endif; ?>

        <?php endwhile; endif; ?>

    </div> <!-- .wpa-container -->
</main>

<?php 
if ( function_exists( 'wpa_get_footer' ) ) {
    wpa_get_footer();
} else {
    get_footer();
}
?>