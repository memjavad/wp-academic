<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('ABSPATH', dirname(dirname(__FILE__)) . '/');

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// ONLY include the file we want to test to avoid needing to mock the whole WP universe
// Actually, wp-academic-post-enhanced.php includes a lot of stuff.
// Wait, wp_academic_post_enhanced_toggle_feature is just one function inside that file.
// We can use Patchwork to mock the other functions if we don't define them early,
// BUT we have to load the file *after* Brain Monkey is set up? No, Brain Monkey can mock undefined functions.
// If the file requires other files, those files might define functions early.

// Let's redefine bootstrap to NOT load the file directly, but let the test load it?
// Or we just extract the function? No, we shouldn't modify the source just to test it.
// Let's just define dummy functions for WP core, but do NOT define the ones we want to mock!

if (!function_exists('add_shortcode')) { function add_shortcode($tag, $func) {} }
if (!function_exists('add_image_size')) { function add_image_size($name, $w, $h, $crop) {} }
if (!function_exists('esc_url')) { function esc_url($url) { return $url; } }
if (!function_exists('esc_html')) { function esc_html($html) { return $html; } }
if (!function_exists('esc_attr')) { function esc_attr($attr) { return $attr; } }

// Define basic WP constants/functions
if (!function_exists('plugin_dir_path')) { function plugin_dir_path($file) { return dirname($file) . '/'; } }
if (!function_exists('plugin_dir_url')) { function plugin_dir_url($file) { return 'http://example.com/wp-content/plugins/wp-academic-post-enhanced/'; } }
if (!function_exists('register_activation_hook')) { function register_activation_hook($file, $function) {} }
if (!function_exists('add_action')) { function add_action($hook, $function_to_add, $priority = 10, $accepted_args = 1) {} }
if (!function_exists('add_filter')) { function add_filter($hook, $function_to_add, $priority = 10, $accepted_args = 1) {} }
if (!function_exists('wp_enqueue_script')) { function wp_enqueue_script($handle, $src = '', $deps = array(), $ver = false, $in_footer = false) {} }
if (!function_exists('wp_enqueue_style')) { function wp_enqueue_style($handle, $src = '', $deps = array(), $ver = false, $media = 'all') {} }
if (!function_exists('is_admin')) { function is_admin() { return true; } }
if (!function_exists('flush_rewrite_rules')) { function flush_rewrite_rules() {} }
if (!function_exists('register_widget')) { function register_widget($w) {} }

// Fix undefined wp_upload_dir
if (!function_exists('wp_upload_dir')) {
    function wp_upload_dir() {
        return [
            'basedir' => ABSPATH . 'wp-content/uploads',
            'baseurl' => 'http://example.com/wp-content/uploads',
            'error'   => false
        ];
    }
}

class MockWPDB {
    public $options = 'wp_options';
    public function query($q) { return true; }
}
global $wpdb;
$wpdb = new MockWPDB();

// Don't require wp-academic-post-enhanced here.
// We will require it in the test setUp() AFTER Monkey\setUp().
