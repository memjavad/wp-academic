<?php
/**
 * Callbacks for Course Settings Fields.
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Callbacks
function wpa_course_section_general_callback() { echo '<p>' . esc_html__( 'Configure global behavior.', 'wp-academic-post-enhanced' ) . '</p>'; }
function wpa_course_section_features_callback() { echo '<p>' . esc_html__( 'Enable/disable learning modules.', 'wp-academic-post-enhanced' ) . '</p>'; }
function wpa_course_section_quizzes_callback() { echo '<p>' . esc_html__( 'Manage quiz settings.', 'wp-academic-post-enhanced' ) . '</p>'; }
function wpa_course_section_certificates_callback() { echo '<p>' . esc_html__( 'Customize PDF certificate.', 'wp-academic-post-enhanced' ) . '</p>'; }
function wpa_course_section_emails_callback() { echo '<p>' . esc_html__( 'Automated email templates.', 'wp-academic-post-enhanced' ) . '</p>'; }
function wpa_course_section_course_page_callback() { echo '<p>' . esc_html__( 'Course page settings.', 'wp-academic-post-enhanced' ) . '</p>'; }
function wpa_course_section_lesson_page_callback() { echo '<p>' . esc_html__( 'Lesson page settings.', 'wp-academic-post-enhanced' ) . '</p>'; }
function wpa_course_section_design_callback() { echo '<p>' . esc_html__( 'Visual customization.', 'wp-academic-post-enhanced' ) . '</p>'; }
function wpa_course_section_labels_callback() { echo '<p>' . esc_html__( 'Frontend text strings.', 'wp-academic-post-enhanced' ) . '</p>'; }
function wpa_course_section_filtering_callback() { echo '<p>' . esc_html__( 'Archive filtering settings.', 'wp-academic-post-enhanced' ) . '</p>'; }
function wpa_course_section_dashboard_callback() { echo '<p>' . esc_html__( 'Student dashboard settings.', 'wp-academic-post-enhanced' ) . '</p>'; }

// Universal helper for rendering text inputs
function wpa_course_render_text_field($key, $class = 'regular-text') {
    $options = wpa_get_course_settings();
    $val = isset($options[$key]) ? $options[$key] : '';
    echo '<input type="text" name="wpa_course_settings[' . esc_attr($key) . ']" value="' . esc_attr($val) . '" class="' . esc_attr($class) . '" />';
}

// Universal helper for checkboxes
function wpa_course_render_checkbox($key, $label) {
    $options = wpa_get_course_settings();
    $val = isset($options[$key]) ? $options[$key] : 0;
    echo '<label><input type="checkbox" name="wpa_course_settings[' . esc_attr($key) . ']" value="1" ' . checked(1, $val, false) . ' /> ' . esc_html($label) . '</label>';
}

// Field Callbacks
function wpa_course_quiz_label_field_callback() { wpa_course_render_text_field('quiz_label'); }
function wpa_course_quiz_success_msg_field_callback() { wpa_course_render_text_field('quiz_success_msg', 'large-text'); }
function wpa_course_quiz_error_msg_field_callback() { wpa_course_render_text_field('quiz_error_msg', 'large-text'); }
function wpa_course_quiz_show_correct_field_callback() { wpa_course_render_checkbox('quiz_show_correct', __('Show correct answer on failure', 'wp-academic-post-enhanced')); }

function wpa_course_enable_sections_callback() { wpa_course_render_checkbox('enable_sections', __('Group lessons into Modules', 'wp-academic-post-enhanced')); }
function wpa_course_enable_quizzes_callback() { wpa_course_render_checkbox('enable_quizzes', __('Enable Quizzes', 'wp-academic-post-enhanced')); }
function wpa_course_enable_certificates_callback() { wpa_course_render_checkbox('enable_certificates', __('Enable Certificates', 'wp-academic-post-enhanced')); }
function wpa_course_enable_drip_content_callback() { wpa_course_render_checkbox('enable_drip_content', __('Enable Drip Content', 'wp-academic-post-enhanced')); }
function wpa_course_enable_emails_callback() { wpa_course_render_checkbox('enable_emails', __('Enable Email Notifications', 'wp-academic-post-enhanced')); }

function wpa_course_cert_title_field_callback() { wpa_course_render_text_field('cert_title'); }
function wpa_course_cert_subtitle_field_callback() { wpa_course_render_text_field('cert_subtitle'); }
function wpa_course_cert_completion_text_field_callback() { wpa_course_render_text_field('cert_completion_text'); }
function wpa_course_cert_logo_field_callback() { 
    $options = wpa_get_course_settings();
    echo '<input type="url" name="wpa_course_settings[cert_logo]" value="' . esc_url($options['cert_logo']) . '" class="large-text" />';
}

function wpa_course_cert_style_field_callback() {
    $options = wpa_get_course_settings();
    $val = $options['cert_style'];
    echo '<select name="wpa_course_settings[cert_style]">';
    foreach(['classic'=>'Classic', 'minimal'=>'Minimal', 'fancy'=>'Fancy'] as $k=>$v) {
        echo '<option value="'.$k.'" '.selected($val, $k, false).'>'.$v.'</option>';
    }
    echo '</select>';
}

function wpa_course_cert_text_color_field_callback() {
    $options = wpa_get_course_settings();
    echo '<input type="text" name="wpa_course_settings[cert_text_color]" value="'.esc_attr($options['cert_text_color']).'" class="wpa-color-picker" />';
}
function wpa_course_cert_bg_color_field_callback() {
    $options = wpa_get_course_settings();
    echo '<input type="text" name="wpa_course_settings[cert_bg_color]" value="'.esc_attr($options['cert_bg_color']).'" class="wpa-color-picker" />';
}
function wpa_course_cert_border_color_field_callback() {
    $options = wpa_get_course_settings();
    echo '<input type="text" name="wpa_course_settings[cert_border_color]" value="'.esc_attr($options['cert_border_color']).'" class="wpa-color-picker" />';
}
function wpa_course_cert_border_width_field_callback() {
    $options = wpa_get_course_settings();
    echo '<input type="number" name="wpa_course_settings[cert_border_width]" value="'.absint($options['cert_border_width']).'" class="small-text" /> px';
}
function wpa_course_cert_orientation_field_callback() {
    $options = wpa_get_course_settings();
    echo '<select name="wpa_course_settings[cert_orientation]">';
    echo '<option value="landscape" '.selected($options['cert_orientation'], 'landscape', false).'>Landscape</option>';
    echo '<option value="portrait" '.selected($options['cert_orientation'], 'portrait', false).'>Portrait</option>';
    echo '</select>';
}
function wpa_course_cert_font_field_callback() {
    $options = wpa_get_course_settings();
    echo '<select name="wpa_course_settings[cert_font]">';
    foreach(['helvetica'=>'Helvetica', 'times'=>'Times', 'courier'=>'Courier'] as $k=>$v) {
        echo '<option value="'.$k.'" '.selected($options['cert_font'], $k, false).'>'.$v.'</option>';
    }
    echo '</select>';
}
function wpa_course_cert_bg_image_field_callback() { 
    $options = wpa_get_course_settings();
    echo '<input type="url" name="wpa_course_settings[cert_background_image]" value="'.esc_url($options['cert_background_image']).'" class="large-text" />';
}
function wpa_course_cert_instructor_name_field_callback() { wpa_course_render_text_field('cert_instructor_name'); }
function wpa_course_cert_signature_field_callback() { 
    $options = wpa_get_course_settings();
    echo '<input type="url" name="wpa_course_settings[cert_signature]" value="'.esc_url($options['cert_signature']).'" class="large-text" />';
}

function wpa_course_email_welcome_subject_field_callback() { wpa_course_render_text_field('email_welcome_subject', 'large-text'); }
function wpa_course_email_welcome_body_field_callback() {
    $options = wpa_get_course_settings();
    echo '<textarea name="wpa_course_settings[email_welcome_body]" rows="5" class="large-text">'.esc_textarea($options['email_welcome_body']).'</textarea>';
}
function wpa_course_email_complete_subject_field_callback() { wpa_course_render_text_field('email_complete_subject', 'large-text'); }
function wpa_course_email_complete_body_field_callback() {
    $options = wpa_get_course_settings();
    echo '<textarea name="wpa_course_settings[email_complete_body]" rows="5" class="large-text">'.esc_textarea($options['email_complete_body']).'</textarea>';
}
function wpa_course_email_quiz_passed_subject_field_callback() { wpa_course_render_text_field('email_quiz_passed_subject', 'large-text'); }
function wpa_course_email_quiz_passed_body_field_callback() {
    $options = wpa_get_course_settings();
    echo '<textarea name="wpa_course_settings[email_quiz_passed_body]" rows="5" class="large-text">'.esc_textarea($options['email_quiz_passed_body']).'</textarea>';
}

function wpa_course_slug_field_callback() { wpa_course_render_text_field('course_slug'); }
function wpa_course_lesson_slug_field_callback() { wpa_course_render_text_field('lesson_slug'); }
function wpa_course_materials_field_callback() { wpa_course_render_checkbox('enable_materials', __('Show materials section', 'wp-academic-post-enhanced')); }
function wpa_course_sequential_field_callback() { wpa_course_render_checkbox('enforce_sequential', __('Enforce order', 'wp-academic-post-enhanced')); }

function wpa_course_grid_columns_field_callback() {
    $options = wpa_get_course_settings();
    echo '<select name="wpa_course_settings[grid_columns]">';
    for($i=2;$i<=4;$i++) echo '<option value="'.$i.'" '.selected($options['grid_columns'], $i, false).'>'.$i.' Columns</option>';
    echo '</select>';
}
function wpa_course_lesson_list_style_field_callback() {
    $options = wpa_get_course_settings();
    echo '<select name="wpa_course_settings[lesson_list_style]">';
    foreach(['simple','boxed','grid','z-pattern','timeline'] as $s) echo '<option value="'.$s.'" '.selected($options['lesson_list_style'], $s, false).'>'.ucfirst($s).'</option>';
    echo '</select>';
}
function wpa_course_curriculum_style_field_callback() {
    $options = wpa_get_course_settings();
    echo '<select name="wpa_course_settings[curriculum_style]">';
    foreach(['default','modern','clean','professional','glass','academic'] as $s) echo '<option value="'.$s.'" '.selected($options['curriculum_style'], $s, false).'>'.ucfirst($s).'</option>';
    echo '</select>';
}

function wpa_course_show_curriculum_duration_field_callback() { wpa_course_render_checkbox('show_curriculum_duration', __('Show duration', 'wp-academic-post-enhanced')); }
function wpa_course_show_curriculum_icons_field_callback() { wpa_course_render_checkbox('show_curriculum_icons', __('Show icons', 'wp-academic-post-enhanced')); }
function wpa_course_show_meta_header_field_callback() { wpa_course_render_checkbox('show_course_meta_header', __('Show header meta', 'wp-academic-post-enhanced')); }
function wpa_course_show_duration_field_callback() { wpa_course_render_checkbox('show_course_duration', __('Show course duration', 'wp-academic-post-enhanced')); }
function wpa_course_show_level_field_callback() { wpa_course_render_checkbox('show_course_level', __('Show difficulty level', 'wp-academic-post-enhanced')); }

function wpa_course_header_bg_color_field_callback() {
    $options = wpa_get_course_settings();
    echo '<input type="text" name="wpa_course_settings[course_header_bg_color]" value="'.esc_attr($options['course_header_bg_color']).'" class="wpa-color-picker" />';
}
function wpa_course_header_text_color_field_callback() {
    $options = wpa_get_course_settings();
    echo '<input type="text" name="wpa_course_settings[course_header_text_color]" value="'.esc_attr($options['course_header_text_color']).'" class="wpa-color-picker" />';
}

function wpa_course_show_sidebar_field_callback() { wpa_course_render_checkbox('show_sidebar', __('Show sidebar', 'wp-academic-post-enhanced')); }
function wpa_course_sidebar_pos_field_callback() {
    $options = wpa_get_course_settings();
    echo '<select name="wpa_course_settings[sidebar_position]"><option value="left" '.selected($options['sidebar_position'], 'left', false).'>Left</option><option value="right" '.selected($options['sidebar_position'], 'right', false).'>Right</option></select>';
}
function wpa_course_video_pos_field_callback() {
    $options = wpa_get_course_settings();
    echo '<select name="wpa_course_settings[video_position]"><option value="top" '.selected($options['video_position'], 'top', false).'>Top</option><option value="bottom" '.selected($options['video_position'], 'bottom', false).'>Bottom</option></select>';
}
function wpa_course_show_breadcrumbs_field_callback() { wpa_course_render_checkbox('show_breadcrumbs', __('Show breadcrumbs', 'wp-academic-post-enhanced')); }
function wpa_course_enable_focus_mode_field_callback() { wpa_course_render_checkbox('enable_focus_mode', __('Enable focus mode', 'wp-academic-post-enhanced')); }
function wpa_course_focus_mode_pos_field_callback() {
    $options = wpa_get_course_settings();
    echo '<select name="wpa_course_settings[focus_mode_position]"><option value="header" '.selected($options['focus_mode_position'], 'header', false).'>Header</option><option value="breadcrumbs" '.selected($options['focus_mode_position'], 'breadcrumbs', false).'>Breadcrumbs</option><option value="floating" '.selected($options['focus_mode_position'], 'floating', false).'>Floating</option></select>';
}
function wpa_course_show_lesson_index_field_callback() { wpa_course_render_checkbox('show_lesson_index', __('Show index', 'wp-academic-post-enhanced')); }
function wpa_course_show_lesson_author_field_callback() { wpa_course_render_checkbox('show_lesson_author', __('Show author', 'wp-academic-post-enhanced')); }
function wpa_course_show_lesson_date_field_callback() { wpa_course_render_checkbox('show_lesson_date', __('Show date', 'wp-academic-post-enhanced')); }
function wpa_course_show_sidebar_progress_field_callback() { wpa_course_render_checkbox('show_sidebar_progress', __('Show progress bar', 'wp-academic-post-enhanced')); }
function wpa_course_show_nav_buttons_field_callback() { wpa_course_render_checkbox('show_nav_buttons', __('Show nav buttons', 'wp-academic-post-enhanced')); }
function wpa_course_show_lesson_instructor_field_callback() { wpa_course_render_checkbox('show_lesson_instructor', __('Show instructor card', 'wp-academic-post-enhanced')); }

function wpa_course_primary_color_field_callback() {
    $options = wpa_get_course_settings();
    echo '<input type="text" name="wpa_course_settings[primary_color]" value="'.esc_attr($options['primary_color']).'" class="wpa-color-picker" />';
}
function wpa_course_accent_color_field_callback() {
    $options = wpa_get_course_settings();
    echo '<input type="text" name="wpa_course_settings[accent_color]" value="'.esc_attr($options['accent_color']).'" class="wpa-color-picker" />';
}
function wpa_course_lesson_title_font_size_field_callback() {
    $options = wpa_get_course_settings();
    echo '<input type="number" step="0.1" name="wpa_course_settings[lesson_title_font_size]" value="'.esc_attr($options['lesson_title_font_size']).'" class="small-text" /> em';
}

function wpa_course_label_start_course_field_callback() { wpa_course_render_text_field('label_start_course'); }
function wpa_course_label_login_enroll_field_callback() { wpa_course_render_text_field('label_login_enroll'); }
function wpa_course_label_curriculum_field_callback() { wpa_course_render_text_field('label_curriculum'); }
function wpa_course_completed_message_field_callback() { 
    $options = wpa_get_course_settings();
    echo '<textarea name="wpa_course_settings[completed_message]" rows="2" class="large-text">'.esc_textarea($options['completed_message']).'</textarea>';
}
function wpa_course_label_mark_complete_field_callback() { wpa_course_render_text_field('label_mark_complete'); }
function wpa_course_label_completed_field_callback() { wpa_course_render_text_field('label_completed'); }
function wpa_course_label_prev_lesson_field_callback() { wpa_course_render_text_field('label_prev_lesson'); }
function wpa_course_label_next_lesson_field_callback() { wpa_course_render_text_field('label_next_lesson'); }
function wpa_course_label_course_home_field_callback() { wpa_course_render_text_field('label_course_home'); }
function wpa_course_label_materials_field_callback() { wpa_course_render_text_field('label_materials'); }
function wpa_course_label_download_field_callback() { wpa_course_render_text_field('label_download'); }
function wpa_course_label_view_course_field_callback() { wpa_course_render_text_field('label_view_course'); }
function wpa_course_label_filter_all_levels_field_callback() { wpa_course_render_text_field('label_filter_all_levels'); }
function wpa_course_label_filter_all_prices_field_callback() { wpa_course_render_text_field('label_filter_all_prices'); }
function wpa_course_label_search_placeholder_field_callback() { wpa_course_render_text_field('label_search_placeholder'); }
function wpa_course_label_filter_button_field_callback() { wpa_course_render_text_field('label_filter_button'); }
function wpa_course_label_reset_button_field_callback() { wpa_course_render_text_field('label_reset_button'); }
function wpa_course_label_no_results_field_callback() { wpa_course_render_text_field('label_no_results', 'large-text'); }
function wpa_course_locked_message_field_callback() {
    $options = wpa_get_course_settings();
    echo '<textarea name="wpa_course_settings[locked_message]" rows="2" class="large-text">'.esc_textarea($options['locked_message']).'</textarea>';
}
function wpa_course_sequential_message_field_callback() {
    $options = wpa_get_course_settings();
    echo '<textarea name="wpa_course_settings[sequential_message]" rows="2" class="large-text">'.esc_textarea($options['sequential_message']).'</textarea>';
}
function wpa_course_hide_archive_title_field_callback() { wpa_course_render_checkbox('hide_archive_title', __('Hide archive title', 'wp-academic-post-enhanced')); }
function wpa_course_label_lesson_suffix_field_callback() { wpa_course_render_text_field('label_lesson_suffix'); }

function wpa_course_archive_hero_enable_field_callback() { wpa_course_render_checkbox('archive_hero_enable', __('Enable hero', 'wp-academic-post-enhanced')); }
function wpa_course_archive_hero_title_field_callback() { wpa_course_render_text_field('archive_hero_title', 'large-text'); }
function wpa_course_archive_hero_text_field_callback() {
    $options = wpa_get_course_settings();
    echo '<textarea name="wpa_course_settings[archive_hero_text]" rows="3" class="large-text">'.esc_textarea($options['archive_hero_text']).'</textarea>';
}
function wpa_course_archive_hero_image_field_callback() {
    $options = wpa_get_course_settings();
    $image_id = $options['archive_hero_image'];
    $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'medium') : '';
    ?>
    <div class="wpa-media-picker-container">
        <div class="wpa-preview-wrapper" style="margin-bottom: 10px;">
            <img src="<?php echo esc_url($image_url); ?>" class="wpa-user-avatar-preview" style="max-width: 300px; display: <?php echo $image_url ? 'block' : 'none'; ?>;">
        </div>
        <input type="hidden" name="wpa_course_settings[archive_hero_image]" value="<?php echo esc_attr($image_id); ?>">
        <button type="button" class="button wpa-media-upload-btn">Select Image</button>
        <button type="button" class="button wpa-media-remove-btn" style="<?php echo !$image_id ? 'display:none;' : ''; ?>">Remove</button>
    </div>
    <?php
}
function wpa_course_archive_hero_layout_field_callback() {
    $options = wpa_get_course_settings();
    echo '<select name="wpa_course_settings[archive_hero_layout]">';
    foreach(['split'=>'Split (Text Left, Image Right)','banner'=>'Banner (Background Image)','minimal'=>'Minimal (Centered Text)','slider'=>'Slider (Recent Courses)'] as $l=>$label) {
        echo '<option value="'.$l.'" '.selected($options['archive_hero_layout'], $l, false).'>'.$label.'</option>';
    }
    echo '</select>';
}

function wpa_course_dashboard_hide_title_field_callback() { wpa_course_render_checkbox('dashboard_hide_title', __('Hide title', 'wp-academic-post-enhanced')); }
function wpa_course_dashboard_hero_bg_field_callback() {
    $options = wpa_get_course_settings();
    $image_id = $options['dashboard_hero_bg'];
    $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'medium') : '';
    ?>
    <div class="wpa-media-picker-container">
        <div class="wpa-preview-wrapper" style="margin-bottom: 10px;">
            <img src="<?php echo esc_url($image_url); ?>" class="wpa-user-avatar-preview" style="max-width: 300px; display: <?php echo $image_url ? 'block' : 'none'; ?>;">
        </div>
        <input type="hidden" name="wpa_course_settings[dashboard_hero_bg]" value="<?php echo esc_attr($image_id); ?>">
        <button type="button" class="button wpa-media-upload-btn">Select Image</button>
        <button type="button" class="button wpa-media-remove-btn" style="<?php echo !$image_id ? 'display:none;' : ''; ?>">Remove</button>
    </div>
    <?php
}
function wpa_course_dashboard_show_avatar_field_callback() { wpa_course_render_checkbox('dashboard_show_avatar', __('Show avatar', 'wp-academic-post-enhanced')); }
function wpa_course_dashboard_welcome_text_field_callback() { wpa_course_render_text_field('dashboard_welcome_text', 'large-text'); }

function wpa_course_filter_by_level_field_callback() { wpa_course_render_checkbox('filter_by_level', __('Filter by level', 'wp-academic-post-enhanced')); }
function wpa_course_filter_by_price_field_callback() { wpa_course_render_checkbox('filter_by_price', __('Filter by price', 'wp-academic-post-enhanced')); }
function wpa_course_filter_by_search_field_callback() { wpa_course_render_checkbox('filter_by_search', __('Filter by search', 'wp-academic-post-enhanced')); }
function wpa_course_enable_course_filters_field_callback() { wpa_course_render_checkbox('enable_course_filters', __('Enable filters', 'wp-academic-post-enhanced')); }

function wpa_course_slider_enable_field_callback() { wpa_course_render_checkbox('slider_enable', __('Enable Slider', 'wp-academic-post-enhanced')); }
function wpa_course_slider_count_field_callback() {
    $options = wpa_get_course_settings();
    $val = !empty($options['slider_count']) ? $options['slider_count'] : 5;
    echo '<input type="number" name="wpa_course_settings[slider_count]" value="'.esc_attr($val).'" class="small-text" min="1" max="20" />';
}
function wpa_course_slider_autoplay_field_callback() { wpa_course_render_checkbox('slider_autoplay', __('Enable Autoplay', 'wp-academic-post-enhanced')); }
function wpa_course_slider_interval_field_callback() {
    $options = wpa_get_course_settings();
    $val = !empty($options['slider_interval']) ? $options['slider_interval'] : 5000;
    echo '<input type="number" name="wpa_course_settings[slider_interval]" value="'.esc_attr($val).'" class="regular-text" step="100" min="1000" /> ms';
}
function wpa_course_slider_show_arrows_field_callback() { wpa_course_render_checkbox('slider_show_arrows', __('Show Arrows', 'wp-academic-post-enhanced')); }
function wpa_course_slider_show_dots_field_callback() { wpa_course_render_checkbox('slider_show_dots', __('Show Dots', 'wp-academic-post-enhanced')); }
function wpa_course_slider_pause_hover_field_callback() { wpa_course_render_checkbox('slider_pause_hover', __('Pause Autoplay on Hover', 'wp-academic-post-enhanced')); }

function wpa_course_section_hero_header_field_callback() { echo '<hr><h3>' . esc_html__( 'Hero Header Settings', 'wp-academic-post-enhanced' ) . '</h3>'; }
