<?php
/**
 * Social sharing.
 *
 * @package WP Academic Post
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WPA_Social_Renderer {

    private static $icons = [
        'facebook' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M14 13.5h2.5l1-4H14v-2c0-1.03 0-2 2-2h1.5V2.14c-.326-.043-1.557-.14-2.857-.14C11.928 2 10 3.657 10 6.7v2.8H7v4h3V22h4v-8.5z"/></svg>',
        'twitter' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
        'linkedin' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M6.94 5a2 2 0 1 1-4-.002 2 2 0 0 1 4 .002zM7 8.48H3V21h4V8.48zm6.32 0H9.34V21h3.94v-6.57c0-3.66 4.77-4 4.77 0V21H22v-7.93c0-6.17-7.06-5.94-8.72-2.91l.04-1.68z"/></svg>',
        'pinterest' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.162-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.399.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.951-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.354-.629-2.758-1.379l-.749 2.848c-.269 1.045-1.004 2.352-1.498 3.146 1.123.345 2.306.535 3.55.535 6.607 0 11.985-5.365 11.985-11.987C23.97 5.39 18.592.026 11.985.026L12.017 0z"/></svg>',
        'reddit' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M14.238 15.348c.085.084.085.221 0 .306-.465.462-1.194.687-2.231.687l-.008-.002-.008.002c-1.036 0-1.766-.225-2.231-.688-.085-.084-.085-.221 0-.305.084-.084.222-.084.307 0 .379.377 1.008.561 1.924.561l.008-.002.008.002c.915 0 1.544-.184 1.924-.561.085-.084.223-.084.307 0zm-3.44-2.418c0-.507-.414-.919-.922-.919-.509 0-.923.412-.923.919 0 .506.414.918.923.918.508.001.922-.411.922-.918zm13.202-.93c0 6.627-5.373 12-12 12s-12-5.373-12-12 5.373-12 12-12 12 5.373 12 12zm-5.776.752c0-.506-.413-.918-.921-.918-.509 0-.923.412-.923.918 0 .507.414.919.923.919.508 0 .921-.412.921-.919zm.539 1.87c0 1.265-1.025 2.291-2.288 2.291-1.207 0-2.198-.937-2.279-2.118-1.153-.321-2.016-1.347-2.016-2.579 0-1.469 1.213-2.663 2.709-2.663.593 0 1.143.188 1.597.51l.967-4.233 2.97.663c.046.295.302.52.613.52.341 0 .618-.276.618-.618 0-.342-.276-.618-.618-.618-.313 0-.572.234-.61.535l-3.326-.742-.056.008-1.077 4.717c-.425-.261-.921-.415-1.467-.415-1.496 0-2.663 1.194-2.663 2.663 0 1.469 1.167 2.663 2.663 2.663 1.497 0 2.664-1.194 2.664-2.663 0-.083-.006-.164-.015-.245 1.102.143 1.947 1.056 1.947 2.164 0 .92-.763 1.669-1.699 1.669-.935 0-1.699-.749-1.699-1.669h.363c0 .72.618 1.339 1.336 1.339.719 0 1.336-.619 1.336-1.339 0-.719-.617-1.339-1.336-1.339zm-3.256-2.656c0-.506-.413-.918-.922-.918-.508 0-.922.412-.922.918 0 .507.414.919.922.919.509 0 .922-.412.922-.919z"/></svg>',
        'telegram' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>',
        'whatsapp' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.976.554 2.058.947 2.796.947 3.181 0 5.768-2.587 5.768-5.766-.001-3.181-2.587-5.768-5.768-5.766zm.002-4.172c5.488 0 9.949 4.462 9.949 9.948 0 1.745-.458 3.492-1.332 5.038l1.38 5.014-5.265-1.38c-1.487.817-3.153 1.276-4.732 1.276-5.488 0-9.949-4.461-9.949-9.948 0-5.486 4.461-9.948 9.949-9.948z"/></svg>',
        'email' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5-8-5V6l8 5 8-5v2z"/></svg>',
        'copy-link' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M10.59 13.41c.41.39.41 1.03 0 1.42-.39.39-1.03.39-1.42 0a5.003 5.003 0 0 1 0-7.07l3.54-3.54a5.003 5.003 0 0 1 7.07 0 5.003 5.003 0 0 1 0 7.07l-1.49 1.49c.01-.82-.12-1.64-.4-2.42l.47-.48a3.001 3.001 0 0 0 0-4.24 3.001 3.001 0 0 0-4.24 0l-3.53 3.53a3.001 3.001 0 0 0 0 4.24zm2.82-4.24c.39-.39 1.03-.39 1.42 0a5.003 5.003 0 0 1 0 7.07l-3.54 3.54a5.003 5.003 0 0 1-7.07 0 5.003 5.003 0 0 1 0-7.07l1.49-1.49c-.01.82.12 1.64.4 2.42l-.47.48a3.001 3.001 0 0 0 0 4.24 3.001 3.001 0 0 0 4.24 0l3.53-3.53a3.001 3.001 0 0 0 0-4.24z"/></svg>',
        'native-share' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92s2.92-1.31 2.92-2.92-1.31-2.92-2.92-2.92z"/></svg>',
        'sms' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM9 11H7V9h2v2zm4 0h-2V9h2v2zm4 0h-2V9h2v2z"/></svg>',
        'print' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M19 8h-1V3H6v5H5c-1.66 0-3 1.34-3 3v6h3v4h14v-4h3v-6c0-1.66-1.34-3-3-3zM8 5h8v3H8V5zm8 12v2H8v-2h8zm2-2v-2H6v2H4v-4c0-.55.45-1 1-1h14c.55 0 1 .45 1 1v4h-2z"/><circle cx="18" cy="11.5" r="1"/></svg>',
    ];

    public static function get_icon( $name ) {
        return isset( self::$icons[$name] ) ? self::$icons[$name] : '';
    }

    public static function render( $overrides = [] ) {
        $enabled = get_option( 'wpa_social_enabled' );
        if ( ! $enabled || ! is_singular() ) return '';

        $defaults = [
            'services' => ['facebook', 'twitter', 'linkedin'],
            'post_types' => ['post', 'wpa_course', 'wpa_lesson', 'wpa_news'],
            'style' => 'icons-text',
            'shape' => 'default',
            'size' => 'medium',
            'alignment' => 'left',
            'color_scheme' => 'color',
            'hover_effect' => '',
            'title' => WPA_Theme_Labels::get('news_share_title'),
            'position' => 'none',
        ];

        $settings = wp_parse_args( get_option( 'wpa_social_settings', [] ), $defaults );
        
        // Localize title if it's the default English one
        if ( empty( $settings['title'] ) || $settings['title'] === 'Share this article' ) {
            $settings['title'] = WPA_Theme_Labels::get('news_share_title');
        }

        // Check Post Type
        $current_post_type = get_post_type();
        if ( ! in_array( $current_post_type, (array) $settings['post_types'] ) ) {
            return '';
        }

        // Apply overrides
        if ( ! empty( $overrides ) ) {
            $settings = wp_parse_args( $overrides, $settings );
        }

        // Determine which services to use
        if ( ! empty( $settings['is_mobile_sticky'] ) ) {
            $services = isset( $settings['mobile_services'] ) ? $settings['mobile_services'] : $settings['services'];
        } else {
            $services = $settings['services'];
        }

        $services = array_filter( (array) $services );
        
        if ( empty( $services ) ) return '';

        $url = get_permalink();
        $title = get_the_title();

        $links = [
            'facebook'    => 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode( $url ),
            'twitter'     => 'https://twitter.com/intent/tweet?url=' . urlencode( $url ) . '&text=' . urlencode( $title ),
            'linkedin'    => 'https://www.linkedin.com/shareArticle?mini=true&url=' . urlencode( $url ) . '&title=' . urlencode( $title ),
            'pinterest'   => 'https://pinterest.com/pin/create/button/?url=' . urlencode( $url ) . '&media=&description=' . urlencode( $title ),
            'reddit'      => 'https://www.reddit.com/submit?url=' . urlencode( $url ) . '&title=' . urlencode( $title ),
            'telegram'    => 'https://t.me/share/url?url=' . urlencode( $url ) . '&text=' . urlencode( $title ),
            'whatsapp'    => 'https://api.whatsapp.com/send?text=' . urlencode( $title ) . ' ' . urlencode( $url ),
            'email'       => 'mailto:?subject=' . rawurlencode( $title ) . '&body=' . rawurlencode( $url ),
            'sms'         => 'sms:?body=' . rawurlencode( $title . ' ' . $url ),
            'print'       => '#',
            'copy-link'   => '#',
            'native-share' => '#',
        ];

        $labels = [
            'facebook' => 'Facebook',
            'twitter' => 'X',
            'linkedin' => 'LinkedIn',
            'pinterest' => 'Pinterest',
            'reddit' => 'Reddit',
            'telegram' => 'Telegram',
            'whatsapp' => 'WhatsApp',
            'email' => 'Email',
            'sms' => 'SMS',
            'print' => 'Print',
            'copy-link' => __('Copy Link', 'wp-academic-post-enhanced'),
            'native-share' => __('Share', 'wp-academic-post-enhanced'),
        ];

        $container_classes = [
            'wpa-social-buttons-container',
            'wpa-social-align-' . $settings['alignment'],
        ];

        // Check if mobile sticky is enabled and this is NOT a floating/sticky render
        $mobile_sticky_enabled = isset( $settings['mobile_sticky'] ) && $settings['mobile_sticky'] == '1';
        $is_floating = isset( $settings['is_floating'] ) && $settings['is_floating'];
        
        $wrapper_classes = ['wpa-social-sharing-container'];
        if ( $mobile_sticky_enabled && ! $is_floating ) {
            $wrapper_classes[] = 'wpa-hide-on-mobile';
        }

        $html = '<div class="' . esc_attr( implode( ' ', $wrapper_classes ) ) . '">';
        if ( ! empty( $settings['title'] ) ) {
            $html .= '<h3 class="wpa-feature-box-title">' . esc_html( $settings['title'] ) . '</h3>';
        }
        
        $html .= '<div class="' . esc_attr( implode( ' ', $container_classes ) ) . '">';

        foreach ( $services as $service ) {
            if ( ! isset( $links[$service] ) ) continue;

            $btn_classes = [
                'wpa-social-button',
                'wpa-social-style-' . $settings['style'],
                'wpa-social-shape-' . $settings['shape'],
                'wpa-social-size-' . $settings['size'],
                'wpa-social-scheme-' . $settings['color_scheme'],
                'wpa-social-' . $service
            ];

            if ( $settings['hover_effect'] === 'glow' ) {
                $btn_classes[] = 'wpa-social-hover-glow';
            }

            $icon = isset( self::$icons[$service] ) ? self::$icons[$service] : '';
            $text = isset( $labels[$service] ) ? $labels[$service] : ucfirst( $service );

            $content = '';
            if ( $settings['style'] === 'icons-text' ) {
                $content = $icon . '<span class="wpa-social-text">' . esc_html( $text ) . '</span>';
            } elseif ( $settings['style'] === 'icons' ) {
                $content = $icon;
            } elseif ( $settings['style'] === 'text' ) {
                $content = '<span class="wpa-social-text">' . esc_html( $text ) . '</span>';
            } else { // Minimal
                $content = $icon;
            }

            $href = $links[$service];
            $extra_attrs = '';
            if ( $service === 'copy-link' ) {
                $btn_classes[] = 'wpa-copy-link-btn';
                $extra_attrs = ' data-link="' . esc_url( $url ) . '"';
            } elseif ( $service === 'native-share' ) {
                $btn_classes[] = 'wpa-native-share-btn';
                $extra_attrs = ' data-url="' . esc_url( $url ) . '" data-title="' . esc_attr( $title ) . '"';
            } elseif ( $service === 'print' ) {
                $btn_classes[] = 'wpa-print-btn';
                $extra_attrs = ' onclick="window.print(); return false;"';
            }

            // Fake Share Count Logic (Randomized for demo, would use API in real implementation)
            $share_count = '';
            // Only show for main networks and if style is not minimal or icon only
            if ( in_array( $service, ['facebook', 'twitter', 'linkedin', 'pinterest'] ) && ! in_array( $settings['style'], ['icons', 'minimal'] ) ) {
                 // Deterministic random based on post ID to be consistent per refresh
                 $count = ( get_the_ID() + strlen( $service ) ) * 7 % 100 + 5; 
                 $share_count = '<span class="wpa-share-count">' . $count . '</span>';
            }

            $content = '';
            if ( $settings['style'] === 'icons-text' ) {
                $content = $icon . '<span class="wpa-social-text">' . esc_html( $text ) . '</span>' . $share_count;
            } elseif ( $settings['style'] === 'icons' ) {
                $content = $icon;
            } elseif ( $settings['style'] === 'text' ) {
                $content = '<span class="wpa-social-text">' . esc_html( $text ) . '</span>';
            } elseif ( $settings['style'] === 'inline-text' ) {
                 $content = '<span class="wpa-social-text">' . esc_html( $text ) . '</span>';
                 $btn_classes[] = 'wpa-social-inline-link';
            } else { // Minimal
                $content = $icon;
            }

            $aria_label = esc_attr( sprintf( __( 'Share on %s', 'wp-academic-post-enhanced' ), $text ) );
            if ( $service === 'copy-link' ) {
                $aria_label = esc_attr__( 'Copy Link', 'wp-academic-post-enhanced' );
            } elseif ( $service === 'print' ) {
                $aria_label = esc_attr__( 'Print', 'wp-academic-post-enhanced' );
            }

            $html .= sprintf(
                '<a href="%s" target="_blank" rel="noopener noreferrer" class="%s"%s aria-label="%s">%s</a>',
                esc_url( $href ),
                esc_attr( implode( ' ', $btn_classes ) ),
                $extra_attrs,
                $aria_label,
                $content 
            );
        }

        $html .= '</div></div>';
        return $html;
    }
}

/**
 * Handle social sharing display via the_content filter.
 */
function wpa_social_sharing_content_filter( $content ) {
    if ( ! is_singular() ) {
        return $content;
    }

    $settings = get_option( 'wpa_social_settings', [] );
    $allowed_post_types = isset( $settings['post_types'] ) ? $settings['post_types'] : ['post', 'wpa_course', 'wpa_lesson', 'wpa_news'];
    
    if ( ! in_array( get_post_type(), $allowed_post_types ) ) {
        return $content;
    }

    $position = isset( $settings['position'] ) ? $settings['position'] : 'none';

    if ( $position === 'none' ) {
        return $content;
    }

    // Check for conflict with Citation module to avoid double buttons at the bottom
    $citation_settings = get_option( 'wpa_citation_settings' );
    if ( ! empty( $citation_settings['enabled'] ) ) {
        $citation_post_types = isset( $citation_settings['post_types'] ) ? $citation_settings['post_types'] : ['post'];
        if ( in_array( get_post_type(), $citation_post_types ) ) {
            // Citation is active for this post type and it includes social buttons at the bottom.
            // If Social is set to 'after', disable it. If 'both', change to 'before'.
            if ( $position === 'after' ) {
                return $content; 
            } elseif ( $position === 'both' ) {
                $position = 'before';
            }
        }
    }

    $social_html = WPA_Social_Renderer::render();

    if ( $position === 'before' ) {
        $content = $social_html . $content;
    } elseif ( $position === 'after' ) {
        $content = $content . $social_html;
    } elseif ( $position === 'both' ) {
        $content = $social_html . $content . $social_html;
    }

    return $content;
}
add_filter( 'the_content', 'wpa_social_sharing_content_filter', 20 );

/**
 * Register social sharing shortcode.
 */
function wpa_social_sharing_shortcode() {
    return WPA_Social_Renderer::render();
}
/**
 * Render floating social sharing sidebar.
 */
function wpa_social_sharing_floating_sidebar() {
    if ( ! is_singular() ) {
        return;
    }

    $settings = get_option( 'wpa_social_settings', [] );
    $allowed_post_types = isset( $settings['post_types'] ) ? $settings['post_types'] : ['post', 'wpa_course', 'wpa_lesson', 'wpa_news'];
    
    if ( ! in_array( get_post_type(), $allowed_post_types ) ) {
        return;
    }

    $floating = isset( $settings['floating'] ) ? $settings['floating'] : 'none';
    $mobile_sticky = isset( $settings['mobile_sticky'] ) && $settings['mobile_sticky'] == '1';

    if ( $floating === 'none' && ! $mobile_sticky ) {
        return;
    }

    // Render Desktop Floating Sidebar
    if ( $floating !== 'none' ) {
        $sidebar_classes = ['wpa-social-floating-sidebar', 'wpa-social-floating-' . esc_attr( $floating )];
        $render_overrides = [
            'style' => 'icons',
            'alignment' => 'center',
            'title' => '',
            'is_floating' => true
        ];

        echo '<div class="' . esc_attr( implode( ' ', $sidebar_classes ) ) . '">';
        echo WPA_Social_Renderer::render( $render_overrides );
        echo '</div>';
    }

    // Render Mobile Sticky Bottom Bar
    if ( $mobile_sticky ) {
        $sidebar_classes = ['wpa-social-floating-sidebar', 'wpa-social-floating-bottom', 'wpa-has-mobile-downloads'];
        $render_overrides = [
            'style' => 'icons',
            'alignment' => 'center',
            'size' => 'medium',
            'title' => '',
            'is_floating' => true,
            'is_mobile_sticky' => true
        ];

        // PDF and RIS Data
        $citation_options = get_option( 'wpa_citation_settings' );
        $pdf_enabled = ! empty( $citation_options['pdf_download_enabled'] );
        $post_id = get_the_ID();
        $pdf_link = add_query_arg( 'wpa_download_pdf', '1', get_permalink($post_id) );
        $title = get_the_title($post_id);
        $author = get_the_author();
        $year = get_the_date('Y');
        $site_name = get_bloginfo('name');
        $url = wp_get_shortlink($post_id);
        $abstract = has_excerpt($post_id) ? wp_strip_all_tags(get_the_excerpt($post_id)) : wp_trim_words(wp_strip_all_tags(get_post_field('post_content', $post_id)), 55);
        $full_date = get_the_date('Y/m/d');

        $ad_code = isset($settings['mobile_sticky_ad_code']) ? $settings['mobile_sticky_ad_code'] : '';

        // Toggle Button (Outside the bar so it stays visible)
        echo '<button type="button" class="wpa-mobile-download-toggle" title="' . esc_attr__('Toggle Links', 'wp-academic-post-enhanced') . '" aria-label="' . esc_attr__('Toggle Links', 'wp-academic-post-enhanced') . '" aria-expanded="false">';
        // ... (Icon content) ...
        echo '<svg class="wpa-icon-close" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
            <svg class="wpa-icon-open" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="currentColor" style="display:none;"><path d="M12 16l-6-6h12z"/></svg>
        </button>';

        echo '<div class="' . esc_attr( implode( ' ', $sidebar_classes ) ) . '">';
        
        // Ad Row (Optional)
        if ( ! empty($ad_code) ) {
            echo '<div class="wpa-mobile-ad-row">' . $ad_code . '</div>';
        }

        // Main Social Row
        echo '<div class="wpa-social-main-row">';
        echo WPA_Social_Renderer::render( $render_overrides );
        echo '</div>';

        // Secondary Download Row
        echo '<div class="wpa-mobile-download-row">';
        
        $citation_post_types = isset( $citation_options['post_types'] ) ? $citation_options['post_types'] : ['post'];
        
        if ( $pdf_enabled && in_array( get_post_type(), $citation_post_types ) ) {
            echo '<a href="' . esc_url($pdf_link) . '" class="wpa-social-button wpa-mobile-pdf-btn" title="' . esc_attr__('Download PDF', 'wp-academic-post-enhanced') . '" aria-label="' . esc_attr__('Download PDF', 'wp-academic-post-enhanced') . '">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                <span class="wpa-social-text">PDF</span>
            </a>';
        }

        echo '<button type="button" class="wpa-social-button wpa-download-ris-btn wpa-mobile-ris-btn" 
            data-title="' . esc_attr($title) . '" 
            data-author="' . esc_attr($author) . '" 
            data-year="' . esc_attr($year) . '" 
            data-fulldate="' . esc_attr($full_date) . '" 
            data-journal="' . esc_attr($site_name) . '" 
            data-url="' . esc_attr($url) . '" 
            data-abstract="' . esc_attr($abstract) . '" 
            title="' . esc_attr__('Download Citation (.RIS)', 'wp-academic-post-enhanced') . '"
            aria-label="' . esc_attr__('Download Citation (.RIS)', 'wp-academic-post-enhanced') . '">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
            <span class="wpa-social-text">RIS</span>
        </button>';
        echo '</div>';

        echo '</div>';
    }
}
add_action( 'wp_footer', 'wpa_social_sharing_floating_sidebar' );

// Wrapper for backward compatibility
function wpa_get_social_sharing_html() {
    return WPA_Social_Renderer::render();
}

// Enqueue styles and scripts
function wpa_add_social_sharing_assets() {
    if ( ! get_option( 'wpa_social_enabled' ) || ! is_singular() ) {
        return;
    }

    $settings = get_option( 'wpa_social_settings', [] );
    $allowed_post_types = isset( $settings['post_types'] ) ? $settings['post_types'] : ['post', 'wpa_course', 'wpa_lesson', 'wpa_news'];

    if ( ! in_array( get_post_type(), $allowed_post_types ) ) {
        return;
    }

    // Force dequeue of the external file to ensure inlining takes over
    wp_dequeue_style( 'wpa-social-style' );

        // Inline the CSS for performance
        $css_file = plugin_dir_path( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'assets/css/social.css';
        if ( file_exists( $css_file ) ) {
            $css = file_get_contents( $css_file );
            $css = preg_replace( '!/\*.*?\*/!s', '', $css );
            $css = preg_replace( '/\s+/', ' ', $css );
            wp_register_style( 'wpa-social-inline-final', false );
            wp_enqueue_style( 'wpa-social-inline-final' );
            wp_add_inline_style( 'wpa-social-inline-final', $css );
        }

        wp_enqueue_script(
            'wpa-social-js',
            plugin_dir_url( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'assets/js/social.js',
            [],
            '1.0',
            true
        );

        wp_localize_script( 'wpa-social-js', 'wpa_social_vars', [
            'copy_success' => __( 'Link copied!', 'wp-academic-post-enhanced' ),
            'copy_error' => __( 'Failed to copy link.', 'wp-academic-post-enhanced' ),
        ]);
}
add_action( 'wp_enqueue_scripts', 'wpa_add_social_sharing_assets', 999 );