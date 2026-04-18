<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Generates citation string for a given format.
 *
 * @param string $format The citation format (e.g., 'apa', 'mla', 'chicago', 'harvard').
 * @param string $author The author's name.
 * @param string $year The publication year.
 * @param string $title The article title.
 * @param string $site_name The site name.
 * @param string $url The article URL.
 * @return string The formatted citation.
 */
function wp_academic_post_enhanced_generate_citation( $format, $author, $year, $title, $site_name, $url ) {
    $label_retrieved = WPA_Theme_Labels::get('cite_retrieved_from');
    $label_available = WPA_Theme_Labels::get('cite_available_at');
    $label_vol       = WPA_Theme_Labels::get('cite_vol');
    $label_no        = WPA_Theme_Labels::get('cite_no');
    $label_pp        = WPA_Theme_Labels::get('cite_pp');

    switch ( $format ) {
        case 'apa':
            return sprintf(
                '%s (%s). %s. %s. %s %s',
                esc_html( $author ),
                esc_html( $year ),
                '<em>' . esc_html( $title ) . '</em>',
                esc_html( $site_name ),
                esc_html( $label_retrieved ),
                esc_html( $url )
            );
        case 'mla':
            return sprintf(
                '%s. "%s." %s, %s, %s.',
                esc_html( $author ),
                esc_html( $title ),
                '<em>' . esc_html( $site_name ) . '</em>',
                get_the_date( 'j M. Y' ),
                esc_html( $url )
            );
        case 'chicago':
            return sprintf(
                '%s. "%s." %s, %s. %s.',
                esc_html( $author ),
                esc_html( $title ),
                esc_html( $site_name ),
                esc_html( $year ),
                esc_html( $url )
            );
        case 'harvard':
            return sprintf(
                '%s (%s) \'%s\', %s. %s: %s.',
                esc_html( $author ),
                esc_html( $year ),
                esc_html( $title ),
                esc_html( $site_name ),
                esc_html( $label_available ),
                esc_html( $url )
            );
        case 'ieee':
            return sprintf(
                "[1] %s, \"%s,\" %s, %s %s, %s %s, %s %s, %s.",
                esc_html( $author ),
                esc_html( $title ),
                '<em>' . esc_html( $site_name ) . '</em>',
                esc_html( $label_vol ), 'X',
                esc_html( $label_no ), 'Y',
                esc_html( $label_pp ), 'Z-Z',
                get_the_date( 'F, Y' )
            );
        case 'ama':
            return sprintf(
                '%s. %s. %s. %s;%s(%s):%s.',
                esc_html( $author ),
                esc_html( $title ),
                '<em>' . esc_html( $site_name ) . '</em>',
                esc_html( $year ),
                'vol', // Placeholder
                'issue', // Placeholder
                'pages' // Placeholder
            );
        case 'bibtex':
            return sprintf(
                '@article{wpa_%d,<br>&nbsp;&nbsp;author&nbsp;=&nbsp;{%s},<br>&nbsp;&nbsp;title&nbsp;&nbsp;=&nbsp;{%s},<br>&nbsp;&nbsp;journal&nbsp;=&nbsp;{%s},<br>&nbsp;&nbsp;year&nbsp;&nbsp;&nbsp;&nbsp;=&nbsp;{%s},<br>&nbsp;&nbsp;url&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;=&nbsp;{%s}<br>}',
                get_the_ID(),
                esc_html( $author ),
                esc_html( $title ),
                esc_html( $site_name ),
                esc_html( $year ),
                esc_url( $url )
            );
    }
}


/**
 * Add the citation to the end of the post content.
 *
 * @param string $content The post content.
 * @return string The modified post content.
 */
function wp_academic_post_enhanced_add_citation( $content ) {
    $options = get_option( 'wpa_citation_settings' );
    $defaults = [
        'enabled' => true,
        'title' => 'Cite this article',
        'styles' => ['apa', 'mla'],
        'position' => 'after_content',
        'default_style' => 'apa',
        'post_types' => ['post', 'wpa_course', 'wpa_lesson', 'wpa_news'],
    ];
    $options = wp_parse_args( $options, $defaults );

    $current_post_type = get_post_type();

    if ( ! $options['enabled'] || ! is_singular() || ! is_main_query() || ! in_array( $current_post_type, $options['post_types'] ) ) {
        return $content;
    }

    $citation_title = $options['title'];
    $selected_styles = $options['styles'];
    $position = $options['position'];
    $default_style = $options['default_style'];

    if ( empty( $selected_styles ) ) {
        return $content; // Don't display if no styles are selected.
    }

    $citation_title = ! empty( $options['title'] ) && $options['title'] !== 'Cite this article' ? $options['title'] : WPA_Theme_Labels::get('cite_box_title');

    $author = get_the_author();
    $year = get_the_date( 'Y' );
    $title = get_the_title();
    $site_name = get_bloginfo( 'name' );
    $url = get_permalink( get_the_ID() );

    $citation_html = '<div class="wpa-feature-box wpa-citation-container">';
    $citation_html .= '<h3 class="wpa-feature-box-title">' . esc_html( $citation_title ) . '</h3>';

    $citation_html .= '<div class="wpa-citation-tabs">';
    
    // Mobile Dropdown
    $citation_html .= '<div class="wpa-citation-mobile-select">';
    $citation_html .= '<select id="wpa-citation-style-selector" class="wpa-citation-select-input">';
    foreach ( $selected_styles as $style ) {
        $selected = ( $style === $default_style ) ? 'selected="selected"' : '';
        $citation_html .= '<option value="' . esc_attr( $style ) . '" ' . $selected . '>' . strtoupper( esc_html( $style ) ) . '</option>';
    }
    $citation_html .= '</select>';
    $citation_html .= '</div>';

    foreach ( $selected_styles as $style ) {
        $checked = ( $style === $default_style ) ? 'checked="checked"' : '';
        $citation_html .= '<input type="radio" name="wpa-citation-style" id="wpa-citation-tab-' . esc_attr( $style ) . '" class="wpa-citation-tab-input" ' . $checked . '>';
        $citation_html .= '<label for="wpa-citation-tab-' . esc_attr( $style ) . '" class="wpa-citation-tab">' . strtoupper( esc_html( $style ) ) . '</label>';
    }
    
    foreach ( $selected_styles as $style ) {
         $citation_html .= '<div id="wpa-citation-content-' . esc_attr( $style ) . '" class="wpa-citation-content">';
         $text = wp_academic_post_enhanced_generate_citation( $style, $author, $year, $title, $site_name, $url );
         $citation_html .= '<p class="wpa-citation-text">' . $text . '</p>';
         $citation_html .= '<button type="button" class="button wpa-btn wpa-btn-secondary wpa-copy-citation-btn" aria-live="polite" data-style="' . esc_attr( $style ) . '">' . esc_html( WPA_Theme_Labels::get('cite_btn_copy') ) . '</button>';
         $citation_html .= '</div>';
    }
    $citation_html .= '</div>'; // .wpa-citation-tabs

    // Download RIS Button
    global $post;
    $abstract = '';
    if ( ! empty( $post->post_excerpt ) ) {
        $abstract = wp_strip_all_tags( $post->post_excerpt );
    } else {
        $abstract = wp_trim_words( wp_strip_all_tags( $post->post_content ), 55 );
    }
    // Sanitize abstract for RIS (remove newlines)
    $abstract = str_replace( ["\r", "\n"], " ", $abstract );
    
    $full_date = get_the_date( 'Y/m/d' );
    
    $citation_html .= '<div class="wpa-citation-actions">';
    $citation_html .= '<button type="button" class="button wpa-btn wpa-btn-secondary wpa-download-ris-btn" data-title="' . esc_attr( $title ) . '" data-author="' . esc_attr( $author ) . '" data-year="' . esc_attr( $year ) . '" data-fulldate="' . esc_attr( $full_date ) . '" data-journal="' . esc_attr( $site_name ) . '" data-url="' . esc_attr( $url ) . '" data-abstract="' . esc_attr( $abstract ) . '">' . esc_html( WPA_Theme_Labels::get('cite_btn_ris') ) . '</button>';
    $citation_html .= '<button type="button" class="button wpa-btn wpa-btn-secondary wpa-download-bib-btn" data-title="' . esc_attr( $title ) . '" data-author="' . esc_attr( $author ) . '" data-year="' . esc_attr( $year ) . '" data-journal="' . esc_attr( $site_name ) . '" data-url="' . esc_attr( $url ) . '" data-id="' . get_the_ID() . '">' . esc_html( WPA_Theme_Labels::get('cite_btn_bib') ) . '</button>';
    
    // Clean PDF Download Button (Server/Endpoint handled)
    $pdf_post_types = isset( $options['pdf_post_types'] ) ? $options['pdf_post_types'] : ['post'];
    if ( ! empty( $options['pdf_download_enabled'] ) && in_array( $current_post_type, $pdf_post_types ) ) {
        $pdf_link = add_query_arg( 'wpa_download_pdf', '1', get_permalink() );
        $citation_html .= '<a href="' . esc_url( $pdf_link ) . '" target="_blank" class="button wpa-btn wpa-btn-secondary wpa-download-pdf-btn">' . esc_html( WPA_Theme_Labels::get('cite_btn_pdf') ) . '</a>';
    }
    
    $citation_html .= '</div>';

    // Inline Script for Tabs and RIS Download
    $citation_html .= '<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Tab Switching
        var styleSelector = document.getElementById("wpa-citation-style-selector");
        if (styleSelector) {
            styleSelector.addEventListener("change", function(e) {
                var radio = document.getElementById("wpa-citation-tab-" + e.target.value);
                if(radio) radio.checked = true;
            });
        }

        // Copy Citation
        document.body.addEventListener("click", function(e) {
            var copyBtn = e.target.closest(".wpa-copy-citation-btn");
            if (copyBtn) {
                e.preventDefault();
                var container = copyBtn.closest(".wpa-citation-content");
                var textElement = container.querySelector(".wpa-citation-text");
                if (textElement) {
                    var textToCopy = textElement.innerText || textElement.textContent;
                    navigator.clipboard.writeText(textToCopy).then(function() {
                        var originalText = copyBtn.innerText;
                        copyBtn.innerText = "Copied!";
                        setTimeout(function() {
                            copyBtn.innerText = originalText;
                        }, 2000);
                    }).catch(function(err) {
                        console.error("Failed to copy text: ", err);
                    });
                }
            }
        });

        // RIS Download
        document.body.addEventListener("click", function(e) {
            var risBtn = e.target.closest(".wpa-download-ris-btn");
            if (risBtn) {
                e.preventDefault();
                var data = risBtn.dataset;
                var filename = (data.title ? data.title.replace(/[^a-z0-9]/gi, "_").toLowerCase().substring(0, 60) : "citation") + ".ris";
                
                var risContent = "TY  - JOUR\r\n" +
                    "TI  - " + (data.title || "") + "\r\n" +
                    "AU  - " + (data.author || "") + "\r\n" +
                    "PY  - " + (data.year || "") + "\r\n" +
                    "DA  - " + (data.fulldate || "") + "\r\n" +
                    "JO  - " + (data.journal || "") + "\r\n" +
                    "UR  - " + (data.url || "") + "\r\n" +
                    "AB  - " + (data.abstract || "") + "\r\n" +
                    "ER  - \r\n";
                
                var blob = new Blob([risContent], { type: "application/x-research-info-systems" });
                var link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }

            // BibTeX Download
            var bibBtn = e.target.closest(".wpa-download-bib-btn");
            if (bibBtn) {
                e.preventDefault();
                var data = bibBtn.dataset;
                var filename = (data.title ? data.title.replace(/[^a-z0-9]/gi, "_").toLowerCase().substring(0, 60) : "citation") + ".bib";
                
                var bibContent = "@article{wpa_" + data.id + ",\r\n" +
                    "  author  = {" + (data.author || "") + "},\r\n" +
                    "  title   = {" + (data.title || "") + "},\r\n" +
                    "  journal = {" + (data.journal || "") + "},\r\n" +
                    "  year    = {" + (data.year || "") + "},\r\n" +
                    "  url     = {" + (data.url || "") + "}\r\n" +
                    "}";
                
                var blob = new Blob([bibContent], { type: "text/plain" });
                var link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        });
    });
    </script>';

    // Add social sharing
    if ( function_exists( 'wpa_get_social_sharing_html' ) ) {
        $citation_html .= wpa_get_social_sharing_html();
    }

    $citation_html .= '</div>'; // .wpa-citation

    if ( 'before_content' === $position ) {
        $content = $citation_html . $content;
    } else {
        $content .= $citation_html;
    }
    
    return $content;
}
add_filter( 'the_content', 'wp_academic_post_enhanced_add_citation', 30 );

/**
 * Enqueue citation styles and scripts.
 * 
 * Styles are now part of the unified theme. This function remains for legacy compatibility
 * or if we need to enqueue JS specific to citation in the future (though JS is currently inline).
 */
function wp_academic_post_enhanced_enqueue_citation_assets() {
    // Check if the citation feature is enabled for the current post type
    $options = get_option( 'wpa_citation_settings' );
    if ( empty( $options['enabled'] ) || ! is_singular() ) {
        return;
    }

    $post_types = isset( $options['post_types'] ) ? $options['post_types'] : ['post'];
    if ( ! in_array( get_post_type(), $post_types ) ) {
        return;
    }

    // Load citation CSS
    wp_enqueue_style(
        'wpa-citation-style',
        plugin_dir_url( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'assets/css/citation.css',
        [],
        WPA_VERSION
    );

    // Load print CSS if needed
    wp_enqueue_style(
        'wpa-print-style',
        plugin_dir_url( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'assets/css/wpa-print.css',
        [],
        WPA_VERSION,
        'print'
    );
}
add_action( 'wp_enqueue_scripts', 'wp_academic_post_enhanced_enqueue_citation_assets' );
