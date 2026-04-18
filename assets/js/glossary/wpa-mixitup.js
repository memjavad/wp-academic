/**
 * WPA Glossary MixItUp Mock/Replacement
 * Provides essential filtering functionality since the original library was truncated.
 */
var mixitup = function(container, config) {
    var $ = jQuery;
    var $container = $(container);
    var targetSelector = config.selectors.target;
    var controlSelector = config.selectors.control;
    
    // Internal State
    var state = {
        activeFilter: 'all',
        container: { id: $container.attr('id') }
    };

    // Handle Control Clicks
    $(document).on('click', controlSelector, function() {
        var $btn = $(this);
        var filter = $btn.attr('data-filter');
        
        // Update Classes
        $(controlSelector).removeClass('active');
        $btn.addClass('active');
        
        // Execute Filtering
        performFilter(filter);
    });

    function performFilter(filter) {
        state.activeFilter = filter;
        
        if (filter === 'all') {
            $container.find(targetSelector).fadeIn(200);
        } else {
            // Hide everything else, show only the target
            $container.find(targetSelector).hide();
            $container.find(filter).fadeIn(200);
        }

        // Trigger Callbacks
        if (config.callbacks && typeof config.callbacks.onMixEnd === 'function') {
            config.callbacks.onMixEnd(state);
        }
    }

    // Return Public API
    return {
        filter: function(selector) {
            performFilter(selector);
        }
    };
};