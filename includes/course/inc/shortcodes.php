<?php
/**
 * Shortcodes for Courses.
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Shortcode: [wpa_student_dashboard]
 */
function wpa_student_dashboard_shortcode() {
    if ( ! is_user_logged_in() ) {
        return '<p>' . __( 'Please log in to view your courses.', 'wp-academic-post-enhanced' ) . '</p>';
    }

    $options = get_option( 'wpa_course_settings' );
    $user_id = get_current_user_id();
    $current_user = wp_get_current_user();
    
    // --- Dashboard Settings ---
    $hide_title = isset( $options['dashboard_hide_title'] ) ? $options['dashboard_hide_title'] : 1;
    $show_avatar = isset( $options['dashboard_show_avatar'] ) ? $options['dashboard_show_avatar'] : 1;
    $bg_id = ! empty( $options['dashboard_hero_bg'] ) ? $options['dashboard_hero_bg'] : '';
    $bg_url = $bg_id ? wp_get_attachment_image_url( $bg_id, 'full' ) : '';
    
    $welcome_template = WPA_Theme_Labels::get( 'dash_welcome' );
    $welcome_text = str_replace( '{name}', $current_user->display_name, $welcome_template );

    // Enrolled courses
    $enrolled_ids = get_user_meta( $user_id, '_wpa_enrolled_courses', true );
    
    // Header HTML
    $header_style = $bg_url ? 'style="background-image: url(' . esc_url( $bg_url ) . ');"' : '';
    $header_class = $bg_url ? 'has-bg' : 'no-bg';
    
    ob_start();
    
    // Hide Title CSS
    if ( $hide_title ) {
        echo '<style>.page-title, .entry-title { display: none !important; }</style>';
    }
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
                    $completed_count = 0;
                    echo date_i18n( get_option( 'date_format' ) ); 
                    ?>
                </div>
            </div>
        </div>
        <?php if ( $bg_url ) : ?><div class="wpa-dash-overlay"></div><?php endif; ?>
    </div>
    <?php
    $header_html = ob_get_clean();

    if ( empty( $enrolled_ids ) || ! is_array( $enrolled_ids ) ) {
        return $header_html . '<p>' . WPA_Theme_Labels::get( 'dash_not_enrolled' ) . '</p>';
    }

    $query = new WP_Query( [
        'post_type' => 'wpa_course',
        'posts_per_page' => -1,
        'post__in' => $enrolled_ids,
        'post_status' => 'publish',
    ] );

    if ( ! $query->have_posts() ) return '<p>' . __( 'No active courses.', 'wp-academic-post-enhanced' ) . '</p>';

    $active_courses = [];
    $completed_courses = [];
    $all_courses_html = '';

    while ( $query->have_posts() ) {
        $query->the_post();
        $course_id = get_the_ID();
        $progress = wpa_course_get_progress( $course_id, $user_id );
        
        $status_class = ( $progress >= 100 ) ? 'completed' : ( ($progress > 0) ? 'in-progress' : 'not-started' );
        $status_text = ( $progress >= 100 ) ? WPA_Theme_Labels::get('status_completed') : ( ($progress > 0) ? WPA_Theme_Labels::get('status_in_progress') : WPA_Theme_Labels::get('status_enrolled') );
        $thumb = get_the_post_thumbnail_url( $course_id, 'medium_large' );

        ob_start();
        ?>
        <div class="wpa-dash-card <?php echo esc_attr( $status_class ); ?>">
            <a href="<?php echo get_permalink(); ?>" class="wpa-dash-thumb" style="background-image: url(<?php echo esc_url( $thumb ); ?>);">
                <span class="wpa-status-badge"><?php echo esc_html( $status_text ); ?></span>
            </a>
            <div class="wpa-dash-body">
                <h4><a href="<?php echo get_permalink(); ?>"><?php echo get_the_title(); ?></a></h4>
                
                <div class="wpa-dash-progress-wrapper">
                    <div class="wpa-course-progress-bar" role="progressbar" aria-valuenow="<?php echo esc_attr( $progress ); ?>" aria-valuemin="0" aria-valuemax="100">
                        <div class="wpa-progress-fill" style="width:<?php echo esc_attr( $progress ); ?>%"></div>
                    </div>
                    <span class="wpa-dash-percent"><?php echo esc_html( $progress ); ?>%</span>
                </div>

                <div class="wpa-dash-footer">
                    <?php if ( $progress >= 100 ) : 
                        $cert_link = add_query_arg( [ 'wpa_download_certificate' => '1', 'course_id' => $course_id ], home_url() ); ?>
                        <a href="<?php echo esc_url( $cert_link ); ?>" class="wpa-btn wpa-btn-outline" target="_blank"><span class="dashicons dashicons-awards"></span> <?php echo WPA_Theme_Labels::get('lesson_certificate'); ?></a>
                    <?php else : ?>
                        <a href="<?php echo get_permalink(); ?>" class="wpa-btn wpa-btn-primary"><?php echo WPA_Theme_Labels::get('lesson_continue'); ?> <span class="dashicons dashicons-arrow-right-alt2"></span></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
        $card_html = ob_get_clean();
        
        $all_courses_html .= $card_html;
        if ( $progress >= 100 ) {
            $completed_courses[] = $card_html;
        } else {
            $active_courses[] = $card_html;
        }
    }
    wp_reset_postdata();

    ob_start();
    echo $header_html;
    ?>
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
    <?php
    return ob_get_clean();
}
add_shortcode( 'wpa_student_dashboard', 'wpa_student_dashboard_shortcode' );

/**
 * Public function to display course grid.
 */
function wpa_get_course_grid( $args = [] ) {
    $options = get_option( 'wpa_course_settings' );
    $default_cols = isset( $options['grid_columns'] ) ? $options['grid_columns'] : 3;

    $defaults = [
        'limit' => 6,
        'cols' => $default_cols,
        'style' => 'grid',
    ];
    
    $atts = wp_parse_args( $args, $defaults );
    return wpa_courses_shortcode( $atts );
}

/**
 * Shortcode: [wpa_courses]
 */
function wpa_courses_shortcode( $atts ) {
    global $wp;
    $options = get_option( 'wpa_course_settings' );
    $default_cols = isset( $options['grid_columns'] ) ? $options['grid_columns'] : 3;

    // Pre-calculate form action and hidden fields robustly
    $current_url = home_url( add_query_arg( array(), $wp->request ) );
    $form_action = strtok( $current_url, '?' );
    $query_string = parse_url( $current_url, PHP_URL_QUERY );
    $hidden_fields = '';
    
    // Define keys to exclude from hidden fields (these are handled by the form inputs)
    $exclude_keys = [ 'wpa_level', 'wpa_price', 'wpa_search' ];

    if ( $query_string ) {
        parse_str( $query_string, $params );
        foreach ( $params as $key => $value ) {
            if ( in_array( $key, $exclude_keys ) ) continue;
            $hidden_fields .= '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '">';
        }
    }

    $atts = shortcode_atts( [
        'limit' => 6,
        'cols' => $default_cols,
        'style' => 'grid', // grid (default), list, compact
    ], $atts );

    // --- Filter Logic ---
    $enable_filters = isset( $options['enable_course_filters'] ) ? $options['enable_course_filters'] : 1;
    $hide_title = isset( $options['hide_archive_title'] ) ? $options['hide_archive_title'] : 0;
    
    // --- Hero & Slider Logic ---
    $slider_html = '';
    $hero_static_html = '';
    $hero_enable = isset( $options['archive_hero_enable'] ) ? $options['archive_hero_enable'] : 0;
    $slider_enable = isset( $options['slider_enable'] ) ? $options['slider_enable'] : 0;
    
    // 1. Slider Section
    if ( $slider_enable ) {
        $count = !empty($options['slider_count']) ? intval($options['slider_count']) : 5;
        $autoplay = isset($options['slider_autoplay']) ? intval($options['slider_autoplay']) : 1;
        $interval = !empty($options['slider_interval']) ? intval($options['slider_interval']) : 5000;
        $show_arrows = isset($options['slider_show_arrows']) ? intval($options['slider_show_arrows']) : 1;
        $show_dots = isset($options['slider_show_dots']) ? intval($options['slider_show_dots']) : 1;
        $pause_hover = isset($options['slider_pause_hover']) ? intval($options['slider_pause_hover']) : 1;

        $slider_query = new WP_Query([
            'post_type' => 'post',
            'posts_per_page' => $count,
            'orderby' => 'date',
            'order' => 'DESC',
            'post_status' => 'publish',
            'no_found_rows' => true // ⚡ Bolt: Prevent expensive SQL_CALC_FOUND_ROWS query since pagination is not needed
        ]);

        if ( $slider_query->have_posts() ) {
            $slider_html .= '<div class="wpa-hero-slider-wrapper" data-autoplay="' . $autoplay . '" data-interval="' . $interval . '" data-pause="' . $pause_hover . '">';
            $slider_html .= '<div class="wpa-hero-slider">';
            $total_slides = $slider_query->post_count;
            
            while ( $slider_query->have_posts() ) {
                $slider_query->the_post();
                $thumb = get_the_post_thumbnail_url( get_the_ID(), 'full' );
                $bg_style = $thumb ? 'background-image: url(' . esc_url( $thumb ) . ');' : 'background-color: #1f2937;';
                
                $slider_html .= '<div class="wpa-hero-slide" style="' . $bg_style . '">';
                $slider_html .= '<div class="wpa-slide-overlay"></div>';
                $slider_html .= '<div class="wpa-slide-content">';
                $slider_html .= '<h2 class="wpa-slide-title"><a href="' . get_permalink() . '">' . get_the_title() . '</a></h2>';
                $slider_html .= '<div class="wpa-slide-desc">' . wp_trim_words( get_the_excerpt(), 20 ) . '</div>';
                $slider_html .= '<a href="' . get_permalink() . '" class="wpa-slide-btn">' . __( 'Read More', 'wp-academic-post-enhanced' ) . '</a>';
                $slider_html .= '</div>';
                $slider_html .= '</div>';
            }
            $slider_html .= '</div>'; // .slider
            
            if ( $show_arrows ) {
                $slider_html .= '<button class="wpa-slider-nav prev" aria-label="Previous"><span class="dashicons dashicons-arrow-left-alt2"></span></button>';
                $slider_html .= '<button class="wpa-slider-nav next" aria-label="Next"><span class="dashicons dashicons-arrow-right-alt2"></span></button>';
            }

            if ( $show_dots ) {
                $slider_html .= '<div class="wpa-slider-dots">';
                for ( $i = 0; $i < $total_slides; $i++ ) {
                    $active_class = ( $i === 0 ) ? 'active' : '';
                    $slider_html .= '<span class="wpa-slider-dot ' . $active_class . '" data-index="' . $i . '"></span>';
                }
                $slider_html .= '</div>';
            }
            
            $slider_html .= '</div>'; // .wrapper
            wp_reset_postdata();
            wp_reset_query();
        }
    }

    // 3. Static Hero Section
    if ( $hero_enable ) {
        $hero_title = ! empty( $options['archive_hero_title'] ) ? $options['archive_hero_title'] : __( 'Our Courses', 'wp-academic-post-enhanced' );
        $hero_text = ! empty( $options['archive_hero_text'] ) ? $options['archive_hero_text'] : '';
        $hero_image_id = ! empty( $options['archive_hero_image'] ) ? $options['archive_hero_image'] : '';
        $hero_image_url = $hero_image_id ? wp_get_attachment_image_url( $hero_image_id, 'full' ) : '';
        $hero_layout = ! empty( $options['archive_hero_layout'] ) ? $options['archive_hero_layout'] : 'split';
        
        if ( $hero_layout !== 'slider' ) {
            $hero_style_attr = '';
            if ( $hero_layout === 'banner' && $hero_image_url ) {
                $hero_style_attr = 'style="background-image: url(' . esc_url( $hero_image_url ) . ');"';
            }

            $hero_static_html .= '<div class="wpa-archive-hero style-' . esc_attr( $hero_layout ) . '" ' . $hero_style_attr . '>';
            $hero_static_html .= '<div class="wpa-hero-content">';
            $hero_static_html .= '<h1 class="wpa-archive-title">' . esc_html( $hero_title ) . '</h1>';
            if ( $hero_text ) {
                $hero_static_html .= '<p class="wpa-archive-desc">' . esc_html( $hero_text ) . '</p>';
            }
            $hero_static_html .= '</div>';

            if ( $hero_layout === 'split' && $hero_image_url ) {
                $hero_static_html .= '<div class="wpa-hero-visual">';
                $hero_static_html .= '<img src="' . esc_url( $hero_image_url ) . '" alt="' . esc_attr( $hero_title ) . '">';
                $hero_static_html .= '</div>';
            }
            $hero_static_html .= '</div>';
        }
    }

    $args = [
        'post_type' => 'wpa_course',
        'posts_per_page' => intval( $atts['limit'] ),
        'post_status' => 'publish',
        'meta_query' => [],
        'no_found_rows' => true, // ⚡ Bolt: Prevent expensive SQL_CALC_FOUND_ROWS query since pagination is not needed
    ];

    $filter_html = '';
    
    if ( $enable_filters ) {
        $filter_level = isset( $options['filter_by_level'] ) ? $options['filter_by_level'] : 1;
        $filter_price = isset( $options['filter_by_price'] ) ? $options['filter_by_price'] : 1;
        $filter_search = isset( $options['filter_by_search'] ) ? $options['filter_by_search'] : 1;

        // Process GET params
        $current_level = isset( $_GET['wpa_level'] ) ? sanitize_text_field( $_GET['wpa_level'] ) : '';
        $current_price = isset( $_GET['wpa_price'] ) ? sanitize_text_field( $_GET['wpa_price'] ) : '';
        $current_search = isset( $_GET['wpa_search'] ) ? sanitize_text_field( $_GET['wpa_search'] ) : '';

        if ( ! empty( $current_level ) ) {
            $args['meta_query'][] = [
                'key' => '_wpa_course_level',
                'value' => $current_level,
                'compare' => '=',
            ];
        }

        if ( ! empty( $current_price ) ) {
            // Price logic: if "Free", check for 0 or Free or empty. If "Paid", check not empty and not Free.
            // Simplified: Exact match for now as stored in meta. 
            // NOTE: Ideally Price should be numeric, but current impl is text string "Free" or "$99".
            // Let's assume user types "Free" or "Paid" in dropdown.
            if ( $current_price === 'Free' ) {
                 $args['meta_query'][] = [
                    'relation' => 'OR',
                    [ 'key' => '_wpa_course_price', 'value' => 'Free', 'compare' => 'LIKE' ],
                    [ 'key' => '_wpa_course_price', 'value' => '0', 'compare' => 'LIKE' ],
                 ];
            } elseif ( $current_price === 'Paid' ) {
                 $args['meta_query'][] = [
                    'key' => '_wpa_course_price',
                    'value' => 'Free',
                    'compare' => 'NOT LIKE',
                 ];
            }
        }

        if ( ! empty( $current_search ) ) {
            $args['s'] = $current_search;
        }

        // Build Filter Bar HTML
        $filter_html .= '<form class="wpa-course-filters" method="get" action="' . esc_url( $form_action ) . '">';
        $filter_html .= $hidden_fields;
        
        if ( $filter_search ) {
            $filter_html .= '<div class="wpa-filter-item search">';
            $filter_html .= '<input type="text" name="wpa_search" placeholder="' . WPA_Theme_Labels::get( 'label_search_placeholder' ) . '" value="' . esc_attr( $current_search ) . '">';
            $filter_html .= '</div>';
        }

        if ( $filter_level ) {
            $filter_html .= '<div class="wpa-filter-item">';
            $filter_html .= '<select name="wpa_level" onchange="this.form.submit()">';
            $filter_html .= '<option value="">' . WPA_Theme_Labels::get( 'label_filter_all_levels' ) . '</option>';
            $filter_html .= '<option value="Beginner" ' . selected( $current_level, 'Beginner', false ) . '>' . __( 'Beginner', 'wp-academic-post-enhanced' ) . '</option>';
            $filter_html .= '<option value="Intermediate" ' . selected( $current_level, 'Intermediate', false ) . '>' . __( 'Intermediate', 'wp-academic-post-enhanced' ) . '</option>';
            $filter_html .= '<option value="Advanced" ' . selected( $current_level, 'Advanced', false ) . '>' . __( 'Advanced', 'wp-academic-post-enhanced' ) . '</option>';
            $filter_html .= '</select>';
            $filter_html .= '</div>';
        }

        if ( $filter_price ) {
            $filter_html .= '<div class="wpa-filter-item">';
            $filter_html .= '<select name="wpa_price" onchange="this.form.submit()">';
            $filter_html .= '<option value="">' . WPA_Theme_Labels::get( 'label_filter_all_prices' ) . '</option>';
            $filter_html .= '<option value="Free" ' . selected( $current_price, 'Free', false ) . '>' . __( 'Free', 'wp-academic-post-enhanced' ) . '</option>';
            $filter_html .= '<option value="Paid" ' . selected( $current_price, 'Paid', false ) . '>' . __( 'Paid', 'wp-academic-post-enhanced' ) . '</option>';
            $filter_html .= '</select>';
            $filter_html .= '</div>';
        }
        
        $filter_html .= '<div class="wpa-filter-actions">';
        $filter_html .= '<button type="submit" class="button">' . WPA_Theme_Labels::get( 'label_filter_button' ) . '</button>';
        if ( $current_level || $current_price || $current_search ) {
            $filter_html .= '<a href="' . get_permalink() . '" class="wpa-reset-link">' . WPA_Theme_Labels::get( 'label_reset_button' ) . '</a>';
        }
        $filter_html .= '</div>';

        $filter_html .= '</form>';
    }

    $query = new WP_Query( $args );

    // Hide Title CSS
    $inline_style = '';
    if ( $hide_title ) {
        $inline_style = '<style>.page-title, .entry-title, .archive-title { display: none !important; }</style>';
    }

    if ( ! $query->have_posts() ) {
        return $inline_style . $filter_html . '<p class="wpa-no-results">' . WPA_Theme_Labels::get( 'label_no_results' ) . '</p>';
    }

    $wrapper_class = 'wpa-course-wrapper view-' . esc_attr( $atts['style'] );
    if ( $atts['style'] === 'grid' ) {
        $wrapper_class .= ' wpa-cols-' . intval( $atts['cols'] );
    }

    $output = $inline_style . $slider_html . $hero_static_html . $filter_html . '<div class="' . esc_attr( $wrapper_class ) . '">';
    
    while ( $query->have_posts() ) {
        $query->the_post();
        $id = get_the_ID();
        $duration = get_post_meta( $id, '_wpa_course_duration', true );
        $level = get_post_meta( $id, '_wpa_course_level', true );
        $thumb = get_the_post_thumbnail_url( $id, 'medium_large' );

        $output .= '<div class="wpa-course-card">';
        
        if ( $atts['style'] !== 'compact' && $thumb ) {
            $output .= '<a href="' . get_permalink() . '" class="wpa-course-thumb" style="background-image:url(' . esc_url( $thumb ) . ');"></a>';
        } elseif ( $atts['style'] === 'compact' && $thumb ) {
             $output .= '<a href="' . get_permalink() . '" class="wpa-course-thumb-mini" style="background-image:url(' . esc_url( $thumb ) . ');"></a>';
        }

        $output .= '<div class="wpa-course-card-body">';
        $output .= '<h3 class="wpa-course-title"><a href="' . get_permalink() . '">' . get_the_title() . '</a></h3>';
        
        if ( $atts['style'] === 'list' ) {
            $output .= '<div class="wpa-course-excerpt">' . get_the_excerpt() . '</div>';
        }

        $output .= '<div class="wpa-card-meta">';
        if ( $duration ) $output .= '<span class="wpa-meta-item"><i class="dashicons dashicons-clock"></i> ' . esc_html( $duration ) . '</span>';
        if ( $level ) $output .= '<span class="wpa-meta-item"><i class="dashicons dashicons-chart-bar"></i> ' . esc_html( $level ) . '</span>';
        $output .= '</div>';
        
        $price = get_post_meta( $id, '_wpa_course_price', true );
        
        $output .= '<div class="wpa-card-footer">';
        if ( $price ) {
            $output .= '<span class="wpa-card-price">' . esc_html( $price ) . '</span>';
        } else {
             $output .= '<span class="wpa-card-price free">' . __( 'Free', 'wp-academic-post-enhanced' ) . '</span>';
        }
        $output .= '<a href="' . get_permalink() . '" class="wpa-btn wpa-btn-outline wpa-btn-sm">' . WPA_Theme_Labels::get( 'label_view_course' ) . ' <span class="dashicons dashicons-arrow-right-alt2"></span></a>';
        $output .= '</div>';

        $output .= '</div>'; // .body
        $output .= '</div>'; // .card
    }
    wp_reset_postdata();

    $output .= '</div>';
    return $output;
}
add_shortcode( 'wpa_courses', 'wpa_courses_shortcode' );