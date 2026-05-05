
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
