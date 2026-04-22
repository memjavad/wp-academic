<?php

define('ABSPATH', __DIR__ . '/../');
require_once __DIR__ . '/bootstrap.php';
require_once dirname(__DIR__) . '/wp-academic-post-enhanced.php';

function test_wpa_render_settings_sections_as_grid_not_isset() {
    global $wp_settings_sections;
    $wp_settings_sections = [];

    ob_start();
    wpa_render_settings_sections_as_grid('test_page');
    $output = ob_get_clean();

    if ($output !== '') {
        echo "FAIL: Expected empty output for un-set page, got: $output\n";
        exit(1);
    }
}

function test_wpa_render_settings_sections_as_grid_basic() {
    global $wp_settings_sections, $wp_settings_fields;

    $wp_settings_sections = [
        'test_page' => [
            'section_1' => [
                'id' => 'section_1',
                'title' => 'Test Section 1',
                'callback' => function() { echo "Test Description 1"; }
            ],
            'section_2' => [
                'id' => 'section_2',
                'title' => 'Test Section 2',
                'callback' => null
            ]
        ]
    ];

    $wp_settings_fields = [
        'test_page' => [
            'section_1' => ['field_1' => []],
            'section_2' => ['field_2' => []]
        ]
    ];

    ob_start();
    wpa_render_settings_sections_as_grid('test_page');
    $output = ob_get_clean();

    $expected_strings = [
        '<div class="wpa-settings-grid">',
        '<div class="wpa-section-card">',
        '<h3>Test Section 1</h3>',
        '<div class="wpa-section-desc">Test Description 1</div>',
        '<table class="form-table" role="presentation">',
        '<!-- mocked do_settings_fields(test_page, section_1) -->',
        '</table>',
        '</div>', // Close card for section 1
        '<div class="wpa-section-card">',
        '<h3>Test Section 2</h3>',
        '<table class="form-table" role="presentation">',
        '<!-- mocked do_settings_fields(test_page, section_2) -->',
        '</table>',
        '</div>' // Close card for section 2
    ];

    foreach ($expected_strings as $str) {
        if (strpos($output, $str) === false) {
            echo "FAIL: Expected string '$str' not found in output: \n$output\n";
            exit(1);
        }
    }
}

function test_wpa_render_settings_sections_as_grid_no_fields() {
    global $wp_settings_sections, $wp_settings_fields;

    $wp_settings_sections = [
        'test_page' => [
            'section_no_fields' => [
                'id' => 'section_no_fields',
                'title' => 'Empty Section',
                'callback' => null
            ]
        ]
    ];

    $wp_settings_fields = [
        'test_page' => [] // No fields for this section
    ];

    ob_start();
    wpa_render_settings_sections_as_grid('test_page');
    $output = ob_get_clean();

    $expected_strings = [
        '<div class="wpa-settings-grid">',
        '<div class="wpa-section-card">',
        '<h3>Empty Section</h3>',
        '</div>', // Card closed immediately without table
    ];

    foreach ($expected_strings as $str) {
        if (strpos($output, $str) === false) {
            echo "FAIL: Expected string '$str' not found in empty output: \n$output\n";
            exit(1);
        }
    }

    if (strpos($output, '<table') !== false) {
        echo "FAIL: Found table when there should be no fields.\n";
        exit(1);
    }
}

// Run tests
echo "Running tests...\n";
test_wpa_render_settings_sections_as_grid_not_isset();
test_wpa_render_settings_sections_as_grid_basic();
test_wpa_render_settings_sections_as_grid_no_fields();
echo "PASS: All tests for wpa_render_settings_sections_as_grid passed.\n";
