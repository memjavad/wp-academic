jQuery(document).ready(function($) {
    var $container = $('#wpa-layout-container');
    var $input = $('#wpa_homepage_layout_input');
    
    // Initial Render
    var layout = [];
    try {
        layout = typeof wpaInitialLayout === 'string' ? JSON.parse(wpaInitialLayout) : wpaInitialLayout;
    } catch (e) {
        console.error('WPA Builder: Failed to parse initial layout JSON:', e);
    }
    if (!Array.isArray(layout)) layout = [];
    
    renderLayout();

    // Sortable
    $container.sortable({
        handle: '.wpa-block-header',
        placeholder: 'wpa-sortable-placeholder',
        update: updateInput
    });

    // Global Actions
    $('.wpa-expand-all').on('click', function() {
        $('.wpa-block-content').slideDown();
        $('.wpa-toggle-edit .dashicons').removeClass('dashicons-arrow-down-alt2').addClass('dashicons-arrow-up-alt2');
    });

    $('.wpa-collapse-all').on('click', function() {
        $('.wpa-block-content').slideUp();
        $('.wpa-toggle-edit .dashicons').removeClass('dashicons-arrow-up-alt2').addClass('dashicons-arrow-down-alt2');
    });

    $('.wpa-clear-layout').on('click', function() {
        if (confirm('Are you sure you want to remove all blocks? This cannot be undone.')) {
            layout = [];
            renderLayout();
            updateInput();
        }
    });

    // Add Block
    $('.wpa-add-block-btn').on('click', function() {
        try {
            var type = $(this).closest('.wpa-lib-item').data('type');
            addBlock(type);
        } catch (e) {
            alert('Builder Error: ' + e.message);
            console.error(e);
        }
    });

    function addBlock(type, dataOverride) {
        if (!wpaBlockDefs[type]) {
            alert('Error: Block definition not found for type "' + type + '".');
            return;
        }

        var newBlock = {
            id: 'block_' + new Date().getTime(),
            type: type,
            data: dataOverride ? JSON.parse(JSON.stringify(dataOverride)) : {}
        };
        
        // Set Defaults if not overriding
        if (!dataOverride) {
            var fields = wpaBlockDefs[type].fields;
            if (fields) {
                for (var key in fields) {
                    if (fields[key].default !== undefined) {
                        newBlock.data[key] = fields[key].default;
                    }
                }
            }
        }

        layout.push(newBlock);
        renderBlock(newBlock, layout.length - 1);
        updateInput();
        
        // Scroll to new block
        var $newEl = $container.children().last();
        $('html, body').animate({ scrollTop: $newEl.offset().top - 100 }, 500);
        
        // Flash effect
        $newEl.css('background-color', '#f0f6fc').delay(300).queue(function(next){
            $(this).css('background-color', ''); 
            next();
        });
    }

    // Remove Block
    $container.on('click', '.wpa-remove-block', function() {
        if (confirm('Are you sure you want to remove this section?')) {
            $(this).closest('.wpa-builder-block').remove();
            updateInput();
        }
    });

    // Duplicate Block
    $container.on('click', '.wpa-duplicate-block', function(e) {
        e.stopPropagation();
        var $block = $(this).closest('.wpa-builder-block');
        var id = $block.data('id');
        var type = $block.data('type');
        
        // Find current data
        var currentData = {};
        $block.find('.wpa-block-field').each(function() {
            var key = $(this).data('key');
            currentData[key] = $(this).val();
        });

        addBlock(type, currentData);
    });

    // Toggle Edit
    $container.on('click', '.wpa-block-header', function(e) {
        if ($(e.target).closest('button').length) return; // Ignore button clicks
        $(this).closest('.wpa-builder-block').find('.wpa-block-content').slideToggle();
        $(this).find('.wpa-toggle-edit .dashicons').toggleClass('dashicons-arrow-down-alt2 dashicons-arrow-up-alt2');
    });

    $container.on('click', '.wpa-toggle-edit', function(e) {
        e.stopPropagation(); // Handled by header click
        $(this).closest('.wpa-block-header').trigger('click');
    });

    // Live Data Update & Summary Update
    $container.on('change input', '.wpa-block-field', function() {
        var $field = $(this);
        var key = $field.data('key');
        
        // Update Summary if this is a title field
        if (key === 'title') {
            var val = $field.val();
            var $summary = $field.closest('.wpa-builder-block').find('.wpa-block-summary');
            if (val) {
                $summary.text(': ' + val);
            } else {
                $summary.text('');
            }
        }
        
        updateInput();
    });

    function renderLayout() {
        $container.empty();
        if (!Array.isArray(layout)) {
            console.error('WPA Builder: Layout is not an array:', layout);
            return;
        }
        layout.forEach(function(block, index) {
            renderBlock(block, index);
        });
    }

    function renderBlock(block, index) {
        if (!wpaBlockDefs || !wpaBlockDefs[block.type]) return;
        
        var def = wpaBlockDefs[block.type];
        var widthClass = (block.data && block.data._width == '50') ? 'wpa-block-width-50' : '';
        var $el = $('<div class="wpa-builder-block ' + widthClass + '" data-id="' + block.id + '" data-type="' + block.type + '"></div>');
        
        // Summary Text
        var summaryText = '';
        if (block.data && block.data.title) {
            summaryText = ': ' + block.data.title;
        }

        var header = `
            <div class="wpa-block-header">
                <span class="dashicons ${def.icon}"></span>
                <span class="wpa-block-title">${def.label} <span class="wpa-block-summary">${escapeHtml(summaryText)}</span></span>
                <div class="wpa-block-actions">
                    <button type="button" class="wpa-duplicate-block" title="Duplicate"><span class="dashicons dashicons-admin-page"></span></button>
                    <button type="button" class="wpa-toggle-edit"><span class="dashicons dashicons-arrow-down-alt2"></span></button>
                    <button type="button" class="wpa-remove-block" title="Remove"><span class="dashicons dashicons-trash"></span></button>
                </div>
            </div>
        `;

        var content = '<div class="wpa-block-content">';
        
        for (var key in def.fields) {
            var field = def.fields[key];
            var val = (block.data && block.data[key]) !== undefined ? block.data[key] : '';
            var inputId = block.id + '_' + key;
            
            content += `<div class="wpa-field-row">
                <label for="${inputId}">${field.label}</label>`;
            
            if (field.type === 'textarea') {
                content += `<textarea id="${inputId}" data-key="${key}" class="wpa-block-field widefat" rows="3">${escapeHtml(val)}</textarea>`;
            } else if (field.type === 'select') {
                content += `<select id="${inputId}" data-key="${key}" class="wpa-block-field widefat">`;
                for (var optKey in field.options) {
                    var selected = val === optKey ? 'selected' : '';
                    content += `<option value="${optKey}" ${selected}>${field.options[optKey]}</option>`;
                }
                content += `</select>`;
            } else {
                content += `<input type="${field.type === 'number' ? 'number' : 'text'}" id="${inputId}" data-key="${key}" class="wpa-block-field widefat" value="${escapeHtml(val)}">`;
            }
            
            content += `</div>`;
        }
        
        content += '</div>';
        
        $el.append(header + content);

        // Live Width Toggle
        $el.find('[data-key="_width"]').on('change', function() {
            if ($(this).val() == '50') {
                $el.addClass('wpa-block-width-50');
            } else {
                $el.removeClass('wpa-block-width-50');
            }
        });

        $container.append($el);
    }

    function updateInput() {
        var newLayout = [];
        $container.find('.wpa-builder-block').each(function() {
            var $block = $(this);
            var id = $block.data('id');
            var type = $block.data('type');
            var data = {};
            
            $block.find('.wpa-block-field').each(function() {
                var key = $(this).data('key');
                data[key] = $(this).val();
            });
            
            newLayout.push({ id: id, type: type, data: data });
        });
        
        layout = newLayout;
        $input.val(JSON.stringify(layout));
    }

    function escapeHtml(text) {
        if (text === null || text === undefined) return '';
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
});
