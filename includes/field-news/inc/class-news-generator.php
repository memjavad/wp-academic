<?php
/**
 * News Generator Orchestrator
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPA_News_Generator {

    private $scopus;
    private $google;
    private $unsplash;
    private $settings;

    public function __construct() {
        $this->settings = get_option( 'wpa_field_news_settings' );
        $this->scopus = new WPA_Scopus_API();
        $this->google = new WPA_Google_AI();
        $this->unsplash = new WPA_Unsplash_API();

        // Register Dynamic Training Filter
        add_filter( 'wpa_news_after_content_training', [ $this, 'hook_related_courses' ], 10, 2 );
    }

    /**
     * Hook for dynamic injection of related courses
     */
    public function hook_related_courses( $content, $post_id ) {
        $tags = wp_get_post_tags( $post_id, ['fields' => 'names'] );
        $title = get_the_title( $post_id );
        return $this->get_related_courses_html( $tags, $title );
    }

    public function fetch_and_store_candidates() {
        $start_time = microtime(true);

        // Update Last Fetch Time
        update_option( 'wpa_field_news_last_fetch_time', current_time( 'timestamp' ) );

        // 1. Determine Topic
        $topic_data = $this->get_next_topic();
        $query_term = $topic_data['query'];
        $cat_id = $topic_data['cat'];

        // 2. Fetch Candidates
        $filters = [
            'doc_type' => isset($this->settings['scopus_doc_type']) ? $this->settings['scopus_doc_type'] : 'ar',
            'min_cites' => isset($this->settings['scopus_min_citations']) ? $this->settings['scopus_min_citations'] : 0,
            'open_access' => isset($this->settings['scopus_open_access']) ? $this->settings['scopus_open_access'] : 0,
            'date_range' => isset($this->settings['scopus_date_range']) ? $this->settings['scopus_date_range'] : 'all',
            'batch_size' => 100, // Target batch size
        ];

        // Attempt 1: Strict
        $candidates = $this->fetch_unique_candidates( $query_term, $filters, $start_time );
        
        // Attempt 2: Relaxed (only if time permits)
        if ( empty( $candidates ) && (microtime(true) - $start_time < 20) ) {
            $filters['min_cites'] = 0; 
            $filters['open_access'] = 0; 
            $filters['date_range'] = 'all';
            $filters['doc_type'] = 'all';
            $candidates = $this->fetch_unique_candidates( $query_term, $filters, $start_time );
        }

        if ( empty( $candidates ) ) return 0;

        $count = 0;
        foreach ( $candidates as $study ) {
            // TIME CHECK: Stop if running too long (45s limit to safely use server resources)
            if ( (microtime(true) - $start_time) > 45 ) {
                if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) error_log('WPA Field News: Time limit reached (45s). Stopping fetch loop.');
                break;
            }

            // Check if already in Repo (ID or DOI)
            if ( $this->is_in_repo( $study['id'] ) ) continue;
            if ( ! empty( $study['doi'] ) && $this->is_in_repo( $study['doi'] ) ) continue;

            // Check if already Published (ID or DOI)
            if ( $this->is_published( $study['id'] ) ) continue;
            if ( ! empty( $study['doi'] ) && $this->is_published( $study['doi'] ) ) continue;

            // Enrich immediately to ensure quality
            if ( ! empty( $study['doi'] ) ) {
                $oa_data = $this->scopus->get_openalex_data( $study['doi'] );
                if ( $oa_data ) {
                    if ( empty( $study['abstract'] ) && ! empty( $oa_data['abstract'] ) ) $study['abstract'] = $oa_data['abstract'];
                    $study['original_abstract'] = !empty($oa_data['abstract']) ? $oa_data['abstract'] : $study['abstract'];
                    $study['sdgs'] = $oa_data['sdgs'];
                    $study['oa_concepts'] = $oa_data['concepts'];
                    if ( ! empty( $oa_data['date'] ) ) $study['date'] = $oa_data['date'];
                    if ( ! empty( $oa_data['journal'] ) ) $study['publication'] = $oa_data['journal'];
                    if ( ! empty( $oa_data['authors'] ) ) $study['authors_list'] = $oa_data['authors'];
                    if ( $oa_data['citations'] > $study['citations'] ) $study['citations'] = $oa_data['citations'];
                    
                    // Capture Country (New)
                    if ( ! empty( $oa_data['authors'][0]['country'] ) ) {
                        $study['country'] = $oa_data['authors'][0]['country'];
                    }
                }
            }

            // Fallback Abstract
            if ( empty( $study['abstract'] ) ) {
                $doi = isset( $study['doi'] ) ? $study['doi'] : '';
                $abs = $this->scopus->fetch_abstract( $study['id'], $doi );
                if ( $abs ) {
                    $study['abstract'] = $abs;
                    $study['original_abstract'] = $abs;
                }
            }

            // Save to Repo if abstract exists
            if ( ! empty( $study['abstract'] ) ) {
                $post_author = isset( $this->settings['default_author'] ) ? intval( $this->settings['default_author'] ) : get_current_user_id();
                
                $post_id = wp_insert_post([
                    'post_type' => 'wpa_study',
                    'post_title' => $study['title'],
                    'post_status' => 'publish',
                    'post_author' => $post_author,
                ]);
                
                if ( ! is_wp_error( $post_id ) ) {
                    update_post_meta( $post_id, '_wpa_scopus_id', $study['id'] );
                    update_post_meta( $post_id, '_wpa_study_data', $study );
                    update_post_meta( $post_id, '_wpa_status', 'pending' );
                    update_post_meta( $post_id, '_wpa_query', $query_term );
                    update_post_meta( $post_id, '_wpa_cat_id', $cat_id );
                    $count++;
                }
            }
        }
        return $count;
    }

    public function generate_post_from_repo( $repo_id ) {
        $study = get_post_meta( $repo_id, '_wpa_study_data', true );
        $cat_id = get_post_meta( $repo_id, '_wpa_cat_id', true );
        $query_term = get_post_meta( $repo_id, '_wpa_query', true );

        if ( empty( $study ) ) throw new Exception( 'Invalid study data.' );

        // 5. Generate Content
        $title = $this->google->generate_content( 'title', $study );
        if ( !$title ) $title = 'New Study: ' . $study['title'];
        $title = trim( $title, "'\"" );

        $body = $this->google->generate_content( 'body', $study );
        if ( !$body ) throw new Exception( 'Failed to generate body.' );

        $excerpt = $this->google->generate_content( 'excerpt', $study );

        $tags_str = $this->google->generate_content( 'tags', $study );
        $tags = $tags_str ? array_map('trim', explode(',', $tags_str)) : [];
        
        if ( ! empty( $study['oa_concepts'] ) ) {
            $tags = array_merge( $tags, $study['oa_concepts'] );
            $tags = array_unique( $tags );
        }

        // 6. Image
        $img_query = $study['title'];
        $img_url = $this->unsplash->fetch_image( $img_query, $query_term );

        // 7. AI Review & Fact Check
        $status_override = '';
        $review_note = '';
        
        // Step A: Broad Review
        if ( ! empty( $this->settings['enable_ai_review'] ) ) {
            $review = $this->google->review_content( $title, $body, $study );
            if ( isset($review['status']) && strtoupper($review['status']) === 'FAIL' ) {
                $status_override = 'draft';
                $review_note = isset($review['reason']) ? $review['reason'] : 'AI Review Failed.';
            }
        }

        // Step B: Scientific Fact Check
        if ( empty($status_override) && ! empty( $this->settings['enable_ai_fact_check'] ) ) {
            $accuracy = $this->google->verify_accuracy( $title, $body, $study );
            if ( isset($accuracy['pass']) && ! $accuracy['pass'] ) {
                $status_override = 'draft';
                $review_note = 'Scientific Accuracy Check Failed. Score: ' . (isset($accuracy['score']) ? $accuracy['score'] : 'N/A');
            }
        }

        // 8. Create Post
        $news_id = $this->create_post( $study, $title, $body, $excerpt, $tags, $img_url, $cat_id, $status_override, $review_note );
        
        // 9. Generate & Store AI Metadata (Highlights, Study Type)
        $ai_meta = $this->google->generate_metadata( $study );
        if ( $ai_meta ) {
            update_post_meta( $news_id, '_wpa_news_highlights', $ai_meta['highlights'] );
            update_post_meta( $news_id, '_wpa_news_study_type', $ai_meta['study_type'] );
            update_post_meta( $news_id, '_wpa_news_difficulty', $ai_meta['difficulty'] );
            update_post_meta( $news_id, '_wpa_news_discussion', $ai_meta['discussion_questions'] );
            update_post_meta( $news_id, '_wpa_news_evidence_strength', $ai_meta['evidence_strength'] );
        }

        // Update Repo Status
        update_post_meta( $repo_id, '_wpa_status', 'processed' );
        update_post_meta( $repo_id, '_wpa_news_post_id', $news_id );
        
        return $news_id;
    }

    private function is_in_repo( $scopus_id ) {
        if ( empty( $scopus_id ) ) return false;
        $q = new WP_Query([
            'post_type' => 'wpa_study',
            'meta_key' => '_wpa_scopus_id',
            'meta_value' => $scopus_id,
            'posts_per_page' => 1,
            'fields' => 'ids',
            'no_found_rows' => true,
        ]);
        return $q->have_posts();
    }

    private function is_published( $scopus_id ) {
        if ( empty( $scopus_id ) ) return false;
        $q = new WP_Query([
            'post_type' => 'wpa_news',
            'meta_key' => '_wpa_scopus_id',
            'meta_value' => $scopus_id,
            'posts_per_page' => 1,
            'fields' => 'ids',
            'no_found_rows' => true,
        ]);
        return $q->have_posts();
    }

    public function generate_post() {
        // 0. Check Repository for Selected Studies First
        $args = [
            'post_type'      => 'wpa_study',
            'posts_per_page' => 1,
            'post_status'    => 'publish',
            'meta_key'       => '_wpa_status',
            'meta_value'     => 'selected',
            'orderby'        => 'date',
            'order'          => 'ASC', // FIFO
            'no_found_rows'  => true,
        ];
        $repo_query = new WP_Query( $args );
        
        if ( $repo_query->have_posts() ) {
            $repo_id = $repo_query->posts[0]->ID;
            return $this->generate_post_from_repo( $repo_id );
        }

        // Fallback: Live Search (if Repo empty)
        $attempts = 0;
        $max_attempts = 3;
        $candidates = [];
        $query_term = '';
        $cat_id = '';
        $global_start = microtime(true);

        while ( empty($candidates) && $attempts < $max_attempts ) {
            // Global timeout check (stop if running > 45s)
            if ( (microtime(true) - $global_start) > 45 ) break;

            $attempts++;
            
            // 1. Determine Topic
            $topic_data = $this->get_next_topic();
            $query_term = $topic_data['query'];
            $cat_id = $topic_data['cat'];

            // 2. Fetch Candidates
            $filters = [
                'doc_type' => isset($this->settings['scopus_doc_type']) ? $this->settings['scopus_doc_type'] : 'ar',
                'min_cites' => isset($this->settings['scopus_min_citations']) ? $this->settings['scopus_min_citations'] : 0,
                'open_access' => isset($this->settings['scopus_open_access']) ? $this->settings['scopus_open_access'] : 0,
                'date_range' => isset($this->settings['scopus_date_range']) ? $this->settings['scopus_date_range'] : 'all',
                'batch_size' => 10, // Small batch for live generation
            ];

            // Attempt 1: Strict
            $candidates = $this->fetch_unique_candidates( $query_term, $filters, $global_start );
            
            // Attempt 2: Relaxed
            if ( empty( $candidates ) ) {
                $filters['min_cites'] = 0; 
                $filters['open_access'] = 0; 
                $filters['date_range'] = 'all';
                $filters['doc_type'] = 'all';
                $candidates = $this->fetch_unique_candidates( $query_term, $filters, $global_start );
            }
        }

        if ( empty( $candidates ) ) {
            throw new Exception( __( 'No new studies found after ' . $attempts . ' attempts. Last query: ', 'wp-academic-post-enhanced' ) . $query_term );
        }

        // 3. Select Study (Iterate until valid abstract found)
        $study = null;
        
        // Let AI pick the best one first
        $best_candidate = $this->google->select_study( $candidates, $query_term );
        
        // Re-order: Put best candidate first, then the rest
        $ordered_candidates = [ $best_candidate ];
        foreach ( $candidates as $c ) {
            if ( $c['id'] !== $best_candidate['id'] ) {
                $ordered_candidates[] = $c;
            }
        }

        foreach ( $ordered_candidates as $candidate ) {
            $study = $candidate;
            
            // 3.5 Enrich with OpenAlex (SDGs, Concepts, Better Abstract)
            if ( ! empty( $study['doi'] ) ) {
                $oa_data = $this->scopus->get_openalex_data( $study['doi'] );
                if ( $oa_data ) {
                    // Use OA abstract if Scopus failed or OA is better
                    if ( empty( $study['abstract'] ) && ! empty( $oa_data['abstract'] ) ) {
                        $study['abstract'] = $oa_data['abstract'];
                    }
                    // Store original abstract for display
                    $study['original_abstract'] = !empty($oa_data['abstract']) ? $oa_data['abstract'] : $study['abstract'];
                    
                    $study['sdgs'] = $oa_data['sdgs'];
                    $study['oa_concepts'] = $oa_data['concepts'];
                    
                    // Prioritize OpenAlex for Date and Journal
                    if ( ! empty( $oa_data['date'] ) ) $study['date'] = $oa_data['date'];
                    if ( ! empty( $oa_data['journal'] ) ) $study['publication'] = $oa_data['journal'];
                    if ( ! empty( $oa_data['biblio'] ) ) $study['biblio'] = $oa_data['biblio'];

                    if ( ! empty( $oa_data['authors'] ) ) {
                        $study['authors_list'] = $oa_data['authors']; // Detailed list
                    }

                    // Update citations if OA is fresher
                    if ( $oa_data['citations'] > $study['citations'] ) {
                        $study['citations'] = $oa_data['citations'];
                    }
                }
            }

            // 4. Ensure Abstract
            if ( empty( $study['abstract'] ) ) {
                $doi = isset( $study['doi'] ) ? $study['doi'] : '';
                $abs = $this->scopus->fetch_abstract( $study['id'], $doi );
                if ( $abs ) {
                    $study['abstract'] = $abs;
                    $study['original_abstract'] = $abs; 
                }
            }

            // If we have an abstract, proceed!
            if ( ! empty( $study['abstract'] ) ) {
                break;
            }
            
            // If not, loop to next candidate...
            $study = null; 
        }

        if ( ! $study ) {
             throw new Exception( __( 'No valid studies with abstracts found in this batch.', 'wp-academic-post-enhanced' ) );
        }

        // 5. Generate Content
        $title = $this->google->generate_content( 'title', $study );
        if ( !$title ) $title = 'New Study: ' . $study['title'];
        $title = trim( $title, "'\"" );

        $body = $this->google->generate_content( 'body', $study );
        if ( !$body ) throw new Exception( 'Failed to generate body.' );

        $excerpt = $this->google->generate_content( 'excerpt', $study );

        $tags_str = $this->google->generate_content( 'tags', $study );
        $tags = $tags_str ? array_map('trim', explode(',', $tags_str)) : [];
        
        if ( ! empty( $study['oa_concepts'] ) ) {
            $tags = array_merge( $tags, $study['oa_concepts'] );
            $tags = array_unique( $tags );
        }

        // 6. Image
        $img_query = $study['title'];
        $img_url = $this->unsplash->fetch_image( $img_query, $query_term );

        // 7. AI Review & Fact Check
        $status_override = '';
        $review_note = '';

        if ( ! empty( $this->settings['enable_ai_review'] ) ) {
            $review = $this->google->review_content( $title, $body, $study );
            if ( isset($review['status']) && strtoupper($review['status']) === 'FAIL' ) {
                $status_override = 'draft';
                $review_note = isset($review['reason']) ? $review['reason'] : 'AI Review Failed.';
            }
        }

        if ( empty($status_override) && ! empty( $this->settings['enable_ai_fact_check'] ) ) {
            $accuracy = $this->google->verify_accuracy( $title, $body, $study );
            if ( isset($accuracy['pass']) && ! $accuracy['pass'] ) {
                $status_override = 'draft';
                $review_note = 'Scientific Accuracy Check Failed. Score: ' . (isset($accuracy['score']) ? $accuracy['score'] : 'N/A');
            }
        }

        // 8. Create Post
        $news_id = $this->create_post( $study, $title, $body, $excerpt, $tags, $img_url, $cat_id, $status_override, $review_note );

        // 9. AI Metadata
        $ai_meta = $this->google->generate_metadata( $study );
        if ( $ai_meta ) {
            update_post_meta( $news_id, '_wpa_news_highlights', $ai_meta['highlights'] );
            update_post_meta( $news_id, '_wpa_news_study_type', $ai_meta['study_type'] );
            update_post_meta( $news_id, '_wpa_news_difficulty', $ai_meta['difficulty'] );
            update_post_meta( $news_id, '_wpa_news_discussion', $ai_meta['discussion_questions'] );
            update_post_meta( $news_id, '_wpa_news_evidence_strength', $ai_meta['evidence_strength'] );
        }

        return $news_id;
    }

    private function fetch_unique_candidates( $query, $filters, $start_time = null ) {
        $target_count = isset($filters['batch_size']) ? $filters['batch_size'] : 10;
        $unique_candidates = [];
        $current_offset = 0;
        $max_scanned = 500; // Safety limit
        
        if ( ! $start_time ) $start_time = microtime(true);

        while ( count( $unique_candidates ) < $target_count && $current_offset < $max_scanned ) {
            
            // TIME CHECK: Stop if global execution > 30s
            if ( (microtime(true) - $start_time) > 30 ) {
                break;
            }

            // Fetch one page (25 items) at offset
            $filters['start_offset'] = $current_offset;
            $batch = $this->scopus->fetch_candidates( $query, $filters );
            
            if ( empty( $batch ) ) break; // End of results
            
            foreach ( $batch as $c ) {
                // Check against Published News AND Repo
                if ( ! $this->is_published( $c['id'] ) && ! $this->is_in_repo( $c['id'] ) ) {
                    $unique_candidates[] = $c;
                }
                
                if ( count( $unique_candidates ) >= $target_count ) break;
            }
            
            $current_offset += 25;
        }
        
        return $unique_candidates;
    }

    private function get_next_topic() {
        $groups = isset( $this->settings['topic_groups'] ) ? $this->settings['topic_groups'] : [];
        if ( empty( $groups ) ) return [ 'query' => isset($this->settings['search_query'])?$this->settings['search_query']:'', 'cat' => '' ];

        $groups = array_values( $groups );
        $idx = (int) get_option( 'wpa_field_news_current_topic_index', 0 );
        if ( $idx >= count( $groups ) ) $idx = 0;
        
        $group = $groups[ $idx ];
        update_option( 'wpa_field_news_current_topic_index', ($idx + 1) % count($groups) );
        
        return $group;
    }

    private function create_post( $study, $title, $content, $excerpt, $tags, $img_url, $cat_id, $status_override = '', $review_note = '' ) {
        // Construct detailed reference
        $year = ! empty( $study['date'] ) ? substr( $study['date'], 0, 4 ) : date('Y');
        $ref_text = esc_html( $study['creator'] ) . ' (' . esc_html( $year ) . '). <em>' . esc_html( $study['title'] ) . '</em>. ' . esc_html( $study['publication'] );
        
        if ( ! empty( $study['biblio']['volume'] ) ) {
            $ref_text .= ', ' . esc_html( $study['biblio']['volume'] );
            if ( ! empty( $study['biblio']['issue'] ) ) {
                $ref_text .= '(' . esc_html( $study['biblio']['issue'] ) . ')';
            }
        }
        if ( ! empty( $study['biblio']['pages'] ) ) {
            $ref_text .= ', ' . esc_html( $study['biblio']['pages'] );
        }
        $ref_text .= '.';
        
        // --- NEW: Related Courses Integration ---
        $courses_html = $this->get_related_courses_html( $tags, $title );
        
        $ref_html = $courses_html . '<hr><h3>' . __( 'Reference', 'wp-academic-post-enhanced' ) . '</h3><p>' . $ref_text . '</p>';
        if ( ! empty( $study['doi'] ) ) {
            $ref_html .= '<p>DOI: <a href="https://doi.org/' . esc_attr( $study['doi'] ) . '" target="_blank">' . esc_html( $study['doi'] ) . '</a></p>';
        }

        // Add Review Note if Failed
        if ( ! empty( $review_note ) ) {
            $content = '<div style="background:#fee2e2; border:1px solid #ef4444; padding:15px; margin-bottom:20px; border-radius:4px; color:#991b1b;"><strong>⚠️ AI Review Failed:</strong> ' . esc_html( $review_note ) . '</div>' . $content;
        }

        // WRAPPER DIV for Styling/Justification
        $final_content = '<div class="wpa-field-news-content">' . $content . $ref_html . '</div>';

        $cats = [];
        if ( ! empty( $cat_id ) ) $cats[] = $cat_id;
        elseif ( isset( $this->settings['post_category'] ) ) $cats[] = $this->settings['post_category'];

        $post_status = ! empty( $status_override ) ? $status_override : ( isset( $this->settings['post_status'] ) ? $this->settings['post_status'] : 'draft' );
        $post_author = isset( $this->settings['default_author'] ) ? intval( $this->settings['default_author'] ) : get_current_user_id();

        $post_data = [
            'post_title'   => $title,
            'post_content' => $final_content,
            'post_excerpt' => $excerpt,
            'post_status'  => $post_status,
            'post_author'  => $post_author,
            'post_type'    => 'wpa_news',
            'post_category' => $cats
        ];

        $post_id = wp_insert_post( $post_data );
        if ( is_wp_error( $post_id ) ) throw new Exception( $post_id->get_error_message() );

        // Update Last Post Time
        update_option( 'wpa_field_news_last_post_time', current_time( 'timestamp' ) );

        if ( isset( $study['id'] ) ) update_post_meta( $post_id, '_wpa_scopus_id', $study['id'] );
        update_post_meta( $post_id, '_wpa_news_metadata', $study );
        
        if ( ! empty( $tags ) ) {
            wp_set_post_tags( $post_id, $tags );
            
            // AUTO LINKING
            $linked_content = $this->auto_link_tags( $final_content, $tags );
            if ( $linked_content !== $final_content ) {
                wp_update_post( [ 'ID' => $post_id, 'post_content' => $linked_content ] );
            }
        }
        
        if ( $img_url ) {
            $safe_title = sanitize_title( $title );
            $filename = substr( $safe_title, 0, 50 ) . '.jpg';
            $this->sideload_media( $img_url, $post_id, $filename, $title, true );
        }

        // PDF (Simple check based on previous logic)
        if ( ! empty($study['openaccess']) && $study['openaccess'] !== 'false' && ! empty($study['links']) ) {
            $pdf_url = '';
            foreach ( $study['links'] as $link ) {
                if ( isset($link['@type']) && $link['@type'] === 'application/pdf' ) {
                    $pdf_url = $link['@href']; break;
                }
            }
            if ( $pdf_url ) {
                $this->sideload_media( $pdf_url, $post_id, 'study.pdf', 'Study PDF', false );
            }
        }

        return $post_id;
    }

    private function auto_link_tags( $content, $tags ) {
        foreach ( $tags as $tag_name ) {
            $term = get_term_by( 'name', $tag_name, 'post_tag' );
            if ( $term && ! is_wp_error( $term ) ) {
                $link = get_term_link( $term );
                if ( ! is_wp_error( $link ) ) {
                    $pattern = '/\b(' . preg_quote( $tag_name, '/' ) . ')\b/i';
                    $content = preg_replace( $pattern, '<a href="' . esc_url( $link ) . '" class="wpa-auto-link">$1</a>', $content, 1 );
                }
            }
        }
        return $content;
    }

    private function ensure_study_has_abstract( &$study ) {
        if ( ! empty( $study['abstract'] ) ) return true;
        
        // Pass DOI if available
        $doi = isset($study['doi']) ? $study['doi'] : '';
        $abs = $this->scopus->fetch_abstract( $study['id'], $doi );
        if ( $abs ) {
            $study['abstract'] = $abs;
            return true;
        }
        return false;
    }

    private function sideload_media( $url, $post_id, $filename, $desc, $is_featured ) {
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        require_once( ABSPATH . 'wp-admin/includes/media.php' );

        $tmp = download_url( $url );
        if ( is_wp_error( $tmp ) ) {
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) error_log( 'Field News Media Sideload Error: ' . $tmp->get_error_message() . ' for URL: ' . $url );
            return;
        }

        $file_array = [ 'name' => $filename, 'tmp_name' => $tmp ];
        $id = media_handle_sideload( $file_array, $post_id, $desc );

        if ( ! is_wp_error( $id ) ) {
            if ( $is_featured ) set_post_thumbnail( $post_id, $id );
            else update_post_meta( $post_id, '_wpa_news_pdf_id', $id );
        } else {
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) error_log( 'Field News Media Handle Error: ' . $id->get_error_message() );
            @unlink( $tmp );
        }
    }

    /**
     * Fetch Related Courses based on tags and title
     */
    private function get_related_courses_html( $tags, $title ) {
        $found_courses = [];
        $max_courses = 3;

        // Clean up title for search keywords (take first 3 words longer than 3 chars)
        $title_clean = strip_tags($title);
        $title_words = explode(' ', $title_clean);
        $title_keywords = array_filter($title_words, function($w) { return mb_strlen($w) > 4; });
        $title_keywords = array_slice($title_keywords, 0, 3);

        // 1. Try tags search
        if ( ! empty( $tags ) ) {
            foreach ( array_slice($tags, 0, 3) as $tag ) {
                $q = new WP_Query([
                    'post_type'      => 'wpa_course',
                    'posts_per_page' => $max_courses,
                    'post_status'    => 'publish',
                    's'              => $tag
                ]);
                if ( $q->have_posts() ) {
                    foreach ( $q->posts as $p ) {
                        $found_courses[$p->ID] = $p;
                        if ( count($found_courses) >= $max_courses ) break 2;
                    }
                }
            }
        }

        // 2. Try title keywords if still empty
        if ( count($found_courses) < $max_courses && ! empty($title_keywords) ) {
            foreach ( $title_keywords as $keyword ) {
                $q = new WP_Query([
                    'post_type'      => 'wpa_course',
                    'posts_per_page' => $max_courses - count($found_courses),
                    'post_status'    => 'publish',
                    's'              => $keyword,
                    'post__not_in'   => array_keys($found_courses)
                ]);
                if ( $q->have_posts() ) {
                    foreach ( $q->posts as $p ) {
                        $found_courses[$p->ID] = $p;
                        if ( count($found_courses) >= $max_courses ) break 2;
                    }
                }
            }
        }

        // 3. Fallback: Latest 3 courses if still empty
        if ( empty($found_courses) ) {
             $q = new WP_Query([
                'post_type'      => 'wpa_course',
                'posts_per_page' => $max_courses,
                'post_status'    => 'publish'
            ]);
            if ( $q->have_posts() ) {
                foreach ( $q->posts as $p ) {
                    $found_courses[$p->ID] = $p;
                }
            }
        }

        if ( empty( $found_courses ) ) return '';

        $html = '<div class="wpa-related-courses-box">';
        $html .= '<h3>🎓 ' . __( 'Recommended Academic Training', 'wp-academic-post-enhanced' ) . '</h3>';
        $html .= '<p>' . __( 'Deepen your knowledge with these specialized courses from our Academy:', 'wp-academic-post-enhanced' ) . '</p>';
        $html .= '<div class="wpa-course-links-grid">';

        foreach ( $found_courses as $course ) {
            $html .= '<a href="' . get_permalink($course->ID) . '" class="wpa-course-link-item">';
            $html .= '<span>' . get_the_title($course->ID) . '</span>';
            $html .= '<span>' . __( 'View Course', 'wp-academic-post-enhanced' ) . ' →</span>';
            $html .= '</a>';
        }

        $html .= '</div></div>';
        return $html;
    }
}