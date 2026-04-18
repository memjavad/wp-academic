<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

function wp_academic_post_enhanced_render_database_optimization_section() {
    ?>
    <p><?php esc_html_e( 'Optimize your database by cleaning up unnecessary data.', 'wp-academic-post-enhanced' ); ?></p>
    
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <input type="hidden" name="action" value="wpa_optimize_database">
        <?php wp_nonce_field( 'wpa_optimize_database', 'wpa_optimize_database_nonce' ); ?>
        
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Cleanup Options', 'wp-academic-post-enhanced' ); ?></th>
                    <td>
                        <fieldset>
                            <label for="clean_revisions">
                                <input type="checkbox" name="wpa_optimize_actions[]" id="clean_revisions" value="revisions" checked>
                                <?php esc_html_e( 'Clean Post Revisions', 'wp-academic-post-enhanced' ); ?>
                            </label>
                            <span class="description">
                                <?php esc_html_e( 'Keep last', 'wp-academic-post-enhanced' ); ?>
                                <input type="number" name="wpa_keep_revisions" value="5" min="0" step="1" class="small-text">
                                <?php esc_html_e( 'revisions', 'wp-academic-post-enhanced' ); ?>
                            </span><br>
                            
                            <label for="clean_auto_drafts">
                                <input type="checkbox" name="wpa_optimize_actions[]" id="clean_auto_drafts" value="auto_drafts" checked>
                                <?php esc_html_e( 'Clean Auto Drafts', 'wp-academic-post-enhanced' ); ?>
                            </label><br>
                            
                            <label for="clean_trashed_posts">
                                <input type="checkbox" name="wpa_optimize_actions[]" id="clean_trashed_posts" value="trashed_posts" checked>
                                <?php esc_html_e( 'Clean Trashed Posts', 'wp-academic-post-enhanced' ); ?>
                            </label><br>
                            
                            <label for="clean_spam_comments">
                                <input type="checkbox" name="wpa_optimize_actions[]" id="clean_spam_comments" value="spam_comments" checked>
                                <?php esc_html_e( 'Clean Spam Comments', 'wp-academic-post-enhanced' ); ?>
                            </label><br>
                            
                            <label for="clean_trashed_comments">
                                <input type="checkbox" name="wpa_optimize_actions[]" id="clean_trashed_comments" value="trashed_comments" checked>
                                <?php esc_html_e( 'Clean Trashed Comments', 'wp-academic-post-enhanced' ); ?>
                            </label><br>
                            
                            <label for="clean_expired_transients">
                                <input type="checkbox" name="wpa_optimize_actions[]" id="clean_expired_transients" value="expired_transients" checked>
                                <?php esc_html_e( 'Clean Expired Transients', 'wp-academic-post-enhanced' ); ?>
                            </label><br>
                            
                            <label for="clean_orphaned_postmeta">
                                <input type="checkbox" name="wpa_optimize_actions[]" id="clean_orphaned_postmeta" value="orphaned_postmeta">
                                <?php esc_html_e( 'Clean Orphaned Post Meta', 'wp-academic-post-enhanced' ); ?>
                            </label><br>
                            
                            <label for="clean_orphaned_commentmeta">
                                <input type="checkbox" name="wpa_optimize_actions[]" id="clean_orphaned_commentmeta" value="orphaned_commentmeta">
                                <?php esc_html_e( 'Clean Orphaned Comment Meta', 'wp-academic-post-enhanced' ); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Scheduled Optimization', 'wp-academic-post-enhanced' ); ?></th>
                    <td>
                        <?php $schedule = get_option( 'wpa_database_optimization_schedule', 'none' ); ?>
                        <select name="wpa_schedule_optimization">
                            <option value="none" <?php selected( $schedule, 'none' ); ?>><?php esc_html_e( 'Disabled', 'wp-academic-post-enhanced' ); ?></option>
                            <option value="daily" <?php selected( $schedule, 'daily' ); ?>><?php esc_html_e( 'Daily', 'wp-academic-post-enhanced' ); ?></option>
                            <option value="weekly" <?php selected( $schedule, 'weekly' ); ?>><?php esc_html_e( 'Weekly', 'wp-academic-post-enhanced' ); ?></option>
                            <option value="monthly" <?php selected( $schedule, 'monthly' ); ?>><?php esc_html_e( 'Monthly', 'wp-academic-post-enhanced' ); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e( 'Automatically run selected cleanup actions.', 'wp-academic-post-enhanced' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Table Optimization', 'wp-academic-post-enhanced' ); ?></th>
                    <td>
                        <label for="optimize_tables">
                            <input type="checkbox" name="wpa_optimize_actions[]" id="optimize_tables" value="optimize_tables">
                            <?php esc_html_e( 'Optimize Database Tables', 'wp-academic-post-enhanced' ); ?>
                        </label>
                        <p class="description"><?php esc_html_e( 'This effectively defragments your MySQL tables.', 'wp-academic-post-enhanced' ); ?></p>
                    </td>
                </tr>
            </tbody>
        </table>

        <?php submit_button( __( 'Run Optimization', 'wp-academic-post-enhanced' ) ); ?>
    </form>
    <?php
}
