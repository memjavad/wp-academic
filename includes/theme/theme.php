<?php
/**
 * Custom Theme Module Core Logic
 * Handles Template Loading and Global Assets.
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once plugin_dir_path( __FILE__ ) . 'class-theme-builder.php';
require_once plugin_dir_path( __FILE__ ) . 'template-functions.php';
require_once plugin_dir_path( __FILE__ ) . 'styling-frontend.php';

class WPA_Theme {

    public function __construct() {
        WPA_Theme_Builder::get_instance();
        add_filter( 'template_include', [ $this, 'load_template' ], 99 ); // High priority
        add_action( 'wp_enqueue_scripts', [ $this, 'disable_theme_assets' ], 9999 ); // Disable Theme Assets
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'admin_assets' ] );
        add_action( 'after_setup_theme', [ $this, 'register_menus' ] );
        add_action( 'widgets_init', [ $this, 'register_sidebars' ] );
        add_filter( 'body_class', [ $this, 'add_glassmorphism_class' ] );
        add_filter( 'body_class', [ $this, 'add_bg_pattern_class' ] );
        add_filter( 'body_class', [ $this, 'add_core_body_class' ] );
        add_action( 'pre_get_posts', [ $this, 'fix_homepage_pagination' ] );
        add_filter( 'document_title_parts', [ $this, 'clean_title_parts' ] );
    }

    /**
     * Remove trailing separators if tagline is empty.
     */
    public function clean_title_parts( $title ) {
        if ( is_front_page() && empty( $title['tagline'] ) ) {
            unset( $title['tagline'] );
        }
        return $title;
    }

    /**
     * Automatically add the core template class to the body.
     */
    public function add_core_body_class( $classes ) {
        if ( ! get_option( 'wpa_homepage_enabled' ) ) {
            return $classes;
        }
        $options = get_option( 'wpa_homepage_settings' );
        if ( empty( $options['enable_homepage'] ) ) {
            return $classes;
        }

        $load_global = ! empty( $options['enable_global_header_footer'] );
        $is_wpa_page = is_front_page() || is_singular( ['wpa_course', 'wpa_lesson', 'wpa_news', 'wpa_glossary'] ) || is_post_type_archive( ['wpa_course', 'wpa_news', 'wpa_glossary'] ) || is_404() || $this->should_apply_global_layout();

        if ( $load_global || $is_wpa_page ) {
            $classes[] = 'wpa-custom-template';
        }
        return $classes;
    }

    /**
     * Fix for 404 errors on paginated homepage when using the builder.
     */
    public function fix_homepage_pagination( $query ) {
        if ( is_admin() || ! $query->is_main_query() || ! get_option( 'wpa_homepage_enabled' ) ) {
            return;
        }

        $options = get_option( 'wpa_homepage_settings' );
        if ( empty( $options['enable_homepage'] ) ) {
            return;
        }

        if ( $query->is_home() || $query->is_front_page() ) {
            // Prevent 404 by ensuring the main query doesn't fail on paged requests
            $query->set( 'posts_per_page', 1 );
            $query->set( 'nopaging', false );
        }
    }

    public function add_glassmorphism_class( $classes ) {
        if ( ! get_option( 'wpa_homepage_enabled' ) ) {
            return $classes;
        }
        $options = get_option( 'wpa_homepage_settings' );
        if ( empty( $options['enable_homepage'] ) ) {
            return $classes;
        }
        if ( ! empty( $options['enable_glassmorphism'] ) ) {
            $classes[] = 'wpa-glass-enabled';
        }
        return $classes;
    }

    public function add_bg_pattern_class( $classes ) {
        if ( ! get_option( 'wpa_homepage_enabled' ) ) {
            return $classes;
        }
        $options = get_option( 'wpa_homepage_settings' );
        if ( empty( $options['enable_homepage'] ) ) {
            return $classes;
        }
        
        // 1. Add Pattern Class
        $style = ! empty( $options['bg_pattern_style'] ) ? $options['bg_pattern_style'] : 'none';
        if ( $style !== 'none' ) {
            $classes[] = 'wpa-bg-pattern-' . $style;
            $intensity = ! empty( $options['bg_pattern_intensity'] ) ? $options['bg_pattern_intensity'] : 'subtle';
            $classes[] = 'wpa-bg-intensity-' . $intensity;
        }

        // 2. Add Dark Mode Theme Class
        $dark_theme = ! empty( $options['dark_mode_theme'] ) ? $options['dark_mode_theme'] : 'default';
        if ( $dark_theme !== 'default' ) {
            $classes[] = 'wpa-dark-theme-' . $dark_theme;
        }

        return $classes;
    }

    public function register_menus() {
        $options = get_option( 'wpa_homepage_settings' );
        if ( empty( $options['enable_homepage'] ) ) {
            return;
        }

        add_theme_support( 'title-tag' );
        register_nav_menus( [
            'wpa-main-menu' => __( 'Academic Header Menu', 'wp-academic-post-enhanced' ),
        ] );
    }

    public function register_sidebars() {
        $options = get_option( 'wpa_homepage_settings' );
        if ( empty( $options['enable_homepage'] ) ) {
            return;
        }

        register_sidebar( [
            'name'          => __( 'Academic Main Sidebar', 'wp-academic-post-enhanced' ),
            'id'            => 'wpa-main-sidebar',
            'description'   => __( 'Main sidebar for standard pages and posts.', 'wp-academic-post-enhanced' ),
            'before_widget' => '<div id="%1$s" class="wpa-card wpa-widget %2$s" style="padding: 20px; margin-bottom: 20px;">',
            'after_widget'  => '</div>',
            'before_title'  => '<h4 class="wpa-widget-title" style="margin-bottom: 15px; border-bottom: 2px solid var(--wpa-accent); padding-bottom: 5px; display: inline-block;">',
            'after_title'   => '</h4>',
        ] );

        register_sidebar( [
            'name'          => __( 'Academic Footer 1', 'wp-academic-post-enhanced' ),
            'id'            => 'wpa-footer-1',
            'before_widget' => '<div id="%1$s" class="wpa-footer-widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h5 class="wpa-footer-widget-title">',
            'after_title'   => '</h5>',
        ] );

        register_sidebar( [
            'name'          => __( 'Academic Footer 2', 'wp-academic-post-enhanced' ),
            'id'            => 'wpa-footer-2',
            'before_widget' => '<div id="%1$s" class="wpa-footer-widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h5 class="wpa-footer-widget-title">',
            'after_title'   => '</h5>',
        ] );

        register_sidebar( [
            'name'          => __( 'Academic Footer 3', 'wp-academic-post-enhanced' ),
            'id'            => 'wpa-footer-3',
            'before_widget' => '<div id="%1$s" class="wpa-footer-widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h5 class="wpa-footer-widget-title">',
            'after_title'   => '</h5>',
        ] );
    }

    public function disable_theme_assets() {
        if ( ! get_option( 'wpa_homepage_enabled' ) ) {
            return;
        }

        $options = get_option( 'wpa_homepage_settings' );
        $load_global = ! empty( $options['enable_global_header_footer'] );
        
        // Define WPA Context
        $is_wpa_page = is_front_page() || is_singular( 'wpa_course' ) || is_singular( 'wpa_lesson' ) || is_singular( 'wpa_news' ) || is_singular( 'wpa_glossary' ) || is_post_type_archive( 'wpa_course' ) || is_post_type_archive( 'wpa_news' ) || is_post_type_archive( 'wpa_glossary' ) || is_404() || $this->should_apply_global_layout();

        // Only disable if Global Header/Footer is enabled OR we are on a WPA page
        if ( $load_global || $is_wpa_page ) {
            if ( ! apply_filters( 'wpa_dequeue_theme_assets', true ) ) {
                return;
            }

             global $wp_styles, $wp_scripts;
             
             // Get theme directories
             $theme_uri = get_template_directory_uri();
             $child_theme_uri = get_stylesheet_directory_uri();
             
             // Dequeue Styles
             if ( isset( $wp_styles->registered ) ) {
                 foreach ( $wp_styles->registered as $handle => $data ) {
                     if ( isset($data->src) && ( strpos( $data->src, $theme_uri ) !== false || strpos( $data->src, $child_theme_uri ) !== false ) ) {
                         wp_dequeue_style( $handle );
                         wp_deregister_style( $handle );
                     }
                 }
             }
             
             // Dequeue Scripts
             if ( isset( $wp_scripts->registered ) ) {
                 foreach ( $wp_scripts->registered as $handle => $data ) {
                     if ( isset($data->src) && ( strpos( $data->src, $theme_uri ) !== false || strpos( $data->src, $child_theme_uri ) !== false ) ) {
                         wp_dequeue_script( $handle );
                         wp_deregister_script( $handle );
                     }
                 }
             }
        }
    }

    public function admin_assets( $hook ) {
        // Update hook check for new page name (if it changes) or keep compatibility
        if ( strpos( $hook, 'page_wpa-homepage-settings-page' ) !== false || strpos( $hook, 'academic-post-enhanced-homepage' ) !== false || strpos( $hook, 'wpa-theme' ) !== false ) {
            wp_enqueue_style( 'wpa-homepage-builder-css', plugin_dir_url( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'assets/css/homepage-builder.css', [], '1.0' );
            wp_enqueue_script( 'wpa-homepage-builder-js', plugin_dir_url( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'assets/js/homepage-builder.js', ['jquery', 'jquery-ui-sortable'], '1.0', true );
        }
    }

    public function load_template( $template ) {
        if ( ! get_option( 'wpa_homepage_enabled' ) ) {
            return $template;
        }

        $options = get_option( 'wpa_homepage_settings' );
        
        // 1. Homepage Logic
        if ( is_front_page() ) {
            if ( ! empty( $options['enable_homepage'] ) ) {
                // Only use the custom builder if 'Force Builder' is checked
                if ( ! isset( $options['homepage_force_builder'] ) || ! empty( $options['homepage_force_builder'] ) ) {
                    $new_template = plugin_dir_path( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'templates/academic-homepage.php';
                    if ( file_exists( $new_template ) ) {
                        return $new_template;
                    }
                } else {
                    // Otherwise, use the standard wrapper for whatever WP thinks the homepage is (Static Page or Blog)
                    $new_template = plugin_dir_path( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'templates/page-wpa.php';
                    if ( file_exists( $new_template ) ) {
                        return $new_template;
                    }
                }
            }
        }

        // 2. Field News Logic (Universal Frontend)
        if ( is_singular( 'wpa_news' ) ) {
            // Check Theme Settings first
            $enable_news_template = ! empty( $options['enable_news_template'] );
            
            // Fallback to Module Settings if not set in Theme
            if ( ! $enable_news_template ) {
                $news_options = get_option( 'wpa_field_news_settings' );
                $enable_news_template = ! empty( $news_options['enable_custom_template'] );
            }
            
            if ( $enable_news_template ) {
                $new_template = plugin_dir_path( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'templates/single-wpa_news.php';
                if ( file_exists( $new_template ) ) {
                    return $new_template;
                }
            }
        }

        // 3. Course Logic
        if ( is_singular( 'wpa_course' ) ) {
            $new_template = plugin_dir_path( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'templates/single-wpa_course.php';
            if ( file_exists( $new_template ) ) {
                remove_filter( 'the_content', 'wpa_course_content_filter' );
                return $new_template;
            }
        }

        // 4. Lesson Logic
        if ( is_singular( 'wpa_lesson' ) ) {
            $new_template = plugin_dir_path( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'templates/single-wpa_lesson.php';
            if ( file_exists( $new_template ) ) {
                remove_filter( 'the_content', 'wpa_lesson_content_filter' );
                return $new_template;
            }
        }

        // 5. Course Archive
        if ( is_post_type_archive( 'wpa_course' ) ) {
            $new_template = plugin_dir_path( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'templates/archive-wpa_course.php';
            if ( file_exists( $new_template ) ) {
                return $new_template;
            }
        }

        // 6. News Archive
        if ( is_post_type_archive( 'wpa_news' ) ) {
            $new_template = plugin_dir_path( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'templates/archive-wpa_news.php';
            if ( file_exists( $new_template ) ) {
                return $new_template;
            }
        }

        // 6.2 Glossary Logic
        if ( is_singular( 'wpa_glossary' ) ) {
            $new_template = plugin_dir_path( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'templates/single-wpa_glossary.php';
            if ( file_exists( $new_template ) ) {
                return $new_template;
            }
        }

        // 6.5 404 Page Logic
        if ( is_404() ) {
            $new_template = plugin_dir_path( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'templates/404-wpa.php';
            if ( file_exists( $new_template ) ) {
                return $new_template;
            }
        }

        // 7. Global Override for Standard Content (and selected Post Types)
        if ( $this->should_apply_global_layout() ) {
            $new_template = plugin_dir_path( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'templates/page-wpa.php';
            if ( file_exists( $new_template ) ) {
                return $new_template;
            }
        }

        return $template;
    }

    public function should_apply_global_layout() {
        $options = get_option( 'wpa_homepage_settings' );
        
        // If "Apply Globally" is checked, apply to all frontend pages (except those with specific templates)
        if ( ! empty( $options['enable_global_header_footer'] ) ) {
            if ( is_front_page() ) return false; 
            if ( is_singular( ['wpa_course', 'wpa_lesson', 'wpa_news', 'wpa_glossary'] ) ) return false;
            if ( is_post_type_archive( ['wpa_course', 'wpa_news', 'wpa_glossary'] ) ) return false;
            if ( is_404() ) return false;
            
            return true;
        }

        // Otherwise, fallback to the selective post type logic
        if ( is_front_page() || is_archive() || is_search() ) return false; 
        
        if ( ! is_singular() ) return false;

        $enabled_types = isset( $options['global_layout_post_types'] ) ? $options['global_layout_post_types'] : [];
        
        // Default to Post and Page if setting is not yet saved
        if ( ! isset( $options['global_layout_post_types'] ) ) {
             $enabled_types = ['post', 'page'];
        }
        
        if ( empty( $enabled_types ) || ! is_array( $enabled_types ) ) return false;

        $post_type = get_post_type();
        return in_array( $post_type, $enabled_types );
    }

    public function enqueue_assets() {
        // --- Core Theme Assets (Unified) ---
        wp_register_style( 'wpa-core-css', plugin_dir_url( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'assets/css/wpa-unified.css', [], WPA_VERSION ); 
        wp_register_script( 'wpa-core-js', plugin_dir_url( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'assets/js/wpa-global-theme.js', [], WPA_VERSION, true );

        $options = get_option( 'wpa_homepage_settings' );
        $load_global = ! empty( $options['enable_global_header_footer'] );
        
        // Determine if we should load assets
        $is_wpa_page = is_front_page() || is_singular( 'wpa_course' ) || is_singular( 'wpa_lesson' ) || is_singular( 'wpa_news' ) || is_post_type_archive( 'wpa_course' ) || is_post_type_archive( 'wpa_news' ) || is_404() || $this->should_apply_global_layout();
        
        // NEW: Always load core variables if Citation or Social are active on this page
        $citation_enabled = get_option('wp_academic_post_enhanced_citation_enabled');
        $social_enabled = get_option('wpa_social_enabled');
        $needs_core_vars = false;

        if ( is_singular() ) {
            if ( $citation_enabled ) {
                $c_settings = get_option('wpa_citation_settings', []);
                $c_types = isset($c_settings['post_types']) ? $c_settings['post_types'] : ['post'];
                if ( in_array(get_post_type(), $c_types) ) $needs_core_vars = true;
            }
            if ( $social_enabled ) {
                $s_settings = get_option('wpa_social_settings', []);
                $s_types = isset($s_settings['post_types']) ? $s_settings['post_types'] : ['post', 'wpa_course', 'wpa_lesson', 'wpa_news'];
                if ( in_array(get_post_type(), $s_types) ) $needs_core_vars = true;
            }
        }

        if ( $load_global || $is_wpa_page || $needs_core_vars ) {
            wp_enqueue_style( 'wpa-core-css' );
            wp_enqueue_script( 'wpa-core-js' );
            
            // Global Variables & Overrides
            $accent = ! empty( $options['accent_color'] ) ? $options['accent_color'] : '#2563eb';
            $header_bg = ! empty( $options['header_bg'] ) ? $options['header_bg'] : 'rgba(255, 255, 255, 0.95)';
            $header_text = ! empty( $options['header_text_color'] ) ? $options['header_text_color'] : '#1f2937';
            $footer_bg = ! empty( $options['footer_bg'] ) ? $options['footer_bg'] : '#f8fafc';
            
            $font_size_body = ! empty( $options['font_size_body'] ) ? $options['font_size_body'] : '1.15';
            $line_height_body = ! empty( $options['line_height_body'] ) ? $options['line_height_body'] : '1.8';
            $font_body = ! empty( $options['font_body'] ) ? $options['font_body'] : 'Inter';
            $font_heading = ! empty( $options['font_heading'] ) ? $options['font_heading'] : 'Lexend';

            // 1. Calculate Container Width
            $width_setting = ! empty( $options['container_width'] ) ? $options['container_width'] : 'standard';
            $custom_width_val = ! empty( $options['container_width_custom'] ) ? $options['container_width_custom'] : '';

            if ( is_singular() ) {
                $post_id = get_the_ID();
                $post_type = get_post_type();
                $pt_setting_key = '';
                if ( $post_type === 'post' ) $pt_setting_key = 'width_post';
                elseif ( $post_type === 'page' ) $pt_setting_key = 'width_page';
                elseif ( $post_type === 'wpa_course' ) $pt_setting_key = 'width_course';
                elseif ( $post_type === 'wpa_lesson' ) $pt_setting_key = 'width_lesson';
                elseif ( $post_type === 'wpa_news' ) $pt_setting_key = 'width_news';
                elseif ( $post_type === 'wpa_glossary' ) $pt_setting_key = 'width_glossary';

                if ( $pt_setting_key && ! empty( $options[$pt_setting_key] ) && $options[$pt_setting_key] !== 'default' ) {
                    $width_setting = $options[$pt_setting_key];
                }

                $meta_width = get_post_meta( $post_id, '_wpa_container_width', true );
                if ( $meta_width && $meta_width !== 'default' ) {
                    $width_setting = $meta_width;
                    if ( $width_setting === 'custom' ) {
                        $meta_custom = get_post_meta( $post_id, '_wpa_container_width_custom', true );
                        if ( ! empty( $meta_custom ) ) $custom_width_val = $meta_custom;
                    }
                }
            }

            $max_width = '1400px'; 
            if ( $width_setting === 'standard' ) $max_width = '1200px';
            if ( $width_setting === 'narrow' ) $max_width = '800px';
            if ( $width_setting === 'wide' ) $max_width = '90%';
            if ( $width_setting === 'full' ) $max_width = '100%';
            if ( $width_setting === 'custom' && ! empty( $custom_width_val ) ) $max_width = $custom_width_val;

            // Enqueue Google Fonts
            $google_fonts = [];
            if ( $font_body ) $google_fonts[] = $font_body;
            if ( $font_heading && $font_heading !== $font_body ) $google_fonts[] = $font_heading;

            if ( ! empty( $google_fonts ) ) {
                $font_query = str_replace( ' ', '+', implode( '|', $google_fonts ) );
                wp_enqueue_style( 'wpa-google-fonts', "https://fonts.googleapis.com/css?family={$font_query}:400,500,600,700,800&display=swap", [], null );
            }

            $custom_css = "
                :root {
                    --wpa-accent: {$accent};
                    --wpa-header-bg: {$header_bg};
                    --wpa-header-text: {$header_text};
                    --wpa-footer-bg: {$footer_bg};
                    --wpa-font-size-body: {$font_size_body}rem;
                    --wpa-line-height-body: {$line_height_body};
                    --wpa-font-body: '{$font_body}', sans-serif;
                    --wpa-font-heading: '{$font_heading}', sans-serif;
                    --wpa-container-width: {$max_width};";

            // Allow other modules (like Glossary) to hook in and append their own variables
            $custom_css = apply_filters( 'wpa_core_css_variables', $custom_css );

            $custom_css .= "
                }
            ";

            $custom_css .= "
                .wpa-container { max-width: var(--wpa-container-width); }
            ";

            wp_add_inline_style( 'wpa-core-css', $custom_css );
        }

        // Homepage Specific Styles (Hero, Cards, etc.)
        if ( is_front_page() && ! empty( $options['enable_homepage'] ) ) {
            // Enqueue Field News Frontend for existing widgets styles if needed
            wp_enqueue_style( 'wpa-field-news-frontend', plugin_dir_url( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'assets/css/field-news-frontend.css', [], '1.0' );

            // Enqueue Builder Styles
            wp_enqueue_style( 'wpa-builder-frontend', plugin_dir_url( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'assets/css/wpa-builder-frontend.css', [], '1.0' );
        }
    }
}

new WPA_Theme();