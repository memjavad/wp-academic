<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Main Schema Generation Class
 * Handles the logic for generating and outputting JSON-LD.
 */
class WP_Academic_Post_Enhanced_Schema {

    private static $instance;

    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action( 'wp_head', [ $this, 'output_schema' ] );
        add_action( 'wp_head', [ $this, 'output_sitewide_schema' ], 99 );
        add_action( 'wp_head', [ $this, 'output_breadcrumbs_schema' ], 100 );
        add_action( 'wp_head', [ $this, 'output_toc_schema' ], 101 );
        add_action( 'wp_head', [ $this, 'output_google_scholar_tags' ] );
        add_action( 'wp_head', [ $this, 'output_hreflang_tags' ], 105 );
        
        // OpenGraph Type Filters for Yoast & RankMath
        add_filter( 'wpseo_opengraph_type', [ $this, 'filter_og_type' ] );
        add_filter( 'rank_math/opengraph/facebook/type', [ $this, 'filter_og_type' ] );
    }

    /**
     * Set og:type to "article" for Glossary Terms and Lessons.
     */
    public function filter_og_type( $type ) {
        if ( is_singular( [ 'wpa_glossary', 'wpa_lesson' ] ) ) {
            return 'article';
        }
        return $type;
    }

    /**
     * Output hreflang tags for international SEO targeting subdomains.
     */
    public function output_hreflang_tags() {
        $current_url = home_url( add_query_arg( [], $GLOBALS['wp']->request ) );
        $variants = [
            'ar' => 'https://arabpsychology.com',
            'es' => 'https://spanish.arabpsychology.com',
            'tr' => 'https://tr-scales.arabpsychology.com',
            'en' => 'https://scales.arabpsychology.com',
        ];

        // Only output if we are on the main domain (ar)
        if ( strpos( $current_url, 'arabpsychology.com' ) !== false && strpos( $current_url, 'www.' ) === false && strpos( $current_url, '//' ) !== false ) {
            // Note: In a production environment, we should only point to translated versions 
            // of the specific post, but for site-level authority, root mapping is a safe fallback.
            echo "\n" . '<!-- Academic Post hreflang -->' . "\n";
            foreach ( $variants as $lang => $url ) {
                echo '<link rel="alternate" hreflang="' . esc_attr( $lang ) . '" href="' . esc_url( $url ) . '" />' . "\n";
            }
            echo '<link rel="alternate" hreflang="x-default" href="' . esc_url( $variants['ar'] ) . '" />' . "\n";
        }
    }

    /**
     * Output TOC ItemList Schema
     */
    public function output_toc_schema() {
        if ( ! is_singular() ) return;

        $options = get_option( 'wpa_toc_settings' );
        $defaults = [
            'enabled' => true,
            'post_types' => ['post', 'wpa_course', 'wpa_lesson', 'wpa_news'],
        ];
        $options = wp_parse_args( $options, $defaults );

        if ( ! $options['enabled'] || ! in_array( get_post_type(), $options['post_types'] ) ) {
            return;
        }

        // Use centralized engine
        if ( class_exists( 'WPA_TOC_Engine' ) ) {
            $content = get_post_field( 'post_content', get_the_ID() );
            $engine = new WPA_TOC_Engine( $content, $options );
            $schema = $engine->get_schema_data();

            if ( ! empty( $schema ) && ! empty( $schema['itemListElement'] ) ) {
                $this->echo_schema( $schema );
            }
        }
    }

    /**
     * Helper: Encode and Output Schema
     */
    private function echo_schema( $data ) {
        if ( empty( $data ) ) return;
        
        $json = wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
        
        // Final safety check: remove any leading spaces in keys that might have been introduced
        // Using preg_replace to catch " @context", "  @type", etc.
        $json = preg_replace( '/"\s+@/', '"@', $json );
        
        echo "\n<!-- WPA Schema Validated -->\n";
        echo '<script type="application/ld+json">' . $json . '</script>' . "\n";
    }

    /**
     * Main Output Function for Single Posts
     */
    public function output_schema() {
        if ( ! is_single() ) return;
        
        $enabled = get_option( 'wp_academic_post_enhanced_schema_enabled', true );
        if ( ! $enabled ) return;

        $post_id = get_the_ID();
        $schema = $this->build_post_schema( $post_id );

        if ( ! empty( $schema ) ) {
            $this->echo_schema( $schema );
        }

        // Generate FAQPage Schema if content supports it
        $this->output_faq_schema( $post_id );
    }

    /**
     * Generate FAQPage Schema from Content
     */
    private function output_faq_schema( $post_id ) {
        $post_type = get_post_type( $post_id );
        $allowed_types = ['post', 'page', 'wpa_news', 'wpa_lesson', 'wpa_course'];
        
        if ( ! in_array( $post_type, $allowed_types ) ) {
            return;
        }

        $content = get_post_field( 'post_content', $post_id );
        $post_title = get_the_title( $post_id );
        
        // Improved regex to find H2/H3/H4 that are followed by text
        // Looks for a header tag and captures the content until the next header or end of content
        if ( preg_match_all( '/<(h[234])[^>]*>(.*?)<\/\1>(.*?)(?=<h[234]|$)/is', $content, $matches, PREG_SET_ORDER ) ) {
            $faq_items = [];
            
            foreach ( $matches as $match ) {
                $question = trim( strip_tags( $match[2] ) );
                $answer   = trim( strip_tags( $match[3] ) );
                
                // Only include if both question and answer are present and substantive
                if ( ! empty( $question ) && ! empty( $answer ) && strlen( $answer ) > 15 ) {
                    // Heuristic: Check if it looks like a question (ends with ? or starts with common question words)
                    $is_likely_question = false;
                    if ( str_ends_with( $question, '?' ) || preg_match( '/^(what|how|why|when|where|who|is|can|are|do|does)/i', $question ) ) {
                        $is_likely_question = true;
                    }
                    
                    // For Academic posts and Lessons, we also accept descriptive headings as FAQ entries
                    if ( $is_likely_question || in_array( $post_type, ['wpa_news', 'wpa_lesson', 'wpa_course'] ) ) {
                        $faq_items[] = [
                            '@type' => 'Question',
                            'name'  => $question . ' ' . __( 'of', 'wp-academic-post-enhanced' ) . ' ' . $post_title,
                            'acceptedAnswer' => [
                                '@type' => 'Answer',
                                'text'  => $answer
                            ]
                        ];
                    }
                }
            }

            if ( ! empty( $faq_items ) ) {
                $faq_schema = [
                    '@context' => 'https://schema.org',
                    '@type'    => 'FAQPage',
                    'mainEntity' => $faq_items
                ];
                $this->echo_schema( $faq_schema );
            }
        }
    }

    /**
     * Build the schema array for a specific post.
     */
    private function build_post_schema( $post_id ) {
        $post = get_post( $post_id );
        $post_type = get_post_type( $post_id );
        
        $mapping = get_option( 'wp_academic_post_enhanced_schema_post_type_mapping', [] );
        $defaults = [
            'wpa_course' => 'Course',
            'wpa_news'   => 'ScholarlyArticle',
            'wpa_lesson' => 'ScholarlyArticle',
            'wpa_glossary' => 'DefinedTerm',
        ];
        $mapping = wp_parse_args( $mapping, $defaults );
        
        $schema_type = isset( $mapping[ $post_type ] ) && ! empty( $mapping[ $post_type ] ) ? $mapping[ $post_type ] : 'Article';

        // Check for Scholarly Settings (Add Book as secondary type)
        $scholarly_options = get_option( 'wp_academic_post_enhanced_schema_scholarly_settings', [] );
        $pt_settings = isset( $scholarly_options[ $post_type ] ) ? $scholarly_options[ $post_type ] : [];
        
        if ( defined('WP_DEBUG') && WP_DEBUG ) {
            error_log( sprintf( 'WPA Schema Debug: Post ID %d, Post Type %s, Mapping Type %s, Add Book Type %s', 
                $post_id, 
                $post_type, 
                $schema_type, 
                isset($pt_settings['add_book_type']) ? ($pt_settings['add_book_type'] ? 'Yes' : 'No') : 'Not Set' 
            ) );
        }

        $final_type = $schema_type;
        if ( $schema_type === 'ScholarlyArticle' && ! empty( $pt_settings['add_book_type'] ) ) {
            $final_type = ['ScholarlyArticle', 'Book'];
        }

        // 1. Initialize Base Schema
        $schema = [
            '@context' => 'https://schema.org',
            '@type'    => $final_type,
            'name'     => get_the_title( $post_id ),
            'url'      => get_permalink( $post_id ),
            'description' => $this->get_description( $post ),
        ];

        // 2. Add Article-Specific Properties (Only for CreativeWork types)
        $is_creative_work = ! in_array( $schema_type, ['DefinedTerm', 'Person', 'Organization', 'Product'] );
        
        if ( $is_creative_work || $schema_type === 'Course' ) {
            $schema['headline'] = get_the_title( $post_id );
            $schema['mainEntityOfPage'] = [
                '@type' => 'WebPage',
                '@id'   => get_permalink( $post_id ),
                'primaryImageOfPage' => $this->get_primary_image_schema( $post_id )
            ];
            $schema['datePublished'] = get_the_date( 'c', $post_id );
            $schema['dateModified']  = get_the_modified_date( 'c', $post_id );
            $schema['inLanguage']    = get_option( 'wp_academic_post_enhanced_schema_in_language', get_bloginfo( 'language' ) );
            $schema['author']        = $this->get_author_schema( $post->post_author );
            $schema['publisher']     = $this->get_publisher_schema();
            
            // For Courses, provider is often required/recommended alongside publisher
            if ( $schema_type === 'Course' ) {
                $schema['provider'] = $schema['publisher'];
            }

            // Images
            $image_data = $this->get_primary_image_schema( $post_id );
            if ( $image_data ) {
                $schema['image'] = $image_data;
                $schema['thumbnailUrl'] = $image_data['url'];
            } else {
                $default_img = get_option( 'wp_academic_post_enhanced_schema_default_image', '' );
                if ( ! empty( $default_img ) ) {
                    $schema['image'] = [
                        '@type' => 'ImageObject',
                        'url'   => $default_img,
                    ];
                    $schema['thumbnailUrl'] = $default_img;
                }
            }

            // Article Body & Comments
            if ( in_array( $schema_type, ['Article', 'ScholarlyArticle', 'TechArticle', 'NewsArticle', 'MedicalScholarlyArticle'] ) ) {
                $schema['articleBody'] = wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
                $schema['commentCount'] = (int) get_comments_number( $post_id );
                
                $comments = $this->get_comments_schema( $post_id );
                if ( ! empty( $comments ) ) {
                    $schema['comment'] = $comments;
                }
            }
        }

        // 3. Automatic DOI Discovery (Valid for CreativeWork)
        if ( $is_creative_work ) {
            $doi = $this->extract_doi( $post->post_content );
            if ( $doi ) {
                $schema['sameAs'] = 'https://doi.org/' . $doi;
            }
        }

        // 4. Specific Enrichments
        if ( in_array( $schema_type, ['ScholarlyArticle', 'TechArticle', 'Thesis', 'MedicalScholarlyArticle', 'NewsArticle', 'Book'] ) ) {
            $schema = $this->enrich_scholarly_article( $schema, $post_id, $post_type );
        }

        if ( $schema_type === 'Course' ) {
            $schema = $this->enrich_course( $schema, $post_id );
        }

        if ( $post_type === 'wpa_lesson' ) {
            $schema = $this->enrich_lesson( $schema, $post_id );
        }

        if ( $schema_type === 'DefinedTerm' ) {
            $schema = $this->enrich_defined_term( $schema, $post_id );
        }

        // Reviews / Rating
        $rating_enabled = get_option( 'wp_academic_post_enhanced_schema_rating_enabled', false );
        if ( $rating_enabled ) {
            $rating = $this->get_aggregate_rating( $post_id, $final_type );
            if ( $rating ) {
                $schema['aggregateRating'] = $rating;
            }
        }

        return array_filter( $schema );
    }

    /**
     * Enrich schema with academic specific properties.
     */
    private function enrich_scholarly_article( $schema, $post_id, $post_type ) {
        $scholarly_options = get_option( 'wp_academic_post_enhanced_schema_scholarly_settings', [] );
        $settings = isset( $scholarly_options[ $post_type ] ) ? $scholarly_options[ $post_type ] : [];

        // Keywords (from Tags)
        $tags = get_the_tags( $post_id );
        if ( $tags ) {
            $keywords = wp_list_pluck( $tags, 'name' );
            $schema['keywords'] = implode( ', ', $keywords );
        }

        // Topics (from Categories) -> about property
        $categories = get_the_category( $post_id );
        if ( $categories ) {
            $schema['about'] = [];
            foreach ( $categories as $cat ) {
                $schema['about'][] = [
                    '@type' => 'Thing',
                    'name'  => $cat->name,
                ];
            }
        }

        // Global Scholarly Settings
        $issn = get_option( 'wp_academic_post_enhanced_scholarly_issn', '' );
        $publication = get_option( 'wp_academic_post_enhanced_scholarly_is_part_of', get_bloginfo( 'name' ) );

        if ( ! empty( $publication ) ) {
            $schema['isPartOf'] = [
                '@type' => 'Periodical',
                'name'  => $publication,
            ];
            if ( ! empty( $issn ) ) {
                $schema['isPartOf']['issn'] = $issn;
            }
        }

        // Word Count
        if ( isset( $settings['wordCountSource'] ) && $settings['wordCountSource'] === 'automatic' ) {
            $content = get_post_field( 'post_content', $post_id );
            $schema['wordCount'] = str_word_count( strip_tags( $content ) );
        } elseif ( ! empty( $settings['wordCount'] ) ) {
            $schema['wordCount'] = absint( $settings['wordCount'] );
        }

        // Citations (Integration with Citation Module)
        $citations = [];
        // 1. From Manual Settings
        $manual_citations = get_option( 'wp_academic_post_enhanced_scholarly_citations', '' );
        if ( ! empty( $manual_citations ) ) {
            $citations = array_merge( $citations, explode( "\n", $manual_citations ) );
        }
        
        if ( ! empty( $citations ) ) {
            $schema['citation'] = array_map( 'trim', array_filter( $citations ) );
        }

        // --- Thesis Specific ---
        if ( isset($schema['@type']) && $schema['@type'] === 'Thesis' ) {
            if ( ! empty( $settings['inSupportOf'] ) ) {
                $schema['inSupportOf'] = $settings['inSupportOf'];
            }
        }

        // --- TechArticle Specific ---
        if ( isset($schema['@type']) && $schema['@type'] === 'TechArticle' ) {
            if ( ! empty( $settings['proficiencyLevel'] ) ) {
                $schema['proficiencyLevel'] = $settings['proficiencyLevel'];
            }
            if ( ! empty( $settings['dependencies'] ) ) {
                $schema['dependencies'] = $settings['dependencies'];
            }
        }

        // --- Field News Specific: Link to Source Research ---
        if ( $post_type === 'wpa_news' ) {
            $study_data = get_post_meta( $post_id, '_wpa_news_metadata', true );
            if ( ! empty( $study_data ) ) {
                $source_url = '';
                if ( ! empty( $study_data['doi'] ) ) {
                    $source_url = 'https://doi.org/' . $study_data['doi'];
                } elseif ( ! empty( $study_data['url'] ) ) {
                    $source_url = $study_data['url'];
                }

                if ( $source_url ) {
                    // Build a complete ScholarlyArticle object for the source research
                    $source_article = [
                        '@type'    => 'ScholarlyArticle',
                        'headline' => isset( $study_data['title'] ) ? $study_data['title'] : '',
                        'url'      => $source_url,
                    ];

                    // Add Authors if available
                    if ( ! empty( $study_data['authors_list'] ) && is_array( $study_data['authors_list'] ) ) {
                        $source_article['author'] = [];
                        foreach ( array_slice($study_data['authors_list'], 0, 5) as $auth ) {
                            $author_obj = [
                                '@type' => 'Person',
                                'name'  => $auth['name']
                            ];
                            if ( ! empty( $auth['orcid'] ) ) $author_obj['sameAs'] = $auth['orcid'];
                            $source_article['author'][] = $author_obj;
                        }
                    } elseif ( ! empty( $study_data['creator'] ) ) {
                        $source_article['author'] = [
                            '@type' => 'Person',
                            'name'  => $study_data['creator']
                        ];
                    }

                    // Add Publication Date
                    if ( ! empty( $study_data['date'] ) ) {
                        $source_article['datePublished'] = date( 'c', strtotime( $study_data['date'] ) );
                    }

                    // Add Journal as Publisher/Provider
                    if ( ! empty( $study_data['publication'] ) ) {
                        $source_article['publisher'] = [
                            '@type' => 'Organization',
                            'name'  => $study_data['publication']
                        ];
                    }

                    $schema['isBasedOn'] = $source_article;
                }
            }
        }

        return $schema;
    }

    /**
     * Enrich Course Schema
     */
    private function enrich_course( $schema, $post_id ) {
        $post_type = get_post_type( $post_id );
        $scholarly_options = get_option( 'wp_academic_post_enhanced_schema_scholarly_settings', [] );
        $settings = isset( $scholarly_options[ $post_type ] ) ? $scholarly_options[ $post_type ] : [];

        // provider is now handled in build_post_schema for consistency
        
        if ( ! empty( $settings['courseCode'] ) ) {
            $schema['courseCode'] = $settings['courseCode'];
        }

        if ( ! empty( $settings['courseMode'] ) ) {
            $schema['courseMode'] = $settings['courseMode'];
        }

        // Fetch Lessons for Syllabus
        $lessons = get_posts([
            'post_type'      => 'wpa_lesson',
            'posts_per_page' => -1,
            'meta_key'       => '_wpa_course_id',
            'meta_value'     => $post_id,
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
        ]);

        if ( ! empty( $lessons ) ) {
            $schema['hasCourseInstance'] = [
                '@type' => 'CourseInstance',
                'courseMode' => ! empty( $settings['courseMode'] ) ? $settings['courseMode'] : 'online',
            ];

            $syllabus = [];
            foreach ( $lessons as $lesson ) {
                $syllabus[] = [
                    '@type' => 'Syllabus',
                    'name'  => $lesson->post_title,
                    'description' => wp_trim_words( wp_strip_all_tags( $lesson->post_content ), 20 ),
                    'url' => get_permalink( $lesson->ID )
                ];
            }
            $schema['syllabusSections'] = $syllabus;
        }
        
        return $schema;
    }

    /**
     * Enrich Lesson Schema
     */
    private function enrich_lesson( $schema, $post_id ) {
        // Link to Parent Course
        $course_id = get_post_meta( $post_id, '_wpa_course_id', true );
        if ( ! empty( $course_id ) ) {
            $schema['isPartOf'] = [
                '@type' => 'Course',
                'name'  => get_the_title( $course_id ),
                'url'   => get_permalink( $course_id ),
                'courseCode' => get_post_meta( $course_id, '_wpa_course_code', true )
            ];
        }

        // Ensure type is appropriate if generic
        if ( isset($schema['@type']) && ($schema['@type'] === 'Article' || $schema['@type'] === 'ScholarlyArticle') ) {
            $schema['@type'] = 'LearningResource';
            $schema['learningResourceType'] = 'Lesson';
        }

        // Check for Quiz
        $quiz_questions = get_post_meta( $post_id, '_wpa_quiz_questions', true );
        if ( ! empty( $quiz_questions ) ) {
            $schema['hasPart'] = [
                '@type' => 'Quiz',
                'name' => get_the_title( $post_id ) . ' - Quiz',
                'learningResourceType' => 'Assessment',
                'interactionStatistic' => [
                    '@type' => 'InteractionCounter',
                    'interactionType' => 'https://schema.org/AssessAction'
                ]
            ];
        }

        return $schema;
    }

    /**
     * Enrich Glossary Term Schema
     */
    private function enrich_defined_term( $schema, $post_id ) {
        // definedTermSet is usually the glossary page
        $glossary_page_id = get_option( 'wpa_glossary_page_id' );
        if ( $glossary_page_id ) {
            $schema['inDefinedTermSet'] = [
                '@type' => 'DefinedTermSet',
                '@id'   => get_permalink( $glossary_page_id ),
                'name'  => get_the_title( $glossary_page_id )
            ];
        } else {
            // Fallback to site URL/glossary if no page set
            $schema['inDefinedTermSet'] = [
                '@type' => 'DefinedTermSet',
                '@id'   => home_url( '/glossary' ),
                'name'  => get_bloginfo( 'name' ) . ' Glossary'
            ];
        }

        // Add External Source link if available
        $external_url = get_post_meta( $post_id, 'wpa_glossary_custom_post_permalink', true );
        if ( ! empty( $external_url ) ) {
            $schema['sameAs'] = esc_url( $external_url );
        }
        
        $schema['termCode'] = $schema['name'];
        return $schema;
    }

    /**
     * Get Author Schema
     */
    private function get_author_schema( $author_id ) {
        $name = get_the_author_meta( 'display_name', $author_id );
        $url  = get_author_posts_url( $author_id );
        $desc = get_the_author_meta( 'description', $author_id );

        // Fallback for empty author name
        if ( empty( $name ) ) {
            $name = get_bloginfo( 'name' );
            $url  = home_url();
        }

        $author_data = [
            '@type' => 'Person',
            'name'  => $name,
            'url'   => $url,
        ];

        if ( ! empty( $desc ) ) {
            $author_data['description'] = $desc;
        }

        // Fetch Academic User Meta
        $academic_title = get_user_meta( $author_id, 'wpa_academic_title', true );
        $affiliation = get_user_meta( $author_id, 'wpa_academic_affiliation', true );
        $orcid = get_user_meta( $author_id, 'wpa_academic_orcid', true );
        $linkedin = get_user_meta( $author_id, 'wpa_academic_linkedin', true );

        if ( ! empty( $academic_title ) ) $author_data['jobTitle'] = $academic_title;
        
        $same_as = [];
        if ( ! empty( $orcid ) ) $same_as[] = $orcid;
        if ( ! empty( $linkedin ) ) $same_as[] = $linkedin;
        
        if ( ! empty( $same_as ) ) {
            $author_data['sameAs'] = ( count( $same_as ) === 1 ) ? $same_as[0] : $same_as;
        }

        if ( ! empty( $affiliation ) ) {
            $author_data['affiliation'] = [
                '@type' => 'Organization',
                'name'  => $affiliation
            ];
        }

        // Global Person Override (Only if no specific user meta is found, to prevent overriding specific authors)
        $global_person = get_option( 'wp_academic_post_enhanced_person_name', '' );
        if ( ! empty( $global_person ) && $name === $global_person && empty( $author_data['jobTitle'] ) ) {
            // Merge extra details from global settings
            $job = get_option( 'wp_academic_post_enhanced_person_jobtitle', '' );
            $affiliation_global = get_option( 'wp_academic_post_enhanced_person_affiliation_name', '' );
            
            if ( ! empty( $job ) ) $author_data['jobTitle'] = $job;
            if ( ! empty( $affiliation_global ) ) {
                $author_data['affiliation'] = [
                    '@type' => 'Organization',
                    'name'  => $affiliation_global
                ];
            }
        }

        return $author_data;
    }

    /**
     * Get Publisher Schema
     */
    private function get_publisher_schema() {
        $logo_url = get_option( 'wp_academic_post_enhanced_schema_publisher_logo', '' );
        
        // Fallback to Site Icon (Favicon) if set in WP
        if ( empty( $logo_url ) && function_exists( 'get_site_icon_url' ) ) {
            $logo_url = get_site_icon_url( 512 );
        }

        $publisher = [
            '@type' => 'Organization',
            'name'  => get_bloginfo( 'name' ),
            'url'   => home_url(),
        ];

        if ( ! empty( $logo_url ) ) {
            $publisher['logo'] = [
                '@type' => 'ImageObject',
                'url'   => $logo_url,
            ];
        }

        return $publisher;
    }

    /**
     * Helper: Get primary image object with dimensions
     */
    private function get_primary_image_schema( $post_id ) {
        $img_id = get_post_thumbnail_id( $post_id );
        $img_url = '';
        $width = 1200;
        $height = 675;
        $caption = '';

        if ( $img_id ) {
            $meta = wp_get_attachment_image_src( $img_id, 'full' );
            if ( $meta ) {
                $img_url = $meta[0];
                $width   = $meta[1];
                $height  = $meta[2];
            }
            $caption = wp_get_attachment_caption( $img_id );
            if ( empty( $caption ) ) {
                $caption = get_post_meta( $img_id, '_wp_attachment_image_alt', true );
            }
        } else {
            // Fallback to content scanning
            $post = get_post( $post_id );
            $first_img = $this->get_first_image_in_content( $post->post_content );
            if ( $first_img ) {
                $img_url = $first_img;
            }
        }

        if ( ! empty( $img_url ) ) {
            $data = [
                '@type' => 'ImageObject',
                'url'   => $img_url,
                'width' => $width,
                'height' => $height,
                'representativeOfPage' => 'http://schema.org/True'
            ];

            if ( ! empty( $caption ) ) {
                $data['caption'] = $caption;
            }

            return $data;
        }

        return null;
    }

    /**
     * Helper: Get comments schema
     */
    private function get_comments_schema( $post_id ) {
        $comments = get_comments([
            'post_id' => $post_id,
            'status'  => 'approve',
            'number'  => 5,
            'order'   => 'DESC'
        ]);

        if ( empty( $comments ) ) return [];

        $schema_comments = [];
        foreach ( $comments as $comment ) {
            $schema_comments[] = [
                '@type' => 'Comment',
                'author' => [
                    '@type' => 'Person',
                    'name'  => $comment->comment_author
                ],
                'dateCreated' => date( 'Y-m-d', strtotime( $comment->comment_date ) ),
                'text' => wp_strip_all_tags( $comment->comment_content )
            ];
        }

        return $schema_comments;
    }

    /**
     * Helper: Extract DOI from content
     */
    private function extract_doi( $content ) {
        if ( preg_match( '/10\.\d{4,9}\/[-._;()\/:A-Z0-9]+/i', $content, $matches ) ) {
            return $matches[0];
        }
        return false;
    }

    /**
     * Helper: Get first image URL from content
     */
    private function get_first_image_in_content( $content ) {
        if ( preg_match( '/<img.+?src=["\'](.+?)["\'].*?>/i', $content, $matches ) ) {
            return $matches[1];
        }
        return false;
    }

    /**
     * Get Aggregate Rating
     */
    private function get_aggregate_rating( $post_id, $schema_type ) {
        $val = get_option( 'wp_academic_post_enhanced_schema_rating_value', '5' );
        $count = get_option( 'wp_academic_post_enhanced_schema_rating_review_count', '1' );
        $item_type_override = get_option( 'wp_academic_post_enhanced_schema_rating_item_reviewed_type', '' );

        // Google Supported Types for Ratings
        $supported_types = [ 'Course', 'Book', 'Event', 'HowTo', 'LocalBusiness', 'Movie', 'Product', 'Recipe', 'SoftwareApplication', 'ScholarlyArticle', 'Thesis' ];

        // If the current type is NOT supported and there is no override, don't output rating to avoid Google errors.
        $check_type = is_array( $schema_type ) ? $schema_type : [ $schema_type ];
        $is_supported = false;
        foreach ( $check_type as $t ) {
            if ( in_array( $t, $supported_types ) ) {
                $is_supported = true;
                break;
            }
        }

        if ( ! $is_supported && empty( $item_type_override ) ) {
            return null;
        }

        $rating = [
            '@type' => 'AggregateRating',
            'ratingValue' => floatval( $val ),
            'reviewCount' => intval( $count ),
            'bestRating' => 5,
            'worstRating' => 1
        ];

        return $rating;
    }

    /**
     * Helper: Clean Description
     */
    private function get_description( $post ) {
        if ( has_excerpt( $post->ID ) ) {
            $desc = wp_strip_all_tags( strip_shortcodes( $post->post_excerpt ) );
        } else {
            $desc = wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ), 55 );
        }

        // Mandatory fallback
        if ( empty( $desc ) ) {
            if ( is_front_page() ) {
                $desc = get_bloginfo( 'description' );
                if ( empty( $desc ) ) {
                    $desc = 'المنصة الأكاديمية الرائدة في علوم علم النفس والتربية، نقدم أحدث الأبحاث والدورات التدريبية المتخصصة.';
                }
            } else {
                $desc = get_the_title( $post->ID );
            }
        }

        return $desc;
    }

    /**
     * Sitewide Schema (Front Page)
     */
    public function output_sitewide_schema() {
        if ( ! is_front_page() ) return;
        
        $enabled = get_option( 'wp_academic_post_enhanced_schema_enabled', true );
        if ( ! $enabled ) return;

        $type = get_option( 'wp_academic_post_enhanced_schema_sitewide_type', 'organization' );
        $data = [
            '@context' => 'https://schema.org',
            '@type'    => ( $type === 'person' ) ? 'Person' : 'Organization',
            'url'      => home_url( '/' ),
            'description' => get_bloginfo( 'description' ) ?: 'عرب سايكلوجي: المنصة الأكاديمية الأولى لعلم النفس والأبحاث العلمية في العالم العربي.',
        ];

        if ( $type === 'person' ) {
            $data['name'] = get_option( 'wp_academic_post_enhanced_schema_sitewide_person_name', '' );
        } else {
            $data['name'] = get_option( 'wp_academic_post_enhanced_schema_sitewide_organization_name', get_bloginfo( 'name' ) );
            $logo = get_option( 'wp_academic_post_enhanced_schema_sitewide_organization_logo', '' );
            if ( ! empty( $logo ) ) $data['logo'] = $logo;
        }

        // Social Profiles
        $socials = get_option( 'wp_academic_post_enhanced_schema_social_profiles', '' );
        if ( ! empty( $socials ) ) {
            $data['sameAs'] = array_map( 'trim', explode( "\n", $socials ) );
        }

        // Search Action
        if ( get_option( 'wp_academic_post_enhanced_schema_sitelinks_searchbox_enabled', true ) ) {
            $data['potentialAction'] = [
                '@type' => 'SearchAction',
                'target' => home_url( '/?s={search_term_string}' ),
                'query-input' => 'required name=search_term_string'
            ];
        }

        $this->echo_schema( $data );
    }

    /**
     * Output BreadcrumbList Schema
     */
    public function output_breadcrumbs_schema() {
        if ( ! is_singular() && ! is_archive() && ! is_home() ) return;
        
        $enabled = get_option( 'wp_academic_post_enhanced_schema_breadcrumbs_enabled', true );
        if ( ! $enabled ) return;

        $items = [];
        $position = 1;

        // Home
        $items[] = [
            '@type' => 'ListItem',
            'position' => $position++,
            'name' => __( 'Home', 'wp-academic-post-enhanced' ),
            'item' => home_url()
        ];

        // Archive or Post hierarchy
        if ( is_singular( 'wpa_lesson' ) ) {
            // Course Link
            $course_id = get_post_meta( get_the_ID(), '_wpa_course_id', true );
            if ( $course_id ) {
                $items[] = [
                    '@type' => 'ListItem',
                    'position' => $position++,
                    'name' => get_the_title( $course_id ),
                    'item' => get_permalink( $course_id )
                ];
            }
        } elseif ( is_singular( 'wpa_course' ) || is_singular( 'wpa_news' ) || is_singular( 'wpa_glossary' ) ) {
            // Post Type Archive
            $pt = get_post_type_object( get_post_type() );
            if ( $pt && $pt->has_archive ) {
                $items[] = [
                    '@type' => 'ListItem',
                    'position' => $position++,
                    'name' => $pt->label,
                    'item' => get_post_type_archive_link( get_post_type() )
                ];
            }
        } elseif ( is_post_type_archive() ) {
             // Archive Title
             $items[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => post_type_archive_title( '', false )
            ];
        }

        // Current Page (if singular)
        if ( is_singular() ) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => get_the_title(),
                'item' => get_permalink()
            ];
        }

        if ( count( $items ) > 1 ) {
            $schema = [
                '@context' => 'https://schema.org',
                '@type'    => 'BreadcrumbList',
                'itemListElement' => $items
            ];
            $this->echo_schema( $schema );
        }
    }

    /**
     * Google Scholar Meta Tags
     */
    public function output_google_scholar_tags() {
        if ( ! is_single() ) return;
        
        // Only if enabled or schema is scholarly
        $post_type = get_post_type();
        $mapping = get_option( 'wp_academic_post_enhanced_schema_post_type_mapping', [] );
        $is_scholarly = ( isset( $mapping[ $post_type ] ) && $mapping[ $post_type ] === 'ScholarlyArticle' );
        
        if ( ! $is_scholarly && ! get_option( 'wp_academic_post_enhanced_google_scholar_enabled', true ) ) return;

        $post_id = get_the_ID();
        $post_type = get_post_type( $post_id );
        $title = get_the_title();
        $author = get_the_author();
        if ( empty( $author ) ) {
            $author = get_bloginfo( 'name' );
        }
        $date = get_the_date( 'Y/m/d' );
        $pdf_url = add_query_arg( 'wpa_download_pdf', '1', get_permalink( $post_id ) );

        echo "\n<!-- Google Scholar Metadata -->\n";
        echo '<meta name="citation_title" content="' . esc_attr( $title ) . '">' . "\n";
        echo '<meta name="citation_author" content="' . esc_attr( $author ) . '">' . "\n";
        echo '<meta name="citation_publication_date" content="' . esc_attr( $date ) . '">' . "\n";
        echo '<meta name="citation_pdf_url" content="' . esc_url( $pdf_url ) . '">' . "\n";

        // Field News Specific: Source Metadata
        if ( $post_type === 'wpa_news' ) {
            $study_data = get_post_meta( $post_id, '_wpa_news_metadata', true );
            if ( ! empty( $study_data ) ) {
                if ( ! empty( $study_data['doi'] ) ) {
                    echo '<meta name="citation_doi" content="' . esc_attr( $study_data['doi'] ) . '">' . "\n";
                }
                if ( ! empty( $study_data['publication'] ) ) {
                    echo '<meta name="citation_journal_title" content="' . esc_attr( $study_data['publication'] ) . '">' . "\n";
                }
            }
        }
        
        // Tags as keywords
        $tags = get_the_tags();
        if ( $tags ) {
            foreach ( $tags as $tag ) {
                echo '<meta name="citation_keywords" content="' . esc_attr( $tag->name ) . '">' . "\n";
            }
        }
    }
}

// Initialize
WP_Academic_Post_Enhanced_Schema::get_instance();
