<?php
/**
 * Unsplash API Handler
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPA_Unsplash_API {

    private $api_key;

    public function __construct() {
        $settings = get_option( 'wpa_field_news_settings' );
        $this->api_key = isset( $settings['unsplash_api_key'] ) ? $settings['unsplash_api_key'] : '';
    }

    public function test_connection( $key ) {
        $url = 'https://api.unsplash.com/search/photos?query=nature&per_page=1';
        $args = [
            'headers' => [ 'Authorization' => 'Client-ID ' . $key, 'Accept-Version' => 'v1' ],
            'timeout' => 20
        ];
        
        $response = wp_remote_get( $url, $args );

        if ( is_wp_error( $response ) ) throw new Exception( 'Connection failed: ' . $response->get_error_message() );
        
        $code = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $code !== 200 ) {
             $err_msg = isset($body['errors']) ? implode(', ', $body['errors']) : 'Status ' . $code;
             throw new Exception( 'Unsplash Error: ' . $err_msg );
        }

        if ( isset( $body['results'][0]['urls']['regular'] ) ) {
            return $body['results'][0]['urls']['regular'];
        }
        
        throw new Exception( 'Connected, but no image found in test search.' );
    }

    public function fetch_image( $keywords, $fallback_query = '' ) {
        if ( empty( $this->api_key ) ) return false;

        $queries = [];
        // 1. Primary Query (Keywords)
        if ( is_array( $keywords ) ) $queries[] = implode( ' ', $keywords );
        else $queries[] = (string) $keywords;

        // 2. Fallback Query (Field)
        if ( ! empty( $fallback_query ) && $fallback_query !== $queries[0] ) $queries[] = $fallback_query;

        foreach ( $queries as $q ) {
            if ( empty( $q ) ) continue;
            
            $url = 'https://api.unsplash.com/search/photos';
            $query_args = [
                'query' => substr( $q, 0, 100 ),
                'per_page' => 1,
                'page' => rand( 1, 5 ),
                'orientation' => 'landscape'
            ];
            $args = [
                'headers' => [ 'Authorization' => 'Client-ID ' . $this->api_key, 'Accept-Version' => 'v1' ],
                'timeout' => 30
            ];

            $response = wp_remote_get( add_query_arg( $query_args, $url ), $args );

            if ( ! is_wp_error( $response ) ) {
                $body = json_decode( wp_remote_retrieve_body( $response ), true );
                if ( ! empty( $body['results'][0]['urls']['regular'] ) ) {
                    return $body['results'][0]['urls']['regular'];
                }
            }
        }

        return false;
    }
}
