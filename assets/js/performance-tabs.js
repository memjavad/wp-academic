jQuery(document).ready(function($) {
    // 1. Vertical Tab Switcher (Main Groups)
    $(document).on('click', '.wpa-vtab', function(e) {
        e.preventDefault();
        var target = $(this).data('target');
        var $targetGroup = $('#' + target);
        
        // Sidebar Active State
        $('.wpa-vtab').removeClass('active');
        $(this).addClass('active');
        
        // Hide all groups
        $('.wpa-group-content').removeClass('active');
        
        // Show target with animation
        setTimeout(function() {
            $targetGroup.addClass('active');
            
            // Automatically activate the first horizontal tab in this group if none active
            if (!$targetGroup.find('.nav-tab-wrapper a.nav-tab-active').length) {
                var $firstTab = $targetGroup.find('.nav-tab-wrapper a.nav-tab').first();
                if ($firstTab.length) {
                    $firstTab.trigger('click');
                }
            }
        }, 10);

        // Update URL hash
        if (history.pushState) {
            history.pushState(null, null, '#' + target);
        }
    });

    // 2. Horizontal Tab Switcher (Scoped to active group or container)
    $(document).on('click', '.nav-tab-wrapper a.nav-tab', function(e) {
        e.preventDefault();
        var $this = $(this);
        var href = $this.attr('href');
        if (!href) return;

        var targetId = href.substring(href.indexOf('#'));
        var $container = $this.closest('.wpa-group-content');
        if (!$container.length) $container = $this.closest('.wrap');

        // Tab State
        $this.siblings('.nav-tab').removeClass('nav-tab-active');
        $this.addClass('nav-tab-active');

        // Content State
        $container.find('.tab-content').removeClass('active').hide();
        var $targetContent = $container.find(targetId);
        if ($targetContent.length) {
            $targetContent.addClass('active').show();
        }

        // Update URL hash without scroll jump
        if (history.pushState) {
            history.pushState(null, null, targetId);
        }
    });

    // Initial Load: Check hash
    var currentHash = window.location.hash;
    if (currentHash) {
        // Try vertical tab first
        var $vTab = $('.wpa-vtab[data-target="' + currentHash.substring(1) + '"]');
        if ($vTab.length) {
            $vTab.trigger('click');
        } else {
            // Try horizontal tab
            var $hTab = $('.nav-tab-wrapper a[href$="' + currentHash + '"]');
            if ($hTab.length) {
                // If it's inside a group, activate the group first
                var $group = $hTab.closest('.wpa-group-content');
                if ($group.length) {
                    $('.wpa-vtab[data-target="' + $group.attr('id') + '"]').addClass('active');
                    $('.wpa-group-content').removeClass('active');
                    $group.addClass('active');
                }
                $hTab.trigger('click');
            }
        }
    }
});