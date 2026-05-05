            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <input type="hidden" name="action" value="wpa_bulk_repo_actions">
                <?php wp_nonce_field( 'wpa_bulk_repo_nonce' ); ?>

                <div class="tablenav top">
                    <div class="alignleft actions bulkactions">
                        <label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label>
                        <select name="bulk_action" id="bulk-action-selector-top">
                            <option value="-1">Bulk Actions</option>
                            <option value="mark_selected">Mark for Publication</option>
                            <option value="delete">Move to Trash</option>
                            <option value="restore">Restore to Pending</option>
                        </select>
                        <input type="submit" id="doaction" class="button action" value="Apply">
                    </div>
                    <?php
                    $big = 999999999;
                    echo '<div class="tablenav-pages">';
                    echo paginate_links( [
                        'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
                        'format' => '&paged=%#%',
                        'current' => $paged,
                        'total' => $query->max_num_pages
                    ] );
                    echo '</div>';
                    ?>
                </div>

                <table class="wp-list-table widefat fixed striped table-view-list posts">
                    <thead>
                        <tr>
                            <td id="cb" class="manage-column column-cb check-column"><input id="cb-select-all-1" type="checkbox"></td>
                            <th class="manage-column column-title" style="width: 30%;"><?php esc_html_e( 'Study Title', 'wp-academic-post-enhanced' ); ?></th>
                            <th class="manage-column column-journal"><?php esc_html_e( 'Journal / Year', 'wp-academic-post-enhanced' ); ?></th>
                            <th class="manage-column column-metrics" style="width: 10%;"><?php esc_html_e( 'Metrics', 'wp-academic-post-enhanced' ); ?></th>
                            <th class="manage-column column-topic"><?php esc_html_e( 'Topic', 'wp-academic-post-enhanced' ); ?></th>
                            <th class="manage-column column-status" style="width: 10%;"><?php esc_html_e( 'Status', 'wp-academic-post-enhanced' ); ?></th>
                            <th class="manage-column column-actions" style="width: 15%;"><?php esc_html_e( 'Actions', 'wp-academic-post-enhanced' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post();
                            $data = get_post_meta( get_the_ID(), '_wpa_study_data', true );
                            $status = get_post_meta( get_the_ID(), '_wpa_status', true );
                            $query_term = get_post_meta( get_the_ID(), '_wpa_query', true );
                            if ( ! $status ) $status = 'pending';
                        ?>
                            <tr>
                                <th scope="row" class="check-column"><input type="checkbox" name="study_ids[]" value="<?php the_ID(); ?>"></th>
                                <td class="title column-title">
                                    <strong><?php the_title(); ?></strong>
                                    <?php if ( ! empty( $data['creator'] ) ) : ?>
                                        <br><span style="color:#666; font-size:0.9em;">by <?php echo esc_html( $data['creator'] ); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="journal column-journal">
                                    <?php echo esc_html( isset($data['publication']) ? $data['publication'] : '-' ); ?>
                                    <br>
                                    <span style="color:#666;"><?php echo esc_html( isset($data['date']) ? substr($data['date'],0,4) : '' ); ?></span>
                                </td>
                                <td class="metrics column-metrics">
                                    <span class="dashicons dashicons-chart-area" style="font-size:16px; width:16px; height:16px;"></span> <?php echo intval( isset($data['citations']) ? $data['citations'] : 0 ); ?>
                                    <?php if ( ! empty($data['openaccess']) && $data['openaccess'] !== 'false' ) : ?>
                                        <br><span style="color:#10b981; font-size:0.8em; font-weight:600;"><span class="dashicons dashicons-unlock" style="font-size:14px; width:14px; height:14px;"></span> OA</span>
                                    <?php endif; ?>
                                </td>
                                <td class="topic column-topic">
                                    <span class="wpa-query-badge" style="background:#eff6ff; color:#1e40af; padding:2px 6px; border-radius:4px; font-size:0.85em; border:1px solid #dbeafe;"><?php echo esc_html( $query_term ); ?></span>
                                </td>
                                <td class="status column-status">
                                    <?php
                                    $badge_color = '#f3f4f6; color:#4b5563'; // Default Pending
                                    if ( $status == 'processed' ) $badge_color = '#dcfce7; color:#166534';
                                    if ( $status == 'selected' ) $badge_color = '#dbeafe; color:#1e40af';
                                    if ( $status == 'ignored' ) $badge_color = '#fee2e2; color:#991b1b';
                                    echo '<span style="background:' . $badge_color . '; padding:4px 8px; border-radius:4px; font-weight:600; font-size:0.8em; text-transform:uppercase;">' . esc_html( $status ) . '</span>';
                                    ?>
                                </td>
                                <td class="actions column-actions">
                                    <?php if ( $status !== 'processed' ) : ?>
                                        <button type="button" class="button button-primary wpa-generate-btn" data-id="<?php the_ID(); ?>">
                                            <?php esc_html_e( 'Generate', 'wp-academic-post-enhanced' ); ?>
                                        </button>
                                    <?php else: ?>
                                        <a href="<?php echo get_edit_post_link( get_post_meta( get_the_ID(), '_wpa_news_post_id', true ) ); ?>" class="button"><?php esc_html_e( 'Edit', 'wp-academic-post-enhanced' ); ?></a>
                                    <?php endif; ?>
                                    <button type="button" class="button wpa-view-data-btn" data-json='<?php echo esc_attr( wp_json_encode($data) ); ?>'>
                                        <span class="dashicons dashicons-visibility" style="margin-top:4px;"></span>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; else : ?>
                            <tr><td colspan="7"><?php esc_html_e( 'No studies found. Click "Fetch New Candidates".', 'wp-academic-post-enhanced' ); ?></td></tr>
                        <?php endif; wp_reset_postdata(); ?>
                    </tbody>
                </table>
            </form>
        </div>
