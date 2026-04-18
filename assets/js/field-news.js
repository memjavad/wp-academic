jQuery(document).ready(function($) {
    $('#wpa-field-news-run-btn').on('click', function(e) {
        e.preventDefault();
        
        var $btn = $(this);
        var $results = $('#wpa-field-news-results');
        
        $btn.prop('disabled', true).html('<span class="wpa-loading"></span> Generating...');
        $results.hide().removeClass('success error').html('');

        $.ajax({
            url: wpaFieldNews.ajax_url,
            type: 'POST',
            data: {
                action: 'wpa_generate_field_news',
                nonce: wpaFieldNews.nonce
            },
            success: function(response) {
                $results.show();
                if (response.success) {
                    $results.addClass('success').html(
                        '<h3>' + response.data.message + '</h3>' +
                        '<p><a href="' + response.data.edit_link + '" class="button button-primary" target="_blank">Edit Generated Post</a></p>'
                    );
                } else {
                    $results.addClass('error').html(
                        '<h3>Error</h3>' +
                        '<p>' + response.data + '</p>'
                    );
                }
            },
            error: function() {
                $results.show().addClass('error').html('<h3>Server Error</h3><p>Please try again later.</p>');
            },
            complete: function() {
                $btn.prop('disabled', false).text('Fetch & Generate News Story');
            }
        });
    });

    // Test API Button Handler
    $('.wpa-test-api-btn').on('click', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var api = $btn.data('api');
        var $input = $('#' + api + '_api_key');
        var keyVal = $input.val();
        var $result = $('#result-' + api);

        if (!keyVal) {
            $result.css('color', '#dc3232').text('Please enter a key first.');
            return;
        }

        $btn.prop('disabled', true).text('Testing...');
        $result.css('color', '#2271b1').text('Connecting...');

        $.ajax({
            url: wpaFieldNews.ajax_url,
            type: 'POST',
            data: {
                action: 'wpa_test_field_news_api',
                nonce: wpaFieldNews.nonce,
                api: api,
                key_value: keyVal
            },
            success: function(response) {
                if (response.success) {
                    $result.css('color', '#46b450').text(response.data);
                } else {
                    $result.css('color', '#dc3232').text(response.data);
                }
            },
            error: function() {
                $result.css('color', '#dc3232').text('Server Error');
            },
            complete: function() {
                $btn.prop('disabled', false).text('Test');
            }
        });
    });

    // Repeater Field Logic
    var $repeater = $('#wpa-topic-repeater');
    
    if ($repeater.length) {
        var $list = $('#wpa-topic-list');
        var tmpl = $('#wpa-topic-row-tmpl').html();
        
        // Add Row
        $('#wpa-add-topic-row').on('click', function(e) {
            e.preventDefault();
            var index = new Date().getTime(); // Unique ID
            var rowHtml = tmpl.replace(/INDEX/g, index);
            $list.append(rowHtml);
        });

        // Remove Row
        $repeater.on('click', '.wpa-remove-row', function(e) {
            e.preventDefault();
            // Don't remove the last row
            if ($list.find('tr').length > 1) {
                $(this).closest('tr').remove();
            } else {
                alert('You must have at least one topic group.');
            }
        });
    }

    // Browser Heartbeat (Keep-Alive for Local Dev)
    if (wpaFieldNews.heartbeat_enabled) {
        setInterval(function() {
            // Minimal ping to admin-ajax to ensure PHP runs
            $.post(wpaFieldNews.ajax_url, { action: 'wpa_heartbeat' });
        }, 60000); // 1 minute
    }
});
