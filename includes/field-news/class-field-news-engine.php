<?php
/**
 * Field News Legacy Wrapper
 * Delegates Admin AJAX calls to new modular classes.
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPA_Field_News_Engine {

    public function __construct() {
        add_action( 'wp_ajax_wpa_generate_field_news', [ $this, 'ajax_generate_news' ] );
        add_action( 'wp_ajax_wpa_test_field_news_api', [ $this, 'ajax_test_api' ] );
    }

    public function ajax_test_api() {
        check_ajax_referer( 'wpa_field_news_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Permission denied.' );

        $api = isset( $_POST['api'] ) ? sanitize_key( $_POST['api'] ) : '';
        $key = isset( $_POST['key_value'] ) ? sanitize_text_field( $_POST['key_value'] ) : '';

        if ( empty( $key ) ) wp_send_json_error( 'API Key is empty.' );

        try {
            switch ( $api ) {
                case 'scopus':
                    $handler = new WPA_Scopus_API();
                    $handler->test_connection( $key );
                    break;
                case 'google':
                    $handler = new WPA_Google_AI();
                    $handler->test_connection( $key );
                    break;
                case 'unsplash':
                    $handler = new WPA_Unsplash_API();
                    $handler->test_connection( $key );
                    break;
                default:
                    throw new Exception( 'Invalid API type.' );
            }
            wp_send_json_success( 'Connection Successful!' );
        } catch ( Exception $e ) {
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( 'Field News API Test Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() );
            }
            wp_send_json_error( 'An error occurred during testing. Please check server logs.' );
        }
    }

    public function ajax_generate_news() {
        check_ajax_referer( 'wpa_field_news_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Permission denied.' );

        try {
            $gen = new WPA_News_Generator();
            $post_id = $gen->generate_post();

            wp_send_json_success( [
                'message' => 'News story created successfully!',
                'post_id' => $post_id,
                'edit_link' => get_edit_post_link( $post_id, '' )
            ] );
        } catch ( Exception $e ) {
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( 'Field News Generate Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() );
            }
            wp_send_json_error( 'An error occurred during generation. Please check server logs.' );
        }
    }
}

new WPA_Field_News_Engine();