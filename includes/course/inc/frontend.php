<?php
/**
 * Frontend Logic for Courses and Lessons.
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Frontend: Display Course Info & Curriculum
 */
function wpa_course_content_filter( $content ) {
    if ( is_singular( 'wpa_course' ) ) {
        $options = get_option( 'wpa_course_settings' );
        
        // Fetch Labels via Theme Manager
        $completed_msg = WPA_Theme_Labels::get( 'completed_message' );
        $label_curriculum = WPA_Theme_Labels::get( 'label_curriculum' );
        $label_start = WPA_Theme_Labels::get( 'label_start_course' );
        $label_login_enroll = WPA_Theme_Labels::get( 'label_login_enroll' );

        // Visibility & Styling
        $list_style = isset( $options['curriculum_style'] ) ? $options['curriculum_style'] : 'modern';

        // Visibility & Styling
        $show_meta_header = isset( $options['show_course_meta_header'] ) ? $options['show_course_meta_header'] : 1;
        $show_duration = isset( $options['show_course_duration'] ) ? $options['show_course_duration'] : 1;
        $show_level = isset( $options['show_course_level'] ) ? $options['show_course_level'] : 1;
        $show_list_duration = isset( $options['show_curriculum_duration'] ) ? $options['show_curriculum_duration'] : 1;
        $show_list_icons = isset( $options['show_curriculum_icons'] ) ? $options['show_curriculum_icons'] : 1;
        
        $header_bg = ! empty( $options['course_header_bg_color'] ) ? $options['course_header_bg_color'] : '#f3f4f6';
        $header_text = ! empty( $options['course_header_text_color'] ) ? $options['course_header_text_color'] : '#1f2937';

        $course_id = get_the_ID();
        $is_enrolled = wpa_course_is_user_enrolled( $course_id );
        
        // Fetch Details
        $duration = get_post_meta( $course_id, '_wpa_course_duration', true );
        $level    = get_post_meta( $course_id, '_wpa_course_level', true );
        $type     = get_post_meta( $course_id, '_wpa_course_type', true );
        $price    = get_post_meta( $course_id, '_wpa_course_price', true );
        $language = get_post_meta( $course_id, '_wpa_course_language', true );
        $lessons_count = count( get_posts( [ 'post_type' => 'wpa_lesson', 'meta_key' => '_wpa_course_id', 'meta_value' => $course_id, 'posts_per_page' => -1 ] ) );

        // --- Course Hero Header (Redesigned) ---
        $course_thumb = get_the_post_thumbnail( $course_id, 'large', ['class' => 'wpa-course-hero-image'] );
        $course_title = get_the_title( $course_id );
        
        $hero_html = '<div class="wpa-course-hero-v2">';
        
        if ( $course_thumb ) {
            $hero_html .= '<div class="wpa-hero-bg-blur">' . $course_thumb . '</div>';
            $hero_html .= '<div class="wpa-hero-thumb-wrap">' . $course_thumb . '</div>';
        }

        $hero_html .= '<div class="wpa-hero-info">';
        $hero_html .= '<h1 class="wpa-hero-title">' . esc_html( $course_title ) . '</h1>';
        
        // Hero Meta Row
        $hero_html .= '<div class="wpa-hero-meta-row">';
        if ( $duration ) $hero_html .= '<span class="wpa-hero-meta-item"><span class="dashicons dashicons-clock"></span> ' . esc_html( $duration ) . '</span>';
        if ( $level )    $hero_html .= '<span class="wpa-hero-meta-item"><span class="dashicons dashicons-chart-bar"></span> ' . esc_html( $level ) . '</span>';
        if ( $price )    $hero_html .= '<span class="wpa-hero-meta-item wpa-price-badge">' . esc_html( $price ) . '</span>';
        $hero_html .= '</div>';

        // Progress or Enroll Button
        if ( is_user_logged_in() ) {
            if ( $is_enrolled ) {
                $progress = wpa_course_get_progress( $course_id );
                $hero_html .= '<div class="wpa-hero-action-area enrolled">';
                $hero_html .= '<div class="wpa-hero-progress-label">' . sprintf( WPA_Theme_Labels::get('label_your_progress'), $progress ) . '</div>';
                $hero_html .= '<div class="wpa-course-progress-bar"><div class="wpa-progress-fill" style="width:' . esc_attr( $progress ) . '%"></div></div>';
                
                // Certificate Button
                if ( $progress >= 100 ) {
                    $cert_link = add_query_arg( [ 'wpa_download_certificate' => '1', 'course_id' => $course_id ], home_url() );
                    $hero_html .= '<div class="wpa-hero-cert-wrap">';
                    $hero_html .= '<a href="' . esc_url( $cert_link ) . '" class="wpa-btn wpa-btn-outline" target="_blank"><span class="dashicons dashicons-awards"></span> ' . WPA_Theme_Labels::get('label_download') . ' ' . WPA_Theme_Labels::get('lesson_certificate') . '</a>';
                    $hero_html .= '</div>';
                }
                
                $hero_html .= '</div>';
            } else {
                $hero_html .= '<div class="wpa-hero-action-area">';
                
                // Check Prerequisite
                $prereq_id = get_post_meta( $course_id, '_wpa_course_prerequisite', true );
                if ( $prereq_id && ! wpa_course_is_course_completed( $prereq_id ) ) {
                    $prereq_title = get_the_title( $prereq_id );
                    $hero_html .= '<a href="' . get_permalink( $prereq_id ) . '" class="wpa-btn wpa-btn-secondary" style="background:#9ca3af; cursor:not-allowed;">' . sprintf( WPA_Theme_Labels::get('lesson_prereq_msg'), $prereq_title ) . '</a>';
                } else {
                    $hero_html .= '<button id="wpa-enroll-course" class="wpa-btn wpa-btn-primary" data-course="' . esc_attr( $course_id ) . '">' . esc_html( $label_start ) . '</button>';
                }
                
                $hero_html .= '</div>';
            }
        } else {
            $hero_html .= '<div class="wpa-hero-action-area">';
            $hero_html .= '<a href="' . wp_login_url( get_permalink() ) . '" class="wpa-btn wpa-btn-primary">' . esc_html( $label_login_enroll ) . '</a>';
            $hero_html .= '</div>';
        }

        $hero_html .= '</div>'; // .wpa-hero-info
        $hero_html .= '</div>'; // .wpa-course-hero-v2

        $meta_html = ''; // Disable old meta box as it's now in hero

        // --- Instructor Sidebar ---
        $author_id    = get_post_field( 'post_author', $course_id );
        $author_name  = get_the_author_meta( 'display_name', $author_id );
        $author_bio   = get_the_author_meta( 'description', $author_id );
        
        // Custom Avatar Check
        $custom_avatar_id = get_user_meta( $author_id, 'wpa_user_custom_avatar', true );
        if ( $custom_avatar_id ) {
            $author_avatar = wp_get_attachment_image( $custom_avatar_id, [120,120], false, ['class' => 'wpa-custom-avatar'] );
        } else {
            $author_avatar = get_avatar( $author_id, 120 );
        }

        $instructor_html = '<div class="wpa-course-sidebar-area">';
        $instructor_html .= '<div class="wpa-instructor-card">';
        $instructor_html .= '<div class="wpa-instructor-header">' . WPA_Theme_Labels::get('course_instructor_heading') . '</div>';
        $instructor_html .= '<div class="wpa-instructor-content">';
        $instructor_html .= '<div class="wpa-instructor-avatar">' . $author_avatar . '</div>';
        $instructor_html .= '<h4 class="wpa-instructor-name">' . esc_html( $author_name ) . '</h4>';
        if ( ! empty( $author_bio ) ) {
            $instructor_html .= '<div class="wpa-instructor-bio">' . wp_strip_all_tags( $author_bio ) . '</div>';
        }
        $instructor_html .= '</div>'; // .instructor-content
        $instructor_html .= '</div>'; // .instructor-card
        $instructor_html .= '</div>'; // .sidebar-area

        // Curriculum List
        $lessons = get_posts( [
            'post_type' => 'wpa_lesson',
            'meta_key' => '_wpa_course_id',
            'meta_value' => $course_id,
            'posts_per_page' => -1,
            'orderby' => 'menu_order',
            'order' => 'ASC',
        ] );

        $list_style_layout = isset( $options['lesson_list_style'] ) ? $options['lesson_list_style'] : 'simple';

        $list_html = '<div class="wpa-course-curriculum style-' . esc_attr( $list_style ) . ' layout-' . esc_attr( $list_style_layout ) . '">';
        $list_html .= '<h3>' . esc_html( $label_curriculum ) . '</h3>';
        
        $enable_sections = isset( $options['enable_sections'] ) ? $options['enable_sections'] : 1;

        if ( ! empty( $lessons ) ) {
            // Bulk cache post meta to prevent N+1 queries
            update_postmeta_cache( wp_list_pluck( $lessons, 'ID' ) );
            $sections = [];
            // Group logic (if enabled) OR Flat List
            if ( $enable_sections ) {
                foreach ( $lessons as $lesson ) {
                    $section_name = get_post_meta( $lesson->ID, '_wpa_lesson_section', true );
                    if ( empty( $section_name ) ) $section_name = __( 'General', 'wp-academic-post-enhanced' );
                    $sections[ $section_name ][] = $lesson;
                }
            } else {
                $sections[ __( 'All Lessons', 'wp-academic-post-enhanced' ) ] = $lessons;
            }

            foreach ( $sections as $section_title => $section_lessons ) {
                if ( $enable_sections && ( count( $sections ) > 1 || $section_title !== __( 'General', 'wp-academic-post-enhanced' ) ) ) {
                    $list_html .= '<h4 class="wpa-section-title">' . esc_html( $section_title ) . '</h4>';
                }
                
                $list_html .= '<ul class="wpa-lesson-list">';
                foreach ( $section_lessons as $lesson ) {
                    $is_done = wpa_course_is_lesson_completed( $lesson->ID );
                    $done_icon = $is_done ? '<span class="dashicons dashicons-yes wpa-lesson-check"></span> ' : '';
                    $lock_icon = ( ! wpa_course_user_can_access( $course_id ) ) ? '<span class="dashicons dashicons-lock wpa-lesson-lock"></span> ' : '';
                    
                    // Determine Type Icon
                    $type_icon = '';
                    if ( $show_list_icons ) {
                        $video_url = get_post_meta( $lesson->ID, '_wpa_lesson_video_url', true );
                        $icon_class = ! empty( $video_url ) ? 'dashicons-video-alt3' : 'dashicons-media-document';
                        $type_icon = '<span class="dashicons ' . $icon_class . ' wpa-lesson-icon"></span> ';
                    }

                    // Duration
                    $lesson_duration = '';
                    if ( $show_list_duration ) {
                        $dur = get_post_meta( $lesson->ID, '_wpa_lesson_duration', true );
                        if ( $dur ) $lesson_duration = '<span class="wpa-lesson-duration">' . esc_html( $dur ) . '</span>';
                    }
                    
                    // Lesson Index (Global)
                    $lesson_index = array_search( $lesson, $lessons ) + 1;
                    
                    $is_rtl = wpa_is_rtl( $lesson->post_title );
                    $rtl_class = $is_rtl ? 'wpa-is-rtl' : 'wpa-is-ltr';

                    $list_html .= '<li class="' . esc_attr( $rtl_class ) . '">';
                    $list_html .= '<a href="' . get_permalink( $lesson->ID ) . '">';
                    $list_html .= '<div class="wpa-list-left">';
                    if ( ! $is_rtl ) {
                        $list_html .= '<span class="wpa-lesson-number">' . $lesson_index . '.</span> ';
                        $list_html .= '<span class="wpa-lesson-title">' . $lock_icon . $done_icon . $type_icon . esc_html( $lesson->post_title ) . '</span>';
                    } else {
                        $list_html .= '<span class="wpa-lesson-title">' . esc_html( $lesson->post_title ) . $lock_icon . $done_icon . $type_icon . '</span>';
                        $list_html .= ' <span class="wpa-lesson-number">.' . $lesson_index . '</span>';
                    }
                    $list_html .= '</div>';
                    $list_html .= $lesson_duration;
                    $list_html .= '</a>';
                    $list_html .= '</li>';
                }
                $list_html .= '</ul>';
            }
        } else {
            $list_html .= '<p>' . WPA_Theme_Labels::get('label_no_results') . '</p>';
        }
        $list_html .= '</div>';

        $main_area = '<div class="wpa-course-main-content">' . $meta_html . $content . $list_html . '</div>';
        
        return '<div class="wpa-course-layout-wrapper">' . $hero_html . $main_area . $instructor_html . '</div>';
    }

    return $content;
}
add_filter( 'the_content', 'wpa_course_content_filter' );

/**
 * Frontend: Display Navigation & Sidebar & Materials on Lesson Page.
 */
function wpa_lesson_content_filter( $content ) {
    if ( is_singular( 'wpa_lesson' ) ) {
        $options = get_option( 'wpa_course_settings' );
        
        $seq_msg = WPA_Theme_Labels::get( 'sequential_message' );
        $locked_msg = WPA_Theme_Labels::get( 'locked_message' );

        $enable_materials = isset( $options['enable_materials'] ) ? $options['enable_materials'] : 1;
        $sidebar_pos = isset( $options['sidebar_position'] ) ? $options['sidebar_position'] : 'right';
        $video_pos = isset( $options['video_position'] ) ? $options['video_position'] : 'top';
        $enforce_seq = isset( $options['enforce_sequential'] ) ? $options['enforce_sequential'] : 0;
        
        // Visibility Settings
        $show_breadcrumbs = isset( $options['show_breadcrumbs'] ) ? $options['show_breadcrumbs'] : 1;
        $show_sidebar = isset( $options['show_sidebar'] ) ? $options['show_sidebar'] : 1;
        $show_nav_buttons = isset( $options['show_nav_buttons'] ) ? $options['show_nav_buttons'] : 1;
        $show_sidebar_progress = isset( $options['show_sidebar_progress'] ) ? $options['show_sidebar_progress'] : 1;
        $show_lesson_instructor = isset( $options['show_lesson_instructor'] ) ? $options['show_lesson_instructor'] : 1;
        
        // Focus Mode Settings
        $enable_focus_mode = isset( $options['enable_focus_mode'] ) ? $options['enable_focus_mode'] : 1;
        $focus_mode_pos = isset( $options['focus_mode_position'] ) ? $options['focus_mode_position'] : 'header';

        // Labels via Theme Manager
        $label_mark_complete = WPA_Theme_Labels::get( 'label_mark_complete' );
        $label_completed = WPA_Theme_Labels::get( 'label_completed' );
        $label_prev = WPA_Theme_Labels::get( 'label_prev_lesson' );
        $label_next = WPA_Theme_Labels::get( 'label_next_lesson' );
        $label_course_home = WPA_Theme_Labels::get( 'label_course_home' );
        $label_materials = WPA_Theme_Labels::get( 'label_materials' );
        $label_download = WPA_Theme_Labels::get( 'label_download' );
        
        $lesson_id = get_the_ID();
        $course_id = get_post_meta( $lesson_id, '_wpa_course_id', true );
        
        // If no course is assigned, just show the lesson content with a basic wrapper
        if ( ! $course_id ) {
            $lesson_header_html = '<div class="wpa-lesson-main-header">';
            $lesson_header_html .= '<h1 class="wpa-lesson-title-main">' . get_the_title( $lesson_id ) . '</h1>';
            $lesson_header_html .= '</div>';
            return '<div class="wpa-lesson-layout sidebar-none"><div class="wpa-lesson-content-area">' . $lesson_header_html . '<div class="wpa-lesson-body">' . $content . '</div></div></div>';
        }
        
        // --- Access Control Check ---
        if ( ! wpa_course_user_can_access( $course_id ) ) {
            return '<div class="wpa-lesson-locked">' . 
                    '<p>' . esc_html( $locked_msg ) . '</p>' . 
                    '<a href="' . wp_login_url( get_permalink() ) . '" class="button">' . WPA_Theme_Labels::get('lesson_log_in') . '</a>' . 
                    '</div>';
        }
        
        $enable_drip_content = isset( $options['enable_drip_content'] ) ? $options['enable_drip_content'] : 1;
        
        // --- Drip Content Check ---
        if ( $enable_drip_content && is_user_logged_in() ) {
            $drip_days = (int) get_post_meta( $lesson_id, '_wpa_lesson_drip_days', true );
            if ( $drip_days > 0 ) {
                $user_id = get_current_user_id();
                
                $enrollment_date = get_user_meta( $user_id, '_wpa_enrollment_date_' . $course_id, true );
                
                if ( $enrollment_date ) {
                    $unlock_time = strtotime( $enrollment_date ) + ( $drip_days * DAY_IN_SECONDS );
                    if ( time() < $unlock_time ) {
                        $unlock_date_display = date_i18n( get_option( 'date_format' ), $unlock_time );
                        return '<div class="wpa-lesson-locked wpa-drip-locked">' . 
                                '<p><span class="dashicons dashicons-clock"></span> ' . sprintf( WPA_Theme_Labels::get('lesson_drip_msg'), $unlock_date_display ) . '</p>' . 
                                '<a href="' . get_permalink( $course_id ) . '" class="button">' . esc_html( $label_course_home ) . '</a>' . 
                                '</div>';
                    }
                }
            }
        }

        // Get all lessons
        $all_lessons = get_posts( [
            'post_type'      => 'wpa_lesson',
            'posts_per_page' => -1,
            'meta_key'       => '_wpa_course_id',
            'meta_value'     => $course_id,
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
        ] );

        if ( ! empty( $all_lessons ) ) {
            // Bulk cache post meta to prevent N+1 queries
            update_postmeta_cache( wp_list_pluck( $all_lessons, 'ID' ) );
        }

        // --- Sequential Check ---
        if ( $enforce_seq && is_user_logged_in() ) {
            $lesson_ids = wp_list_pluck( $all_lessons, 'ID' );
            $current_index = array_search( $lesson_id, $lesson_ids );
            
            if ( $current_index > 0 ) {
                $prev_lesson_id = $lesson_ids[ $current_index - 1 ];
                if ( ! wpa_course_is_lesson_completed( $prev_lesson_id ) ) {
                    return '<div class="wpa-lesson-locked">' . 
                            '<p><span class="dashicons dashicons-lock"></span> ' . esc_html( $seq_msg ) . '</p>' . 
                            '<a href="' . get_permalink( $prev_lesson_id ) . '" class="button">' . esc_html( $label_prev ) . '</a>' . 
                            '</div>';
                }
            }
        }

        // --- Video Embed ---
        $video_html = '';
        $video_url = get_post_meta( $lesson_id, '_wpa_lesson_video_url', true );
        if ( ! empty( $video_url ) ) {
            $video_embed = wp_oembed_get( $video_url );
            if ( $video_embed ) {
                $video_html = '<div class="wpa-video-container">' . $video_embed . '</div>';
            }
        }

        // --- Breadcrumbs ---
        $breadcrumbs_html = '';
        if ( $show_breadcrumbs ) {
            $breadcrumbs_html = '<div class="wpa-lesson-header-row">';
            $breadcrumbs_html .= '<div class="wpa-lesson-breadcrumbs">';
            $breadcrumbs_html .= '<a href="' . get_permalink( $course_id ) . '">' . get_the_title( $course_id ) . '</a>';
            $breadcrumbs_html .= '<span class="wpa-sep">/</span>';
            $breadcrumbs_html .= '<span class="wpa-current">' . get_the_title( $lesson_id ) . '</span>';
            $breadcrumbs_html .= '</div>';
            
            if ( $enable_focus_mode && $focus_mode_pos === 'breadcrumbs' ) {
                $breadcrumbs_html .= '<button id="wpa-focus-mode-toggle" class="button wpa-focus-btn" title="' . WPA_Theme_Labels::get('lesson_focus_mode') . '"><span class="dashicons dashicons-fullscreen-alt"></span> ' . WPA_Theme_Labels::get('lesson_focus_mode') . '</button>';
            }
            
            $breadcrumbs_html .= '</div>';
        }

        // --- Lesson Meta (Index, Author, Date) ---
        $meta_html = '';
        $show_index = isset( $options['show_lesson_index'] ) ? $options['show_lesson_index'] : 1;
        $show_author = isset( $options['show_lesson_author'] ) ? $options['show_lesson_author'] : 1;
        $show_date = isset( $options['show_lesson_date'] ) ? $options['show_lesson_date'] : 1;

        if ( $show_index || $show_author || $show_date ) {
            $meta_html .= '<div class="wpa-lesson-meta-header">';
            
            if ( $show_index ) {
                $lesson_ids = wp_list_pluck( $all_lessons, 'ID' );
                $current_index = array_search( $lesson_id, $lesson_ids ) + 1;
                $total_lessons = count( $lesson_ids );
                $meta_html .= '<span class="wpa-meta-item wpa-lesson-index"><span class="dashicons dashicons-media-document"></span> ' . sprintf( WPA_Theme_Labels::get('lesson_index_text'), $current_index, $total_lessons ) . '</span>';
            }

            if ( $show_author ) {
                $author_id = get_post_field( 'post_author', $lesson_id );
                $author_name = get_the_author_meta( 'display_name', $author_id );
                $meta_html .= '<span class="wpa-meta-item wpa-lesson-author"><span class="dashicons dashicons-admin-users"></span> ' . esc_html( $author_name ) . '</span>';
            }

            if ( $show_date ) {
                $date_format = get_option( 'date_format' );
                $last_updated = get_the_modified_date( $date_format, $lesson_id );
                $meta_html .= '<span class="wpa-meta-item wpa-lesson-date"><span class="dashicons dashicons-calendar-alt"></span> ' . esc_html( $last_updated ) . '</span>';
            }
            
            $meta_html .= '</div>';
        }

        // --- Lesson Materials ---
        $materials_html = '';
        if ( $enable_materials ) {
            $materials_raw = get_post_meta( $lesson_id, '_wpa_lesson_materials', true );
            if ( ! empty( $materials_raw ) ) {
                $lines = explode( "\n", $materials_raw );
                $materials_html .= '<div class="wpa-lesson-materials">';
                $materials_html .= '<h5><span class="dashicons dashicons-paperclip"></span> ' . esc_html( $label_materials ) . '</h5><ul>';
                foreach ( $lines as $line ) {
                    $parts = explode( '|', $line );
                    $url = trim( $parts[0] );
                    $label_text = isset( $parts[1] ) ? trim( $parts[1] ) : $label_download;
                    if ( ! empty( $url ) ) {
                        $materials_html .= '<li><a href="' . esc_url( $url ) . '" target="_blank">' . esc_html( $label_text ) . ' <span class="dashicons dashicons-download"></span></a></li>';
                    }
                }
                $materials_html .= '</ul></div>';
            }
        }

        // --- Sidebar (Desktop) / Bottom (Mobile) List ---
        $sidebar_html = '';
        if ( $show_sidebar ) {
            $sidebar_html = '<div class="wpa-lesson-sidebar">';
            $sidebar_html .= '<h4><a href="' . get_permalink( $course_id ) . '">' . get_the_title( $course_id ) . '</a></h4>';
            
            // Progress Bar in Sidebar
            if ( is_user_logged_in() && $show_sidebar_progress ) {
                $progress = wpa_course_get_progress( $course_id );
                $sidebar_html .= '<div class="wpa-course-progress-bar wpa-sidebar-progress">';
                $sidebar_html .= '<div class="wpa-progress-fill" style="width:' . esc_attr( $progress ) . '%"></div>';
                $sidebar_html .= '</div>';
            }

            // Inject Instructor Card
            // --- Instructor Sidebar (For Lesson Page) ---
            $author_id    = get_post_field( 'post_author', $course_id );
            $author_name  = get_the_author_meta( 'display_name', $author_id );
            $author_bio   = get_the_author_meta( 'description', $author_id );
            $custom_avatar_id = get_user_meta( $author_id, 'wpa_user_custom_avatar', true );
            if ( $custom_avatar_id ) {
                $author_avatar = wp_get_attachment_image( $custom_avatar_id, [80,80], false, ['class' => 'wpa-custom-avatar'] );
            } else {
                $author_avatar = get_avatar( $author_id, 80 );
            }

            $instructor_sidebar_html = '<div class="wpa-lesson-instructor-card">';
            $instructor_sidebar_html .= '<div class="wpa-lesson-instructor-header">' . WPA_Theme_Labels::get('lesson_instructor') . '</div>';
            $instructor_sidebar_html .= '<div class="wpa-lesson-instructor-content">';
            $instructor_sidebar_html .= '<div class="wpa-lesson-instructor-avatar">' . $author_avatar . '</div>';
            $instructor_sidebar_html .= '<div class="wpa-lesson-instructor-info">';
            $instructor_sidebar_html .= '<h5 class="wpa-lesson-instructor-name">' . esc_html( $author_name ) . '</h5>';
            $instructor_sidebar_html .= '</div>';
            $instructor_sidebar_html .= '</div>'; // .content
            if ( ! empty( $author_bio ) ) {
                $instructor_sidebar_html .= '<div class="wpa-lesson-instructor-bio">' . wp_trim_words( wp_strip_all_tags( $author_bio ), 20 ) . '</div>';
            }
            $instructor_sidebar_html .= '</div>'; // .wpa-lesson-instructor-card

            if ( $show_lesson_instructor ) {
                $sidebar_html .= $instructor_sidebar_html;
            }

            // Group by Section
            $enable_sections = isset( $options['enable_sections'] ) ? $options['enable_sections'] : 1;

            $prev_id = false;
            $next_id = false;
            $found_current = false;

            if ( $enable_sections ) {
                $sections = [];
                foreach ( $all_lessons as $lesson ) {
                    $section_name = get_post_meta( $lesson->ID, '_wpa_lesson_section', true );
                    if ( empty( $section_name ) ) $section_name = __( 'General', 'wp-academic-post-enhanced' );
                    $sections[ $section_name ][] = $lesson;
                }

                $sidebar_html .= '<div class="wpa-sidebar-sections">';
                
                foreach ( $sections as $section_title => $section_lessons ) {
                    if ( count( $sections ) > 1 || $section_title !== __( 'General', 'wp-academic-post-enhanced' ) ) {
                        $section_count = count( $section_lessons );
                        $sidebar_html .= '<h5 class="wpa-sidebar-section-title wpa-sidebar-section-toggle">' . esc_html( $section_title ) . ' <div class="wpa-section-meta"><span class="wpa-section-count">' . $section_count . '</span> <span class="dashicons dashicons-arrow-down-alt2"></span></div></h5>';
                    }
                    $sidebar_html .= '<ul class="wpa-sidebar-list">';
                    
                    foreach ( $section_lessons as $l ) {
                        $is_current = ( $l->ID === $lesson_id );
                        $is_done = wpa_course_is_lesson_completed( $l->ID );
                        $done_icon = $is_done ? '<span class="dashicons dashicons-yes wpa-lesson-check"></span>' : '';
                        $class = $is_current ? 'class="current-lesson"' : '';
                        
                        // Prev/Next Logic inside loop
                        if ( $is_current ) {
                            $found_current = true;
                        } elseif ( ! $found_current ) {
                            $prev_id = $l->ID;
                        } elseif ( $found_current && ! $next_id ) {
                            $next_id = $l->ID;
                        }

                        $sidebar_html .= '<li ' . $class . '><a href="' . get_permalink( $l->ID ) . '">' . $done_icon . ' ' . esc_html( $l->post_title ) . '</a></li>';
                    }
                    $sidebar_html .= '</ul>';
                }
                $sidebar_html .= '</div>';
            } else {
                // Flat Sidebar
                $sidebar_html .= '<ul class="wpa-sidebar-list">';
                foreach ( $all_lessons as $l ) {
                    $is_current = ( $l->ID === $lesson_id );
                    $is_done = wpa_course_is_lesson_completed( $l->ID );
                    $done_icon = $is_done ? '<span class="dashicons dashicons-yes wpa-lesson-check"></span>' : '';
                    $class = $is_current ? 'class="current-lesson"' : '';
                    
                    if ( $is_current ) {
                        $found_current = true;
                    } elseif ( ! $found_current ) {
                        $prev_id = $l->ID;
                    } elseif ( $found_current && ! $next_id ) {
                        $next_id = $l->ID;
                    }

                    $sidebar_html .= '<li ' . $class . '><a href="' . get_permalink( $l->ID ) . '">' . $done_icon . ' ' . esc_html( $l->post_title ) . '</a></li>';
                }
                $sidebar_html .= '</ul>';
            }
            
            // --- Lesson Notes (In Sidebar) ---
            $sidebar_html .= '<div class="wpa-lesson-notes-section sidebar-notes">';
            $sidebar_html .= '<h5><span class="dashicons dashicons-edit"></span> ' . WPA_Theme_Labels::get('lesson_notes') . '</h5>';
            $sidebar_html .= '<textarea id="wpa-lesson-notes-input" placeholder="' . WPA_Theme_Labels::get('lesson_notes_placeholder') . '"></textarea>';
            $sidebar_html .= '<span class="wpa-notes-status">' . WPA_Theme_Labels::get('lesson_notes_saved') . '</span>';
            $sidebar_html .= '</div>';
            
            $sidebar_html .= '</div>';
        } else {
            // Need to calculate prev/next even if sidebar is hidden for the nav buttons
            $prev_id = false;
            $next_id = false;
            $found_current = false;
            foreach ( $all_lessons as $l ) {
                if ( $l->ID === $lesson_id ) {
                    $found_current = true;
                } elseif ( ! $found_current ) {
                    $prev_id = $l->ID;
                } elseif ( $found_current && ! $next_id ) {
                    $next_id = $l->ID;
                }
            }
        }        

        // --- Bottom Navigation ---
        $nav_html = '';
        if ( $show_nav_buttons ) {
            $nav_html = '<div class="wpa-course-navigation">';
            if ( $prev_id ) {
                $nav_html .= '<a href="' . get_permalink( $prev_id ) . '" class="wpa-nav-prev">&larr; ' . esc_html( $label_prev ) . '</a>';
            } else {
                $nav_html .= '<span></span>'; // Spacer
            }
            
            // Mark Complete Button
            if ( is_user_logged_in() ) {
                $is_completed = wpa_course_is_lesson_completed( $lesson_id );
                $btn_text = $is_completed ? $label_completed : $label_mark_complete;
                $btn_class = $is_completed ? 'button wpa-btn-completed' : 'button wpa-btn-complete';
                $disabled = $is_completed ? 'disabled' : '';
                
                $nav_html .= '<button id="wpa-mark-complete" class="' . esc_attr( $btn_class ) . '" data-lesson="' . esc_attr( $lesson_id ) . '" ' . $disabled . '>' . esc_html( $btn_text ) . '</button>';
            } else {
                $nav_html .= '<a href="' . get_permalink( $course_id ) . '" class="wpa-nav-course">' . esc_html( $label_course_home ) . '</a>';
            }

            if ( $next_id ) {
                $nav_html .= '<a href="' . get_permalink( $next_id ) . '" class="wpa-nav-next">' . esc_html( $label_next ) . ' &rarr;</a>';
            } else {
                $nav_html .= '<span></span>';
            }
            $nav_html .= '</div>';
        }

        // --- Lesson Header (Title + Focus Mode) ---
        $lesson_header_html = '<div class="wpa-lesson-main-header">';
        $lesson_header_html .= '<h1 class="wpa-lesson-title-main">' . get_the_title( $lesson_id ) . '</h1>';
        
        if ( $enable_focus_mode && $focus_mode_pos !== 'breadcrumbs' ) {
            $btn_class = 'button wpa-focus-btn wpa-focus-pos-' . esc_attr( $focus_mode_pos );
            $lesson_header_html .= '<div class="wpa-focus-wrapper header-inline pos-' . esc_attr( $focus_mode_pos ) . '">';
            $lesson_header_html .= '<button id="wpa-focus-mode-toggle" class="' . $btn_class . '" title="' . __( 'Toggle Focus Mode', 'wp-academic-post-enhanced' ) . '"><span class="dashicons dashicons-fullscreen-alt"></span> ' . __( 'Focus Mode', 'wp-academic-post-enhanced' ) . '</button>';
            $lesson_header_html .= '</div>';
        }
        $lesson_header_html .= '</div>';

        // Wrap content
        $layout_class = 'wpa-lesson-layout sidebar-' . ( $show_sidebar ? esc_attr( $sidebar_pos ) : 'none' );
        
        $lesson_body = '<div class="wpa-lesson-body">' . $content . '</div>';
        
        if ( $video_pos === 'top' ) {
            $main_content = $lesson_header_html . $breadcrumbs_html . $meta_html . $video_html . $lesson_body . $materials_html . $nav_html;
        } else {
            $main_content = $lesson_header_html . $breadcrumbs_html . $meta_html . $lesson_body . $video_html . $materials_html . $nav_html;
        }        

        $layout_html = '<div class="' . esc_attr( $layout_class ) . '">';
        $layout_html .= '<div class="wpa-lesson-content-area">' . $main_content . '</div>';
        $layout_html .= $sidebar_html;
        $layout_html .= '</div>';

        return $layout_html;
    }

    return $content;
}
add_filter( 'the_content', 'wpa_lesson_content_filter' );
