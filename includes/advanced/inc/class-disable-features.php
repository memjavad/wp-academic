<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class WP_Academic_Post_Enhanced_Disable_Features {

    private static $instance;

    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        if ( get_option('wp_academic_post_enhanced_disable_comments') ) {
            add_action('admin_init', [ $this, 'disable_comments_post_types_support' ]);
            add_filter('comments_open', '__return_false', 20, 2);
            add_filter('pings_open', '__return_false', 20, 2);
            add_filter('comments_array', '__return_empty_array', 10, 2);
            add_action('admin_menu', [ $this, 'disable_comments_menu' ]);
            add_action('init', [ $this, 'disable_comments_admin_bar' ]);
        }

        if ( get_option('wp_academic_post_enhanced_disable_pages') ) {
            add_action('admin_menu', [ $this, 'disable_pages_menu' ]);
            add_action('wp_before_admin_bar_render', [ $this, 'disable_pages_admin_bar' ]);
            add_action('init', [ $this, 'disable_pages_redirect' ]);
        }

        if ( get_option('wp_academic_post_enhanced_disable_xmlrpc') ) {
            add_filter( 'xmlrpc_enabled', '__return_false' );
        }

        if ( get_option('wp_academic_post_enhanced_disable_rest_api') ) {
            add_filter( 'rest_authentication_errors', function( $result ) {
                if ( ! empty( $result ) ) {
                    return $result;
                }
                if ( ! is_user_logged_in() ) {
                    return new WP_Error( 'rest_not_logged_in', 'You are not currently logged in.', [ 'status' => 401 ] );
                }
                return $result;
            });
        }

        if ( get_option('wp_academic_post_enhanced_disable_feeds') ) {
            add_action( 'template_redirect', [ $this, 'disable_feeds_redirect' ], 1 );
            remove_action( 'wp_head', 'feed_links', 2 );
            remove_action( 'wp_head', 'feed_links_extra', 3 );
        }

        if ( get_option('wp_academic_post_enhanced_disable_emoji') ) {
            add_action( 'init', [ $this, 'disable_emojis' ] );
        }

        if ( get_option('wp_academic_post_enhanced_disable_embeds') ) {
            add_action( 'init', [ $this, 'disable_embeds_init' ], 9999 );
        }

        if ( get_option('wp_academic_post_enhanced_disable_jquery_migrate') ) {
            add_action( 'wp_default_scripts', [ $this, 'disable_jquery_migrate_script' ] );
        }

        if ( get_option('wp_academic_post_enhanced_disable_gutenberg_css_frontend') ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'disable_gutenberg_css_frontend_styles' ], 100 );
        }

        if ( get_option('wp_academic_post_enhanced_disable_global_styles') ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'remove_global_styles' ], 100 );
            add_action( 'wp_footer', [ $this, 'remove_svg_filters' ] );
        }

        if ( get_option('wp_academic_post_enhanced_disable_classic_theme_styles') ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'remove_classic_theme_styles' ], 100 );
        }

        if ( get_option('wp_academic_post_enhanced_disable_search') ) {
            add_action( 'template_redirect', [ $this, 'disable_search_redirect' ] );
            add_filter( 'get_search_form', '__return_empty_string' );
        }
    }

    public function disable_search_redirect() {
        if ( is_search() ) {
            wp_redirect( home_url(), 301 );
            exit;
        }
    }

    public function disable_comments_post_types_support() {
        $post_types = get_post_types();
        foreach ($post_types as $post_type) {
            if(post_type_supports($post_type, 'comments')) {
                remove_post_type_support($post_type, 'comments');
                remove_post_type_support($post_type, 'trackbacks');
            }
        }
    }

    public function disable_comments_menu() {
        remove_menu_page('edit-comments.php');
    }

    public function disable_comments_admin_bar() {
        if (is_admin_bar_showing()) {
            remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
        }
    }

    public function disable_pages_menu() {
        remove_menu_page('edit.php?post_type=page');
    }

    public function disable_pages_admin_bar() {
        global $wp_admin_bar;
        $wp_admin_bar->remove_node('new-page');
    }

    public function disable_pages_redirect() {
        if ( is_page() ) {
            wp_redirect( home_url(), 301 );
            exit;
        }
    }

    public function disable_feeds_redirect() {
        if ( is_feed() ) {
            wp_redirect( home_url(), 301 );
            exit;
        }
    }

    public function disable_emojis() {
        remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
        remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
        remove_action( 'wp_print_styles', 'print_emoji_styles' );
        remove_action( 'admin_print_styles', 'print_emoji_styles' );
        remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
        remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
        remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
        add_filter( 'tiny_mce_plugins', [ $this, 'disable_emojis_tinymce' ] );
        add_filter( 'wp_resource_hints', [ $this, 'disable_emojis_remove_dns_prefetch' ], 10, 2 );
    }

    public function disable_emojis_tinymce( $plugins ) {
        if ( is_array( $plugins ) ) {
            return array_diff( $plugins, [ 'wpemoji' ] );
        }
        return $plugins;
    }

    public function disable_emojis_remove_dns_prefetch( $urls, $relation_type ) {
        if ( 'dns-prefetch' == $relation_type ) {
            $emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/13.1.0/svg/' );
            $urls = array_diff( $urls, [ $emoji_svg_url ] );
        }
        return $urls;
    }

    public function disable_embeds_init() {
        remove_action( 'rest_api_init', 'wp_oembed_register_route' );
        remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
        remove_action( 'wp_head', 'wp_oembed_add_host_js' );
        add_filter( 'embed_oembed_discover', '__return_false' );
        remove_filter( 'render_block', 'wp_render_embed_block' );
        remove_filter( 'widget_text_parse_content', 'wp_staticize_emoji' );
        remove_filter( 'widget_text_parse_content', 'wp_embed_handler_html' );
        remove_action( 'embed_content_handler', 'wp_embed_handler_html' );
        remove_filter( 'embed_oembed_result', 'wp_filter_oembed_result', 10 );
        remove_action( 'init', 'wp_embed_register_settings' );
        remove_filter( 'pre_oembed_result', 'wp_filter_pre_oembed_result', 10 );
    }

    public function disable_jquery_migrate_script( $scripts ) {
        if ( ! is_admin() && isset( $scripts->registered['jquery'] ) ) {
            $script = $scripts->registered['jquery'];
            if ( $script->deps ) {
                $script->deps = array_diff( $script->deps, [ 'jquery-migrate' ] );
            }
        }
    }

    public function disable_gutenberg_css_frontend_styles() {
        if ( ! is_admin() ) {
            wp_dequeue_style( 'wp-block-library' );
            wp_dequeue_style( 'wp-block-library-theme' );
            wp_dequeue_style( 'wc-block-style' );
        }
    }

    public function remove_global_styles() {
        wp_dequeue_style( 'global-styles' );
    }

    public function remove_classic_theme_styles() {
        wp_dequeue_style( 'classic-theme-styles' );
    }

    public function remove_svg_filters() {
        remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );
        remove_action( 'wp_footer', 'wp_enqueue_global_styles' );
        remove_action( 'wp_footer', 'wp_render_duotone_filter_svg' );
    }
}
