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
function apply_filters($tag, $value) {
    global $mock_apply_filters;
    return isset($mock_apply_filters[$tag]) ? $mock_apply_filters[$tag] : $value;
}
function get_post_meta($post_id, $key, $single = false) {
    global $mock_get_post_meta;
    return isset($mock_get_post_meta) ? $mock_get_post_meta : '';
}
function update_post_meta($post_id, $key, $value) {
    global $mock_update_post_meta;
    $mock_update_post_meta = ['post_id' => $post_id, 'key' => $key, 'value' => $value];
    return true;
}
function plugin_dir_url() { return 'http://example.com/wp-content/plugins/wp-academic-post-enhanced/'; }
function __() { return func_get_arg(0); }
function _e() { echo func_get_arg(0); }
function esc_html() { return func_get_arg(0); }
function esc_attr() { return func_get_arg(0); }

function wp_parse_args($args, $defaults = []) {
    if (is_object($args)) {
        $r = get_object_vars($args);
    } elseif (is_array($args)) {
        $r = &$args;
    } else {
        wp_parse_str($args, $r);
    }
    if (is_array($defaults) && !empty($defaults)) {
        return array_merge($defaults, $r);
    }
    return $r;
}
function wp_parse_str($string, &$array) {
    parse_str($string, $array);
}

function get_post_type($post = null) {
    return 'post';
}
function is_singular($post_types = '') {
    return true;
}
function is_main_query() {
    return true;
}
function get_the_ID() {
    return 1;
}
function get_post_field($field, $post, $context = 'display') {
    return 'test content';
}

function wp_enqueue_style($handle, $src = '', $deps = [], $ver = false, $media = 'all') {}
function wp_add_inline_style($handle, $data) {}
function wp_enqueue_script($handle, $src = '', $deps = [], $ver = false, $in_footer = false) {}
if (!defined('WP_ACADEMIC_POST_ENHANCED_FILE')) {
    define('WP_ACADEMIC_POST_ENHANCED_FILE', __FILE__);
}
if (!defined('WPA_VERSION')) {
    define('WPA_VERSION', '1.0.0');
}
