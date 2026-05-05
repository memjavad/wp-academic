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
