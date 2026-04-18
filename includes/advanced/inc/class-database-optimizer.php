<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class WP_Academic_Post_Enhanced_Database_Optimizer {

    private static $instance;

    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action( 'admin_post_wpa_optimize_database', [ $this, 'process_optimization' ] );
        add_action( 'wpa_scheduled_database_optimization', [ $this, 'run_scheduled_optimization' ] );
    }

    public function process_optimization() {
        if ( ! isset( $_POST['wpa_optimize_database_nonce'] ) || ! wp_verify_nonce( $_POST['wpa_optimize_database_nonce'], 'wpa_optimize_database' ) ) {
            wp_die( __( 'Security check failed.', 'wp-academic-post-enhanced' ) );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'You do not have permission to perform this action.', 'wp-academic-post-enhanced' ) );
        }

        $actions = isset( $_POST['wpa_optimize_actions'] ) ? (array) $_POST['wpa_optimize_actions'] : [];
        $keep_revisions = isset( $_POST['wpa_keep_revisions'] ) ? absint( $_POST['wpa_keep_revisions'] ) : 0;
        
        // Save scheduling options
        if ( isset( $_POST['wpa_schedule_optimization'] ) ) {
            $schedule = sanitize_key( $_POST['wpa_schedule_optimization'] );
            update_option( 'wpa_database_optimization_schedule', $schedule );
            $this->schedule_event( $schedule );
        }

        $results = $this->run_optimization_actions( $actions, $keep_revisions );

        // Redirect back with results
        $query_args = [
            'page' => 'wp-academic-post-enhanced-performance',
            'tab' => 'database-optimization',
            'optimized' => 'true',
            'count' => array_sum( $results ),
        ];
        wp_safe_redirect( add_query_arg( $query_args, admin_url( 'admin.php' ) ) );
        exit;
    }

    /**
     * Trigger optimizations directly (used by the unified caching admin).
     */
    public function process_optimization_direct($actions, $keep_revisions = 0) {
        return $this->run_optimization_actions((array)$actions, $keep_revisions);
    }

    public function run_scheduled_optimization() {
        $actions = [
            'revisions', 'auto_drafts', 'trashed_posts', 'spam_comments', 
            'trashed_comments', 'expired_transients', 'orphaned_postmeta', 'orphaned_commentmeta'
        ];
        $keep_revisions = 10; // Default safe limit for automated tasks
        $this->run_optimization_actions( $actions, $keep_revisions );
    }

    public function get_autoload_stats() {
        global $wpdb;
        
        // Total Autoload Size
        $total_bytes = $wpdb->get_var( "SELECT SUM(LENGTH(option_value)) FROM $wpdb->options WHERE autoload = 'yes'" );
        
        // Top 20 Offenders
        $top_options = $wpdb->get_results( "
            SELECT option_name, LENGTH(option_value) as size, option_value 
            FROM $wpdb->options 
            WHERE autoload = 'yes' 
            ORDER BY size DESC 
            LIMIT 20
        " );

        // Count total autoloaded options
        $count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->options WHERE autoload = 'yes'" );

        return [
            'total_size' => $total_bytes,
            'count' => $count,
            'top_options' => $top_options
        ];
    }

    private function run_optimization_actions( $actions, $keep_revisions = 0 ) {
        $results = [];
        foreach ( $actions as $action ) {
            switch ( $action ) {
                case 'revisions':
                    $results['revisions'] = $this->clean_revisions( $keep_revisions );
                    break;
                case 'auto_drafts':
                    $results['auto_drafts'] = $this->clean_auto_drafts();
                    break;
                case 'trashed_posts':
                    $results['trashed_posts'] = $this->clean_trashed_posts();
                    break;
                case 'spam_comments':
                    $results['spam_comments'] = $this->clean_spam_comments();
                    break;
                case 'trashed_comments':
                    $results['trashed_comments'] = $this->clean_trashed_comments();
                    break;
                case 'expired_transients':
                    $results['expired_transients'] = $this->clean_expired_transients();
                    break;
                case 'orphaned_postmeta':
                    $results['orphaned_postmeta'] = $this->clean_orphaned_postmeta();
                    break;
                case 'orphaned_commentmeta':
                    $results['orphaned_commentmeta'] = $this->clean_orphaned_commentmeta();
                    break;
                case 'optimize_tables':
                    $results['optimize_tables'] = $this->optimize_tables();
                    break;
            }
        }
        return $results;
    }

    private function schedule_event( $frequency ) {
        wp_clear_scheduled_hook( 'wpa_scheduled_database_optimization' );
        if ( 'none' !== $frequency ) {
            if ( ! wp_next_scheduled( 'wpa_scheduled_database_optimization' ) ) {
                wp_schedule_event( time(), $frequency, 'wpa_scheduled_database_optimization' );
            }
        }
    }

    private function clean_revisions( $keep = 0 ) {
        global $wpdb;

        if ( $keep > 0 ) {
             $posts_with_revisions = $wpdb->get_col( "SELECT DISTINCT post_parent FROM $wpdb->posts WHERE post_type = 'revision'" );
             $deleted = 0;
             foreach ( $posts_with_revisions as $post_id ) {
                 $revisions = wp_get_post_revisions( $post_id, [ 'order' => 'DESC', 'orderby' => 'date' ] );
                 $to_delete = array_slice( $revisions, $keep );
                 foreach ( $to_delete as $revision ) {
                     wp_delete_post_revision( $revision->ID );
                     $deleted++;
                 }
             }
             return $deleted;

        } else {
            // Delete all revisions in batches of 1000
            $deleted_count = 0;
            while ( true ) {
                $revision_ids = $wpdb->get_col( "SELECT ID FROM $wpdb->posts WHERE post_type = 'revision' LIMIT 1000" );
                
                if ( empty( $revision_ids ) ) {
                    break;
                }
                
                $ids_string = implode( ',', array_map( 'absint', $revision_ids ) );
                
                // Delete Meta
                $wpdb->query( "DELETE FROM $wpdb->postmeta WHERE post_id IN ($ids_string)" );
                // Delete Term Relationships
                $wpdb->query( "DELETE FROM $wpdb->term_relationships WHERE object_id IN ($ids_string)" );
                // Delete Posts
                $wpdb->query( "DELETE FROM $wpdb->posts WHERE ID IN ($ids_string)" );
                
                $deleted_count += count( $revision_ids );
                
                // Stop if we processed a partial batch, meaning we are done
                if ( count( $revision_ids ) < 1000 ) {
                    break;
                }
                
                // Optional: Sleep to let DB breathe
                // usleep( 100000 ); 
            }
            return $deleted_count;
        }
    }

    private function clean_auto_drafts() {
        global $wpdb;
        $sql = "DELETE a,b,c FROM $wpdb->posts a
                LEFT JOIN $wpdb->term_relationships b ON (a.ID = b.object_id)
                LEFT JOIN $wpdb->postmeta c ON (a.ID = c.post_id)
                WHERE a.post_type = 'auto-draft'";
        return $wpdb->query( $sql );
    }

    private function clean_trashed_posts() {
        global $wpdb;
        $sql = "DELETE a,b,c FROM $wpdb->posts a
                LEFT JOIN $wpdb->term_relationships b ON (a.ID = b.object_id)
                LEFT JOIN $wpdb->postmeta c ON (a.ID = c.post_id)
                WHERE a.post_status = 'trash'";
        return $wpdb->query( $sql );
    }

    private function clean_spam_comments() {
        global $wpdb;
        $sql = "DELETE FROM $wpdb->comments WHERE comment_approved = 'spam'";
        $wpdb->query( $sql );
        $sql = "DELETE FROM $wpdb->commentmeta WHERE comment_id NOT IN (SELECT comment_ID FROM $wpdb->comments)";
        return $wpdb->query( $sql );
    }

    private function clean_trashed_comments() {
        global $wpdb;
        $sql = "DELETE FROM $wpdb->comments WHERE comment_approved = 'trash'";
        $wpdb->query( $sql );
        $sql = "DELETE FROM $wpdb->commentmeta WHERE comment_id NOT IN (SELECT comment_ID FROM $wpdb->comments)";
        return $wpdb->query( $sql );
    }

    private function clean_expired_transients() {
        global $wpdb;
        $time = time();
        $sql = "DELETE a, b FROM $wpdb->options a, $wpdb->options b
                WHERE a.option_name LIKE '_transient_%'
                AND a.option_name NOT LIKE '_transient_timeout_%'
                AND b.option_name = CONCAT( '_transient_timeout_', SUBSTRING( a.option_name, 12 ) )
                AND b.option_value < $time";
        return $wpdb->query( $sql );
    }

    private function clean_orphaned_postmeta() {
        global $wpdb;
        $sql = "DELETE FROM $wpdb->postmeta WHERE post_id NOT IN (SELECT ID FROM $wpdb->posts)";
        return $wpdb->query( $sql );
    }

    private function clean_orphaned_commentmeta() {
        global $wpdb;
        $sql = "DELETE FROM $wpdb->commentmeta WHERE comment_id NOT IN (SELECT comment_ID FROM $wpdb->comments)";
        return $wpdb->query( $sql );
    }

    private function optimize_tables() {
        global $wpdb;
        $tables = $wpdb->get_results( "SHOW TABLES", ARRAY_N );
        $query = "OPTIMIZE TABLE ";
        foreach ( $tables as $table ) {
            $query .= $table[0] . ",";
        }
        $query = rtrim( $query, ',' );
        return $wpdb->query( $query );
    }
}

WP_Academic_Post_Enhanced_Database_Optimizer::get_instance();
