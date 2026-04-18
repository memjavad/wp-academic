<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class WP_Academic_Post_Enhanced_CDN_Rewrite {

    private static $instance;
    private $cdn_url;
    private $site_url;
    private $included_extensions = [ 'png', 'jpg', 'jpeg', 'gif', 'svg', 'webp', 'css', 'js', 'pdf', 'mp3', 'mp4' ];
    private $excluded_strings = [];

    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        $options = get_option( 'wp_academic_post_enhanced_cdn_options', [] );
        $this->cdn_url = isset( $options['cdn_url'] ) ? untrailingslashit( $options['cdn_url'] ) : '';
        $this->site_url = untrailingslashit( home_url() ); // Use home_url() for frontend links

        if ( ! empty( $this->cdn_url ) && ! is_admin() ) {
            add_action( 'template_redirect', [ $this, 'start_buffering' ], 1 );
        }
    }

    public function start_buffering() {
        ob_start( [ $this, 'rewrite_urls' ] );
    }

    public function rewrite_urls( $content ) {
        if ( empty( $this->cdn_url ) || $this->cdn_url === $this->site_url ) {
            return $content;
        }

        // Escape site URL for regex
        $escaped_site_url = preg_quote( $this->site_url, '/' );
        $extensions = implode( '|', $this->included_extensions );

        // Regex to match local URLs ending with allowed extensions
        // Use [\'\\] to match either single or double quotes and escape them for PHP
        $pattern = '/\b(?:src|href|data-src|srcset)=[\'\](' . $escaped_site_url . '\/[^\'\\]+\.(' . $extensions . ')(?:[\?][^\'\\]*)?)[\'\\]/i';

        return preg_replace_callback( $pattern, [ $this, 'replace_callback' ], $content );
    }

    private function replace_callback( $matches ) {
        $original_url = $matches[1];

        // Check exclusions
        foreach ( $this->excluded_strings as $exclusion ) {
            if ( strpos( $original_url, $exclusion ) !== false ) {
                return $matches[0]; // Return full match unchanged
            }
        }

        $new_url = str_replace( $this->site_url, $this->cdn_url, $original_url );
        return str_replace( $original_url, $new_url, $matches[0] );
    }
}

WP_Academic_Post_Enhanced_CDN_Rewrite::get_instance();
