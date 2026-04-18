<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class WP_Academic_Post_Enhanced_404_Handler {

    private static $instance;

    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action( 'template_redirect', [ $this, 'handle_404_redirection' ] );
    }

    /**
     * Redirect 404 errors to the parent URL.
     */
    public function handle_404_redirection() {
        if ( ! is_404() ) {
            return;
        }

        $enabled = get_option( 'wp_academic_post_enhanced_404_parent_redirect', false );
        if ( ! $enabled ) {
            return;
        }

        $requested_url = home_url( add_query_arg( [], $GLOBALS['wp']->request ) );
        $path = parse_url( $requested_url, PHP_URL_PATH );
        
        if ( empty( $path ) || $path === '/' ) {
            return;
        }

        // Remove trailing slash for processing
        $clean_path = rtrim( $path, '/' );
        
        // Find the last slash to determine the parent
        $last_slash_pos = strrpos( $clean_path, '/' );
        
        if ( $last_slash_pos !== false ) {
            $parent_path = substr( $clean_path, 0, $last_slash_pos + 1 );
            $parent_url = home_url( $parent_path );

            // Final safety check to avoid infinite loops if home_url is nested
            if ( $requested_url !== $parent_url ) {
                wp_safe_redirect( $parent_url, 301 );
                exit;
            }
        } else {
            // No slash found (unlikely given WP home_url structure), redirect to home
            wp_safe_redirect( home_url( '/' ), 301 );
            exit;
        }
    }
}
