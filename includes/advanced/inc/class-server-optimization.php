<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class WP_Academic_Post_Enhanced_Server_Optimization {

    private static $instance;

    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action( 'admin_init', [ $this, 'update_htaccess_settings' ] );
        
        $server_options = get_option('wp_academic_post_enhanced_server_optimization_options', []);
        if ( !empty($server_options['wp_academic_post_enhanced_http2_push_enabled']) ) {
            add_action( 'wp_head', [ $this, 'http2_push' ] );
        }
    }

    public function update_htaccess_settings() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        if ( isset( $_GET['page'] ) && $_GET['page'] === 'wp-academic-post-enhanced-performance' && isset( $_GET['settings-updated'] ) ) {
            require_once ABSPATH . 'wp-admin/includes/misc.php';

            $server_options = get_option( 'wp_academic_post_enhanced_server_optimization_options', [] );
            $page_options = get_option( 'wp_academic_post_enhanced_page_optimization_options', [] );

            // Legacy/Advanced options might still be in server_options
            $esi_enabled = !empty( $server_options['wp_academic_post_enhanced_esi_enabled'] );
            
            $gzip_compression_enabled = !empty( $server_options['wp_academic_post_enhanced_gzip_compression_enabled'] );
            $http2_push_enabled = !empty( $server_options['wp_academic_post_enhanced_http2_push_enabled'] );
            $keep_alive_enabled = !empty( $server_options['wp_academic_post_enhanced_keep_alive_enabled'] );
            $hotlink_protection_enabled = !empty( $server_options['wp_academic_post_enhanced_hotlink_protection_enabled'] );
            $vary_accept_encoding_enabled = !empty( $server_options['wp_academic_post_enhanced_vary_accept_encoding_enabled'] );

            $css_minify_method = isset( $page_options['wp_academic_post_enhanced_css_minify_method'] ) ? $page_options['wp_academic_post_enhanced_css_minify_method'] : 'none';
            $js_minify_method = isset( $page_options['wp_academic_post_enhanced_js_minify_method'] ) ? $page_options['wp_academic_post_enhanced_js_minify_method'] : 'none';
            $html_minify_method = isset( $page_options['wp_academic_post_enhanced_html_minify_method'] ) ? $page_options['wp_academic_post_enhanced_html_minify_method'] : 'none';

            $css_litespeed = ( 'litespeed' === $css_minify_method );
            $js_litespeed = ( 'litespeed' === $js_minify_method );
            $html_litespeed = ( 'litespeed' === $html_minify_method );

            if ( $esi_enabled || $css_litespeed || $js_litespeed || $html_litespeed || $gzip_compression_enabled || $http2_push_enabled || $keep_alive_enabled || $hotlink_protection_enabled || $vary_accept_encoding_enabled ) {
                $this->update_htaccess_rules( $esi_enabled, $css_litespeed, $js_litespeed, $html_litespeed, $gzip_compression_enabled, $http2_push_enabled, $keep_alive_enabled, $hotlink_protection_enabled, $vary_accept_encoding_enabled );
            } else {
                $this->remove_htaccess_rules();
            }
        }
    }

    public function http2_push() {
        if ( headers_sent() ) return;
        $resources = [];
        $styles = wp_styles();
        $scripts = wp_scripts();

        foreach ( $styles->queue as $handle ) {
            if ( isset($styles->registered[$handle]) && $styles->registered[$handle]->src ) {
                $resources[] = '<' . $styles->registered[$handle]->src . '>; rel=preload; as=style';
            }
        }

        foreach ( $scripts->queue as $handle ) {
             if ( isset($scripts->registered[$handle]) && $scripts->registered[$handle]->src ) {
                $resources[] = '<' . $scripts->registered[$handle]->src . '>; rel=preload; as=script';
            }
        }

        if ( ! empty( $resources ) ) {
            header( 'Link: ' . implode( ',', $resources ), false );
        }
    }

    public function update_htaccess_rules( $esi_enabled, $css_minify_enabled, $js_minify_enabled, $html_minify_enabled, $gzip_compression_enabled, $http2_push_enabled, $keep_alive_enabled, $hotlink_protection_enabled, $vary_accept_encoding_enabled ) {
        $htaccess_file = get_home_path() . ".htaccess";

        if ( is_writable( $htaccess_file ) ) {
            $rules = [];

            if ( $esi_enabled ) {
                $rules[] = '<IfModule Litespeed>';
                $rules[] = '    CacheEngine esi on';
                $rules[] = '</IfModule>';
            }

            if ( $css_minify_enabled ) {
                $rules[] = '<IfModule Litespeed>';
                $rules[] = '    CacheEngine on';
                $rules[] = '    AddOutputFilterByType DEFLATE text/css';
                $rules[] = '</IfModule>';
            }

            if ( $js_minify_enabled ) {
                $rules[] = '<IfModule Litespeed>';
                $rules[] = '    CacheEngine on';
                $rules[] = '    AddOutputFilterByType DEFLATE application/javascript';
                $rules[] = '</IfModule>';
            }

            if ( $html_minify_enabled ) {
                $rules[] = '<IfModule Litespeed>';
                $rules[] = '    CacheEngine on';
                $rules[] = '    AddOutputFilterByType DEFLATE text/html';
                $rules[] = '</IfModule>';
            }

            if ( $gzip_compression_enabled ) {
                $rules[] = '<IfModule mod_deflate.c>';
                $rules[] = '    AddOutputFilterByType DEFLATE text/plain';
                $rules[] = '    AddOutputFilterByType DEFLATE text/html';
                $rules[] = '    AddOutputFilterByType DEFLATE text/xml';
                $rules[] = '    AddOutputFilterByType DEFLATE text/css';
                $rules[] = '    AddOutputFilterByType DEFLATE application/xml';
                $rules[] = '    AddOutputFilterByType DEFLATE application/xhtml+xml';
                $rules[] = '    AddOutputFilterByType DEFLATE application/rss+xml';
                $rules[] = '    AddOutputFilterByType DEFLATE application/javascript';
                $rules[] = '    AddOutputFilterByType DEFLATE application/x-javascript';
                $rules[] = '    AddOutputFilterByType DEFLATE image/svg+xml';
                $rules[] = '</IfModule>';
            }

            if ( $keep_alive_enabled ) {
                $rules[] = '<ifModule mod_headers.c>';
                $rules[] = '    Header set Connection keep-alive';
                $rules[] = '</ifModule>';
            }

            if ( $hotlink_protection_enabled ) {
                $rules[] = '<IfModule mod_rewrite.c>';
                $rules[] = '    RewriteEngine on';
                $rules[] = '    RewriteCond %{HTTP_REFERER} !^$';
                $rules[] = '    RewriteCond %{HTTP_REFERER} !^http(s)?://(www\.)?' . str_replace( 'www.', '', $_SERVER['HTTP_HOST'] ) . ' [NC]';
                $rules[] = '    RewriteRule \.(jpg|jpeg|png|gif)$ - [NC,F,L]';
                $rules[] = '</IfModule>';
            }

            if ( $vary_accept_encoding_enabled ) {
                $rules[] = '<IfModule mod_headers.c>';
                $rules[] = '    Header append Vary Accept-Encoding';
                $rules[] = '</IfModule>';
            }

            // Security Headers (Always recommended if we are touching htaccess)
            $rules[] = '<IfModule mod_headers.c>';
            $rules[] = '    Header set X-Content-Type-Options nosniff';
            $rules[] = '    Header set X-Frame-Options SAMEORIGIN';
            $rules[] = '    Header set X-XSS-Protection "1; mode=block"';
            $rules[] = '</IfModule>';

            insert_with_markers( $htaccess_file, 'WP Academic Post Enhanced Server Optimization', $rules );
        }
    }

    public function remove_htaccess_rules() {
        $htaccess_file = get_home_path() . '.htaccess';
        if ( is_writable( $htaccess_file ) ) {
            insert_with_markers( $htaccess_file, 'WP Academic Post Enhanced Server Optimization', [] );
        }
    }
}