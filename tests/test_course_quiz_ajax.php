<?php

// Define ABSPATH to satisfy security checks in WordPress files
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/../');
}

require_once __DIR__ . '/bootstrap.php';

// Mock variables to control behavior
$mock_is_user_logged_in = false;
$mock_get_post_meta_questions = [];
$mock_get_post_meta_passing_grade = 80;
$mock_get_user_meta_passed = [];
$mock_json_error_called = false;
$mock_json_error_msg = '';
$mock_json_success_called = false;
$mock_json_success_msg = '';
$mock_do_action_calls = [];

function check_ajax_referer($action, $query_arg = false, $die = true) {
    // Do nothing, assume it passes
}

function is_user_logged_in() {
    global $mock_is_user_logged_in;
    return $mock_is_user_logged_in;
}

function wp_send_json_error($data = null, $status_code = null, $options = 0) {
    global $mock_json_error_called, $mock_json_error_msg;
    $mock_json_error_called = true;
    $mock_json_error_msg = $data;
    // Prevent script from exiting, normally this dies but we need to continue execution for tests
    throw new Exception("wp_send_json_error: " . (is_string($data) ? $data : json_encode($data)));
}

function wp_send_json_success($data = null, $status_code = null, $options = 0) {
    global $mock_json_success_called, $mock_json_success_msg;
    $mock_json_success_called = true;
    $mock_json_success_msg = $data;
    // Prevent script from exiting
    throw new Exception("wp_send_json_success: " . (is_string($data) ? $data : json_encode($data)));
}

function get_post_meta($post_id, $key, $single = false) {
    global $mock_get_post_meta_questions, $mock_get_post_meta_passing_grade;
    if ($key === '_wpa_quiz_questions') {
        return $mock_get_post_meta_questions;
    }
    if ($key === '_wpa_quiz_passing_grade') {
        return $mock_get_post_meta_passing_grade;
    }
    return '';
}

function get_user_meta($user_id, $key, $single = false) {
    global $mock_get_user_meta_passed;
    if ($key === '_wpa_passed_quizzes') {
        return $mock_get_user_meta_passed;
    }
    return [];
}

function update_user_meta($user_id, $meta_key, $meta_value, $prev_value = '') {
    global $mock_get_user_meta_passed;
    if ($meta_key === '_wpa_passed_quizzes') {
        $mock_get_user_meta_passed = $meta_value;
    }
    return true;
}

function get_current_user_id() {
    return 1;
}

function do_action($tag, ...$args) {
    global $mock_do_action_calls;
    $mock_do_action_calls[] = [
        'tag' => $tag,
        'args' => $args
    ];
}

class WPA_Theme_Labels {
    public static function get($key) {
        return "Quiz Passed";
    }
}

// Include the class file
require_once __DIR__ . '/../includes/course/class-course-quiz.php';

// Initialize the class
$quiz = new WPA_Course_Quiz();

// Test 1: Not logged in
echo "Running Test 1: Not logged in...\n";
$mock_is_user_logged_in = false;
$mock_json_error_called = false;
$mock_json_error_msg = '';

try {
    $quiz->ajax_submit_quiz();
} catch (Exception $e) {
    // Expected exception from wp_send_json_error
}

if (!$mock_json_error_called || $mock_json_error_msg !== 'Please log in.') {
    echo "FAIL: Expected 'Please log in.' error. Got: " . print_r($mock_json_error_msg, true) . "\n";
    exit(1);
} else {
    echo "PASS: Test 1\n";
}

// Test 2: No questions found
echo "Running Test 2: No questions found...\n";
$mock_is_user_logged_in = true;
$_POST['lesson_id'] = 123;
$mock_get_post_meta_questions = [];
$mock_json_error_called = false;
$mock_json_error_msg = '';

try {
    $quiz->ajax_submit_quiz();
} catch (Exception $e) {
    // Expected exception
}

if (!$mock_json_error_called || $mock_json_error_msg !== 'No questions found.') {
    echo "FAIL: Expected 'No questions found.' error. Got: " . print_r($mock_json_error_msg, true) . "\n";
    exit(1);
} else {
    echo "PASS: Test 2\n";
}

// Test 3: Failed Quiz (below passing grade)
echo "Running Test 3: Failed Quiz...\n";
$mock_is_user_logged_in = true;
$_POST['lesson_id'] = 123;
// 2 questions, passing grade 80. Answering 1 correct means 50% score.
$mock_get_post_meta_questions = [
    0 => ['correct' => 1],
    1 => ['correct' => 2]
];
$mock_get_post_meta_passing_grade = 80;
$_POST['answers'] = [
    0 => 1, // Correct
    1 => 0  // Incorrect
];
$mock_json_error_called = false;
$mock_json_error_msg = '';

try {
    $quiz->ajax_submit_quiz();
} catch (Exception $e) {
    // Expected exception
}

if (!$mock_json_error_called || strpos($mock_json_error_msg, 'You scored 50%') === false) {
    echo "FAIL: Expected failed quiz error. Got: " . print_r($mock_json_error_msg, true) . "\n";
    exit(1);
} else {
    echo "PASS: Test 3\n";
}

// Test 4: Passed Quiz (at or above passing grade)
echo "Running Test 4: Passed Quiz...\n";
$mock_is_user_logged_in = true;
$_POST['lesson_id'] = 123;
// 2 questions, passing grade 80. Answering 2 correct means 100% score.
$mock_get_post_meta_questions = [
    0 => ['correct' => 1],
    1 => ['correct' => 2]
];
$mock_get_post_meta_passing_grade = 80;
$_POST['answers'] = [
    0 => 1, // Correct
    1 => 2  // Correct
];
$mock_get_user_meta_passed = [];
$mock_do_action_calls = [];
$mock_json_success_called = false;
$mock_json_success_msg = '';

try {
    $quiz->ajax_submit_quiz();
} catch (Exception $e) {
    // Expected exception
}

if (!$mock_json_success_called) {
    echo "FAIL: Expected passed quiz success. Got no success call.\n";
    exit(1);
} elseif (!in_array(123, $mock_get_user_meta_passed)) {
    echo "FAIL: Lesson ID not added to user meta.\n";
    exit(1);
} elseif (empty($mock_do_action_calls) || $mock_do_action_calls[0]['tag'] !== 'wpa_course_quiz_passed') {
    echo "FAIL: Action wpa_course_quiz_passed not triggered correctly.\n";
    exit(1);
} else {
    echo "PASS: Test 4\n";
}

echo "All tests passed successfully!\n";
