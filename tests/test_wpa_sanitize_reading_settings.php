<?php

define('ABSPATH', __DIR__ . '/../');
require_once __DIR__ . '/bootstrap.php';
require_once dirname(__DIR__) . '/includes/reading/reading-admin.php';

// Mock WP sanitize functions used by wpa_sanitize_reading_settings
if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($str) {
        return strip_tags(trim($str));
    }
}
if (!function_exists('sanitize_key')) {
    function sanitize_key($key) {
        return strtolower(preg_replace('/[^a-z0-9_\-]/i', '', $key));
    }
}
if (!function_exists('sanitize_hex_color')) {
    function sanitize_hex_color($color) {
        if ('' === $color) {
            return '';
        }
        if (preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $color)) {
            return $color;
        }
        return '';
    }
}
if (!function_exists('absint')) {
    function absint($maybeint) {
        return abs((int) $maybeint);
    }
}

function test_wpa_sanitize_reading_settings_happy_path() {
    $input = [
        'time_enabled' => '1',
        'time_label' => '  Min read  ',
        'time_position' => 'before_content',
        'resizer_enabled' => '1',
        'resizer_position' => 'manual',
        'resizer_content_selector' => '.entry-content',
        'resizer_btn_color' => '#ffffff',
        'resizer_btn_bg_color' => '#000000',
        'progress_enabled' => '1',
        'progress_color' => '#ff0000',
        'progress_height' => '5',
        'progress_position' => 'bottom',
    ];

    $result = wpa_sanitize_reading_settings($input);

    if ($result['time_enabled'] !== true) {
        echo "FAIL: Expected time_enabled to be true\n"; exit(1);
    }
    if ($result['time_label'] !== 'Min read') {
        echo "FAIL: Expected time_label to be 'Min read'\n"; exit(1);
    }
    if ($result['time_position'] !== 'before_content') {
        echo "FAIL: Expected time_position to be 'before_content'\n"; exit(1);
    }

    if ($result['resizer_enabled'] !== true) {
        echo "FAIL: Expected resizer_enabled to be true\n"; exit(1);
    }
    if ($result['resizer_position'] !== 'manual') {
        echo "FAIL: Expected resizer_position to be 'manual'\n"; exit(1);
    }
    if ($result['resizer_content_selector'] !== '.entry-content') {
        echo "FAIL: Expected resizer_content_selector to be '.entry-content'\n"; exit(1);
    }
    if ($result['resizer_btn_color'] !== '#ffffff') {
        echo "FAIL: Expected resizer_btn_color to be '#ffffff'\n"; exit(1);
    }
    if ($result['resizer_btn_bg_color'] !== '#000000') {
        echo "FAIL: Expected resizer_btn_bg_color to be '#000000'\n"; exit(1);
    }

    if ($result['progress_enabled'] !== true) {
        echo "FAIL: Expected progress_enabled to be true\n"; exit(1);
    }
    if ($result['progress_color'] !== '#ff0000') {
        echo "FAIL: Expected progress_color to be '#ff0000'\n"; exit(1);
    }
    if ($result['progress_height'] !== 5) {
        echo "FAIL: Expected progress_height to be 5\n"; exit(1);
    }
    if ($result['progress_position'] !== 'bottom') {
        echo "FAIL: Expected progress_position to be 'bottom'\n"; exit(1);
    }
}

function test_wpa_sanitize_reading_settings_empty_missing() {
    // Missing all keys.
    $input = [];

    // Set custom error handler to catch warnings/notices
    set_error_handler(function($errno, $errstr) {
        echo "FAIL: Caught PHP Warning/Notice: $errstr\n";
        exit(1);
    });

    $result = wpa_sanitize_reading_settings($input);

    restore_error_handler();

    if ($result['time_enabled'] !== false) {
        echo "FAIL: Expected time_enabled to be false when missing\n"; exit(1);
    }
    if ($result['resizer_enabled'] !== false) {
        echo "FAIL: Expected resizer_enabled to be false when missing\n"; exit(1);
    }
    if ($result['progress_enabled'] !== false) {
        echo "FAIL: Expected progress_enabled to be false when missing\n"; exit(1);
    }
    if ($result['progress_height'] !== 0) {
        echo "FAIL: Expected progress_height to be 0 for empty string\n"; exit(1);
    }
}

function test_wpa_sanitize_reading_settings_sanitization_edge_cases() {
    $input = [
        'time_label' => '<b>Bold</b> min read <script>alert(1)</script>',
        'time_position' => 'Some! Invalid Position @123',
        'resizer_position' => 'manual',
        'resizer_content_selector' => '.entry-content',
        'resizer_btn_color' => 'invalid-color',
        'resizer_btn_bg_color' => '#12345Z',
        'progress_color' => '#FFF',
        'progress_height' => '-10',
        'progress_position' => 'top',
    ];

    $result = wpa_sanitize_reading_settings($input);

    if ($result['time_label'] !== 'Bold min read alert(1)') {
        echo "FAIL: Expected time_label to be stripped of tags, got '{$result['time_label']}'\n"; exit(1);
    }
    if ($result['time_position'] !== 'someinvalidposition123') {
        echo "FAIL: Expected time_position to be sanitized key, got '{$result['time_position']}'\n"; exit(1);
    }
    if ($result['resizer_btn_color'] !== '') {
        echo "FAIL: Expected resizer_btn_color to be empty for invalid color, got '{$result['resizer_btn_color']}'\n"; exit(1);
    }
    if ($result['resizer_btn_bg_color'] !== '') {
        echo "FAIL: Expected resizer_btn_bg_color to be empty for invalid color, got '{$result['resizer_btn_bg_color']}'\n"; exit(1);
    }
    if ($result['progress_color'] !== '#FFF') {
        echo "FAIL: Expected progress_color to be '#FFF', got '{$result['progress_color']}'\n"; exit(1);
    }
    if ($result['progress_height'] !== 10) {
        echo "FAIL: Expected progress_height to be absolute int (10), got '{$result['progress_height']}'\n"; exit(1);
    }
}

echo "Running wpa_sanitize_reading_settings tests...\n";
test_wpa_sanitize_reading_settings_happy_path();
test_wpa_sanitize_reading_settings_empty_missing();
test_wpa_sanitize_reading_settings_sanitization_edge_cases();
echo "PASS: All tests for wpa_sanitize_reading_settings passed.\n";
