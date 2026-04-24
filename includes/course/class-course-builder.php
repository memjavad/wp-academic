<?php
/**
 * Visual Course Builder Class.
 * Handles Sections and Drag-and-Drop Lessons.
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPA_Course_Builder {

    public function __construct() {
        add_action( 'add_meta_boxes', [ $this, 'register_meta_box' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
        
        // AJAX Handlers
        add_action( 'wp_ajax_wpa_add_lesson', [ $this, 'ajax_add_lesson' ] );
        add_action( 'wp_ajax_wpa_bulk_add_lessons', [ $this, 'ajax_bulk_add_lessons' ] );
        add_action( 'wp_ajax_wpa_remove_lesson', [ $this, 'ajax_remove_lesson' ] );
        add_action( 'wp_ajax_wpa_save_course_structure', [ $this, 'ajax_save_structure' ] );
    }

    public function register_meta_box() {
        add_meta_box(
            'wpa_course_builder',
            __( 'Course Curriculum', 'wp-academic-post-enhanced' ),
            [ $this, 'render_builder' ],
            'wpa_course',
            'normal',
            'high'
        );
    }

    public function render_builder( $post ) {
        // Get all lessons for this course
        $lessons = get_posts( [
            'post_type'      => 'wpa_lesson',
            'posts_per_page' => -1,
            'meta_key'       => '_wpa_course_id',
            'meta_value'     => $post->ID,
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
        ] );

        // Group by Section
        $sections = [];
        $unassigned = [];

        // Pre-cache postmeta to avoid N+1 queries in the loop
        if ( ! empty( $lessons ) ) {
            update_postmeta_cache( wp_list_pluck( $lessons, 'ID' ) );
        }

        foreach ( $lessons as $lesson ) {
            $sect = get_post_meta( $lesson->ID, '_wpa_lesson_section', true );
            if ( ! empty( $sect ) ) {
                $sections[ $sect ][] = $lesson;
            } else {
                $unassigned[] = $lesson;
            }
        }
        
        // Get Section Order (if stored) - for now just arbitrary or alphabetic?
        // Ideally we should store section order in course meta.
        $section_order = get_post_meta( $post->ID, '_wpa_course_section_order', true );
        if ( ! empty( $section_order ) && is_array( $section_order ) ) {
            // Reorder $sections based on key
            $ordered_sections = [];
            foreach ( $section_order as $sect_name ) {
                if ( isset( $sections[ $sect_name ] ) ) {
                    $ordered_sections[ $sect_name ] = $sections[ $sect_name ];
                    unset( $sections[ $sect_name ] );
                }
            }
            // Append remaining (newly created elsewhere?)
            $ordered_sections = array_merge( $ordered_sections, $sections );
            $sections = $ordered_sections;
        }

        ?>
        <div id="wpa-course-builder-wrapper">
            <div class="wpa-builder-toolbar">
                <input type="text" id="wpa-new-lesson-title" placeholder="<?php esc_attr_e( 'New Lesson Title...', 'wp-academic-post-enhanced' ); ?>">
                <button type="button" id="wpa-add-lesson-btn" class="button button-primary"><?php esc_html_e( 'Add Lesson', 'wp-academic-post-enhanced' ); ?></button>
                <button type="button" id="wpa-add-section-btn" class="button button-secondary"><?php esc_html_e( 'Add Section', 'wp-academic-post-enhanced' ); ?></button>
                <button type="button" id="wpa-bulk-add-toggle" class="button button-secondary"><?php esc_html_e( 'Bulk Add', 'wp-academic-post-enhanced' ); ?></button>
                <span class="spinner"></span>
            </div>

            <div id="wpa-bulk-add-area" style="display:none; padding:15px; background:#fff; border:1px solid #ccd0d4; margin-bottom:20px;">
                <p><strong><?php esc_html_e( 'Bulk Add Lessons', 'wp-academic-post-enhanced' ); ?></strong></p>
                <p class="description"><?php esc_html_e( 'Enter lesson titles, one per line. They will be added to the "Unassigned" section.', 'wp-academic-post-enhanced' ); ?></p>
                <textarea id="wpa-bulk-lessons-input" rows="5" class="widefat" style="margin-bottom:10px;"></textarea>
                <button type="button" id="wpa-bulk-import-btn" class="button button-primary"><?php esc_html_e( 'Import Lessons', 'wp-academic-post-enhanced' ); ?></button>
                <button type="button" id="wpa-bulk-cancel-btn" class="button"><?php esc_html_e( 'Cancel', 'wp-academic-post-enhanced' ); ?></button>
            </div>

            <div id="wpa-builder-canvas">
                <!-- Sections -->
                <?php foreach ( $sections as $sect_name => $sect_lessons ) : ?>
                    <div class="wpa-builder-section" data-section="<?php echo esc_attr( $sect_name ); ?>">
                        <div class="wpa-section-header">
                            <span class="dashicons dashicons-move wpa-section-handle"></span>
                            <input type="text" class="wpa-section-title-input" value="<?php echo esc_attr( $sect_name ); ?>">
                            <span class="dashicons dashicons-trash wpa-section-remove" title="<?php esc_attr_e( 'Delete Section', 'wp-academic-post-enhanced' ); ?>"></span>
                        </div>
                        <ul class="wpa-section-lessons">
                            <?php foreach ( $sect_lessons as $lesson ) : 
                                echo $this->get_lesson_html( $lesson );
                            endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>

                <!-- Unassigned / General Section -->
                <div class="wpa-builder-section wpa-unassigned-section" data-section="">
                    <div class="wpa-section-header">
                        <strong><?php esc_html_e( 'Unassigned Lessons', 'wp-academic-post-enhanced' ); ?></strong>
                    </div>
                    <ul class="wpa-section-lessons">
                        <?php foreach ( $unassigned as $lesson ) : 
                            echo $this->get_lesson_html( $lesson );
                        endforeach; ?>
                    </ul>
                </div>
            </div>
            
            <p class="description"><?php esc_html_e( 'Drag and drop to reorder lessons and sections. Changes are saved automatically.', 'wp-academic-post-enhanced' ); ?></p>
        </div>

        <!-- Templates -->
        <script type="text/template" id="wpa-section-template">
            <div class="wpa-builder-section" data-section="">
                <div class="wpa-section-header">
                    <span class="dashicons dashicons-move wpa-section-handle"></span>
                    <input type="text" class="wpa-section-title-input" value="<?php esc_attr_e( 'New Section', 'wp-academic-post-enhanced' ); ?>" placeholder="Section Name">
                    <span class="dashicons dashicons-trash wpa-section-remove"></span>
                </div>
                <ul class="wpa-section-lessons"></ul>
            </div>
        </script>
        
        <style>
            #wpa-builder-canvas { background: #f0f0f1; padding: 20px; border: 1px solid #ccd0d4; min-height: 200px; }
            .wpa-builder-section { background: #fff; border: 1px solid #e5e7eb; margin-bottom: 20px; border-radius: 4px; }
            .wpa-section-header { padding: 10px; background: #f9fafb; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; gap: 10px; cursor: move; }
            .wpa-section-title-input { border: none; background: transparent; font-weight: 600; font-size: 1.1em; width: 100%; box-shadow: none !important; }
            .wpa-section-title-input:focus { background: #fff; box-shadow: 0 0 0 1px #2271b1 !important; }
            .wpa-section-lessons { margin: 0; padding: 10px; min-height: 50px; }
            .wpa-builder-lesson-item { background: #fff; border: 1px solid #d1d5db; margin-bottom: 8px; padding: 8px 12px; display: flex; align-items: center; justify-content: space-between; cursor: move; border-radius: 3px; }
            .wpa-builder-lesson-item:hover { border-color: #2271b1; }
            .wpa-lesson-handle { color: #ccc; margin-right: 10px; cursor: move; }
            .wpa-section-remove { color: #d63638; cursor: pointer; margin-left: auto; }
            .wpa-unassigned-section { border-style: dashed; }
            .ui-sortable-placeholder { border: 1px dashed #2271b1; background: #f0f6fc; height: 40px; margin-bottom: 8px; visibility: visible !important; }
        </style>
        <?php
    }

    public function enqueue_scripts( $hook ) {
        global $post;
        if ( ( $hook === 'post.php' || $hook === 'post-new.php' ) && isset( $post ) && 'wpa_course' === $post->post_type ) {
            wp_enqueue_script( 'jquery-ui-sortable' );
            wp_enqueue_script( 'wpa-course-builder', plugin_dir_url( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'assets/js/course-builder.js', [ 'jquery', 'jquery-ui-sortable' ], '1.2', true );
            
            wp_localize_script( 'wpa-course-builder', 'wpa_course_vars', [
                'nonce' => wp_create_nonce( 'wpa_course_builder_nonce' )
            ] );
        }
    }

    // --- AJAX Logic ---

    public function ajax_save_structure() {
        check_ajax_referer( 'wpa_course_builder_nonce', 'nonce' );
        
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( 'Unauthorized' );
        }

        $course_id = intval( $_POST['course_id'] );
        $structure = isset( $_POST['structure'] ) ? $_POST['structure'] : []; // Array of { name: 'S1', lessons: [1,2] }

        if ( empty( $course_id ) ) wp_send_json_error( 'No course ID' );

        $section_names_ordered = [];
        $global_menu_order = 0;

        foreach ( $structure as $sect ) {
            $sect_name = sanitize_text_field( $sect['name'] );
            $lesson_ids = isset( $sect['lessons'] ) ? (array) $sect['lessons'] : [];
            
            if ( ! empty( $sect_name ) ) {
                $section_names_ordered[] = $sect_name;
            }

            foreach ( $lesson_ids as $lesson_id ) {
                $lid = intval( $lesson_id );
                if ( $lid ) {
                    // Update Section
                    update_post_meta( $lid, '_wpa_lesson_section', $sect_name );
                    // Update Order
                    wp_update_post( [ 'ID' => $lid, 'menu_order' => $global_menu_order ] );
                    $global_menu_order++;
                }
            }
        }

        // Save Section Order for next render
        update_post_meta( $course_id, '_wpa_course_section_order', $section_names_ordered );

        wp_send_json_success();
    }

    // Reuse existing add/remove but they need to append to unassigned list in JS
    public function ajax_add_lesson() {
        check_ajax_referer( 'wpa_course_builder_nonce', 'nonce' );
        if ( ! current_user_can( 'edit_posts' ) ) wp_send_json_error();

        $title = sanitize_text_field( $_POST['title'] );
        $course_id = intval( $_POST['course_id'] );

        $options = get_option( 'wpa_course_settings' );
        $suffix = isset( $options['label_lesson_suffix'] ) ? $options['label_lesson_suffix'] : ' in the field of psychology';
        $content = $title . $suffix;

        $lesson_id = wp_insert_post( [
            'post_type'    => 'wpa_lesson',
            'post_title'   => $title,
            'post_content' => $content,
            'post_status'  => 'publish',
            'meta_input'   => [ '_wpa_course_id' => $course_id ]
        ] );

        if ( is_wp_error( $lesson_id ) ) wp_send_json_error( $lesson_id->get_error_message() );

        $html = $this->get_lesson_html( get_post( $lesson_id ) );
        wp_send_json_success( [ 'html' => $html ] );
    }

    public function ajax_remove_lesson() {
        check_ajax_referer( 'wpa_course_builder_nonce', 'nonce' );
        if ( ! current_user_can( 'edit_posts' ) ) wp_send_json_error();
        $lesson_id = intval( $_POST['lesson_id'] );
        delete_post_meta( $lesson_id, '_wpa_course_id' );
        wp_send_json_success();
    }

    public function ajax_bulk_add_lessons() {
        check_ajax_referer( 'wpa_course_builder_nonce', 'nonce' );
        if ( ! current_user_can( 'edit_posts' ) ) wp_send_json_error( 'Unauthorized' );

        $titles = isset( $_POST['titles'] ) ? sanitize_textarea_field( $_POST['titles'] ) : '';
        $course_id = intval( $_POST['course_id'] );

        if ( empty( $titles ) || empty( $course_id ) ) wp_send_json_error( 'Invalid data' );

        $lines = explode( "\n", $titles );
        $html = '';
        $count = 0;

        $options = get_option( 'wpa_course_settings' );
        $suffix = isset( $options['label_lesson_suffix'] ) ? $options['label_lesson_suffix'] : ' in the field of psychology';

        foreach ( $lines as $line ) {
            $title = sanitize_text_field( trim( $line ) );
            if ( empty( $title ) ) continue;

            $content = $title . $suffix;

            $lesson_id = wp_insert_post( [
                'post_type'    => 'wpa_lesson',
                'post_title'   => $title,
                'post_content' => $content,
                'post_status'  => 'publish',
                'meta_input'   => [ '_wpa_course_id' => $course_id ]
            ] );

            if ( ! is_wp_error( $lesson_id ) ) {
                $lesson = get_post( $lesson_id );
                $html .= $this->get_lesson_html( $lesson );
                $count++;
            }
        }

        wp_send_json_success( [ 'html' => $html, 'count' => $count ] );
    }

    private function get_lesson_html( $lesson ) {
        ob_start();
        ?>
        <li class="wpa-builder-lesson-item" data-id="<?php echo esc_attr( $lesson->ID ); ?>">
            <span class="wpa-lesson-handle"><span class="dashicons dashicons-menu"></span></span>
            <span class="wpa-lesson-title"><?php echo esc_html( $lesson->post_title ); ?></span>
            <span class="wpa-lesson-actions">
                <a href="<?php echo get_edit_post_link( $lesson->ID ); ?>" target="_blank" class="dashicons dashicons-edit"></a>
                <a href="#" class="dashicons dashicons-trash wpa-lesson-remove"></a>
            </span>
        </li>
        <?php
        return ob_get_clean();
    }
}

new WPA_Course_Builder();