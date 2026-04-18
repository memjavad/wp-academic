jQuery(document).ready(function($) {
    // 1. Color Picker
    if ( typeof $.fn.wpColorPicker === 'function' ) {
        $('.wpa-color-picker').wpColorPicker();
    }

    // 2. Media Library Uploader
    var frame;
    $(document).on('click', '.wpa-media-upload-btn', function(e) {
        e.preventDefault();
        
        var $btn = $(this);
        var $container = $btn.closest('.wpa-media-picker-container');
        var $input = $container.find('input[type="hidden"]');
        var $preview = $container.find('.wpa-user-avatar-preview');
        var $removeBtn = $container.find('.wpa-media-remove-btn');

        if (frame) {
            frame.open();
            return;
        }

        frame = wp.media({
            title: $btn.data('title') || 'Select Image',
            button: { text: $btn.data('button') || 'Use Image' },
            multiple: false
        });

        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            $input.val(attachment.id);
            var thumb = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;
            $preview.attr('src', thumb).show();
            $removeBtn.show();
        });

        frame.open();
    });

    $(document).on('click', '.wpa-media-remove-btn', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var $container = $btn.closest('.wpa-media-picker-container');
        $container.find('input[type="hidden"]').val('');
        $container.find('.wpa-user-avatar-preview').hide();
        $btn.hide();
    });

    // 3. Tab Switching Logic (Restored for Course Admin Page)
    $(document).on('click', '.nav-tab-wrapper a.nav-tab', function(e) {
        e.preventDefault();

        var $this = $(this);
        var href = $this.attr('href');
        
        if (!href) return;

        // Parse ID
        var hashIndex = href.indexOf('#');
        if (hashIndex === -1) return;
        var targetId = href.substring(hashIndex);

        var $container = $this.closest('.wpa-settings-wrapper');
        
        // Tab Activation
        $container.find('.nav-tab-wrapper a.nav-tab').removeClass('nav-tab-active');
        $this.addClass('nav-tab-active');

        // Content Activation
        $container.find('.tab-content').removeClass('active').hide(); // Hide all
        $container.find(targetId).addClass('active').show(); // Show target

        // Update URL state
        if (history.pushState) {
            history.pushState(null, null, targetId);
        }
    });

    // Initial Tab Activation
    var currentHash = window.location.hash;
    if ( currentHash ) {
        var $initialTab = $('.nav-tab-wrapper a[href$="' + currentHash + '"]');
        if ( $initialTab.length ) {
            $initialTab.trigger('click');
        }
    }
});