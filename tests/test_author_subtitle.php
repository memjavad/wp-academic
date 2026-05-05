<?php
define('ABSPATH', __DIR__ . '/../');
require_once __DIR__ . '/bootstrap.php';
require_once dirname(__DIR__) . '/includes/author/author.php';

function test_author_subtitle_enabled_and_present() {
    global $mock_get_option, $mock_is_single, $mock_in_the_loop, $mock_is_main_query, $mock_get_the_ID, $mock_get_post_meta;

    $mock_get_option = ['wp_academic_post_enhanced_author_subtitle_enabled' => true];
    $mock_is_single = true;
    $mock_in_the_loop = true;
    $mock_is_main_query = true;
    $mock_get_the_ID = 1;
    $mock_get_post_meta = [1 => ['_wp_academic_post_enhanced_author_subtitle' => 'Senior Researcher']];

    $content = 'Original Content';
    $result = wp_academic_post_enhanced_display_author_subtitle($content);

    $expected = '<p class="wpa-author-subtitle">Senior Researcher</p>Original Content';
    if ($result !== $expected) {
        echo "FAIL: Expected '$expected', got '$result'\n";
        exit(1);
    }
}

function test_author_subtitle_disabled() {
    global $mock_get_option, $mock_is_single, $mock_in_the_loop, $mock_is_main_query, $mock_get_the_ID, $mock_get_post_meta;

    $mock_get_option = ['wp_academic_post_enhanced_author_subtitle_enabled' => false];
    $mock_is_single = true;
    $mock_in_the_loop = true;
    $mock_is_main_query = true;
    $mock_get_the_ID = 1;
    $mock_get_post_meta = [1 => ['_wp_academic_post_enhanced_author_subtitle' => 'Senior Researcher']];

    $content = 'Original Content';
    $result = wp_academic_post_enhanced_display_author_subtitle($content);

    $expected = 'Original Content';
    if ($result !== $expected) {
        echo "FAIL: Expected '$expected', got '$result'\n";
        exit(1);
    }
}

function test_author_subtitle_not_single() {
    global $mock_get_option, $mock_is_single, $mock_in_the_loop, $mock_is_main_query, $mock_get_the_ID, $mock_get_post_meta;

    $mock_get_option = ['wp_academic_post_enhanced_author_subtitle_enabled' => true];
    $mock_is_single = false;
    $mock_in_the_loop = true;
    $mock_is_main_query = true;
    $mock_get_the_ID = 1;
    $mock_get_post_meta = [1 => ['_wp_academic_post_enhanced_author_subtitle' => 'Senior Researcher']];

    $content = 'Original Content';
    $result = wp_academic_post_enhanced_display_author_subtitle($content);

    $expected = 'Original Content';
    if ($result !== $expected) {
        echo "FAIL: Expected '$expected', got '$result'\n";
        exit(1);
    }
}

echo "Running tests...\n";
test_author_subtitle_enabled_and_present();
test_author_subtitle_disabled();
test_author_subtitle_not_single();
echo "PASS: All tests for author subtitle passed.\n";
