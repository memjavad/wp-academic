<?php

// Mock basic WP functions
function plugin_dir_path($file) {
    return dirname($file) . '/';
}
function register_activation_hook($file, $function) {}
function add_action($tag, $function_to_add, $priority = 10, $accepted_args = 1) {}
function is_admin() { return false; }
global $mock_get_option; $mock_get_option = []; function get_option($option, $default = false) { global $mock_get_option; if (is_array($mock_get_option) && array_key_exists($option, $mock_get_option)) { return $mock_get_option[$option]; } return $default; }
function update_option($option, $value) { return false; }
function flush_rewrite_rules() {}
function register_widget($widget) {}

// For `do_settings_fields` which is used in the function to test
if (!function_exists('do_settings_fields')) {
    function do_settings_fields($page, $section) {
        echo "<!-- mocked do_settings_fields($page, $section) -->";
    }
}

function wp_upload_dir() {
    return [
        'basedir' => '/tmp/wp-uploads',
        'baseurl' => 'http://example.com/wp-content/uploads',
    ];
}
function add_shortcode() {}
function add_filter() {}
function plugin_dir_url() { return 'http://example.com/wp-content/plugins/wp-academic-post-enhanced/'; }
function __() { return func_get_arg(0); }
function _e() { echo func_get_arg(0); }
function esc_html() { return func_get_arg(0); }
function esc_attr() { return func_get_arg(0); }

global $mock_is_single, $mock_in_the_loop, $mock_is_main_query, $mock_get_the_ID, $mock_get_post_meta;
$mock_is_single = false;
$mock_in_the_loop = false;
$mock_is_main_query = false;
$mock_get_the_ID = 1;
$mock_get_post_meta = [];

if (!function_exists("is_single")) {
    function is_single() { global $mock_is_single; return $mock_is_single; }
}
if (!function_exists("in_the_loop")) {
    function in_the_loop() { global $mock_in_the_loop; return $mock_in_the_loop; }
}
if (!function_exists("is_main_query")) {
    function is_main_query() { global $mock_is_main_query; return $mock_is_main_query; }
}
if (!function_exists("get_the_ID")) {
    function get_the_ID() { global $mock_get_the_ID; return $mock_get_the_ID; }
}
if (!function_exists("get_post_meta")) {
    function get_post_meta($post_id, $key, $single = false) {
        global $mock_get_post_meta;
        if (isset($mock_get_post_meta[$post_id][$key])) {
            return $mock_get_post_meta[$post_id][$key];
        }
        return $single ? "" : [];
    }
}
