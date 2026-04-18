<?php
/**
 * Course Management Module
 * Handles Custom Post Types (Course, Lesson) and Frontend Logic.
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$inc_dir = plugin_dir_path( __FILE__ ) . 'inc/';

// 1. Helpers (Must be loaded first)
require_once $inc_dir . 'helpers.php';

// 2. Custom Post Types
require_once $inc_dir . 'cpt.php';

// 3. Meta Boxes
require_once $inc_dir . 'meta-boxes.php';

// 4. Shortcodes
require_once $inc_dir . 'shortcodes.php';

// 5. Frontend Logic (Content Filters)
require_once $inc_dir . 'frontend.php';

// 6. Assets & AJAX
require_once $inc_dir . 'assets-ajax.php';

// Load Course Builder
require_once plugin_dir_path( __FILE__ ) . 'class-course-builder.php';
// Load Quiz Feature
require_once plugin_dir_path( __FILE__ ) . 'class-course-quiz.php';
// Load Email Feature
require_once plugin_dir_path( __FILE__ ) . 'class-course-emails.php';
// Load Certificate Feature
require_once plugin_dir_path( __FILE__ ) . 'class-course-certificate.php';