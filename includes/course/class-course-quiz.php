<?php
/**
 * Enhanced Quiz Feature for Lessons.
 * Supports Multiple Questions and Passing Grades.
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPA_Course_Quiz {

    public function __construct() {
        $options = get_option( 'wpa_course_settings' );
        $enabled = isset( $options['enable_quizzes'] ) ? $options['enable_quizzes'] : 1;
        
        if ( ! $enabled ) {
            return;
        }

        add_action( 'add_meta_boxes', [ $this, 'add_quiz_meta_box' ] );
        add_action( 'save_post', [ $this, 'save_quiz_meta' ] );
        add_action( 'wp_ajax_wpa_course_submit_quiz', [ $this, 'ajax_submit_quiz' ] );
        add_filter( 'the_content', [ $this, 'render_quiz_on_lesson' ], 20 );
        
        // Admin scripts for quiz builder
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );
    }

    public function enqueue_admin_scripts( $hook ) {
        if ( 'post.php' === $hook || 'post-new.php' === $hook ) {
            wp_enqueue_script( 'wpa-quiz-admin', plugin_dir_url( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'assets/js/quiz-admin.js', ['jquery', 'jquery-ui-sortable'], '1.0', true );
            wp_enqueue_style( 'wpa-quiz-admin-css', plugin_dir_url( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'assets/css/admin-settings.css' ); // Reuse existing or add new
        }
    }

    /**
     * Add Quiz Meta Box
     */
    public function add_quiz_meta_box() {
        add_meta_box(
            'wpa_lesson_quiz',
            __( 'Lesson Quiz', 'wp-academic-post-enhanced' ),
            [ $this, 'render_quiz_meta_box' ],
            'wpa_lesson',
            'normal',
            'high'
        );
    }

    /**
     * Render Quiz Meta Box (Multiple Questions)
     */
    public function render_quiz_meta_box( $post ) {
        // Fetch new structure
        $questions = get_post_meta( $post->ID, '_wpa_quiz_questions', true );
        $passing_grade = get_post_meta( $post->ID, '_wpa_quiz_passing_grade', true );
        if ( empty( $passing_grade ) ) $passing_grade = 80;

        // Migration/Fallback for old single question
        if ( empty( $questions ) ) {
            $old_q = get_post_meta( $post->ID, '_wpa_quiz_question', true );
            if ( $old_q ) {
                $questions = [
                    [
                        'question' => $old_q,
                        'options' => get_post_meta( $post->ID, '_wpa_quiz_options', true ),
                        'correct' => get_post_meta( $post->ID, '_wpa_quiz_correct', true ),
                    ]
                ];
            } else {
                $questions = [];
            }
        }

        wp_nonce_field( 'wpa_quiz_save', 'wpa_quiz_nonce' );
        ?>
        <div id="wpa-quiz-builder-wrapper">
            <p>
                <label for="wpa_quiz_passing_grade"><strong><?php esc_html_e( 'Passing Grade (%)', 'wp-academic-post-enhanced' ); ?></strong></label>
                <input type="number" name="wpa_quiz_passing_grade" id="wpa_quiz_passing_grade" value="<?php echo esc_attr( $passing_grade ); ?>" min="0" max="100" class="small-text">
            </p>
            
            <div id="wpa-quiz-questions-container">
                <?php 
                if ( ! empty( $questions ) ) {
                    foreach ( $questions as $idx => $q ) {
                        $this->render_question_item( $idx, $q );
                    }
                }
                ?>
            </div>

            <p>
                <button type="button" class="button button-primary" id="wpa-add-question-btn"><?php esc_html_e( 'Add Question', 'wp-academic-post-enhanced' ); ?></button>
            </p>
            
            <!-- Template for JS -->
            <script type="text/template" id="wpa-question-template">
                <?php $this->render_question_item( 'INDEX', ['question' => '', 'options' => ['', '', '', ''], 'correct' => 0] ); ?>
            </script>
        </div>
        <style>
            .wpa-quiz-item { background: #f9fafb; border: 1px solid #ccd0d4; padding: 15px; margin-bottom: 15px; border-radius: 4px; }
            .wpa-quiz-header { display: flex; justify-content: space-between; margin-bottom: 10px; cursor: move; }
            .wpa-quiz-options-list li { display: flex; align-items: center; margin-bottom: 5px; }
            .wpa-quiz-remove { color: #b32d2e; text-decoration: none; }
        </style>
        <?php
    }

    private function render_question_item( $idx, $data ) {
        $q_text = isset( $data['question'] ) ? $data['question'] : '';
        $options = isset( $data['options'] ) ? $data['options'] : ['', '', '', ''];
        $correct = isset( $data['correct'] ) ? $data['correct'] : 0;
        ?>
        <div class="wpa-quiz-item" data-index="<?php echo esc_attr( $idx ); ?>">
            <div class="wpa-quiz-header">
                <strong><?php esc_html_e( 'Question', 'wp-academic-post-enhanced' ); ?></strong>
                <a href="#" class="wpa-quiz-remove"><?php esc_html_e( 'Remove', 'wp-academic-post-enhanced' ); ?></a>
            </div>
            <textarea name="wpa_quiz[<?php echo esc_attr( $idx ); ?>][question]" rows="2" class="widefat" placeholder="<?php esc_attr_e( 'Enter question...', 'wp-academic-post-enhanced' ); ?>"><?php echo esc_textarea( $q_text ); ?></textarea>
            
            <p style="margin: 10px 0 5px;"><strong><?php esc_html_e( 'Answers (Select Correct)', 'wp-academic-post-enhanced' ); ?></strong></p>
            <ul class="wpa-quiz-options-list">
                <?php for ( $i = 0; $i < 4; $i++ ) : 
                    $opt_val = isset( $options[$i] ) ? $options[$i] : '';
                ?>
                <li>
                    <input type="radio" name="wpa_quiz[<?php echo esc_attr( $idx ); ?>][correct]" value="<?php echo $i; ?>" <?php checked( $correct, $i ); ?>>
                    <input type="text" name="wpa_quiz[<?php echo esc_attr( $idx ); ?>][options][]" value="<?php echo esc_attr( $opt_val ); ?>" class="widefat" style="margin-left: 10px;">
                </li>
                <?php endfor; ?>
            </ul>
        </div>
        <?php
    }

    /**
     * Save Quiz Data
     */
    public function save_quiz_meta( $post_id ) {
        if ( ! isset( $_POST['wpa_quiz_nonce'] ) || ! wp_verify_nonce( $_POST['wpa_quiz_nonce'], 'wpa_quiz_save' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        if ( isset( $_POST['wpa_quiz_passing_grade'] ) ) {
            update_post_meta( $post_id, '_wpa_quiz_passing_grade', absint( $_POST['wpa_quiz_passing_grade'] ) );
        }

        if ( isset( $_POST['wpa_quiz'] ) && is_array( $_POST['wpa_quiz'] ) ) {
            $questions = [];
            foreach ( $_POST['wpa_quiz'] as $item ) {
                if ( empty( $item['question'] ) ) continue;
                
                $options = array_map( 'sanitize_text_field', $item['options'] );
                $questions[] = [
                    'question' => sanitize_textarea_field( $item['question'] ),
                    'options'  => $options,
                    'correct'  => intval( $item['correct'] ),
                ];
            }
            update_post_meta( $post_id, '_wpa_quiz_questions', $questions );
        } else {
            // If empty, maybe clear?
            delete_post_meta( $post_id, '_wpa_quiz_questions' );
        }
    }

    /**
     * Render Quiz on Lesson Page
     */
    public function render_quiz_on_lesson( $content ) {
        if ( ! is_singular( 'wpa_lesson' ) || ! is_user_logged_in() ) {
            return $content;
        }

        $post_id = get_the_ID();
        $questions = get_post_meta( $post_id, '_wpa_quiz_questions', true );
        
        // Fallback
        if ( empty( $questions ) ) {
            if ( $old_q ) {
                $questions = [
                    [
                        'question' => $old_q,
                        'options' => get_post_meta( $post_id, '_wpa_quiz_options', true ),
                        'correct' => get_post_meta( $post_id, '_wpa_quiz_correct', true ),
                    ]
                ];
            } else {
                return $content;
            }
        }

        $user_id = get_current_user_id();
        $passed_quizzes = get_user_meta( $user_id, '_wpa_passed_quizzes', true );
        if ( ! is_array( $passed_quizzes ) ) $passed_quizzes = [];
        $is_passed = in_array( $post_id, $passed_quizzes );

        $quiz_label = WPA_Theme_Labels::get( 'quiz_label' );
        $success_msg = WPA_Theme_Labels::get( 'quiz_success_msg' );

        ob_start();
        ?>
        <div class="wpa-lesson-quiz <?php echo $is_passed ? 'quiz-passed' : ''; ?>">
            <h3><span class="dashicons dashicons-clipboard"></span> <?php echo esc_html( $quiz_label ); ?></h3>
            
            <?php if ( $is_passed ) : ?>
                <div class="wpa-quiz-success-msg"><p><span class="dashicons dashicons-yes"></span> <?php echo esc_html( $success_msg ); ?></p></div>
            <?php else : ?>
                <form id="wpa-quiz-form" data-lesson="<?php echo esc_attr( $post_id ); ?>">
                    <?php foreach ( $questions as $idx => $q ) : ?>
                        <div class="wpa-frontend-question" data-idx="<?php echo $idx; ?>">
                            <p class="wpa-quiz-question"><strong><?php echo esc_html( ($idx + 1) . '. ' . $q['question'] ); ?></strong></p>
                            <ul class="wpa-quiz-options">
                                <?php foreach ( $q['options'] as $opt_idx => $opt ) : 
                                    if ( empty( $opt ) ) continue;
                                ?>
                                    <li>
                                        <label>
                                            <input type="radio" name="wpa_quiz_answer[<?php echo $idx; ?>]" value="<?php echo esc_attr( $opt_idx ); ?>"> 
                                            <?php echo esc_html( $opt ); ?>
                                        </label>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                    
                    <button type="submit" class="button button-primary wpa-submit-quiz-btn"><?php esc_html_e( 'Submit Quiz', 'wp-academic-post-enhanced' ); ?></button>
                    <div class="wpa-quiz-feedback"></div>
                </form>
            <?php endif; ?>
        </div>
        <?php
        $quiz_html = ob_get_clean();

        // Inject
        if ( strpos( $content, 'class="wpa-course-navigation"' ) !== false ) {
            $content = str_replace( '<div class="wpa-course-navigation"', $quiz_html . '<div class="wpa-course-navigation"', $content );
            if ( ! $is_passed ) {
                $content = str_replace( 'id="wpa-mark-complete"', 'id="wpa-mark-complete" disabled="disabled" title="' . __( 'Pass the quiz to complete this lesson.', 'wp-academic-post-enhanced' ) . '" style="opacity:0.5; pointer-events:none;"', $content );
            }
        } else {
            $content .= $quiz_html;
        }

        return $content;
    }

    /**
     * AJAX: Handle Quiz Submission
     */
    public function ajax_submit_quiz() {
        check_ajax_referer( 'wpa_course_progress_nonce', 'nonce' );
        
        if ( ! is_user_logged_in() ) wp_send_json_error( __( 'Please log in.', 'wp-academic-post-enhanced' ) );

        $lesson_id = intval( $_POST['lesson_id'] );
        $answers = isset( $_POST['answers'] ) ? (array) $_POST['answers'] : []; // Array of idx => answer_idx
        
        $questions = get_post_meta( $lesson_id, '_wpa_quiz_questions', true );
        // Fallback
        if ( empty( $questions ) ) {
             $old_q = get_post_meta( $lesson_id, '_wpa_quiz_question', true );
             if ( $old_q ) {
                 $questions = [ [ 'correct' => get_post_meta( $lesson_id, '_wpa_quiz_correct', true ) ] ];
             }
        }

        if ( empty( $questions ) ) wp_send_json_error( 'No questions found.' );

        $score = 0;
        $total = count( $questions );
        
        foreach ( $questions as $idx => $q ) {
            if ( isset( $answers[$idx] ) && intval( $answers[$idx] ) === intval( $q['correct'] ) ) {
                $score++;
            }
        }

        $percentage = ($score / $total) * 100;
        $passing_grade = get_post_meta( $lesson_id, '_wpa_quiz_passing_grade', true );
        if ( empty( $passing_grade ) ) $passing_grade = 80;

        $success_msg = WPA_Theme_Labels::get( 'quiz_success_msg' );
        
        if ( $percentage >= $passing_grade ) {
            $user_id = get_current_user_id();
            $passed = get_user_meta( $user_id, '_wpa_passed_quizzes', true );
            if ( ! is_array( $passed ) ) $passed = [];
            
            if ( ! in_array( $lesson_id, $passed ) ) {
                $passed[] = $lesson_id;
                update_user_meta( $user_id, '_wpa_passed_quizzes', $passed );
                do_action( 'wpa_course_quiz_passed', $lesson_id, $user_id );
            }
            
            wp_send_json_success( [ 
                'message' => $success_msg . sprintf( ' (Score: %d%%)', round($percentage) ),
                'passed' => true
            ] );
        } else {
            $msg = sprintf( __( 'You scored %d%%. Minimum required is %d%%. Please try again.', 'wp-academic-post-enhanced' ), round($percentage), $passing_grade );
            wp_send_json_error( $msg );
        }
    }
}

new WPA_Course_Quiz();