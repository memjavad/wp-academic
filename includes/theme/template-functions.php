<?php
/**
 * Unified Header & Footer Template Functions
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Render the Unified Academic Header
 *
 * @param array $args Optional arguments to override settings.
 */
function wpa_get_header( $args = [] ) {
    $defaults = [
        'show_progress' => false,
        'sticky'        => true,
    ];
    
    // Merge defaults with passed args
    $args = wp_parse_args( $args, $defaults );
    
    // Get Global Settings
    $options = get_option( 'wpa_homepage_settings', [] );
    
    // Overrides from options if not manually passed
    if ( ! isset( $args['sticky_force'] ) ) { 
        $args['sticky'] = ! empty( $options['header_sticky'] );
    }

    $header_class = 'wpa-news-header';
    if ( $args['sticky'] ) {
        $header_class .= ' wpa-sticky';
    }

    ?>
    <!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo( 'charset' ); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <script>
            (function() {
                try {
                    var savedMode = localStorage.getItem('wpa_dark_mode');
                    var systemPrefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                    if (savedMode === 'dark' || (!savedMode && systemPrefersDark)) {
                        document.documentElement.classList.add('wpa-dark-mode');
                        document.addEventListener('DOMContentLoaded', function() {
                            document.body.classList.add('wpa-dark-mode');
                        });
                    }
                } catch (e) {}
            })();
        </script>
        <?php wp_head(); ?>
    </head>
    <body <?php body_class( 'wpa-custom-template' ); ?>>
    <?php if ( function_exists( 'wp_body_open' ) ) wp_body_open(); ?>
    
    <header class="<?php echo esc_attr( $header_class ); ?>">
        <div class="wpa-container wpa-header-inner">
            <div class="wpa-branding">
                <?php 
                $logo_url = ! empty( $options['header_logo'] ) ? $options['header_logo'] : '';
                
                if ( $logo_url ) {
                    echo '<a href="' . esc_url( home_url( '/' ) ) . '"><img src="' . esc_url( $logo_url ) . '" alt="' . get_bloginfo( 'name' ) . '" style="height:40px;"></a>';
                } elseif ( has_custom_logo() ) {
                    the_custom_logo();
                } else {
                    echo '<a href="' . esc_url( home_url( '/' ) ) . '" class="wpa-site-title">' . get_bloginfo( 'name' ) . '</a>';
                }
                ?>
            </div>
            
            <button class="wpa-mobile-toggle" aria-label="<?php esc_attr_e( 'Toggle Menu', 'wp-academic-post-enhanced' ); ?>" aria-expanded="false">
                <?php echo WPA_Icons::get('menu'); ?>
            </button>

            <nav class="wpa-header-nav" aria-label="<?php esc_attr_e( 'Main Navigation', 'wp-academic-post-enhanced' ); ?>">
                <?php 
                $menu_type = isset($options['menu_type']) ? $options['menu_type'] : 'wp';
                $menu_id = isset($options['menu_wp_id']) ? $options['menu_wp_id'] : '';
                $custom_links = isset($options['menu_custom_links']) ? $options['menu_custom_links'] : '';
                
                $menu_rendered = false;

                if ( $menu_type === 'wp' && ! empty( $menu_id ) ) {
                    wp_nav_menu( [ 'menu' => $menu_id, 'container' => false, 'menu_class' => 'wpa-header-menu', 'depth' => 3 ] );
                    $menu_rendered = true;
                } elseif ( $menu_type === 'custom' && ! empty( $custom_links ) ) {
                    echo '<ul class="wpa-header-menu">';
                    $lines = explode( "\n", $custom_links );
                    foreach ( $lines as $line ) {
                        $parts = explode( '|', $line );
                        $label = trim( $parts[0] );
                        $url = isset( $parts[1] ) ? trim( $parts[1] ) : '#';
                        if ( $label ) {
                            echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>';
                        }
                    }
                    echo '</ul>';
                    $menu_rendered = true;
                }

                if ( ! $menu_rendered ) {
                    if ( has_nav_menu( 'wpa-main-menu' ) ) {
                        wp_nav_menu( [ 'theme_location' => 'wpa-main-menu', 'container' => false, 'menu_class' => 'wpa-header-menu', 'depth' => 3 ] );
                    } else {
                        // Fallback
                        echo '<ul class="wpa-header-menu"><li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'wp-academic-post-enhanced' ) . '</a></li></ul>';
                    }
                }
                ?>
            </nav>

            <div class="wpa-header-actions">
                <!-- Dark Mode Toggle -->
                <button id="wpa-dark-mode-toggle" class="wpa-btn wpa-btn-icon wpa-btn-text" aria-label="<?php esc_attr_e( 'Toggle Dark Mode', 'wp-academic-post-enhanced' ); ?>" style="margin-right: 10px;">
                    <?php echo WPA_Icons::get('moon'); ?>
                </button>

                <?php
                $show_btn = isset( $options['header_show_btn'] ) ? $options['header_show_btn'] : 1;
                
                if ( $show_btn ) :
                    $cta_text = ! empty( $options['header_btn_text'] ) ? $options['header_btn_text'] : __( 'Get Started', 'wp-academic-post-enhanced' );
                    $cta_url  = ! empty( $options['header_btn_url'] ) ? $options['header_btn_url'] : '#';
                    ?>
                    <a href="<?php echo esc_url( $cta_url ); ?>" class="wpa-btn wpa-btn-primary wpa-btn-pill wpa-header-btn">
                        <?php echo esc_html( $cta_text ); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ( $args['show_progress'] ) : ?>
        <div class="wpa-header-progress">
            <div class="wpa-header-progress-bar" id="wpa-reading-progress-bar"></div>
        </div>
        <?php endif; ?>
    </header>

    <!-- Mobile Drawer -->
    <div class="wpa-mobile-menu-overlay" aria-hidden="true">
        <div class="wpa-mobile-menu-content">
            <button id="wpa-dark-mode-toggle-mobile" class="wpa-btn wpa-btn-icon wpa-btn-text" aria-label="<?php esc_attr_e( 'Toggle Dark Mode', 'wp-academic-post-enhanced' ); ?>">
                <?php echo WPA_Icons::get('moon'); ?>
            </button>
            <button class="wpa-mobile-close" aria-label="<?php esc_attr_e( 'Close Menu', 'wp-academic-post-enhanced' ); ?>">&times;</button>
            <!-- Mobile Menu Clone Target -->
            <div id="wpa-mobile-menu-container"></div>
        </div>
    </div>
    <?php
}

/**
 * Render the Unified Academic Footer
 *
 * @param array $args Optional arguments.
 */
function wpa_get_footer( $args = [] ) {
    $options = get_option( 'wpa_homepage_settings', [] );
    
    $copyright = ! empty($options['footer_copyright']) ? $options['footer_copyright'] : '&copy; ' . date('Y') . ' ' . get_bloginfo('name');
    $socials = ! empty($options['footer_social']) ? array_filter(array_map('trim', explode("\n", $options['footer_social'])))
 : [];
    
    ?>
    <footer class="wpa-news-footer wpa-custom-footer">
        <?php if ( is_active_sidebar('wpa-footer-1') || is_active_sidebar('wpa-footer-2') || is_active_sidebar('wpa-footer-3') ) : ?>
            <div class="wpa-container">
                <div class="wpa-footer-widgets">
                    <?php if ( is_active_sidebar('wpa-footer-1') ) : ?><div class="wpa-footer-col"><?php dynamic_sidebar('wpa-footer-1'); ?></div><?php endif; ?>
                    <?php if ( is_active_sidebar('wpa-footer-2') ) : ?><div class="wpa-footer-col"><?php dynamic_sidebar('wpa-footer-2'); ?></div><?php endif; ?>
                    <?php if ( is_active_sidebar('wpa-footer-3') ) : ?><div class="wpa-footer-col"><?php dynamic_sidebar('wpa-footer-3'); ?></div><?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="wpa-container">
            <div class="wpa-footer-content">
                <div class="wpa-footer-left">
                    <p><?php echo wp_kses_post( $copyright ); ?></p>
                </div>
                <?php if ( ! empty( $socials ) ) : ?>
                    <div class="wpa-footer-social">
                        <?php foreach ( $socials as $link ) : 
                            $icon = 'admin-site';
                            if ( strpos($link, 'twitter') !== false ) $icon = 'twitter';
                            if ( strpos($link, 'facebook') !== false ) $icon = 'facebook';
                            if ( strpos($link, 'linkedin') !== false ) $icon = 'linkedin';
                            if ( strpos($link, 'instagram') !== false ) $icon = 'instagram';
                        ?>
                            <a href="<?php echo esc_url($link); ?>" target="_blank" aria-label="<?php echo esc_attr( $icon ); ?>"><?php echo WPA_Icons::get( $icon ); ?></a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </footer>

    <!-- Scroll to Top -->
    <button id="wpa-scroll-top" class="wpa-scroll-top" aria-label="Scroll to Top">
        <?php echo WPA_Icons::get( 'arrow-up-alt2' ); ?>
    </button>

    <?php wp_footer(); ?>
    </body>
    </html>
    <?php
}

/**
 * Get a cleaned version of a post title, stripping leaked descriptions or AI artifacts.
 * Useful for sidebars and small UI cards.
 */
function wpa_get_clean_title( $post_id = null, $max_chars = 70 ) {
    $post_id = $post_id ? $post_id : get_the_ID();
    $title = get_the_title( $post_id );
    
    // 1. Strip everything after common AI/News delimiters if title is too long
    if ( mb_strlen( $title ) > 40 ) {
        $delimiters = [ ' | ', ' : ', ' — ', ' - ' ];
        foreach ( $delimiters as $delim ) {
            if ( strpos( $title, $delim ) !== false ) {
                $parts = explode( $delim, $title );
                // Be careful not to strip short legitimate parts
                if ( mb_strlen( trim($parts[0]) ) > 10 ) {
                    $title = trim($parts[0]);
                    break;
                }
            }
        }
    }
    
    // 2. Final trim if still too long
    if ( mb_strlen( $title ) > $max_chars ) {
        $title = mb_substr( $title, 0, $max_chars ) . '...';
    }
    
    return $title;
}


/**
 * Helper to get a setting from theme options or module options with a fallback default.
 *
 * @param string $key_theme   The key to look for in theme options.
 * @param string $key_module  The key to look for in module options.
 * @param mixed  $default     The default value if neither is set.
 * @param array  $theme_opts  The theme options array.
 * @param array  $module_opts The module options array.
 * @return mixed
 */
if ( ! function_exists( 'wpa_get_setting' ) ) {
    function wpa_get_setting( $key_theme, $key_module, $default, $theme_opts, $module_opts ) {
        if ( isset( $theme_opts[$key_theme] ) ) return $theme_opts[$key_theme];
        if ( isset( $module_opts[$key_module] ) ) return $module_opts[$key_module];
        return $default;
    }
}
