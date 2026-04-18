<?php
/**
 * Handles the Clean PDF Download (mPDF Server-Side) functionality with Caching.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPA_PDF_Download {

    private $cache_dir;

    public function __construct() {
        $upload_dir = wp_upload_dir();
        $this->cache_dir = $upload_dir['basedir'] . '/wpa-pdf-cache';

        add_action( 'template_redirect', [ $this, 'handle_pdf_request' ] );
        add_action( 'save_post', [ $this, 'clear_pdf_cache_on_save' ], 10, 2 );
    }

    /**
     * Clear cached PDF when a post is updated.
     */
    public function clear_pdf_cache_on_save( $post_id, $post ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        
        $cache_file = $this->cache_dir . '/post-' . $post_id . '.pdf';
        if ( file_exists( $cache_file ) ) {
            @unlink( $cache_file );
        }
    }

    public function handle_pdf_request() {
        if ( is_singular() && isset( $_GET['wpa_download_pdf'] ) && '1' === $_GET['wpa_download_pdf'] ) {
            $post_id = get_the_ID();
            $options = get_option( 'wpa_citation_settings' );
            $allowed_post_types = isset( $options['pdf_post_types'] ) ? $options['pdf_post_types'] : ['post'];
            
            if ( ! in_array( get_post_type(), $allowed_post_types ) ) {
                return;
            }

            // 1. Check Cache First
            $cache_file = $this->cache_dir . '/post-' . $post_id . '.pdf';
            $filename = sanitize_title( get_the_title() ) . '.pdf';

            if ( file_exists( $cache_file ) && ! isset( $_GET['nocache'] ) ) {
                $this->serve_pdf( $cache_file, $filename );
                exit;
            }

            // 2. No cache, generate it
            // Clean any existing buffers
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            $this->generate_pdf( $cache_file, $filename );
            exit;
        }
    }

    private function serve_pdf( $file_path, $display_name ) {
        header( 'Content-Type: application/pdf' );
        header( 'Content-Disposition: attachment; filename="' . $display_name . '"' );
        header( 'Content-Length: ' . filesize( $file_path ) );
        readfile( $file_path );
    }

    private function generate_pdf( $cache_path, $filename ) {
        global $post;
        $citation_options = get_option( 'wpa_citation_settings' );
        $visible_elements = isset( $citation_options['pdf_elements'] ) ? $citation_options['pdf_elements'] : ['header', 'footer', 'cover_title', 'cover_meta', 'cover_citation'];
        
        // Styling Options
        $text_color = isset( $citation_options['pdf_text_color'] ) ? $citation_options['pdf_text_color'] : '#111111';
        $heading_color = isset( $citation_options['pdf_heading_color'] ) ? $citation_options['pdf_heading_color'] : '#000000';
        $link_color = isset( $citation_options['pdf_link_color'] ) ? $citation_options['pdf_link_color'] : '#0000EE';
        $citation_bg_color = isset( $citation_options['pdf_citation_bg_color'] ) ? $citation_options['pdf_citation_bg_color'] : '#fcfcfc';
        $watermark_text = isset( $citation_options['pdf_watermark_text'] ) ? $citation_options['pdf_watermark_text'] : '';

        // Increase memory and execution time
        ini_set('memory_limit', '512M');
        set_time_limit(300);
        
        // Load mPDF
        $autoload_path = plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
        if ( ! file_exists( $autoload_path ) ) {
            wp_die( 'PDF library missing.' );
        }
        require_once $autoload_path;

        // Data Preparation
        $title = get_the_title( $post );
        $author = get_the_author_meta( 'display_name', $post->post_author );
        $date = get_the_date( '', $post );
        $site_name = get_bloginfo( 'name' );
        
        // Extract Keywords from Categories and Tags
        $categories = wp_get_post_categories($post->ID, ['fields' => 'names']);
        $tags = wp_get_post_tags($post->ID, ['fields' => 'names']);
        $keywords = implode(', ', array_merge($categories, $tags));
        
        // Use Excerpt as Subject
        $subject = !empty($post->post_excerpt) ? wp_strip_all_tags($post->post_excerpt) : wp_trim_words(wp_strip_all_tags($post->post_content), 30);

        $site_url = home_url();
        $site_url_clean = str_replace( ['http://', 'https://'], '', $site_url );
        $shortlink = wp_get_shortlink( $post->ID );

        // Content Cleaning
        $content = $post->post_content;

        // Strip Shortcodes (Registered and Unregistered)
        $content = strip_shortcodes($content);
        $content = preg_replace('/\[\/?.*?\]/s', '', $content);

        $content = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F\xAD]/u', '', $content);
        $content = str_replace(["\xC2\xA0", '&nbsp;', "\xA0"], ' ', $content);
        
        // ASCII Normalization
        $replacements = [
            "\xE2\x80\x98" => "'", "\xE2\x80\x99" => "'",
            "\xE2\x80\x9C" => '"', "\xE2\x80\x9D" => '"',
            "\xE2\x80\x93" => '-', "\xE2\x80\x94" => '--',
            "\xE2\x80\xA6" => '...',
        ];
        $content = str_replace(array_keys($replacements), array_values($replacements), $content);

        // Simple Tags (Restored <img>, <pre>, <code>, and tables for rich content)
        $content = strip_tags($content, '<h1><h2><h3><h4><h5><h6><p><br><strong><b><em><a><img><pre><code><table><thead><tbody><tfoot><tr><th><td>');
        
        // Preserve essential attributes for specific tags
        $content = preg_replace_callback('/<(h1|h2|h3|h4|h5|h6|p|br|strong|b|em|pre|code|table|thead|tbody|tfoot|tr|th|td)([^>]*)>/i', function($m) {
            return "<{$m[1]}>";
        }, $content);
        
        $content = wpautop($content);
        $content = trim($content);
        $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8');

        $is_arabic = preg_match( '/[\p{Arabic}]/u', $title . ' ' . wp_strip_all_tags($content) );
        $has_chinese = preg_match( '/[\x{4e00}-\x{9fa5}]/u', $title . ' ' . wp_strip_all_tags($content) );
        
        $selected_font = isset( $citation_options['pdf_font'] ) ? $citation_options['pdf_font'] : 'lateef';

        if ( ! file_exists( $this->cache_dir ) ) {
            wp_mkdir_p( $this->cache_dir );
        }

        // Initialize mPDF
        $mode = $is_arabic ? 'utf-8' : 'c';
        
        $config = [
            'mode' => $mode, 
            'format' => 'A4',
            'tempDir' => $this->cache_dir,
            'margin_left' => 20,
            'margin_right' => 20,
            'margin_top' => 25,
            'margin_bottom' => 25,
            'margin_header' => 10,
            'margin_footer' => 10,
            'compress' => true,
            'allow_charset_conversion' => false,
            'simpleTables' => true,
            'useSubstitutions' => false,
        ];

        // Font Configuration
        $font_dir = plugin_dir_path( __FILE__ ) . 'fonts';
        $default_config = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $default_font_data = (new \Mpdf\Config\FontVariables())->getDefaults();
        
        $config['fontDir'] = array_merge( $default_config['fontDir'], [ $font_dir ] );
        $config['fontdata'] = $default_font_data['fontdata'] + [
            'amiri' => [ 'R' => 'Amiri-Regular.ttf', 'useOTL' => 0x00, 'useKashida' => 75 ],
            'notoarabic' => [ 'R' => 'NotoNaskhArabic-Regular.ttf', 'B' => 'NotoNaskhArabic-Bold.ttf', 'useOTL' => 0x00 ],
            'lateef' => [ 'R' => 'Lateef-Regular.ttf', 'useOTL' => 0x00 ],
            'notosans' => [ 'R' => 'NotoSans-Regular.ttf', 'B' => 'NotoSans-Bold.ttf', 'I' => 'NotoSans-Italic.ttf' ],
            'notoserif' => [ 'R' => 'NotoSerif-Regular.ttf', 'B' => 'NotoSerif-Bold.ttf', 'I' => 'NotoSerif-Italic.ttf' ],
        ];

        if ( $is_arabic ) {
            $config['default_font'] = ( in_array($selected_font, ['amiri', 'notoarabic', 'lateef']) ) ? $selected_font : 'amiri';
        } else {
            if ( in_array($selected_font, ['notosans', 'notoserif']) ) {
                $config['default_font'] = $selected_font;
            } else {
                $config['default_font'] = 'helvetica';
            }
        }

        try {
            $mpdf = new \Mpdf\Mpdf( $config );
            $mpdf->SetTitle($title);
            $mpdf->SetAuthor($author);
            $mpdf->SetSubject($subject);
            $mpdf->SetKeywords($keywords);
            $mpdf->SetCreator('WP Academic Post Enhanced');
            $mpdf->useSubstitutions = true;

            if ( ! empty( $watermark_text ) ) {
                $mpdf->SetWatermarkText( $watermark_text );
                $mpdf->showWatermarkText = true;
                $mpdf->watermarkTextAlpha = 0.1;
            }

            if ( $is_arabic ) {
                $mpdf->repackageTTF = true;
                $mpdf->percentSubset = 0;
                $mpdf->SetDirectionality('rtl');
                $mpdf->autoScriptToLang = true;
                $mpdf->autoLangToFont = true;
            }

            if ( $has_chinese ) {
                $mpdf->autoScriptToLang = true;
                $mpdf->autoLangToFont = true;
            }

            // Styles
            $ui_font = $is_arabic ? 'amiri' : 'helvetica';
            $stylesheet = '
                body { font-family: ' . $ui_font . ', sans-serif; font-size: 11pt; line-height: 1.6; color: ' . $text_color . '; }
                .cover-page { text-align: center; padding-top: 50mm; }
                .cover-title { font-size: 28pt; font-weight: bold; margin-bottom: 10mm; color: ' . $heading_color . '; line-height: 1.2; font-family: ' . $ui_font . '; }
                .cover-title a { color: ' . $heading_color . '; text-decoration: none; }
                .cover-meta { font-size: 14pt; color: #444; margin-bottom: 20mm; font-family: ' . $ui_font . '; }
                .cover-citation-box { 
                    background-color: ' . $citation_bg_color . '; border: 0.5pt solid #eee; padding: 8mm; 
                    text-align: ' . ($is_arabic ? 'right' : 'left') . '; 
                    direction: ' . ($is_arabic ? 'rtl' : 'ltr') . '; 
                    margin: 0 auto; width: 85%;
                }
                .citation-label { font-weight: bold; font-size: 9pt; text-transform: uppercase; color: #999; margin-bottom: 3mm; font-family: ' . $ui_font . '; }
                .citation-text { font-size: 10pt; color: #333; font-family: ' . $ui_font . '; }
                .citation-text a { color: ' . $link_color . '; text-decoration: underline; }
                
                p { margin-bottom: 4mm; text-align: justify; page-break-inside: auto !important; }
                h1, h2, h3, h4, h5, h6 { font-weight: bold; margin: 15pt 0 5pt 0; page-break-after: avoid; color: ' . $heading_color . '; }
                h1 { font-size: 18pt; } h2 { font-size: 15pt; } h3 { font-size: 13pt; }
                a { color: ' . $link_color . '; text-decoration: underline; }
                
                img { max-width: 100%; height: auto; margin: 5mm auto; display: block; }
                pre { 
                    background-color: #f5f5f5; 
                    border: 0.5pt solid #ddd; 
                    padding: 5mm; 
                    font-family: courier, monospace; 
                    font-size: 9pt; 
                    line-height: 1.4; 
                    white-space: pre-wrap; 
                    word-wrap: break-word;
                    margin-bottom: 5mm;
                }
                code { 
                    background-color: #f5f5f5; 
                    font-family: courier, monospace; 
                    padding: 0.5mm 1mm; 
                    font-size: 10pt; 
                }

                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 5mm;
                    table-layout: fixed;
                }
                th, td {
                    border: 0.1mm solid #000000;
                    padding: 2mm;
                    vertical-align: top;
                    font-size: 10pt;
                }
                th {
                    background-color: #f2f2f2;
                    font-weight: bold;
                    text-align: center;
                }

                .pdf-header { width: 100%; border-bottom: 0.5pt solid #eee; padding-bottom: 2mm; border-collapse: separate; direction: ' . ($is_arabic ? 'rtl' : 'ltr') . '; }
                .pdf-header td { border: none; padding: 0; vertical-align: bottom; }
                .header-title { width: 80%; text-align: ' . ($is_arabic ? 'right' : 'left') . '; font-family: ' . $ui_font . '; font-size: 8pt; color: #aaa; }
                .header-title a { color: #aaa; text-decoration: none; }
                .header-page { width: 20%; text-align: ' . ($is_arabic ? 'left' : 'right') . '; font-family: ' . $ui_font . '; font-size: 8pt; color: #aaa; }
                
                .pdf-footer { width: 100%; font-family: ' . $ui_font . '; font-size: 8pt; color: #aaa; border-top: 0.5pt solid #eee; padding-top: 2mm; border-collapse: separate; direction: ' . ($is_arabic ? 'rtl' : 'ltr') . '; }
                .pdf-footer td { border: none; padding: 0; text-align: ' . ($is_arabic ? 'right' : 'left') . '; }
                .pdf-footer td.footer-right { text-align: ' . ($is_arabic ? 'left' : 'right') . '; }
                .pdf-footer a { color: #aaa; text-decoration: none; }
            ';

            $mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);

            // PAGE 1: COVER
            $cover_html = '<div class="cover-page">';
            
            if ( in_array( 'cover_title', $visible_elements ) ) {
                $cover_html .= '<div class="cover-title"><a href="' . esc_url($shortlink) . '">' . esc_html( $title ) . '</a></div>';
            }

            if ( in_array( 'cover_meta', $visible_elements ) ) {
                $authored_label = $is_arabic ? 'تأليف' : __( 'Authored by', 'wp-academic-post-enhanced' );
                $cover_html .= '<div class="cover-meta">
                    ' . $authored_label . ' <br>
                    <strong>' . esc_html( $author ) . '</strong> <br><br>
                    <span style="font-size: 11pt; color: #888;">' . esc_html( $date ) . '</span>
                </div>';
            }

            if ( in_array( 'cover_citation', $visible_elements ) ) {
                $citation_label = $is_arabic ? 'الاقتباس الموصى به' : __( 'Recommended Citation', 'wp-academic-post-enhanced' );
                $retrieved_label = $is_arabic ? 'تم الاسترجاع من' : 'Retrieved from';
                
                $cover_html .= '<div class="cover-citation-box">
                    <div class="citation-label">' . $citation_label . '</div>
                    <div class="citation-text">
                        ' . sprintf(
                            '%s (%s). %s. %s. ' . $retrieved_label . ' <a href="%s">%s</a>',
                            esc_html( $author ),
                            esc_html( get_the_date( 'Y', $post ) ),
                            '<em>' . esc_html( $title ) . '</em>',
                            esc_html( $site_name ),
                            esc_url( $shortlink ),
                            esc_html( $shortlink )
                        ) . '
                    </div>
                </div>';
            }
            
            $cover_html .= '</div>';

            $mpdf->WriteHTML($cover_html, \Mpdf\HTMLParserMode::HTML_BODY);

            // PAGE 2+: Header/Footer Setup
            if ( in_array( 'header', $visible_elements ) ) {
                $header_html = '
                <table class="pdf-header">
                    <tr>
                        <td class="header-title"><a href="' . esc_url($shortlink) . '">' . esc_html($title) . '</a></td>
                        <td class="header-page">{PAGENO}</td>
                    </tr>
                </table>';
                $mpdf->SetHTMLHeader($header_html);
            }
            
            if ( in_array( 'footer', $visible_elements ) ) {
                $footer_html = '<table class="pdf-footer"><tr><td><a href="' . esc_url($site_url) . '">' . esc_html($site_name) . '</a></td><td class="footer-right"><a href="' . esc_url($site_url) . '">' . esc_html($site_url_clean) . '</a></td></tr></table>';
                $mpdf->SetHTMLFooter($footer_html);
            }

            // PAGE 2+: CONTENT
            $mpdf->AddPage();
            $mpdf->WriteHTML($content, \Mpdf\HTMLParserMode::HTML_BODY);

            // Save to Cache
            $mpdf->Output( $cache_path, 'F' );

            // Serve the newly generated file
            $this->serve_pdf( $cache_path, $filename );
            exit;

        } catch ( \Mpdf\MpdfException $e ) {
            wp_die( 'PDF Error: ' . $e->getMessage() );
        }
    }
}

new WPA_PDF_Download();