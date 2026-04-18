<?php
/**
 * Course Management Admin Module
 * Handles Settings Page, User Profile Fields, and Admin Menus.
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$inc_dir = plugin_dir_path( __FILE__ ) . 'inc/';

// 1. Admin Menu & Pages
require_once $inc_dir . 'admin-menu.php';

// 2. User Profile Fields
require_once $inc_dir . 'admin-user.php';

// 3. Settings Callbacks (Must be loaded before settings registration)
require_once $inc_dir . 'admin-settings-callbacks.php';

// 4. Settings Registration
require_once $inc_dir . 'admin-settings.php';
