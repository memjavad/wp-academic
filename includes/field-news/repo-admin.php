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
        $statuses = [ 'pending', 'selected', 'processed', 'ignored' ];
        $counts = [
            'all' => wp_count_posts( 'wpa_study' )->publish
        ];

        foreach ( $statuses as $status ) {
            // ⚡ Bolt: Replaced WP_Query with a direct $wpdb aggregate count to prevent expensive SQL_CALC_FOUND_ROWS execution
            $count = $wpdb->get_var( $wpdb->prepare( "
                SELECT COUNT(*) FROM {$wpdb->posts} p
                INNER JOIN {$wpdb->postmeta} pm ON ( p.ID = pm.post_id )
                WHERE p.post_type = 'wpa_study'
                AND p.post_status = 'publish'
                AND pm.meta_key = '_wpa_status'
                AND pm.meta_value = %s
            ", $status ) );
            $counts[ $status ] = (int) $count;
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
        ?>
        <div class="wrap wpa-settings-wrapper wpa-repo-wrap">
            <h1 class="wp-heading-inline"><?php esc_html_e( 'Study Repository', 'wp-academic-post-enhanced' ); ?></h1>
            
            <button type="button" id="wpa-fetch-trigger" class="page-title-action"><?php esc_html_e( 'Fetch New Candidates', 'wp-academic-post-enhanced' ); ?></button>
            <?php if ( $filter_status === 'pending' || $filter_status === 'all' ) : ?>
                <button type="button" id="wpa-ai-screen-trigger" class="page-title-action" style="border-color:#2271b1; color:#2271b1;"><?php esc_html_e( 'Run AI Screening', 'wp-academic-post-enhanced' ); ?></button>
            <?php endif; ?>
            <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=wpa_export_studies&status=' . $filter_status ), 'wpa_export_studies_nonce' ) ); ?>" class="page-title-action"><?php esc_html_e( 'Export CSV', 'wp-academic-post-enhanced' ); ?></a>
            
            <div style="display:inline-block; margin-left:10px;">
                <?php wp_dropdown_categories([
                    'show_option_none' => __( 'Select Import Category', 'wp-academic-post-enhanced' ),
                    'name' => 'wpa_import_cat',
                    'id' => 'wpa-import-cat',
                    'class' => '',
                    'hide_empty' => 0
                ]); ?>
                <button type="button" id="wpa-import-ris-trigger" class="page-title-action" style="margin-left:5px;"><?php esc_html_e( 'Import RIS', 'wp-academic-post-enhanced' ); ?></button>
            </div>
            
            <input type="file" id="wpa-ris-file-input" style="display:none;" accept=".ris">
            
            <span id="wpa-fetch-status" style="margin-left: 10px; display: none; align-items: center;">
                <span class="spinner is-active" style="float:none; margin:0 5px 0 0;"></span>
                <span class="msg" style="font-weight: 500; color: #2271b1;">Fetching studies... this may take 30-60 seconds.</span>
            </span>
            
            <hr class="wp-header-end">
            
            <ul class="subsubsub">
                <li class="all"><a href="<?php echo esc_url( add_query_arg('status', 'all', $base_url) ); ?>" class="<?php echo $filter_status == 'all' ? 'current' : ''; ?>">All <span class="count">(<?php echo $counts['all']; ?>)</span></a> |</li>
                <li class="pending"><a href="<?php echo esc_url( add_query_arg('status', 'pending', $base_url) ); ?>" class="<?php echo $filter_status == 'pending' ? 'current' : ''; ?>">Pending <span class="count">(<?php echo $counts['pending']; ?>)</span></a> |</li>
                <li class="selected"><a href="<?php echo esc_url( add_query_arg('status', 'selected', $base_url) ); ?>" class="<?php echo $filter_status == 'selected' ? 'current' : ''; ?>">Selected <span class="count">(<?php echo $counts['selected']; ?>)</span></a> |</li>
                <li class="processed"><a href="<?php echo esc_url( add_query_arg('status', 'processed', $base_url) ); ?>" class="<?php echo $filter_status == 'processed' ? 'current' : ''; ?>">Processed <span class="count">(<?php echo $counts['processed']; ?>)</span></a> |</li>
                <li class="ignored"><a href="<?php echo esc_url( add_query_arg('status', 'ignored', $base_url) ); ?>" class="<?php echo $filter_status == 'ignored' ? 'current' : ''; ?>">Ignored <span class="count">(<?php echo $counts['ignored']; ?>)</span></a></li>
            </ul>

            <form method="get" action="<?php echo esc_url( admin_url('edit.php') ); ?>">
                <input type="hidden" name="post_type" value="wpa_news" />
                <input type="hidden" name="page" value="wpa-field-news-repo" />
                <?php if ($filter_status !== 'all') : ?>
                    <input type="hidden" name="status" value="<?php echo esc_attr($filter_status); ?>" />
                <?php endif; ?>
                <p class="search-box">
                    <label class="screen-reader-text" for="post-search-input"><?php _e( 'Search Studies', 'wp-academic-post-enhanced' ); ?>:</label>
                    <input type="search" id="post-search-input" name="s" value="<?php echo esc_attr( $search ); ?>">
                    <input type="submit" id="search-submit" class="button" value="<?php _e( 'Search Studies', 'wp-academic-post-enhanced' ); ?>">
                </p>
            </form>

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

        <div id="wpa-data-modal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.7); align-items:center; justify-content:center;">
            <div style="background:#fff; width:80%; max-width:800px; height:85%; padding:30px; border-radius:12px; position:relative; display:flex; flex-direction:column; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);">
                <h2 style="margin-top:0; border-bottom:1px solid #eee; padding-bottom:15px; margin-bottom:20px;">Study Details</h2>
                <button id="wpa-modal-close" style="position:absolute; right:20px; top:20px; border:none; background:none; font-size:24px; cursor:pointer; color:#666;">&times;</button>
                <div id="wpa-data-content" style="flex:1; overflow-y:auto; padding-right:10px;">
                    <!-- Data rendered here -->
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // View Raw Data
            $('.wpa-view-data-btn').on('click', function() {
                var data = $(this).data('json');
                var html = '<dl style="display:grid; grid-template-columns: 120px 1fr; gap:10px;">';
                
                // Fields to display nicely
                var fields = {
                    'title': 'Title',
                    'creator': 'Author(s)',
                    'publication': 'Journal',
                    'date': 'Date',
                    'abstract': 'Abstract',
                    'doi': 'DOI',
                    'citations': 'Citations'
                };

                for (var key in fields) {
                    if (data[key]) {
                        html += '<dt style="font-weight:700; color:#555;">' + fields[key] + ':</dt>';
                        html += '<dd style="margin:0; padding-bottom:10px; border-bottom:1px solid #f0f0f0;">' + data[key] + '</dd>';
                    }
                }
                
                // Show other data as raw json at bottom if needed, or just specific fields
                html += '</dl>';
                
                $('#wpa-data-content').html(html);
                $('#wpa-data-modal').css('display', 'flex');
            });

            $('#wpa-modal-close, #wpa-data-modal').on('click', function(e) {
                if (e.target !== this && e.target.id !== 'wpa-modal-close') return;
                $('#wpa-data-modal').hide();
            });

            // RIS Import
            $('#wpa-import-ris-trigger').on('click', function(e) {
                e.preventDefault();
                var catId = $('#wpa-import-cat').val();
                if (catId == '-1' || catId == '') {
                    alert('<?php esc_html_e('Please select a category for the imported studies first.', 'wp-academic-post-enhanced'); ?>');
                    return;
                }
                $('#wpa-ris-file-input').click();
            });

            $('#wpa-ris-file-input').on('change', function() {
                var file = this.files[0];
                var catId = $('#wpa-import-cat').val();
                if (!file) return;

                var btn = $('#wpa-import-ris-trigger');
                var status = $('#wpa-fetch-status');
                var formData = new FormData();
                
                formData.append('action', 'wpa_import_ris_file');
                formData.append('nonce', '<?php echo wp_create_nonce('wpa_repo_nonce'); ?>');
                formData.append('ris_file', file);
                formData.append('cat_id', catId);

                btn.prop('disabled', true);
                status.css('display', 'inline-flex');
                status.find('.msg').css('color', '#2271b1').text('Importing RIS file...');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        if (res.success) {
                            status.find('.msg').css('color', '#166534').text('Success! Imported ' + res.data.count + ' studies. Reloading...');
                            setTimeout(function() { window.location.reload(); }, 1500);
                        } else {
                            alert('Error: ' + res.data);
                            status.hide();
                            btn.prop('disabled', false);
                        }
                    },
                    error: function(xhr, textStatus, errorThrown) {
                        alert('Upload Failed: ' + textStatus);
                        status.hide();
                        btn.prop('disabled', false);
                    }
                });
                
                // Reset input
                $(this).val('');
            });

            // Checkbox All
            $('#cb-select-all-1').on('click', function() {
                $('input[name="study_ids[]"]').prop('checked', this.checked);
            });

            // Generate News (Existing)
            $('.wpa-generate-btn').on('click', function(e) {
                e.preventDefault();
                var btn = $(this);
                var id = btn.data('id');
                var row = btn.closest('tr');
                
                btn.text('Generating...').prop('disabled', true);
                
                $.post(ajaxurl, {
                    action: 'wpa_generate_from_repo',
                    study_id: id,
                    nonce: '<?php echo wp_create_nonce('wpa_repo_nonce'); ?>'
                }, function(res) {
                    if (res.success) {
                        btn.text('Done').removeClass('button-primary').addClass('disabled');
                        row.find('.status span').css({background:'#dcfce7', color:'#166534'}).text('PROCESSED');
                        // Optional: Redirect to edit page
                        // window.location.href = 'post.php?post=' + res.data.id + '&action=edit';
                    } else {
                        alert('Error: ' + res.data);
                        btn.text('Generate News').prop('disabled', false);
                    }
                });
            });

            // Fetch Studies
            $('#wpa-fetch-trigger').on('click', function(e) {
                e.preventDefault();
                var btn = $(this);
                var status = $('#wpa-fetch-status');
                
                btn.prop('disabled', true);
                status.css('display', 'inline-flex');
                status.find('.msg').css('color', '#2271b1').text('Fetching from Scopus... please wait.');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'wpa_fetch_repo_studies',
                        nonce: '<?php echo wp_create_nonce('wpa_repo_nonce'); ?>'
                    },
                    timeout: 120000, // 2 minutes
                    success: function(res) {
                        if (res.success) {
                            var count = res.data.count;
                            if (count > 0) {
                                status.find('.msg').css('color', '#166534').text('Success! Found ' + count + ' new studies. Reloading...');
                                setTimeout(function() { window.location.reload(); }, 1000);
                            } else {
                                status.find('.msg').css('color', '#b91c1c').text('No new candidates found with current settings.');
                                btn.prop('disabled', false);
                            }
                        } else {
                            status.find('.msg').css('color', 'red').text('Error: ' + res.data);
                            btn.prop('disabled', false);
                        }
                    },
                    error: function(xhr, textStatus, errorThrown) {
                        var errMsg = 'Request Failed: ' + textStatus + ' (' + xhr.status + ')';
                        if (textStatus === 'timeout') errMsg = 'Request Timed Out (Client Side).';
                        status.find('.msg').css('color', 'red').text(errMsg);
                        btn.prop('disabled', false);
                    }
                });
            });

            // AI Bulk Screening
            $('#wpa-ai-screen-trigger').on('click', function(e) {
                e.preventDefault();
                var btn = $(this);
                var status = $('#wpa-fetch-status');
                
                if (!confirm('Run AI Screening on up to 20 pending studies? This consumes AI quota.')) return;

                btn.prop('disabled', true);
                status.css('display', 'inline-flex');
                status.find('.msg').css('color', '#2271b1').text('AI is screening studies... this may take a minute.');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'wpa_ai_bulk_screen',
                        nonce: '<?php echo wp_create_nonce('wpa_repo_nonce'); ?>'
                    },
                    timeout: 120000, // 2 minutes
                    success: function(res) {
                        if (res.success) {
                            var msg = 'Screening complete! ';
                            if (res.data.selected > 0) msg += res.data.selected + ' selected. ';
                            if (res.data.ignored > 0) msg += res.data.ignored + ' ignored.';
                            status.find('.msg').css('color', '#166534').text(msg);
                            setTimeout(function() { window.location.reload(); }, 1500);
                        } else {
                            status.find('.msg').css('color', 'red').text('Error: ' + res.data);
                            btn.prop('disabled', false);
                        }
                    },
                    error: function(xhr, textStatus, errorThrown) {
                        status.find('.msg').css('color', 'red').text('Screening Failed: ' + textStatus);
                        btn.prop('disabled', false);
                    }
                });
            });
        });
        </script>
        <?php
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