<?php
/**
 * Admin Menu and Pages for Course Module.
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add the Course submenu page.
 */
function wpa_add_course_admin_menu() {
    $enabled = get_option( 'wpa_course_enabled', false );
    if ( $enabled ) {
        add_submenu_page(
            'edit.php?post_type=wpa_course',
            __( 'Course Settings', 'wp-academic-post-enhanced' ),
            __( 'Settings', 'wp-academic-post-enhanced' ),
            'manage_options',
            'wp-academic-post-enhanced-course',
            'wpa_course_page'
        );
        add_submenu_page(
            'edit.php?post_type=wpa_course',
            __( 'Course Instructions', 'wp-academic-post-enhanced' ),
            __( 'Instructions', 'wp-academic-post-enhanced' ),
            'manage_options',
            'wpa-course-instructions',
            'wpa_course_help_page'
        );
    }
}
add_action( 'admin_menu', 'wpa_add_course_admin_menu' );

/**
 * Display the Course settings page.
 */
function wpa_course_page() {
    ?>
    <div class="wrap wpa-settings-wrapper">
        <div class="wpa-header">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        </div>
        
        <h2 class="nav-tab-wrapper">
            <a href="#general" class="nav-tab nav-tab-active"><?php esc_html_e( 'General', 'wp-academic-post-enhanced' ); ?></a>
            <a href="#course-page" class="nav-tab"><?php esc_html_e( 'Course Page', 'wp-academic-post-enhanced' ); ?></a>
            <a href="#lesson-page" class="nav-tab"><?php esc_html_e( 'Lesson Page', 'wp-academic-post-enhanced' ); ?></a>
            <a href="#filtering" class="nav-tab"><?php esc_html_e( 'Main Page', 'wp-academic-post-enhanced' ); ?></a>
            <a href="#dashboard" class="nav-tab"><?php esc_html_e( 'Student Dashboard', 'wp-academic-post-enhanced' ); ?></a>
            <a href="#quizzes" class="nav-tab"><?php esc_html_e( 'Quizzes', 'wp-academic-post-enhanced' ); ?></a>
            <a href="#certificates" class="nav-tab"><?php esc_html_e( 'Certificates', 'wp-academic-post-enhanced' ); ?></a>
            <a href="#emails" class="nav-tab"><?php esc_html_e( 'Emails', 'wp-academic-post-enhanced' ); ?></a>
            <a href="#design" class="nav-tab"><?php esc_html_e( 'Design', 'wp-academic-post-enhanced' ); ?></a>
            <a href="#labels" class="nav-tab"><?php esc_html_e( 'Labels', 'wp-academic-post-enhanced' ); ?></a>
        </h2>

        <form action="options.php" method="post">
            <?php settings_fields( 'wpa_course_options' ); ?>
            
            <div class="wpa-card">
                <div id="general" class="tab-content active">
                    <?php wpa_render_specific_section( 'wpa_course', 'wpa_course_section_general' ); ?>
                    <?php wpa_render_specific_section( 'wpa_course', 'wpa_course_section_features' ); ?>
                </div>

                <div id="course-page" class="tab-content">
                    <?php wpa_render_specific_section( 'wpa_course', 'wpa_course_section_course_page' ); ?>
                </div>

                <div id="lesson-page" class="tab-content">
                    <?php wpa_render_specific_section( 'wpa_course', 'wpa_course_section_lesson_page' ); ?>
                </div>

                <div id="filtering" class="tab-content">
                    <?php wpa_render_specific_section( 'wpa_course', 'wpa_course_section_filtering' ); ?>
                </div>

                <div id="dashboard" class="tab-content">
                    <?php wpa_render_specific_section( 'wpa_course', 'wpa_course_section_dashboard' ); ?>
                </div>

                <div id="quizzes" class="tab-content">
                    <?php wpa_render_specific_section( 'wpa_course', 'wpa_course_section_quizzes' ); ?>
                </div>

                <div id="certificates" class="tab-content">
                    <?php wpa_render_specific_section( 'wpa_course', 'wpa_course_section_certificates' ); ?>
                </div>

                <div id="emails" class="tab-content">
                    <?php wpa_render_specific_section( 'wpa_course', 'wpa_course_section_emails' ); ?>
                </div>

                <div id="design" class="tab-content">
                    <?php wpa_render_specific_section( 'wpa_course', 'wpa_course_section_design' ); ?>
                </div>

                <div id="labels" class="tab-content">
                    <?php wpa_render_specific_section( 'wpa_course', 'wpa_course_section_labels' ); ?>
                </div>
            </div> <!-- .wpa-card -->
            
            <div style="margin-top: 20px;">
                <?php submit_button(); ?>
            </div>
        </form>
    </div>
    <?php
}

/**
 * Display the Course Help page.
 */
function wpa_course_help_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Course Module Instructions', 'wp-academic-post-enhanced' ); ?></h1>
        
        <div class="wpa-card" style="background:#fff; padding:20px; border:1px solid #ccd0d4; margin-top:20px;">
            <h2><?php esc_html_e( 'Shortcodes', 'wp-academic-post-enhanced' ); ?></h2>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>Shortcode</th>
                        <th>Description</th>
                        <th>Parameters</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>[wpa_courses]</code></td>
                        <td>Displays a grid or list of available courses.</td>
                        <td>
                            <code>limit="6"</code> (Number of courses)<br>
                            <code>cols="3"</code> (Grid columns: 2, 3, 4)<br>
                            <code>style="grid"</code> (Options: grid, list, compact)
                        </td>
                    </tr>
                    <tr>
                        <td><code>[wpa_student_dashboard]</code></td>
                        <td>Displays the enrolled courses and progress for the logged-in user.</td>
                        <td>None</td>
                    </tr>
                </tbody>
            </table>
            <h3>5. Email Notifications</h3>
            <p>Automated emails are sent for:</p>
            <ul>
                <li><strong>Welcome:</strong> When a user clicks "Start Course".</li>
                <li><strong>Completion:</strong> When a user finishes the last lesson.</li>
                <li><strong>Quiz Passed:</strong> When a user passes a lesson quiz.</li>
            </ul>
            <p>You can customize the subject and body of these emails in the <strong>Emails</strong> tab of the Course Settings.</p>

            <h3>6. Recent Posts Slider</h3>
            <p>You can display a slider of your most recent blog posts at the top of the course archive.</p>
            <ol>
                <li>Go to the <strong>Main Page</strong> tab in Course Settings.</li>
                <li>Enable the <strong>Enable Recent Posts Slider</strong> checkbox.</li>
                <li>Set the number of slides to show.</li>
                <li>Click <strong>Save Changes</strong>.</li>
            </ol>
        </div>
    </div>
    <?php
}

/**
 * Enqueue Color Picker & Media Library
 */
function wpa_course_admin_enqueue( $hook ) {
    if ( strpos( $hook, 'wp-academic-post-enhanced-course' ) !== false || 'profile.php' === $hook || 'user-edit.php' === $hook ) {
        wp_enqueue_media();
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wpa-course-admin', plugin_dir_url( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'assets/js/course-admin.js', ['wp-color-picker', 'jquery'], '1.3', true );
    }
}
add_action( 'admin_enqueue_scripts', 'wpa_course_admin_enqueue' );