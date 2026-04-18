<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Performance Tweaks Module
 * 
 * Implements a wide range of WordPress core optimizations and bloat removal.
 */
class WP_Academic_Post_Enhanced_Performance_Tweaks {

    private static $instance;

    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        // 1. Heartbeat Control
        $heartbeat_setting = get_option( 'wp_academic_post_enhanced_heartbeat', 'default' );
        if ( 'disable' === $heartbeat_setting ) {
            add_action( 'init', [ $this, 'disable_heartbeat' ], 1 );
        } elseif ( '60' === $heartbeat_setting ) {
            add_filter( 'heartbeat_settings', [ $this, 'set_heartbeat_interval' ] );
        }

        // 2. Post Revisions
        $revisions_setting = get_option( 'wp_academic_post_enhanced_post_revisions', 'default' );
        if ( 'disable' === $revisions_setting ) {
            if ( ! defined( 'WP_POST_REVISIONS' ) ) define( 'WP_POST_REVISIONS', false );
        } elseif ( is_numeric( $revisions_setting ) ) {
            if ( ! defined( 'WP_POST_REVISIONS' ) ) define( 'WP_POST_REVISIONS', (int) $revisions_setting );
        }

        // 3. Emojis Removal
        if ( get_option( 'wp_academic_post_enhanced_disable_emoji' ) ) {
            add_action( 'init', [ $this, 'disable_emojis' ] );
        }

        // 4. Embeds Removal
        if ( get_option( 'wp_academic_post_enhanced_disable_embeds' ) ) {
            add_action( 'init', [ $this, 'disable_embeds' ], 9999 );
        }

        // 5. jQuery Migrate Removal
        if ( get_option( 'wp_academic_post_enhanced_disable_jquery_migrate' ) ) {
            add_action( 'wp_default_scripts', [ $this, 'remove_jquery_migrate' ] );
        }

        // 6. XML-RPC Disable
        if ( get_option( 'wp_academic_post_enhanced_disable_xmlrpc' ) ) {
            add_filter( 'xmlrpc_enabled', '__return_false' );
            add_filter( 'wp_headers', [ $this, 'remove_x_pingback' ] );
        }

        // 7. REST API for Guests
        if ( get_option( 'wp_academic_post_enhanced_disable_rest_api_guests' ) ) {
            add_filter( 'rest_authentication_errors', [ $this, 'disable_rest_api_for_guests' ] );
        }

        // 8. RSS Feeds Disable
        if ( get_option( 'wp_academic_post_enhanced_disable_rss_feeds' ) ) {
            add_action( 'do_feed', [ $this, 'disable_feeds' ], 1 );
            add_action( 'do_feed_rdf', [ $this, 'disable_feeds' ], 1 );
            add_action( 'do_feed_rss', [ $this, 'disable_feeds' ], 1 );
            add_action( 'do_feed_rss2', [ $this, 'disable_feeds' ], 1 );
            add_action( 'do_feed_atom', [ $this, 'disable_feeds' ], 1 );
            add_action( 'do_feed_rss2_comments', [ $this, 'disable_feeds' ], 1 );
            add_action( 'do_feed_atom_comments', [ $this, 'disable_feeds' ], 1 );
            remove_action( 'wp_head', 'feed_links', 2 );
            remove_action( 'wp_head', 'feed_links_extra', 3 );
        }

        // 9. Global Styles & SVG Filters (Block Editor Bloat)
        if ( get_option( 'wp_academic_post_enhanced_disable_global_styles' ) ) {
            add_action( 'init', [ $this, 'disable_global_styles' ] );
        }

        // 10. Login Language Switcher
        if ( get_option( 'wp_academic_post_enhanced_disable_login_language_switcher' ) ) {
            add_filter( 'login_display_language_dropdown', '__return_false' );
        }

        // 11. Header Cleanup (RSD, WLW, Shortlinks)
        if ( get_option( 'wp_academic_post_enhanced_remove_rsd_wlw_shortlinks' ) ) {
            remove_action( 'wp_head', 'rsd_link' );
            remove_action( 'wp_head', 'wlwmanifest_link' );
            remove_action( 'wp_head', 'wp_shortlink_wp_head' );
        }

        // 12. Autosave Interval
        $autosave = get_option( 'wp_academic_post_enhanced_autosave_interval' );
        if ( ! empty( $autosave ) && is_numeric( $autosave ) ) {
            add_filter( 'block_editor_settings_all', function( $settings ) use ( $autosave ) {
                $settings['autosaveInterval'] = intval( $autosave );
                return $settings;
            } );
        }

        // --- Existing Tweaks ---
        if ( get_option( 'wp_academic_post_enhanced_disable_self_pings' ) ) {
            add_action( 'pre_ping', [ $this, 'disable_self_pings' ] );
        }

        if ( get_option( 'wp_academic_post_enhanced_remove_query_strings' ) ) {
            add_filter( 'script_loader_src', [ $this, 'remove_query_strings' ], 15, 1 );
            add_filter( 'style_loader_src', [ $this, 'remove_query_strings' ], 15, 1 );
        }

        if ( get_option( 'wp_academic_post_enhanced_disable_gravatars' ) ) {
            add_filter( 'get_avatar', [ $this, 'disable_gravatars' ], 1, 5 );
        }

        if ( get_option( 'wp_academic_post_enhanced_disable_google_fonts' ) ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'disable_google_fonts' ], 100 );
        }

        if ( get_option( 'wp_academic_post_enhanced_disable_font_awesome' ) ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'disable_font_awesome' ], 100 );
        }

        if ( get_option( 'wp_academic_post_enhanced_preload_requests' ) ) {
            add_action( 'wp_head', [ $this, 'preload_critical_requests' ], 1 );
        }

        if ( get_option( 'wp_academic_post_enhanced_dns_prefetch' ) ) {
            add_action( 'wp_head', [ $this, 'dns_prefetch' ], 0 );
        }

        if ( get_option( 'wp_academic_post_enhanced_disable_google_maps' ) ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'disable_google_maps' ], 100 );
        }

        if ( get_option( 'wp_academic_post_enhanced_disable_wc_cart_fragmentation' ) ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'disable_wc_cart_fragmentation' ], 99 );
        }

        if ( get_option( 'wp_academic_post_enhanced_scripts_to_footer' ) ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'move_scripts_to_footer' ], 9999 );
        }

        if ( get_option( 'wp_academic_post_enhanced_defer_scripts' ) ) {
            add_filter( 'script_loader_tag', [ $this, 'add_defer_attribute' ], 10, 2 );
        }

        if ( get_option( 'wp_academic_post_enhanced_jquery_to_footer' ) ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'move_jquery_to_footer' ], 9999 );
        }
    }

    // --- Callback Methods ---

    public function disable_heartbeat() { wp_deregister_script('heartbeat'); }
    public function set_heartbeat_interval( $settings ) { $settings['interval'] = 60; return $settings; }

    public function disable_emojis() {
        remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
        remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
        remove_action( 'wp_print_styles', 'print_emoji_styles' );
        remove_action( 'admin_print_styles', 'print_emoji_styles' );
        remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
        remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
        remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
        add_filter( 'tiny_mce_plugins', function($plugins) { return is_array($plugins) ? array_diff($plugins, ['wpemoji']) : []; } );
        add_filter( 'wp_resource_hints', function($urls, $relation_type) {
            if ( 'dns-prefetch' === $relation_type ) {
                $emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/14.0.0/svg/' );
                $urls = array_diff( $urls, [ $emoji_svg_url ] );
            }
            return $urls;
        }, 10, 2 );
    }

    public function disable_embeds() {
        remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
        remove_action( 'wp_head', 'wp_oembed_add_host_js' );
        add_filter( 'embed_oembed_discover', '__return_false' );
        remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );
        remove_action( 'wp_head', 'wp_oembed_add_discovery_links', 10 );
        remove_action( 'wp_head', 'wp_oembed_add_host_js', 10 );
    }

    public function remove_jquery_migrate( $scripts ) {
        if ( ! is_admin() && isset( $scripts->registered['jquery'] ) ) {
            $script = $scripts->registered['jquery'];
            if ( $script->deps ) {
                $script->deps = array_diff( $script->deps, [ 'jquery-migrate' ] );
            }
        }
    }

    public function disable_rest_api_for_guests( $errors ) {
        if ( ! is_user_logged_in() ) {
            return new WP_Error( 'rest_forbidden', __( 'REST API forbidden for guests.', 'wp-academic-post-enhanced' ), [ 'status' => rest_authorization_required_code() ] );
        }
        return $errors;
    }

    public function disable_feeds() {
        wp_die( __( 'No feed available, please visit our <a href="'. esc_url(home_url('/')) .'">homepage</a>!', 'wp-academic-post-enhanced' ) );
    }

    public function disable_global_styles() {
        remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
        remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );
        remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );
    }

    public function remove_query_strings( $src ) {
        return ( strpos( $src, '?ver=' ) ) ? remove_query_arg( 'ver', $src ) : $src;
    }

    public function remove_x_pingback( $headers ) {
        unset( $headers['X-Pingback'] );
        return $headers;
    }

    public function disable_google_fonts() {
        global $wp_styles;
        foreach ( $wp_styles->registered as $handle => $style ) {
            if ( strpos( $style->src, 'fonts.googleapis.com' ) !== false ) wp_dequeue_style( $handle );
        }
    }

    public function disable_font_awesome() {
        global $wp_styles;
        foreach ( $wp_styles->registered as $handle => $style ) {
            if ( strpos( $style->src, 'fontawesome.com' ) !== false || strpos( $style->src, 'font-awesome' ) !== false ) wp_dequeue_style( $handle );
        }
    }

    public function move_scripts_to_footer() {
        if ( is_admin() ) return;
        remove_action( 'wp_head', 'wp_print_scripts' );
        remove_action( 'wp_head', 'wp_print_head_scripts', 9 );
        remove_action( 'wp_head', 'wp_enqueue_scripts', 1 );
    }

    public function move_jquery_to_footer() {
        if ( is_admin() ) return;
        wp_deregister_script( 'jquery' );
        wp_register_script( 'jquery', includes_url( '/js/jquery/jquery.js' ), false, null, true );
        wp_enqueue_script( 'jquery' );
    }

    public function add_defer_attribute( $tag, $handle ) {
        if ( is_admin() || strpos( $tag, 'defer' ) !== false || $handle === 'jquery' ) return $tag;
        return str_replace( ' src', ' defer src', $tag );
    }

    public function disable_wc_cart_fragmentation() {
        if ( class_exists( 'WooCommerce' ) && ! is_cart() && ! is_checkout() && ! is_account_page() ) {
            wp_dequeue_script( 'wc-cart-fragments' );
            wp_dequeue_script( 'woocommerce' );
            wp_dequeue_script( 'wc-add-to-cart' );
        }
    }

    public function disable_self_pings( &$links ) {
        $home = get_option( 'home' );
        foreach ( $links as $l => $link ) {
            if ( 0 === strpos( $link, $home ) ) unset($links[$l]);
        }
    }

    public function disable_gravatars( $avatar, $id_or_email, $size, $default, $alt ) {
        return '<img alt="' . esc_attr( $alt ) . '" src="' . esc_url( get_admin_url() . 'images/mystery-man.jpg' ) . '" class="avatar avatar-' . $size . ' photo" height="' . $size . '" width="' . $size . '" />';
    }

    public function preload_critical_requests() {
        $urls = get_option( 'wp_academic_post_enhanced_preload_requests', '' );
        if ( empty( $urls ) ) return;
        foreach ( explode( "\n", $urls ) as $url ) {
            $url = trim( $url );
            if ( ! empty( $url ) ) {
                $as = 'style';
                if ( preg_match( '/\.(woff2|woff|ttf|otf)$/', $url ) ) $as = 'font';
                elseif ( preg_match( '/\.js$/', $url ) ) $as = 'script';
                elseif ( preg_match( '/\.(jpg|jpeg|png|gif|svg|webp)$/', $url ) ) $as = 'image';
                echo '<link rel="preload" href="' . esc_url( $url ) . '" as="' . esc_attr( $as ) . '"' . ( 'font' === $as ? ' crossorigin' : '' ) . '>' . "\n";
            }
        }
    }

    public function dns_prefetch() {
        $urls = get_option( 'wp_academic_post_enhanced_dns_prefetch', '' );
        if ( empty( $urls ) ) return;
        foreach ( explode( "\n", $urls ) as $url ) {
            $url = trim( $url );
            if ( ! empty( $url ) ) echo '<link rel="dns-prefetch" href="//' . esc_attr( $url ) . '">' . "\n";
        }
    }

    public function disable_google_maps() {
        global $wp_scripts;
        foreach ( $wp_scripts->registered as $handle => $script ) {
            if ( strpos( $script->src, 'maps.googleapis.com' ) !== false || strpos( $script->src, 'maps.google.com' ) !== false ) wp_dequeue_script( $handle );
        }
    }
}

WP_Academic_Post_Enhanced_Performance_Tweaks::get_instance();