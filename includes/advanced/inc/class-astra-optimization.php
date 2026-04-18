<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Astra Theme Optimization Engine
 * 
 * Specifically targets Astra theme bloat and optimizes its asset delivery.
 */
class WP_Academic_Post_Enhanced_Astra_Optimization {
    private static $instance;
    private $options;

    public static function get_instance() {
        if ( ! isset( self::$instance ) ) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        $this->options = get_option( 'wp_academic_post_enhanced_astra_settings', [] );
        if ( ! $this->is_astra_active() ) return;

        // 1. Asset Optimization Hooks
        add_action( 'wp_enqueue_scripts', [ $this, 'optimize_astra_assets' ], 999 );
        
        // 2. Dynamic CSS Minification
        add_filter( 'astra_dynamic_theme_css', [ $this, 'minify_astra_dynamic_css' ], 999 );

        // 3. Script Loader Filters (Force Removal)
        add_filter( 'script_loader_tag', [ $this, 'force_remove_scripts' ], 999, 2 );

        // 4. Font Optimization
        if ( ! empty( $this->options['disable_astra_fonts'] ) ) {
            add_filter( 'astra_google_fonts_selected', '__return_empty_array' );
        }
    }

    private function is_astra_active() {
        $theme = wp_get_theme();
        return ( $theme->get('Name') === 'Astra' || $theme->get('Template') === 'astra' );
    }

    /**
     * Dequeue and Deregister specific Astra assets.
     */
    public function optimize_astra_assets() {
        // Disable Astra Block Editor CSS (Heavy)
        if ( ! empty( $this->options['disable_block_editor_css'] ) ) {
            wp_dequeue_style( 'astra-block-editor-styles' );
        }

        // Disable Astra Legacy FontAwesome
        if ( ! empty( $this->options['disable_fontawesome'] ) ) {
            wp_dequeue_style( 'astra-font-awesome' );
        }

        // Disable Add to Cart Quantity JS
        if ( ! empty( $this->options['disable_cart_js'] ) ) {
            wp_dequeue_script( 'astra-add-to-cart-quantity-js' );
        }

        // Disable Breadcrumb CSS
        if ( ! empty( $this->options['disable_breadcrumb_css'] ) ) {
            wp_dequeue_style( 'astra-breadcrumb-css' );
        }

        // Disable Sidebar CSS
        if ( ! empty( $this->options['disable_sidebar_css'] ) ) {
            wp_dequeue_style( 'astra-sidebar-css' );
        }

        // Disable Flexibility JS (Unconditionally disable to prevent Googlebot from parsing JS strings as relative URLs)
        wp_dequeue_script( 'astra-flexibility' );

        // Disable Astra Flexbox Compatibility (Modern browser focus)
        if ( ! empty( $this->options['disable_astra_flexbox'] ) ) {
            wp_dequeue_style( 'astra-flexbox-grid' );
        }
    }

    /**
     * Minify Astra's massive dynamic inline CSS.
     */
    public function minify_astra_dynamic_css( $css ) {
        if ( empty( $this->options['minify_dynamic_css'] ) ) return $css;
        return WP_Academic_Post_Enhanced_Advanced_Minifier::get_instance()->minify_css( $css );
    }

    /**
     * Forcefully block script tags from outputting if they match specific IDs.
     */
    public function force_remove_scripts( $tag, $handle ) {
        if ( $handle === 'astra-flexibility' ) {
            return '';
        }
        return $tag;
    }
}

WP_Academic_Post_Enhanced_Astra_Optimization::get_instance();
