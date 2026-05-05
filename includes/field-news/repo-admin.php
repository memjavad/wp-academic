<?php
/**
 * Field News Study Repository Admin Page
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPA_Study_Repo_Page {

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
        add_action( 'wp_ajax_wpa_generate_from_repo', [ $this, 'ajax_generate' ] );
        add_action( 'wp_ajax_wpa_fetch_repo_studies', [ $this, 'ajax_fetch_studies' ] );
        add_action( 'wp_ajax_wpa_import_ris_file', [ $this, 'ajax_import_ris' ] );
        add_action( 'wp_ajax_wpa_ai_bulk_screen', [ $this, 'ajax_ai_bulk_screen' ] );
        add_action( 'admin_post_wpa_export_studies', [ $this, 'handle_export' ] );
        add_action( 'admin_post_wpa_bulk_repo_actions', [ $this, 'handle_bulk_actions' ] );
    }

    public function handle_bulk_actions() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized' );
        }

        check_admin_referer( 'wpa_bulk_repo_nonce' );

        $action = isset( $_POST['bulk_action'] ) ? sanitize_text_field( $_POST['bulk_action'] ) : '';
        $ids = isset( $_POST['study_ids'] ) ? array_map( 'intval', $_POST['study_ids'] ) : [];

        if ( empty( $ids ) || $action === '-1' ) {
            wp_safe_redirect( wp_get_referer() );
            exit;
        }

        $count = 0;
        if ( $action === 'delete' ) {
            foreach ( $ids as $id ) {
                if ( wp_trash_post( $id ) ) $count++;
            }
            $msg = $count . ' studies moved to trash.';
        } elseif ( $action === 'restore' ) {
            foreach ( $ids as $id ) {
                update_post_meta( $id, '_wpa_status', 'pending' );
                $count++;
            }
            $msg = $count . ' studies restored to pending.';
        } elseif ( $action === 'generate' ) {
            // Batch generation logic could go here
        }

        wp_safe_redirect( add_query_arg( 'msg', urlencode($msg), wp_get_referer() ) );
        exit;
    }

    public function handle_export() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized' );
        }

        check_admin_referer( 'wpa_export_studies_nonce' );

        $status_filter = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : 'all';

        $args = [
            'post_type'      => 'wpa_study',
            'posts_per_page' => -1, // Export all
            'post_status'    => 'publish',
        ];

        if ( $status_filter !== 'all' ) {
            $args['meta_key'] = '_wpa_status';
            $args['meta_value'] = $status_filter;
        }

        $query = new WP_Query( $args );

        header( 'Content-Type: text/csv' );
        header( 'Content-Disposition: attachment; filename="studies-export-' . date( 'Y-m-d' ) . '.csv"' );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );

        $output = fopen( 'php://output', 'w' );

        // Header Row
        fputcsv( $output, [ 'ID', 'Title', 'Journal', 'Publication Date', 'Citations', 'DOI', 'Status', 'Generated Post ID' ] );

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $id = get_the_ID();
                $data = get_post_meta( $id, '_wpa_study_data', true );
                $status = get_post_meta( $id, '_wpa_status', true );
                $news_id = get_post_meta( $id, '_wpa_news_post_id', true );

                fputcsv( $output, [
                    $id,
                    get_the_title(),
                    isset( $data['publication'] ) ? $data['publication'] : '',
                    isset( $data['date'] ) ? $data['date'] : '',
                    isset( $data['citations'] ) ? $data['citations'] : 0,
                    isset( $data['doi'] ) ? $data['doi'] : '',
                    $status ? $status : 'pending',
                    $news_id ? $news_id : ''
                ] );
            }
        }

        fclose( $output );
        exit;
    }

    public function register_menu() {
        add_submenu_page(
            'edit.php?post_type=wpa_news',
            __( 'Study Repository', 'wp-academic-post-enhanced' ),
            __( 'Study Repository', 'wp-academic-post-enhanced' ),
            'manage_options',
            'wpa-field-news-repo',
            [ $this, 'render_page' ]
        );
    }

    private function get_status_counts() {
        global $wpdb;
        $counts = [
            'all' => wp_count_posts( 'wpa_study' )->publish,
            'pending' => 0,
            'selected' => 0,
            'processed' => 0,
            'ignored' => 0,
        ];

        // ⚡ Bolt: Use a single aggregated query instead of 4 separate WP_Query calls to avoid expensive SQL_CALC_FOUND_ROWS
        $results = $wpdb->get_results( "
            SELECT pm.meta_value as status, COUNT(p.ID) as count
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'wpa_study'
            AND p.post_status = 'publish'
            AND pm.meta_key = '_wpa_status'
            AND pm.meta_value IN ('pending', 'selected', 'processed', 'ignored')
            GROUP BY pm.meta_value
        " );

        if ( $results ) {
            foreach ( $results as $row ) {
                if ( isset( $counts[ $row->status ] ) ) {
                    $counts[ $row->status ] = (int) $row->count;
                }
            }
        }

        return $counts;
    }

    public function render_page() {
        // Pagination & Search
        $paged = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
        $search = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
        
        $args = [
            'post_type'      => 'wpa_study',
            'posts_per_page' => 20,
            'paged'          => $paged,
            'post_status'    => 'publish'
        ];
        
        if ( ! empty( $search ) ) {
            $args['s'] = $search;
        }

        $filter_status = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : 'selected';
        if ( $filter_status !== 'all' ) {
            $args['meta_key'] = '_wpa_status';
            $args['meta_value'] = $filter_status;
        }

        $query = new WP_Query( $args );
        $base_url = admin_url('edit.php?post_type=wpa_news&page=wpa-field-news-repo');
        $counts = $this->get_status_counts();
        require plugin_dir_path( __FILE__ ) . 'views/repo-header.php';
        require plugin_dir_path( __FILE__ ) . 'views/repo-table.php';
        require plugin_dir_path( __FILE__ ) . 'views/repo-modal.php';
        require plugin_dir_path( __FILE__ ) . 'views/repo-scripts.php';
    }

    public function ajax_fetch_studies() {
        check_ajax_referer( 'wpa_repo_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Unauthorized' );
        }

        // Increase time limit for batch fetch
        if ( function_exists('set_time_limit') ) @set_time_limit( 300 ); 

        try {
            $gen = new WPA_News_Generator();
            $count = $gen->fetch_and_store_candidates();
            wp_send_json_success( [ 'count' => $count ] );
        } catch ( Throwable $e ) {
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( 'Field News Fetch Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() );
            }
            wp_send_json_error( 'An error occurred during fetch. Please check server logs.' );
        }
    }

    public function ajax_import_ris() {
        check_ajax_referer( 'wpa_repo_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Unauthorized' );
        }

        if ( empty( $_FILES['ris_file'] ) ) {
            wp_send_json_error( 'No file uploaded.' );
        }

        $file = $_FILES['ris_file'];
        if ( $file['error'] !== UPLOAD_ERR_OK ) {
            wp_send_json_error( 'Upload error: ' . $file['error'] );
        }

        $content = file_get_contents( $file['tmp_name'] );
        if ( empty( $content ) ) {
            wp_send_json_error( 'Empty file.' );
        }
        
        $cat_id = isset( $_POST['cat_id'] ) ? intval( $_POST['cat_id'] ) : 0;

        // Simple RIS Parser
        $entries = preg_split( '/ER\s*-\s*/', $content );
        $count = 0;

        foreach ( $entries as $entry_str ) {
            $entry_str = trim( $entry_str );
            if ( empty( $entry_str ) ) continue;

            $study = $this->parse_ris_entry( $entry_str );
            
            if ( ! empty( $study['title'] ) && ! empty( $study['abstract'] ) ) {
                // Check if already exists (Title, DOI, Scopus ID)
                if ( $this->study_exists( $study['title'], $study ) ) continue;

                $options = get_option( 'wpa_field_news_settings' );
                $post_author = isset( $options['default_author'] ) ? intval( $options['default_author'] ) : get_current_user_id();

                $post_id = wp_insert_post([
                    'post_type' => 'wpa_study',
                    'post_title' => $study['title'],
                    'post_status' => 'publish',
                    'post_author' => $post_author,
                ]);

                if ( ! is_wp_error( $post_id ) ) {
                    update_post_meta( $post_id, '_wpa_study_data', $study );
                    update_post_meta( $post_id, '_wpa_status', 'pending' );
                    update_post_meta( $post_id, '_wpa_query', 'Manual Import' );
                    if ( $cat_id > 0 ) {
                        update_post_meta( $post_id, '_wpa_cat_id', $cat_id );
                    }
                    
                    if ( ! empty( $study['doi'] ) ) {
                         update_post_meta( $post_id, '_wpa_scopus_id', $study['doi'] ); // Use DOI as ID for manual imports
                    }
                    
                    $count++;
                }
            }
        }

        wp_send_json_success( [ 'count' => $count ] );
    }

    private function parse_ris_entry( $entry ) {
        $lines = explode( "\n", $entry );
        $study = [
            'id' => '',
            'title' => '',
            'creator' => '',
            'publication' => '',
            'date' => '',
            'doi' => '',
            'abstract' => '',
            'citations' => 0,
            'openaccess' => false,
            'type' => 'Article',
            'links' => []
        ];

        foreach ( $lines as $line ) {
            $line = trim( $line );
            if ( strlen( $line ) < 6 ) continue;
            
            $tag = substr( $line, 0, 2 );
            $val = trim( substr( $line, 6 ) );

            switch ( $tag ) {
                case 'TI':
                case 'T1':
                    $study['title'] = $val;
                    break;
                case 'AU':
                case 'A1':
                    if ( empty( $study['creator'] ) ) $study['creator'] = $val;
                    break;
                case 'JO':
                case 'JF':
                case 'T2':
                    $study['publication'] = $val;
                    break;
                case 'PY':
                case 'Y1':
                    $study['date'] = $val;
                    break;
                case 'DO':
                    $study['doi'] = $val;
                    break;
                case 'AB':
                case 'N2':
                    $study['abstract'] = $val;
                    break;
            }
        }
        
        return $study;
    }

    private function study_exists( $title, $study_data = [] ) {
        global $wpdb;
        
        // 1. Check by Title (Exact match in wpa_study or wpa_news)
        $title_check = $wpdb->get_var( $wpdb->prepare( 
            "SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type IN ('wpa_study', 'wpa_news') LIMIT 1", 
            $title 
        ) );
        if ( $title_check ) return true;

        // 2. Check by Scopus ID / DOI (if available)
        $ids_to_check = [];
        if ( ! empty( $study_data['id'] ) ) $ids_to_check[] = $study_data['id'];
        if ( ! empty( $study_data['doi'] ) ) $ids_to_check[] = $study_data['doi'];

        if ( ! empty( $ids_to_check ) ) {
            $meta_query = array_map( function($id) {
                return $id;
            }, $ids_to_check );
            
            // Format for SQL IN clause
            $placeholders = implode( ',', array_fill( 0, count( $meta_query ), '%s' ) );
            
            $id_check = $wpdb->get_var( $wpdb->prepare( 
                "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wpa_scopus_id' AND meta_value IN ($placeholders) LIMIT 1", 
                $meta_query 
            ) );
            
            if ( $id_check ) return true;
        }

        return false;
    }

    public function ajax_generate() {
        check_ajax_referer( 'wpa_repo_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Unauthorized' );
        }
        $id = intval( $_POST['study_id'] );
        
        try {
            $gen = new WPA_News_Generator();
            $news_id = $gen->generate_post_from_repo( $id );
            wp_send_json_success( [ 'id' => $news_id ] );
        } catch ( Throwable $e ) {
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( 'Field News Generate Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() );
            }
            wp_send_json_error( 'An error occurred during generation. Please check server logs.' );
        }
    }

    public function ajax_ai_bulk_screen() {
        check_ajax_referer( 'wpa_repo_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Unauthorized' );
        }

        // Get up to 20 pending studies
        $args = [
            'post_type'      => 'wpa_study',
            'posts_per_page' => 20,
            'post_status'    => 'publish',
            'meta_key'       => '_wpa_status',
            'meta_value'     => 'pending',
            'orderby'        => 'date',
            'order'          => 'DESC'
        ];
        $query = new WP_Query( $args );
        
        if ( ! $query->have_posts() ) {
            wp_send_json_error( 'No pending studies to screen.' );
        }

        $studies = [];
        while ( $query->have_posts() ) {
            $query->the_post();
            $id = get_the_ID();
            $data = get_post_meta( $id, '_wpa_study_data', true );
            $studies[] = [
                'id' => $id,
                'title' => get_the_title(),
                'abstract' => isset($data['abstract']) ? $data['abstract'] : ''
            ];
        }
        wp_reset_postdata();

        try {
            $gen = new WPA_News_Generator(); // This wraps Google AI
            // We need to access Google AI directly or add a wrapper method. 
            // Let's add a wrapper in News Generator for cleanliness or just instantiate Google AI here if public.
            // Google AI is private in Generator. Let's instantiate it directly.
            require_once plugin_dir_path( __FILE__ ) . 'inc/class-google-ai.php';
            $ai = new WPA_Google_AI();
            
            $results = $ai->bulk_screen_studies( $studies );
            
            $ignored_count = 0;
            $selected_count = 0;

            if ( ! empty( $results['ignored'] ) ) {
                foreach ( $results['ignored'] as $ignored_id ) {
                    update_post_meta( $ignored_id, '_wpa_status', 'ignored' );
                    $ignored_count++;
                }
            }

            if ( ! empty( $results['selected'] ) ) {
                foreach ( $results['selected'] as $selected_id ) {
                    update_post_meta( $selected_id, '_wpa_status', 'selected' );
                    $selected_count++;
                }
            }
            
            wp_send_json_success( [ 'processed' => count($studies), 'ignored' => $ignored_count, 'selected' => $selected_count ] );

        } catch ( Throwable $e ) {
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( 'Field News AI Screen Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() );
            }
            wp_send_json_error( 'An AI error occurred. Please check server logs.' );
        }
    }
}

new WPA_Study_Repo_Page();