<?php

// Mock basic WP functions
function plugin_dir_path($file) {
    return dirname($file) . '/';
}
function register_activation_hook($file, $function) {}
function add_action($tag, $function_to_add, $priority = 10, $accepted_args = 1) {}
function is_admin() { return false; }
function get_option($option) { return false; }
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
