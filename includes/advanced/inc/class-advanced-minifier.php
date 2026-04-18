<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Advanced Minification Engine
 * 
 * Provides heuristic-based minification for HTML, CSS, and JS.
 */
class WP_Academic_Post_Enhanced_Advanced_Minifier {

    private static $instance;

    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Advanced HTML Minifier
     */
    public function minify_html( $html ) {
        // 1. Minify internal Style blocks
        $html = preg_replace_callback( '/<style\b[^>]*>(.*?)<\/style>/is', function( $match ) {
            return str_replace($match[1], $this->minify_css($match[1]), $match[0]);
        }, $html );

        // 2. Minify internal Script blocks
        $html = preg_replace_callback( '/<script\b[^>]*>(.*?)<\/script>/is', function( $match ) {
            if ( trim($match[1]) && strpos($match[1], 'application/ld+json') === false ) {
                return str_replace($match[1], $this->minify_js($match[1]), $match[0]);
            }
            return $match[0];
        }, $html );

        // Protect pre, code, textarea
        $placeholders = [];
        $html = preg_replace_callback( '/<(pre|code|textarea)\b[^>]*>.*?<\/\1>/is', function( $match ) use ( &$placeholders ) {
            $key = '<!--WPA_MIN_PH_' . count( $placeholders ) . '-->';
            $placeholders[$key] = $match[0];
            return $key;
        }, $html );

        // 3. Remove HTML comments
        $html = preg_replace( '/<!--(?!\s*(?:[\']|<!|>))(?:(?!-->).)*-->/s', '', $html );

        // 4. Collapse multiple whitespaces
        $html = preg_replace( '/\s{2,}/', ' ', $html );

        // 5. Remove whitespace between tags
        $html = preg_replace( '/>\s+</', '><', $html );

        // Restore protected tags
        if ( ! empty( $placeholders ) ) {
            $html = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $html );
        }

        // 6. Basic SVG Optimization
        $html = preg_replace_callback( '/<svg\b[^>]*>(.*?)<\/svg>/is', [ $this, 'optimize_svg' ], $html );

        return trim( $html );
    }

    /**
     * Minify SVG content
     */
    private function optimize_svg( $match ) {
        $svg = $match[0];
        // Remove comments
        $svg = preg_replace( '/<!--.*?-->/s', '', $svg );
        // Remove unnecessary whitespace
        $svg = preg_replace( '/>\s+</', '><', $svg );
        $svg = preg_replace( '/\s{2,}/', ' ', $svg );
        // Shorten decimals in paths
        $svg = preg_replace( '/(\d+\.\d{2})\d+/', '$1', $svg );
        return $svg;
    }

    /**
     * Advanced JS Minifier
     */
    public function minify_js( $js ) {
        if ( empty($js) ) return '';
        // 1. Remove comments
        $js = preg_replace( '!/\*.*?\*/!s', '', $js );
        $js = preg_replace( '!^\s*//.*!m', '', $js );

        // 2. Protect Strings
        $protected_strings = [];
        $js = preg_replace_callback(
            '/"(?:[^"\\\\]|\\\\.)*"|\'(?:[^\'\\\\]|\\\\.)*\'/s',
            function( $match ) use ( &$protected_strings ) {
                $key = '<!--WPA_STR_' . count( $protected_strings ) . '-->';
                $protected_strings[ $key ] = $match[0];
                return $key;
            },
            $js
        );

        // 3. Remove whitespace around operators
        $js = preg_replace( '/\s*([\{\}\(\);,=\+\-\*\/<>!\?&|])\s*/', '$1', $js );
        
        // 4. Remove redundant horizontal spaces (leaving newlines alone)
        $js = preg_replace( '/[ \t]{2,}/', ' ', $js );

        // 5. Restore Strings
        if ( ! empty( $protected_strings ) ) {
            $js = str_replace( array_keys( $protected_strings ), array_values( $protected_strings ), $js );
        }

        return trim( $js );
    }

    /**
     * Advanced CSS Minifier
     */
    public function minify_css( $css ) {
        if ( empty($css) ) return '';
        // 1. Remove comments
        $css = preg_replace( '!/\*.*?\*/!s', '', $css );
        // 2. Remove whitespace around symbols aggressively
        $css = preg_replace( '/\s*([{\}:;,>~\+])\s*/', '$1', $css );
        // 3. Remove last semicolon
        $css = str_replace( ';}', '}', $css );
        // 4. Shorten Zero values
        $css = preg_replace( '/\b0(px|em|rem|%)?/', '0', $css );
        // 5. Shorten Hex colors
        $css = preg_replace( '/#([a-f0-9])\1([a-f0-9])\2([a-f0-9])\3/i', '#$1$2$3', $css );
        // 6. Remove double semi-colons and extra spaces
        $css = preg_replace( '/;{2,}/', ';', $css );
        $css = preg_replace( '/\s{2,}/', ' ', $css );
        return trim( $css );
    }
}