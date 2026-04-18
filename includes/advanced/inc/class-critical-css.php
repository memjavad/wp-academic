<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class WP_Academic_Post_Enhanced_Critical_CSS {

    private static $instance;

    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        $options = get_option( 'wp_academic_post_enhanced_page_optimization_options', [] );
        if ( ! empty( $options['critical_css_enabled'] ) ) {
            add_action( 'wp_head', [ $this, 'inline_critical_css' ], 1 );
            add_filter( 'style_loader_tag', [ $this, 'async_load_css' ], 9999, 4 );
        }
    }

    public function inline_critical_css() {
        if ( ! is_singular() && ! is_home() && ! is_front_page() ) {
            return;
        }

        $options = get_option( 'wp_academic_post_enhanced_page_optimization_options', [] );
        $critical_css = '';

        // 1. Global Critical CSS
        if ( ! empty( $options['global_critical_css'] ) ) {
            $critical_css .= $options['global_critical_css'];
        }

        // 2. Post Type Specific Critical CSS
        if ( is_singular() ) {
            $post_type = get_post_type();
            if ( ! empty( $options['critical_css'][$post_type] ) ) {
                $critical_css .= $options['critical_css'][$post_type];
            }
        }
        
        // 3. Front Page Specific
        if ( is_front_page() && ! empty( $options['front_page_critical_css'] ) ) {
            $critical_css .= $options['front_page_critical_css'];
        }

        if ( ! empty( $critical_css ) ) {
            echo '<style id="wpa-critical-css">' . wp_strip_all_tags( $critical_css ) . '</style>';
        }
    }

    public function async_load_css( $html, $handle, $href, $media ) {
        // Don't async critical css
        if ( strpos( $html, "id='wpa-critical-css'") !== false ) {
            return $html;
        }
        $html = str_replace("rel='stylesheet'", "rel='preload' as='style' onload=\"this.onload=null;this.rel='stylesheet'\"", $html);
        return $html;
    }
}

WP_Academic_Post_Enhanced_Critical_CSS::get_instance();
