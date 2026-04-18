<?php
/**
 * Field News Frontend Logic
 * Displays metadata box on news posts.
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPA_Field_News_Frontend {

    public function __construct() {
        add_filter( 'the_content', [ $this, 'inject_highlights' ], 5 );
        add_filter( 'the_content', [ $this, 'inject_discussion_section' ], 15 );
        add_filter( 'the_content', [ $this, 'inject_metadata_box' ], 20 );
        add_filter( 'the_content', [ $this, 'inject_related_courses' ], 40 );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ] );
        add_action( 'wp_head', [ $this, 'inject_custom_styles' ], 999 );
    }

    /**
     * Inject Discussion Questions
     */
    public function inject_discussion_section( $content ) {
        if ( ! is_singular( 'wpa_news' ) ) return $content;

        $questions = get_post_meta( get_the_ID(), '_wpa_news_discussion', true );
        if ( empty( $questions ) || ! is_array( $questions ) ) return $content;

        $label = WPA_Theme_Labels::get( 'news_discussion_title' );
        if ( empty($label) ) $label = __( 'Discussion & Critical Thinking', 'wp-academic-post-enhanced' );

        ob_start();
        ?>
        <div class="wpa-discussion-box">
            <h3 class="wpa-discussion-title"><?php echo WPA_Icons::get('clipboard'); ?> <?php echo esc_html( $label ); ?></h3>
            <ul class="wpa-discussion-list">
                <?php foreach ( $questions as $q ) : ?>
                    <li><?php echo esc_html( $q ); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
        $html = ob_get_clean();
        return $content . $html;
    }

    /**
     * Inject AI Highlights at the top
     */
    public function inject_highlights( $content ) {
        if ( ! is_singular( 'wpa_news' ) ) return $content;

        $highlights = get_post_meta( get_the_ID(), '_wpa_news_highlights', true );
        if ( empty( $highlights ) || ! is_array( $highlights ) ) return $content;

        $label = WPA_Theme_Labels::get( 'news_highlights_title' );
        if ( empty($label) ) $label = __( 'Key Highlights', 'wp-academic-post-enhanced' );

        ob_start();
        ?>
        <div class="wpa-news-highlights-box">
            <div class="wpa-highlights-header">
                <?php echo WPA_Icons::get('star'); ?>
                <span><?php echo esc_html( $label ); ?></span>
            </div>
            <ul class="wpa-highlights-list">
                <?php foreach ( $highlights as $item ) : ?>
                    <li><?php echo esc_html( $item ); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
        $html = ob_get_clean();
        return $html . $content;
    }

    /**
     * Inject Related Courses at the bottom
     */
    public function inject_related_courses( $content ) {
        if ( ! is_singular( 'wpa_news' ) ) return $content;

        $tags = wp_get_post_tags( get_the_ID() );
        if ( empty( $tags ) ) return $content;

        $tag_ids = wp_list_pluck( $tags, 'term_id' );

        $courses = get_posts([
            'post_type' => 'wpa_course',
            'posts_per_page' => 3,
            'tag__in' => $tag_ids,
            'post__not_in' => [ get_the_ID() ]
        ]);

        if ( empty( $courses ) ) return $content;

        $label = WPA_Theme_Labels::get( 'news_related_courses_title' );
        if ( empty($label) ) $label = __( 'Deepen Your Knowledge', 'wp-academic-post-enhanced' );

        ob_start();
        ?>
        <div class="wpa-related-courses-section">
            <h3 class="wpa-related-title"><?php echo esc_html( $label ); ?></h3>
            <div class="wpa-related-courses-grid">
                <?php foreach ( $courses as $course ) : ?>
                    <a href="<?php echo get_permalink( $course->ID ); ?>" class="wpa-course-card-mini">
                        <?php if ( has_post_thumbnail( $course->ID ) ) : ?>
                            <div class="wpa-course-card-thumb">
                                <?php echo get_the_post_thumbnail( $course->ID, 'thumbnail' ); ?>
                            </div>
                        <?php endif; ?>
                        <div class="wpa-course-card-info">
                            <span class="wpa-course-card-title"><?php echo esc_html( $course->post_title ); ?></span>
                            <span class="wpa-course-card-meta"><?php echo esc_html( WPA_Theme_Labels::get( 'news_academic_course' ) ); ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        $html = ob_get_clean();
        return $content . $html;
    }

    public function inject_custom_styles() {
        // Use Global Theme Settings
        $theme_options = get_option( 'wpa_homepage_settings', [] );
        $options = get_option( 'wpa_field_news_settings', [] ); // Fallback
        
        $should_inject = false;
        
        // 1. Standard News Check
        if ( is_singular( 'wpa_news' ) && ! empty( $theme_options['enable_news_template'] ) ) {
            $should_inject = true;
        }
        
        // 2. Global Styling Check (Legacy support or if theme allows)
        if ( ! $should_inject && ! empty( $options['apply_style_globally'] ) ) {
            $allowed_types = isset( $options['global_post_types'] ) ? explode( ',', $options['global_post_types'] ) : [];
            $allowed_types = array_map( 'trim', $allowed_types );
            
            if ( empty( $allowed_types ) && is_singular() ) {
                $should_inject = true;
            } elseif ( is_singular() && in_array( get_post_type(), $allowed_types ) ) {
                $should_inject = true;
            }
        }

        if ( ! $should_inject ) return;

        // Map to Theme Settings
        $bg = ! empty( $theme_options['news_bg_color'] ) ? $theme_options['news_bg_color'] : '#ffffff';
        $font_size = ! empty( $theme_options['news_font_size'] ) ? $theme_options['news_font_size'] : '18';

        echo '<style>
            body.wpa-custom-template:not(.wpa-dark-mode) {
                background-color: ' . esc_attr( $bg ) . ' !important;
            }
            
            /* Article Typography */
            .wpa-article-title { font-family: var(--wpa-font-heading); font-size: 2.5rem; line-height: 1.2; margin-bottom: 10px; }
            .wpa-article-meta { opacity: 0.8; font-size: 0.9rem; margin-bottom: 30px; }
            
            .wpa-article-content { 
                font-family: var(--wpa-font-body);
                font-size: var(--wpa-font-size-body); 
                line-height: var(--wpa-line-height-body);
                text-align: justify; 
            }
            .wpa-article-content p { margin-bottom: 1.5em; }
            .wpa-article-content h2 { font-size: 1.8em; margin-top: 2em; margin-bottom: 0.5em; }
            .wpa-article-content h3 { font-size: 1.4em; margin-top: 1.5em; margin-bottom: 0.5em; }
            
            /* Image Styles */
            .wpa-featured-image img { width: 100%; height: auto; border-radius: var(--wpa-radius-lg) !important; margin-bottom: 30px; }

            /* UNIFIED COMPONENT: Card System
             * Applies to Highlights, Discussion, Study Metadata, Citation Box
             */
            .wpa-news-highlights-box,
            .wpa-discussion-box,
            .wpa-news-meta-box,
            .wpa-citation-container {
                background: var(--wpa-bg-white, #fff);
                border: var(--wpa-border);
                border-radius: var(--wpa-radius-lg);
                padding: 30px;
                margin-bottom: 40px;
                box-shadow: var(--wpa-shadow);
            }

            /* UNIFIED COMPONENT: Section Titles */
            .wpa-highlights-header,
            .wpa-discussion-title,
            .wpa-meta-title,
            .wpa-feature-box-title {
                display: flex;
                align-items: center;
                gap: 12px;
                font-size: 1.25rem !important;
                font-weight: 700 !important;
                margin: 0 0 20px 0 !important;
                color: var(--wpa-text-main);
                border: none !important;
                padding: 0 !important;
                line-height: 1.2;
                text-transform: none; /* Reset distinct casing */
                letter-spacing: normal;
            }

            .wpa-highlights-header .wpa-icon,
            .wpa-discussion-title .wpa-icon {
                color: var(--wpa-accent);
                width: 24px;
                height: 24px;
            }

            /* UNIFIED COMPONENT: Lists (Highlights & Discussion) */
            .wpa-highlights-list,
            .wpa-discussion-list {
                list-style: none !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .wpa-highlights-list li,
            .wpa-discussion-list li {
                position: relative;
                padding-left: 30px;
                margin-bottom: 15px;
                line-height: 1.6;
                font-size: 1.05rem;
                color: var(--wpa-text-main);
                font-style: normal; /* Reset italic from discussion */
            }

            /* Bullet/Icon Styling */
            .wpa-highlights-list li::before { content: "→"; position: absolute; left: 0; color: var(--wpa-accent); font-weight: bold; }
            .wpa-discussion-list li::before { content: "Q:"; position: absolute; left: 0; color: var(--wpa-accent); font-weight: 800; }

            /* RTL Adjustments for Lists */
            body.rtl .wpa-highlights-list li,
            body.rtl .wpa-discussion-list li { padding-left: 0; padding-right: 30px; }
            body.rtl .wpa-highlights-list li::before { content: "←"; left: auto; right: 0; }
            body.rtl .wpa-discussion-list li::before { left: auto; right: 0; }

            /* UNIFIED COMPONENT: Badges */
            .wpa-badge { 
                display: inline-flex; 
                align-items: center; 
                gap: 6px; 
                padding: 4px 12px; 
                border-radius: var(--wpa-radius-pill, 999px); 
                font-size: 0.75rem; 
                font-weight: 600; 
                text-transform: uppercase; 
                line-height: 1;
            }
            .wpa-badge-type { background: var(--wpa-bg-light); color: var(--wpa-text-muted); border: 1px solid var(--wpa-border-color); }
            .wpa-badge-oa { background: #dcfce7; color: #14532d; border: 1px solid #bbf7d0; }
            .wpa-badge-strength { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
            .wpa-badge-strength .wpa-icon { color: #d97706; }

            /* Study Box Specific Overrides (Inner Layout) */
            .wpa-news-meta-box { padding: 0 !important; overflow: hidden; }
            .wpa-news-meta-box .wpa-box-header { padding: 25px 30px 15px; background: var(--wpa-bg-light); border-bottom: var(--wpa-border); }
            .wpa-news-meta-box .wpa-box-body { padding: 30px; }
            .wpa-news-meta-box .wpa-box-footer { padding: 20px 30px; background: var(--wpa-bg-light); border-top: var(--wpa-border); }

            /* Related Courses Section */
            .wpa-related-courses-section { margin-top: 60px; padding-top: 40px; border-top: 2px solid var(--wpa-border-color); }
            .wpa-related-title { font-size: 1.5rem; margin-bottom: 25px; font-weight: 700; }
            .wpa-related-courses-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
            
            .wpa-course-card-mini { display: flex; gap: 15px; background: var(--wpa-bg-white); border: var(--wpa-border); padding: 15px; border-radius: 12px; text-decoration: none !important; color: inherit !important; transition: all 0.2s; }
            .wpa-course-card-mini:hover { border-color: var(--wpa-accent); transform: translateY(-2px); box-shadow: var(--wpa-shadow-hover); }
            .wpa-course-card-thumb img { width: 60px; height: 60px; border-radius: 8px; object-fit: cover; }
            .wpa-course-card-info { display: flex; flex-direction: column; justify-content: center; }
            .wpa-course-card-title { font-weight: 600; font-size: 0.95rem; line-height: 1.3; margin-bottom: 4px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
            .wpa-course-card-meta { font-size: 0.75rem; opacity: 0.6; text-transform: uppercase; letter-spacing: 0.05em; }

            /* Footer Actions */
            .wpa-footer-actions-row { display: flex; justify-content: space-between; align-items: center; width: 100%; flex-wrap: wrap; gap: 10px; }
            .wpa-doi-actions { margin-top: 10px; }

            /* Footer */
            .wpa-news-footer { margin-top: 60px; padding: 60px 0; border-top: 1px solid var(--wpa-border-color, rgba(0,0,0,0.1)); font-size: 0.9rem; opacity: 0.7; text-align: center; }
            
            /* Sidebar / Widget Details */
            .wpa-facts-list { list-style: none; margin: 0; padding: 20px; font-size: 0.9rem; }
            .wpa-facts-list li { margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid var(--wpa-border-color, #f3f4f6); display: flex; justify-content: space-between; }
            .wpa-facts-list li:last-child { margin-bottom: 0; padding-bottom: 0; border-bottom: none; }
            
            .wpa-author-profile { padding: 20px; display: flex; align-items: center; gap: 15px; }
            .wpa-author-avatar { width: 50px; height: 50px; background: var(--wpa-bg-light, #f3f4f6); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: var(--wpa-text-muted, #9ca3af); }
            .wpa-author-avatar .wpa-icon { width: 24px; height: 24px; }
            
            .wpa-author-details { font-size: 0.9rem; line-height: 1.3; }
            .wpa-author-org { display: block; font-size: 0.8rem; opacity: 0.7; margin-top: 2px; }
            
            /* Icons */
            .wpa-news-meta-box .wpa-icon { width: 1.1em; height: 1.1em; vertical-align: -0.2em; opacity: 0.7; }
            .wpa-badge .wpa-icon { width: 14px; height: 14px; margin-right: 4px; vertical-align: middle; opacity: 1; }
            .wpa-orcid-icon { color: #a3e635; margin-left: 5px; }
            .wpa-orcid-icon .wpa-icon { width: 16px; height: 16px; vertical-align: middle; opacity: 1; }
            .wpa-citation-box .wpa-icon { font-size: 24px; width: 24px; height: 24px; margin-top: 2px; opacity: 0.4; }
            .wpa-btn .wpa-icon { width: 18px; height: 18px; margin-left: 8px; vertical-align: middle; opacity: 1; }
            
            /* ToC Widget */
            .wpa-toc-list { list-style: none; margin: 0; padding: 0; }
            .wpa-toc-list li { border-bottom: 1px solid var(--wpa-border-color, #f3f4f6); }
            .wpa-toc-list li:last-child { border-bottom: none; }
            .wpa-toc-list a { display: block; padding: 10px 20px; text-decoration: none; color: var(--wpa-text-muted, #4b5563); font-size: 0.9rem; transition: all 0.2s; }
            .wpa-toc-list a:hover { background: var(--wpa-bg-light, #f9fafb); color: var(--wpa-text-main, #111827); padding-left: 25px; }
            
            /* General Widget Content */
            .wpa-widget ul { padding: 0; margin: 0; list-style: none; }
            .wpa-widget li { padding: 10px 20px; border-bottom: 1px solid var(--wpa-border-color, #f3f4f6); }
            .wpa-widget li:last-child { border-bottom: none; }
            .wpa-widget a { text-decoration: none; color: inherit; }
            .wpa-widget a:hover { color: inherit; }
            
            /* RTL Support */
            body.rtl .wpa-toc-list a:hover { padding-left: 20px; padding-right: 25px; }
            
            /* Tags */
            .wpa-tags-list { margin-top: 40px; }
        </style>';
    }

    public function enqueue_styles() {
        // Styles are now part of the unified wpa-core-css in theme.php
    }

    public static function get_label( $key ) {
        $map = [
            'journal' => 'news_journal',
            'published' => 'news_published',
            'authors' => 'news_authors',
            'institution' => 'news_institution',
            'citations' => 'news_citations',
            'keywords' => 'news_keywords',
            'type' => 'news_type',
            'open_access' => 'news_open_access',
            'view_full' => 'news_view_full',
            'download_pdf' => 'news_download_pdf',
            'about_title' => 'news_about_title',
            'citation_text' => 'news_citation_text',
            'toc_title' => 'news_toc_title',
            'facts_title' => 'news_facts_title',
            'author_title_sidebar' => 'news_author_title_sidebar',
            'share_title' => 'news_share_title',
            'latest_news' => 'news_latest',
            'year' => 'news_year',
            'read_time' => 'news_read_time',
            'back_to_home' => 'news_back_to_home',
            'follow_google_news' => 'news_follow_google',
        ];

        $label_key = isset( $map[$key] ) ? $map[$key] : $key;
        return WPA_Theme_Labels::get( $label_key );
    }

    public function inject_metadata_box( $content ) {
        if ( ! is_singular( 'wpa_news' ) ) {
            return $content;
        }

        $theme_options = get_option( 'wpa_homepage_settings' );
        $options = get_option( 'wpa_field_news_settings' );
        
        // Prioritize Theme Settings for "Enable" if it exists there, otherwise fallback or assume enabled if template is active
        $enabled = isset( $theme_options['news_show_author_box'] ) ? $theme_options['news_show_author_box'] : ( isset( $options['meta_box_enable'] ) ? $options['meta_box_enable'] : 0 );

        if ( ! $enabled ) {
            return $content;
        }

        $post_id = get_the_ID();
        $meta = get_post_meta( $post_id, '_wpa_news_metadata', true );

        if ( empty( $meta ) ) {
            return $content;
        }

        // Build the box
        $title = ! empty( $theme_options['news_meta_box_title'] ) ? $theme_options['news_meta_box_title'] : ( ! empty( $options['meta_box_title'] ) ? $options['meta_box_title'] : $this->get_label('about_title') );
        $position = isset( $theme_options['news_meta_box_position'] ) ? $theme_options['news_meta_box_position'] : ( isset( $options['meta_box_position'] ) ? $options['meta_box_position'] : 'after' );

        // Visibility Checks (Theme Settings -> Field News Settings Fallback)
        $show_journal = isset( $theme_options['news_show_meta_journal'] ) ? $theme_options['news_show_meta_journal'] : ( isset( $options['show_meta_journal'] ) ? $options['show_meta_journal'] : 0 );
        $show_logo    = isset( $theme_options['news_show_journal_logo'] ) ? $theme_options['news_show_journal_logo'] : ( isset( $options['show_meta_journal_logo'] ) ? $options['show_meta_journal_logo'] : 0 );
        $show_date    = isset( $theme_options['news_show_meta_date'] ) ? $theme_options['news_show_meta_date'] : ( isset( $options['show_meta_date'] ) ? $options['show_meta_date'] : 0 );
        $show_authors = isset( $theme_options['news_show_meta_authors'] ) ? $theme_options['news_show_meta_authors'] : ( isset( $options['show_meta_authors'] ) ? $options['show_meta_authors'] : 0 );
        $show_affil   = isset( $theme_options['news_show_meta_affiliations'] ) ? $theme_options['news_show_meta_affiliations'] : ( isset( $options['show_meta_affiliations'] ) ? $options['show_meta_affiliations'] : 0 );
        $show_type    = isset( $theme_options['news_show_meta_type'] ) ? $theme_options['news_show_meta_type'] : ( isset( $options['show_meta_type'] ) ? $options['show_meta_type'] : 0 );
        $show_keys    = isset( $theme_options['news_show_meta_keywords'] ) ? $theme_options['news_show_meta_keywords'] : ( isset( $options['show_meta_keywords'] ) ? $options['show_meta_keywords'] : 0 );
        $show_oa      = isset( $theme_options['news_show_meta_openaccess'] ) ? $theme_options['news_show_meta_openaccess'] : ( isset( $options['show_meta_openaccess'] ) ? $options['show_meta_openaccess'] : 0 );
        $show_doi     = isset( $theme_options['news_show_meta_doi'] ) ? $theme_options['news_show_meta_doi'] : ( isset( $options['show_meta_doi'] ) ? $options['show_meta_doi'] : 0 );
        $show_sdgs    = isset( $theme_options['news_show_meta_sdgs'] ) ? $theme_options['news_show_meta_sdgs'] : ( isset( $options['show_meta_sdgs'] ) ? $options['show_meta_sdgs'] : 0 );
        $show_cites   = isset( $theme_options['news_show_meta_citations'] ) ? $theme_options['news_show_meta_citations'] : ( isset( $options['show_meta_citations'] ) ? $options['show_meta_citations'] : 0 );
        $show_concepts = isset( $theme_options['news_show_meta_concepts'] ) ? $theme_options['news_show_meta_concepts'] : ( isset( $options['show_meta_concepts'] ) ? $options['show_meta_concepts'] : 0 );

        ob_start();
        ?>
        <div class="wpa-news-meta-box wpa-unified-study-box">
            <!-- Header: Title & Badge -->
            <div class="wpa-box-header">
                <div class="wpa-header-main">
                    <h3 class="wpa-meta-title"><?php echo esc_html( $title ); ?></h3>
                    <div class="wpa-header-badges">
                        <?php 
                        $strength = get_post_meta( $post_id, '_wpa_news_evidence_strength', true );
                        if ( ! empty( $strength ) ) : ?>
                            <span class="wpa-badge wpa-badge-strength" title="<?php esc_attr_e( 'Reliability of Research Findings', 'wp-academic-post-enhanced' ); ?>"><?php echo WPA_Icons::get('shield'); ?> <?php echo esc_html( $strength ); ?></span>
                        <?php endif; ?>
                        <?php if ( $show_oa && ! empty($meta['openaccess']) && $meta['openaccess'] !== 'false' ) : ?>
                            <span class="wpa-badge wpa-badge-oa"><?php echo WPA_Icons::get('unlock'); ?> <?php echo esc_html( $this->get_label('open_access') ); ?></span>
                        <?php endif; ?>
                        <?php if ( $show_type && ! empty( $meta['type'] ) ) : ?>
                            <span class="wpa-badge wpa-badge-type"><?php echo esc_html( $meta['type'] ); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="wpa-box-body">
                <div class="wpa-study-grid">
                    <!-- Column 1: Source & Authors -->
                    <div class="wpa-study-col-main">
                        <?php if ( $show_journal && ! empty( $meta['publication'] ) ) : ?>
                            <div class="wpa-source-card compact">
                                <div class="wpa-journal-details">
                                    <span class="wpa-journal-name"><?php echo esc_html( $meta['publication'] ); ?></span>
                                    <div class="wpa-meta-subrow">
                                        <?php if ( $show_date && ! empty( $meta['date'] ) ) : ?>
                                            <span class="wpa-meta-date"><?php echo WPA_Icons::get('calendar-alt'); ?> <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $meta['date'] ) ) ); ?></span>
                                        <?php endif; ?>

                                        <?php if ( $show_cites && ! empty( $meta['citations'] ) && intval( $meta['citations'] ) > 0 ) : ?>
                                            <span class="wpa-dot-sep">&bull;</span>
                                            <span class="wpa-meta-cites"><?php echo WPA_Icons::get('star'); ?> <?php echo esc_html( $meta['citations'] ); ?> <?php echo esc_html( $this->get_label('citations') ); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Authors Section -->
                        <?php if ( $show_authors ) : ?>
                            <div class="wpa-authors-block">
                                <?php if ( ! empty( $meta['authors_list'] ) ) : ?>
                                    <div class="wpa-authors-list">
                                        <?php foreach ( array_slice($meta['authors_list'], 0, 5) as $auth ) : ?>
                                            <span class="wpa-author-item">
                                                <?php echo esc_html( $auth['name'] ); ?>
                                                <?php if ( ! empty($auth['orcid']) ) : ?>
                                                    <a href="<?php echo esc_url( $auth['orcid'] ); ?>" target="_blank" class="wpa-orcid-icon"><?php echo WPA_Icons::get('orcid'); ?></a>
                                                <?php endif; ?>
                                            </span>
                                        <?php endforeach; ?>
                                        <?php if (count($meta['authors_list']) > 5) echo '<span class="wpa-author-item">...</span>'; ?>
                                    </div>
                                    
                                    <?php
                                    // Extract Countries
                                    $countries = [];
                                    foreach ( $meta['authors_list'] as $auth ) {
                                        if ( ! empty( $auth['country'] ) ) $countries[] = $auth['country'];
                                    }
                                    $countries = array_unique( $countries );
                                    
                                    if ( ! empty( $countries ) && $show_affil ) : ?>
                                        <div class="wpa-affiliations-list">
                                            <?php echo WPA_Icons::get('building'); ?>
                                            <span><?php echo implode( ', ', array_map( 'esc_html', $countries ) ); ?></span>
                                        </div>
                                    <?php endif; ?>

                                <?php elseif ( ! empty( $meta['creator'] ) ) : ?>
                                    <span class="wpa-author-simple"><?php echo esc_html( $meta['creator'] ); ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Column 2: Quick Citation -->
                    <div class="wpa-study-col-side">
                        <div class="wpa-quick-citation-box">
                            <span class="wpa-section-label"><?php echo esc_html( $this->get_label('citation_text') ); ?></span>
                            <?php 
                            // Compact Citation Preview (Using Original Study Author)
                            $cite_title = ! empty( $meta['title'] ) ? $meta['title'] : get_the_title();
                            $cite_year = ! empty( $meta['date'] ) ? substr( $meta['date'], 0, 4 ) : get_the_date('Y');
                            
                            $author_str = '';
                            if ( ! empty( $meta['authors_list'][0]['name'] ) ) {
                                $author_str = $meta['authors_list'][0]['name'];
                                if ( count($meta['authors_list']) > 1 ) $author_str .= ' et al.';
                            } elseif ( ! empty( $meta['creator'] ) ) {
                                $author_str = $meta['creator'];
                            } else {
                                $author_str = get_the_author(); // Final fallback
                            }

                            $citation_preview = esc_html($author_str) . ' (' . esc_html($cite_year) . '). ' . esc_html($cite_title);
                            ?>
                            <div class="wpa-citation-preview">
                                <code><?php echo $citation_preview; ?></code>
                            </div>
                            
                            <?php if ( $show_doi && ! empty( $meta['doi'] ) ) : ?>
                                <div class="wpa-doi-actions">
                                    <a href="https://doi.org/<?php echo esc_attr( $meta['doi'] ); ?>" target="_blank" class="wpa-btn wpa-btn-text wpa-btn-sm">
                                        DOI: <?php echo esc_html( $meta['doi'] ); ?> <?php echo WPA_Icons::get('external'); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Topics & Concepts (Compact) -->
                <?php if ( $show_concepts && (! empty($meta['oa_concepts']) || ! empty($meta['sdgs'])) ) : ?>
                    <div class="wpa-study-footer-meta compact">
                        <div class="wpa-concepts-row">
                            <?php if ( ! empty( $meta['oa_concepts'] ) ) : ?>
                                <?php foreach ( array_slice($meta['oa_concepts'], 0, 4) as $c ) : ?>
                                    <span class="wpa-tag wpa-tag-concept"><?php echo esc_html( trim( $c ) ); ?></span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            
                            <?php if ( ! empty( $meta['sdgs'] ) ) : ?>
                                <?php foreach ( array_slice($meta['sdgs'], 0, 3) as $sdg ) : ?>
                                    <span class="wpa-tag wpa-tag-sdg" title="<?php echo esc_attr( $sdg['name'] ); ?>"><?php echo esc_html( $sdg['name'] ); ?></span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Footer Actions (Compact) -->
            <div class="wpa-box-footer">
                <div class="wpa-footer-actions-row">
                    <div class="wpa-footer-actions-left">
                        <?php if ( ! empty( $meta['original_abstract'] ) ) : ?>
                            <details class="wpa-abstract-details-compact">
                                <summary class="wpa-btn wpa-btn-primary wpa-btn-sm wpa-abstract-btn">
                                    <?php echo WPA_Icons::get('text-page'); ?> <?php echo WPA_Theme_Labels::get('news_read_original'); ?>
                                </summary>
                                <div class="wpa-raw-abstract-overlay">
                                    <?php echo wp_kses_post( $meta['original_abstract'] ); ?>
                                </div>
                            </details>
                        <?php endif; ?>
                    </div>
                    <div class="wpa-footer-actions-right">
                        <?php 
                        $pdf_id = get_post_meta( $post_id, '_wpa_news_pdf_id', true );
                        if ( $pdf_id ) {
                            $pdf_url = wp_get_attachment_url( $pdf_id );
                            if ( $pdf_url ) {
                                echo '<a href="' . esc_url( $pdf_url ) . '" target="_blank" class="wpa-btn wpa-btn-outline wpa-btn-sm">';
                                echo WPA_Icons::get('pdf') . ' ' . esc_html( $this->get_label('download_pdf') );
                                echo '</a>';
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
        $box_html = ob_get_clean();

        if ( $position === 'before' ) {
            return $box_html . $content;
        } elseif ( $position === 'bottom' ) {
            return $content . $box_html;
        } else {
            return $content . $box_html;
        }
    }
}

new WPA_Field_News_Frontend();