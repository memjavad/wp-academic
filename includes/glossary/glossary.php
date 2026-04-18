<?php
/**
 * Glossary Module Loader
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WPA_Glossary_Module Class
 */
class WPA_Glossary_Module {

	/**
	 * Instance
	 */
	protected static $_instance = null;

	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Include files
	 */
	private function includes() {
		require_once plugin_dir_path( __FILE__ ) . 'helpers.php';
		require_once plugin_dir_path( __FILE__ ) . 'cpt.php';
		
		if ( is_admin() ) {
			require_once plugin_dir_path( __FILE__ ) . 'glossary-admin.php';
		} else {
			require_once plugin_dir_path( __FILE__ ) . 'class-linkify.php';
			require_once plugin_dir_path( __FILE__ ) . 'frontend.php';
		}
	}

	/**
	 * Init Hooks
	 */
	private function init_hooks() {
		// Module specific init hooks can go here
	}
}

// Initialize
WPA_Glossary_Module::get_instance();
