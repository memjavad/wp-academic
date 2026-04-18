<?php
/**
 * Scopus API Handler
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPA_Scopus_API {

    private $api_key;
    private $inst_token;

    public function __construct() {
        $settings = get_option( 'wpa_field_news_settings' );
        $this->api_key = isset( $settings['scopus_api_key'] ) ? $settings['scopus_api_key'] : '';
        $this->inst_token = isset( $settings['scopus_inst_token'] ) ? $settings['scopus_inst_token'] : '';
    }

    public function test_connection( $key ) {
        $url = 'https://api.elsevier.com/content/search/scopus?query=psychology&count=1';
        $args = [ 
            'headers' => [ 'X-ELS-APIKey' => $key, 'Accept' => 'application/json' ],
            'timeout' => 20
        ];
        $response = wp_remote_get( $url, $args );

        if ( is_wp_error( $response ) ) throw new Exception( 'Connection failed: ' . $response->get_error_message() );
        
        $code = wp_remote_retrieve_response_code( $response );
        if ( $code !== 200 ) {
            $body = json_decode( wp_remote_retrieve_body( $response ), true );
            $msg = isset($body['service-error']['status']['statusText']) ? $body['service-error']['status']['statusText'] : 'Status ' . $code;
            throw new Exception( 'Scopus Error: ' . $msg );
        }
        return true;
    }

    public function fetch_candidates( $query_term, $filters = [] ) {
        if ( empty( $this->api_key ) || empty( $query_term ) ) return [];

        $api_query = 'TITLE-ABS-KEY(' . $query_term . ')';

        if ( ! empty( $filters['doc_type'] ) && $filters['doc_type'] !== 'all' ) {
            $api_query .= ' AND DOCTYPE(' . $filters['doc_type'] . ')';
        }

        if ( ! empty( $filters['min_cites'] ) ) {
            $api_query .= ' AND CITEDBYCOUNT > ' . intval( $filters['min_cites'] );
        }

        if ( ! empty( $filters['open_access'] ) ) {
            $api_query .= ' AND OPENACCESS(1)';
        }

        // Date logic handled by caller or simplified here
        $date_filter_days = 0;
        if ( ! empty( $filters['date_range'] ) ) {
            if ( strpos( $filters['date_range'], 'last_' ) === 0 && strpos( $filters['date_range'], '_days' ) !== false ) {
                // Parse days
                $parts = explode( '_', $filters['date_range'] );
                $days = intval( $parts[1] );
                $date_filter_days = $days;
                // API Query optimization: Fetch broadly from last 2 years
                $api_query .= ' AND PUBYEAR > ' . (date('Y') - 2);
            } elseif ( $filters['date_range'] === 'last_365_days' ) {
                $date_filter_days = 365;
                $api_query .= ' AND PUBYEAR > ' . (date('Y') - 2);
            } elseif ( is_numeric( $filters['date_range'] ) ) {
                $api_query .= ' AND PUBYEAR IS ' . $filters['date_range'];
            }
        }

        $url = 'https://api.elsevier.com/content/search/scopus';
        $args = [
            'headers' => [ 'X-ELS-APIKey' => $this->api_key, 'Accept' => 'application/json' ],
            'timeout' => 15 // Fail fast
        ];

        if ( ! empty( $this->inst_token ) ) {
            $args['headers']['X-ELS-Insttoken'] = $this->inst_token;
        }

        // Pagination loop
        $total_requested = isset($filters['batch_size']) ? $filters['batch_size'] : 10;
        $start_offset = isset($filters['start_offset']) ? intval($filters['start_offset']) : 0;
        $per_page = 25; // Standard Scopus limit per request
        
        // If start_offset is set, we fetch just ONE page of results starting there
        if ( isset($filters['start_offset']) ) {
            $max_pages = 1;
            $total_requested = $per_page;
        } else {
            $max_pages = ceil( $total_requested / $per_page ); 
        }

        $candidates = [];

        for ( $page = 0; $page < $max_pages; $page++ ) {
            $current_start = $start_offset + ( $page * $per_page );
            
            $paged_url = add_query_arg( [
                'start' => $current_start,
                'query' => $api_query,
                'sort'  => '-coverDate',
                'count' => $per_page,
                'view'  => 'STANDARD',
            ], $url );

            $response = wp_remote_get( $paged_url, $args );

        if ( is_wp_error( $response ) ) {
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) error_log( 'Field News Scopus Error: ' . $response->get_error_message() );
            return [];
        }

        $code = wp_remote_retrieve_response_code( $response );
        $raw_body = wp_remote_retrieve_body( $response );

        if ( $code !== 200 ) {
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) error_log( 'Field News Scopus API Error (' . $code . '): ' . $raw_body );
            return [];
        }

        $body = json_decode( $raw_body, true );

        if ( ! isset( $body['search-results']['entry'] ) ) {
            // End of results or empty
            break; 
        }

            foreach ( $body['search-results']['entry'] as $entry ) {
                // Strict Date Filtering (PHP side)
                if ( $date_filter_days > 0 ) {
                    $cover_date = isset($entry['prism:coverDate']) ? $entry['prism:coverDate'] : '';
                    if ( ! empty( $cover_date ) ) {
                        $timestamp = strtotime( $cover_date );
                        $cutoff = strtotime( '-' . $date_filter_days . ' days' );
                        if ( $timestamp < $cutoff ) {
                            continue; // Skip items older than range
                        }
                    }
                }

                $id = isset( $entry['dc:identifier'] ) ? $entry['dc:identifier'] : ( isset( $entry['eid'] ) ? $entry['eid'] : '' );
                
                // Parse Entry
                $candidates[] = [
                    'id' => $id,
                    'title' => isset($entry['dc:title']) ? $entry['dc:title'] : '',
                    'creator' => isset($entry['dc:creator']) ? $entry['dc:creator'] : 'Unknown',
                    'publication' => isset($entry['prism:publicationName']) ? $entry['prism:publicationName'] : '',
                    'date' => isset($entry['prism:coverDate']) ? $entry['prism:coverDate'] : '',
                    'doi' => isset($entry['prism:doi']) ? $entry['prism:doi'] : '',
                    'abstract' => isset($entry['dc:description']) ? $entry['dc:description'] : '',
                    'keywords' => isset($entry['authkeywords']) ? $entry['authkeywords'] : $query_term,
                    'citations' => isset($entry['citedby-count']) ? $entry['citedby-count'] : '0',
                    'volume' => isset($entry['prism:volume']) ? $entry['prism:volume'] : '',
                    'issue' => isset($entry['prism:issueIdentifier']) ? $entry['prism:issueIdentifier'] : '',
                    'pages' => isset($entry['prism:pageRange']) ? $entry['prism:pageRange'] : '',
                    'openaccess' => isset($entry['openaccessFlag']) ? $entry['openaccessFlag'] : false,
                    'type' => isset($entry['subtypeDescription']) ? $entry['subtypeDescription'] : 'Article',
                    'affiliation' => $this->extract_affiliation($entry),
                    'links' => isset($entry['link']) ? $entry['link'] : []
                ];
            }

            if ( count( $candidates ) >= $total_requested ) break;
        }

        return $candidates;
    }

    public function fetch_abstract( $id, $doi = '' ) {
        if ( empty( $this->api_key ) || empty( $id ) ) return false;

        // 1. Try Scopus first
        $url = 'https://api.elsevier.com/content/abstract/eid/' . $id;
        $args = [
            'headers' => [ 'X-ELS-APIKey' => $this->api_key, 'Accept' => 'application/json' ],
            'timeout' => 10
        ];

        if ( ! empty( $this->inst_token ) ) {
            $args['headers']['X-ELS-Insttoken'] = $this->inst_token;
        }

        $response = wp_remote_get( $url, $args );
        if ( ! is_wp_error( $response ) ) {
            $body = json_decode( wp_remote_retrieve_body( $response ), true );
            $abs = isset( $body['abstracts-retrieval-response']['coredata']['dc:description'] ) ? $body['abstracts-retrieval-response']['coredata']['dc:description'] : false;
            if ( $abs ) return $abs;
        }

        // 2. Fallback to OpenAlex if DOI is available
        if ( ! empty( $doi ) ) {
            $oa_abs = $this->fetch_abstract_from_openalex( $doi );
            if ( $oa_abs ) return $oa_abs;
        }

        return false;
    }

    public function get_openalex_data( $doi ) {
        if ( empty( $doi ) ) return false;
        
        $doi = str_replace( 'https://doi.org/', '', $doi );
        $url = 'https://api.openalex.org/works/https://doi.org/' . $doi;
        
        $response = wp_remote_get( $url, [ 'timeout' => 10 ] );
        if ( is_wp_error( $response ) ) return false;

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        
        $data = [
            'abstract' => '',
            'concepts' => [],
            'sdgs' => [],
            'authors' => [],
            'citations' => isset($body['cited_by_count']) ? $body['cited_by_count'] : 0,
            'pdf_url' => isset($body['best_oa_location']['pdf_url']) ? $body['best_oa_location']['pdf_url'] : '',
            'date' => isset($body['publication_date']) ? $body['publication_date'] : '',
            'year' => isset($body['publication_year']) ? $body['publication_year'] : '',
            'journal' => isset($body['primary_location']['source']['display_name']) ? $body['primary_location']['source']['display_name'] : '',
            'biblio' => [
                'volume' => isset($body['biblio']['volume']) ? $body['biblio']['volume'] : '',
                'issue'  => isset($body['biblio']['issue']) ? $body['biblio']['issue'] : '',
                'pages'  => (isset($body['biblio']['first_page']) && isset($body['biblio']['last_page'])) ? $body['biblio']['first_page'] . '-' . $body['biblio']['last_page'] : ''
            ]
        ];

        // Abstract
        if ( isset( $body['abstract_inverted_index'] ) ) {
            $data['abstract'] = $this->reconstruct_inverted_index( $body['abstract_inverted_index'] );
        }

        // Detailed Authors
        if ( ! empty( $body['authorships'] ) ) {
            foreach ( $body['authorships'] as $ship ) {
                $author = [
                    'name' => isset($ship['author']['display_name']) ? $ship['author']['display_name'] : 'Unknown',
                    'orcid' => isset($ship['author']['orcid']) ? $ship['author']['orcid'] : '',
                    'affiliation' => isset($ship['institutions'][0]['display_name']) ? $ship['institutions'][0]['display_name'] : '',
                    'country' => isset($ship['institutions'][0]['country_code']) ? $ship['institutions'][0]['country_code'] : ''
                ];
                $data['authors'][] = $author;
            }
        }

        // Concepts (Top 5)
        if ( ! empty( $body['concepts'] ) ) {
            foreach ( array_slice($body['concepts'], 0, 5) as $c ) {
                $data['concepts'][] = $c['display_name'];
            }
        }

        // SDGs
        if ( ! empty( $body['sustainable_development_goals'] ) ) {
            foreach ( $body['sustainable_development_goals'] as $sdg ) {
                $data['sdgs'][] = [
                    'name' => $sdg['display_name'],
                    'id'   => $sdg['id']
                ];
            }
        }

        return $data;
    }

    /**
     * Fetch abstract from OpenAlex using DOI (Legacy/Fallback)
     */
    private function fetch_abstract_from_openalex( $doi ) {
        // Remove https://doi.org/ if present
        $doi = str_replace( 'https://doi.org/', '', $doi );
        $url = 'https://api.openalex.org/works/https://doi.org/' . $doi;
        
        $response = wp_remote_get( $url, [ 'timeout' => 20 ] );
        if ( is_wp_error( $response ) ) return false;

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        
        // OpenAlex uses 'abstract_inverted_index'
        if ( isset( $body['abstract_inverted_index'] ) ) {
            return $this->reconstruct_inverted_index( $body['abstract_inverted_index'] );
        }

        return false;
    }

    /**
     * Reconstruct text from OpenAlex inverted index
     */
    private function reconstruct_inverted_index( $index ) {
        if ( ! is_array( $index ) ) return '';
        
        $text_array = [];
        foreach ( $index as $word => $positions ) {
            foreach ( $positions as $pos ) {
                $text_array[ $pos ] = $word;
            }
        }
        
        ksort( $text_array );
        return implode( ' ', $text_array );
    }

    private function extract_affiliation( $entry ) {
        if ( isset($entry['affiliation']) ) {
            if ( isset($entry['affiliation'][0]['affilname']) ) return $entry['affiliation'][0]['affilname'];
            if ( isset($entry['affiliation']['affilname']) ) return $entry['affiliation']['affilname'];
        }
        return '';
    }
}
