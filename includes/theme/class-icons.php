<?php
/**
 * SVG Icon Helper
 * Replaces Dashicons with inline SVGs for better performance and quality.
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPA_Icons {

    public static function get( $icon, $class = '' ) {
        $icons = self::get_icons();
        
        if ( ! isset( $icons[ $icon ] ) ) {
            return '';
        }

        $svg_content = $icons[ $icon ];
        $css_class = 'wpa-icon wpa-icon-' . esc_attr( $icon );
        if ( $class ) {
            $css_class .= ' ' . esc_attr( $class );
        }

        return '<span class="' . $css_class . '" aria-hidden="true">' . $svg_content . '</span>';
    }

    private static function get_icons() {
        return [
            // Arrows
            'arrow-down-alt2'  => '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M5 6l5 5 5-5 2 1-7 7-7-7 2-1z"/></svg>',
            'arrow-up-alt2'    => '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M15 14l-5-5-5 5-2-1 7-7 7 7-2 1z"/></svg>',
            'arrow-left-alt2'  => '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M14 5l-5 5 5 5 1 2-7-7 7-7-1 1z"/></svg>',
            'arrow-right-alt2' => '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M6 15l5-5-5-5-1-2 7 7-7 7 1-1z"/></svg>',
            
            // UI Controls
            'menu'             => '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M2 4h16v2H2V4zm0 5h16v2H2V9zm0 5h16v2H2v-2z"/></svg>',
            'search'           => '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M13.5 12.5l4 4-1 1-4-4a6.5 6.5 0 111-1zm-6 0a5 5 0 100-10 5 5 0 000 10z"/></svg>',
            'cross'            => '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M10 8.5l5-5 1.5 1.5-5 5 5 5-1.5 1.5-5-5-5 5-1.5-1.5 5-5-5-5 1.5-1.5 5 5z"/></svg>', // close
            
            // Content Types
            'format-quote'     => '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M5 10a3 3 0 110-6 3 3 0 010 6zm0 2a5 5 0 00-5 5h3a2 2 0 112 0v-5zm10-2a3 3 0 110-6 3 3 0 010 6zm0 2a5 5 0 00-5 5h3a2 2 0 112 0v-5z"/></svg>',
            'format-image'     => '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M2 4h16a2 2 0 012 2v8a2 2 0 01-2 2H2a2 2 0 01-2-2V6a2 2 0 012-2zm0 2v8h16V6H2zm3 2a1.5 1.5 0 110 3 1.5 1.5 0 010-3zm11 7H4l4-5 3 3 2-2 3 4z"/></svg>',
            'text-page'        => '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M4 2h10l4 4v12H4V2zm10 4V3l3 3h-3zM6 10h8v2H6v-2zm0 4h8v2H6v-2z"/></svg>',
            'welcome-learn-more' => '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M10 2L2 7l8 5 8-5-8-5zm0 16l-8-5v-6l8 5 8-5v6l-8 5z"/></svg>', // course hat
            'chart-bar'        => '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M2 17h16v2H2v-2zm3-6h2v5H5v-5zm4-4h2v9H9V7zm4-6h2v15h-2V1z"/></svg>',
            'megaphone'        => '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M16 4l-4 4-2-2L6 10H2v4h4l4 4 2-2 4 4V4zM7 11H4v-2h3v2z"/></svg>', // news
            'groups'           => '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M7 8a4 4 0 110-8 4 4 0 010 8zm0 1c2.15 0 4.2.4 6.1 1.09L12 16h-1.25L10 20H4l-.75-4H2L.9 10.09A17.93 17.93 0 017 9zm8.31.17c1.32.18 2.59.48 3.8.92L18 16h-1.25L16 20h-2.25l-.95-4H11l2.2-5.52c.69-.19 1.4-.35 2.11-.48z"/></svg>',
            'email'            => '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M2 4h16a2 2 0 012 2v8a2 2 0 01-2 2H2a2 2 0 01-2-2V6a2 2 0 012-2zm0 2v.5l8 5 8-5V6H2zm0 10h16V8l-8 5-8-5v8z"/></svg>',
            'calendar-alt'     => '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M4 2h2v2H4V2zm10 0h2v2h-2V2zM2 5h16a2 2 0 012 2v9a2 2 0 01-2 2H2a2 2 0 01-2-2V7a2 2 0 012-2zm0 2v2h16V7H2zm0 4v6h16v-6H2z"/></svg>',
            'admin-users'      => '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M7 8a4 4 0 110-8 4 4 0 010 8zm0 1c2.67 0 8 1.34 8 4v3H1v-3c0-2.66 5.33-4 8-4z"/></svg>', // person
            'clock'            => '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M10 2a8 8 0 100 16 8 8 0 000-16zm0 14A6 6 0 1110 4a6 6 0 010 12zm.5-10H9v6l4.5 2.5.5-1-4-2V6z"/></svg>',
            'star'             => '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>',
            'building'         => '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd" /></svg>',
            'orcid'            => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.372 0 0 5.372 0 12s5.372 12 12 12 12-5.372 12-12S18.628 0 12 0zM7.369 4.378c.541 0 .981.44.981.981 0 .542-.44.981-.981.981s-.981-.439-.981-.981c0-.541.44-.981.981-.981zm-.69 2.418h1.38v10.17h-1.38V6.796zm10.107 0c1.606 0 2.828.456 3.67 1.369.83.912 1.244 2.262 1.244 4.045 0 2.108-.425 3.586-1.277 4.435-.851.849-2.019 1.274-3.506 1.274h-3.817V6.796h3.686zm-2.436 1.38v7.41h1.454c1.067 0 1.879-.318 2.436-.954.557-.637.835-1.656.835-3.056 0-1.251-.252-2.175-.759-2.773-.506-.597-1.264-.896-2.274-.896h-1.692z"/></svg>',
            'lightbulb'        => '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1h4v1a2 2 0 11-4 0zM12 14c.015-.34.208-.646.477-.859a4 4 0 10-4.954 0c.27.213.462.519.477.859h4z"/></svg>',
            'earth'            => '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM4.332 8.027a6.012 6.012 0 011.912-2.706C6.512 5.73 6.974 6 7.5 6A1.5 1.5 0 019 7.5V8a2 2 0 004 0 2 2 0 011.523-1.943A5.977 5.977 0 0116 10c0 .34-.028.675-.083 1H15a2 2 0 00-2 2v2.197A5.973 5.977 0 0110 16v-2a2 2 0 00-2-2 2 2 0 01-2-2 2 2 0 00-1.668-1.973z" clip-rule="evenodd" /></svg>',
            
            // Social
            'twitter'          => '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M18.9 4.53a7.8 7.8 0 01-2.25.62 3.94 3.94 0 001.72-2.17 7.87 7.87 0 01-2.49.95 3.93 3.93 0 00-6.7 3.58A11.16 11.16 0 011 3.4a3.93 3.93 0 001.22 5.24 3.9 3.9 0 01-1.78-.49v.05a3.93 3.93 0 003.15 3.85 3.92 3.92 0 01-1.77.07 3.93 3.93 0 003.67 2.73 7.88 7.88 0 01-4.87 1.68 7.9 7.9 0 01-.93-.05 11.1 11.1 0 006.03 1.77c7.23 0 11.18-6 11.18-11.18 0-.17 0-.34-.01-.51a8 8 0 001.96-2.03z"/></svg>',
            'facebook'         => '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M17 1H3a2 2 0 00-2 2v14a2 2 0 002 2h7v-7H8V9h2V7.5a3.5 3.5 0 013.5-3.5h2V6h-2a1 1 0 00-1 1v2h3l-1 3h-2v7h4.5a2 2 0 002-2V3a2 2 0 00-2-2z"/></svg>',
            'linkedin'         => '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M17 1H3a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V3a2 2 0 00-2-2zM6 16H3V7h3v9zm-1.5-10a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm13 10h-3v-5c0-1-.5-1.5-1.5-1.5S11.5 10 11.5 11v5H8.5V7h3v1.5c.5-1 1.5-1.5 3-1.5 2 0 3 1.5 3 3.5v5.5z"/></svg>',
            'instagram'        => '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M10 1.8c2.67 0 3 .01 4.05.06 1.06.05 1.63.22 2.01.37.5.2.93.47 1.37.9.44.44.7.88.9 1.37.15.38.32.95.37 2.01.05 1.05.06 1.38.06 4.05s-.01 3-.06 4.05c-.05 1.06-.22 1.63-.37 2.01a3.67 3.67 0 01-.9 1.37c-.44.44-.88.7-1.37.9-.38.15-.95.32-2.01.37-1.05.05-1.38.06-4.05.06s-3-.01-4.05-.06c-1.06-.05-1.63-.22-2.01-.37a3.67 3.67 0 01-1.37-.9 3.67 3.67 0 01-.9-1.37c-.15-.38-.32-.95-.37-2.01-.05-1.05-.06-1.38-.06-4.05s.01-3 .06-4.05c.05-1.06.22-1.63.37-2.01a3.67 3.67 0 01.9-1.37c.44-.44.88-.7 1.37-.9.38-.15.95-.32 2.01-.37 1.05-.05 1.38-.06 4.05-.06M10 0C7.28 0 6.91.01 5.84.06 4.77.11 4.04.28 3.4.53a5.45 5.45 0 00-1.97 1.28A5.45 5.45 0 00.15 3.78C-.1 4.42-.27 5.15-.32 6.22-.37 7.29-.38 7.66-.38 10.38s.01 3.09.06 4.16c.05 1.07.22 1.8.47 2.44.26.66.6 1.22 1.28 1.88.66.66 1.22 1 1.88 1.28.64.25 1.37.42 2.44.47 1.07.05 1.44.06 4.16.06s3.09-.01 4.16-.06c1.07-.05 1.8-.22 2.44-.47.66-.26 1.22-.6 1.88-1.28.66-.66 1-.1.22 1.28-1.88.64-.25 1.37-.42 2.44-.47 1.07-.05 1.44-.06 4.16-.06zm0 4.86a5.14 5.14 0 100 10.28 5.14 5.14 0 000-10.28zm0 8.48a3.34 3.34 0 110-6.68 3.34 3.34 0 010 6.68zm5.34-7.9a1.2 1.2 0 100 2.4 1.2 1.2 0 000-2.4z"/></svg>',
            'admin-site'       => '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M10 2a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12z"/></svg>', // globe
            
            // Misc
            'lock'             => '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M10 2a5 5 0 00-5 5v2a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2V7a5 5 0 00-5-5zm-3 5a3 3 0 016 0v2H7V7z"/></svg>',
            'unlock'           => '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M10 2a5 5 0 00-5 5v2a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2V7H13V7a3 3 0 00-6 0v2H7V7a5 5 0 005-5z"/></svg>',
            'yes'              => '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M16.7 5.3a1 1 0 010 1.4l-8 8a1 1 0 01-1.4 0l-4-4a1 1 0 011.4-1.4L8 12.6l7.3-7.3a1 1 0 011.4 0z"/></svg>',
            'external'         => '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M11 3a1 1 0 100 2h2.59L6.3 12.3a1 1 0 101.4 1.4L15 6.41V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z"/><path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z"/></svg>',
            'pdf'              => '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 3.414L15.586 7A2 2 0 0116 8.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" /></svg>',
            'download'         => '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M13 8V2H7v6H2l8 8 8-8h-5zM0 18h20v2H0v-2z"/></svg>',
            'awards'           => '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292zM10 15a2 2 0 110-4 2 2 0 010 4z"/></svg>',
            'edit'             => '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828zM4 12v4h4v-4H4z"/></svg>',
            'trash'            => '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>',
            'cover-image'      => '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" /></svg>',
            'images-alt2'      => '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"/></svg>',
            'list-view'        => '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M4 4h12v2H4V4zm0 5h12v2H4V9zm0 5h12v2H4v-2z"/></svg>',
            'editor-code'      => '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>',
            'moon'             => '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/></svg>',
            'sun'              => '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707 0.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd" /></svg>',
            'fullscreen-alt'   => '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M3 4a1 1 0 011-1h4a1 1 0 010 2H5v3a1 1 0 01-2 0V4zm9-1a1 1 0 011 1v3a1 1 0 11-2 0V5h-3a1 1 0 110-2h4zm-9 9a1 1 0 012 0v3h3a1 1 0 110 2H4a1 1 0 01-1-1v-4zm12 0a1 1 0 112 0v4a1 1 0 01-1 1h-4a1 1 0 110-2h3v-3z"/></svg>',
            'expand'           => '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>',
        ];
    }
}
