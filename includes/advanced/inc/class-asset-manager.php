<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Asset Manager - Pro Version
 * 
 * Advanced conditional loading/unloading for CSS and JS.
 */
class WP_Academic_Post_Enhanced_Asset_Manager {

    private static $instance;
    private $options;

    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        $this->options = get_option( 'wp_academic_post_enhanced_asset_manager_options', [] );

        if ( is_admin() ) {
            // Admin logic
        } else {
            add_action( 'wp_enqueue_scripts', [ $this, 'process_assets' ], 9999 );
            
            if ( current_user_can( 'manage_options' ) ) {
                add_action( 'wp_footer', [ $this, 'track_assets' ], 9999 );
            }
        }
    }

    public function process_assets() {
        if ( empty( $this->options['unloads'] ) ) return;

        foreach ( $this->options['unloads'] as $handle => $rule ) {
            $should_unload = $this->evaluate_rule( $rule );

            if ( $should_unload ) {
                if ( $rule['type'] === 'script' ) {
                    wp_dequeue_script( $handle );
                    wp_deregister_script( $handle );
                } else {
                    wp_dequeue_style( $handle );
                    wp_deregister_style( $handle );
                }
            }
        }
    }

    private function evaluate_rule( $rule ) {
        $mode = $rule['mode'] ?? 'exclude'; // 'exclude' (Unload On) or 'include' (Load Only On)
        $locations = $rule['locations'] ?? [];
        
        if ( empty($locations) ) return false;

        $is_match = false;

        foreach ( $locations as $loc ) {
            // Devices
            if ( $loc === 'mobile' && wp_is_mobile() ) $is_match = true;
            if ( $loc === 'desktop' && ! wp_is_mobile() ) $is_match = true;

            // Special Pages
            if ( $loc === 'homepage' && is_front_page() ) $is_match = true;
            if ( $loc === 'archive' && (is_archive() || is_home()) ) $is_match = true;

            // Post Types
            if ( strpos($loc, 'pt:') === 0 ) {
                $pt = str_replace('pt:', '', $loc);
                if ( is_singular($pt) ) $is_match = true;
            }

            if ($is_match) break;
        }

        // Logic Flip:
        // Exclude Mode: If matched location, then unload (true).
        // Include Mode: If NOT matched location, then unload (true).
        return ( $mode === 'exclude' ) ? $is_match : ! $is_match;
    }

    public function track_assets() {
        global $wp_scripts, $wp_styles;
        $tracked = get_option( 'wpa_tracked_assets', [ 'scripts' => [], 'styles' => [] ] );
        $changed = false;

        foreach ( $wp_scripts->done as $handle ) {
            if ( ! isset( $tracked['scripts'][$handle] ) ) {
                $tracked['scripts'][$handle] = $wp_scripts->registered[$handle]->src ?? 'internal';
                $changed = true;
            }
        }
        foreach ( $wp_styles->done as $handle ) {
            if ( ! isset( $tracked['styles'][$handle] ) ) {
                $tracked['styles'][$handle] = $wp_styles->registered[$handle]->src ?? 'internal';
                $changed = true;
            }
        }
        if ( $changed ) update_option( 'wpa_tracked_assets', $tracked );
    }
}

WP_Academic_Post_Enhanced_Asset_Manager::get_instance();