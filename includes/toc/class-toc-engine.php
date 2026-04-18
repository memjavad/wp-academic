<?php
/**
 * Unified Table of Contents Engine - V4 (Elite Academic Metrics)
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPA_TOC_Engine {

    private $content;
    private $options;
    private $headings = [];

    public function __construct( $content = '', $options = [] ) {
        $this->content = $content;
        $this->set_options( $options );
        
        if ( ! empty( $content ) ) {
            $this->parse();
        }
    }

    public function set_options( $options ) {
        $defaults = [
            'allowed_headings' => ['h1', 'h2', 'h3'],
            'min_headings'     => 2,
            'title'            => 'Table of Contents',
            'collapsible'      => false,
            'start_collapsed'  => false,
            'line_spacing'     => 0.2,
            'font_size'        => 0.85,
            'padding'          => 15,
            'title_top_margin' => 0,
            'words_per_minute' => 200, // Standard academic reading speed
        ];
        $this->options = wp_parse_args( $options, $defaults );
    }

    public function parse() {
        if ( empty( $this->content ) ) return;

        $allowed = $this->options['allowed_headings'];
        $levels = implode( '', array_map( function($h) { return substr($h, 1); }, $allowed ) );
        if ( empty( $levels ) ) return;

        $pattern = '/<h([' . $levels . '])([^>]*)>(.*?)<\/h\1>/i';
        $parts = preg_split($pattern, $this->content, -1, PREG_SPLIT_DELIM_CAPTURE);
        
        $this->content = $parts[0];
        $heading_index = 0;

        for ($i = 1; $i < count($parts); $i += 4) {
            $level = (int)$parts[$i];
            $attrs = $parts[$i+1];
            $title_raw = $parts[$i+2];
            $text_after = isset($parts[$i+3]) ? $parts[$i+3] : '';
            
            $title_clean = strip_tags($title_raw);
            
            // 1. Calculate Section Metrics
            $word_count = str_word_count(strip_tags($text_after));
            $read_time_mins = max(1, ceil($word_count / $this->options['words_per_minute']));
            
            // 2. Extract Snippet
            $snippet = wp_trim_words(strip_tags($text_after), 20);

            // 3. Handle IDs
            if ( preg_match( '/id=["\']([^"\']+)["\']/i', $attrs, $id_match ) ) {
                $id = $id_match[1];
            } else {
                $id = sanitize_title( $title_clean );
                if ( empty( $id ) ) $id = 'section-' . $heading_index;
                $original_id = $id;
                $counter = 1;
                while ( $this->id_exists( $id ) ) {
                    $id = $original_id . '-' . $counter;
                    $counter++;
                }
                $attrs .= ' id="' . esc_attr( $id ) . '"';
            }

            $this->headings[] = [
                'id'         => $id,
                'title'      => $title_clean,
                'level'      => $level,
                'url'        => '#' . $id,
                'snippet'    => $snippet,
                'read_time'  => $read_time_mins,
                'word_count' => $word_count
            ];

            $this->content .= "<h{$level}{$attrs} class=\"wpa-toc-anchor\" data-section-id=\"{$id}\">{$title_raw}</h{$level}>" . $text_after;
            $heading_index++;
        }
    }

    private function id_exists( $id ) {
        foreach ( $this->headings as $h ) {
            if ( $h['id'] === $id ) return true;
        }
        return false;
    }

    public function get_content() { return $this->content; }
    public function get_headings() { return $this->headings; }

    public function get_html() {
        if ( count( $this->headings ) < $this->options['min_headings'] ) return '';

        $classes = ['wpa-feature-box', 'wpa-toc-container'];
        if ( $this->options['collapsible'] ) {
            $classes[] = 'wpa-toc-collapsible';
            if ( $this->options['start_collapsed'] ) $classes[] = 'wpa-toc-collapsed';
        }

        $container_style = '';
        if ( ! empty( $this->options['padding'] ) ) {
            $container_style .= 'padding:' . esc_attr( $this->options['padding'] ) . 'px;';
        }
        if ( ! empty( $this->options['font_size'] ) ) {
            $container_style .= 'font-size:' . esc_attr( $this->options['font_size'] ) . 'rem;';
        }
        if ( ! empty( $container_style ) ) {
            $container_style = ' style="' . $container_style . '"';
        }

        $html  = '<div class="' . implode( ' ', $classes ) . '"' . $container_style . '>';
        $title = esc_html( $this->options['title'] );
        $title_style = ! empty( $this->options['title_top_margin'] ) ? ' style="margin-top:' . esc_attr( $this->options['title_top_margin'] ) . 'px;"' : '';

        $html .= '<div class="wpa-toc-header">';
        if ( $this->options['collapsible'] ) {
            $html .= '<h2 class="wpa-feature-box-title wpa-toc-toggle"' . $title_style . '>' . $title . '<span class="wpa-toc-arrow"></span></h2>';
        } elseif ( $title ) {
            $html .= '<h2 class="wpa-feature-box-title"' . $title_style . '>' . $title . '</h2>';
        }
        $html .= '</div>';

        $style = ( $this->options['collapsible'] && $this->options['start_collapsed'] ) ? 'style="display:none;"' : '';
        $html .= '<nav class="wpa-toc-nav" ' . $style . '>';
        $html .= $this->build_nested_list( $this->headings );
        $html .= '</nav>';
        $html .= '</div>';

        return $html;
    }

    private function build_nested_list( $headings ) {
        $html = '<ul class="wpa-toc-list">';
        $last_level = false;
        $li_style = ! empty( $this->options['line_spacing'] ) ? ' style="margin-bottom:' . esc_attr( $this->options['line_spacing'] ) . 'em;"' : '';

        foreach ( $headings as $h ) {
            if ( $last_level !== false ) {
                if ( $h['level'] > $last_level ) {
                    $html .= '<ul class="wpa-toc-sub">';
                } elseif ( $h['level'] < $last_level ) {
                    $html .= str_repeat( '</li></ul>', $last_level - $h['level'] ) . '</li>';
                } else {
                    $html .= '</li>';
                }
            }
            
            $time_label = $h['read_time'] . 'm';
            
            $html .= '<li class="wpa-toc-item level-' . $h['level'] . '" data-anchor="' . esc_attr($h['id']) . '"' . $li_style . '>';
            $html .= '<a href="' . esc_attr( $h['url'] ) . '">';
            $html .= '<span class="wpa-toc-item-title">' . esc_html( $h['title'] ) . '</span>';
            $html .= '<span class="wpa-toc-time" title="' . esc_attr__('Estimated reading time for this section', 'wp-academic-post-enhanced') . '">' . $time_label . '</span>';
            $html .= '</a>';
            $last_level = $h['level'];
        }
        $html .= '</li>' . str_repeat( '</ul></li>', max(0, $last_level - 2) ) . '</ul>';
        return $html;
    }

    /**
     * Elite Schema: Adds timeRequired and Semantic Content Mapping
     */
    public function get_schema_data() {
        if ( count( $this->headings ) < $this->options['min_headings'] ) return [];

        $schema = [
            '@context' => 'https://schema.org',
            '@type'    => 'ItemList',
            'name'     => $this->options['title'],
            'mainEntityOfPage' => get_permalink(),
            'itemListElement'  => [],
        ];

        foreach ( $this->headings as $key => $h ) {
            $schema['itemListElement'][] = [
                '@type' => 'ListItem',
                'position' => $key + 1,
                'item' => [
                    '@type' => 'WebPageElement',
                    '@id'   => get_permalink() . $h['url'],
                    'name'  => $h['title'],
                    'description' => $h['snippet'],
                    'timeRequired' => 'PT' . $h['read_time'] . 'M', // ISO 8601 Duration Format
                    'cssSelector' => '#' . $h['id']
                ]
            ];
        }

        return $schema;
    }
}
