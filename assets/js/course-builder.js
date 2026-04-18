jQuery(document).ready(function($) {
    var $canvas = $('#wpa-builder-canvas');
    var $addSectionBtn = $('#wpa-add-section-btn');
    var $addLessonBtn = $('#wpa-add-lesson-btn');
    var $lessonInput = $('#wpa-new-lesson-title');
    var $spinner = $('.wpa-builder-toolbar .spinner');
    var courseId = $('#post_ID').val();

    // 1. Initialize Sortables
    function initSortables() {
        // Sortable Sections
        $canvas.sortable({
            handle: '.wpa-section-handle',
            placeholder: 'ui-sortable-placeholder',
            items: '.wpa-builder-section:not(.wpa-unassigned-section)', // Unassigned fixed at bottom? Or sortable? Let's fix it.
            update: function() { saveStructure(); }
        });

        // Sortable Lessons (Connected)
        $('.wpa-section-lessons').sortable({
            connectWith: '.wpa-section-lessons',
            handle: '.wpa-lesson-handle',
            placeholder: 'ui-sortable-placeholder',
            update: function(event, ui) {
                // Only save once per move (sender or receiver)
                if (this === ui.item.parent()[0]) {
                    saveStructure();
                }
            }
        });
    }
    initSortables();

    // 2. Add Section
    $addSectionBtn.on('click', function(e) {
        e.preventDefault();
        var template = $('#wpa-section-template').html();
        // Insert before Unassigned section
        $('.wpa-unassigned-section').before(template);
        initSortables(); // Re-init
    });

    // 3. Remove Section
    $canvas.on('click', '.wpa-section-remove', function() {
        if (!confirm('Delete this section? Lessons inside will be moved to Unassigned.')) return;
        
        var $section = $(this).closest('.wpa-builder-section');
        var $lessons = $section.find('.wpa-builder-lesson-item');
        
        // Move lessons to unassigned
        $('.wpa-unassigned-section .wpa-section-lessons').append($lessons);
        $section.remove();
        saveStructure();
    });

    // 4. Add Lesson
    $addLessonBtn.on('click', function(e) {
        e.preventDefault();
        var title = $lessonInput.val();
        if (!title) return;

        $addLessonBtn.prop('disabled', true);
        $spinner.addClass('is-active');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wpa_add_lesson',
                nonce: wpa_course_vars.nonce,
                course_id: courseId,
                title: title
            },
            success: function(res) {
                if (res.success) {
                    $('.wpa-unassigned-section .wpa-section-lessons').append(res.data.html);
                    $lessonInput.val('');
                    initSortables(); // Bind events to new item
                    saveStructure(); // Save it being in unassigned (sets order)
                } else {
                    alert(res.data);
                }
            },
            complete: function() {
                $addLessonBtn.prop('disabled', false);
                $spinner.removeClass('is-active');
            }
        });
    });

    // 5. Remove Lesson
    $canvas.on('click', '.wpa-lesson-remove', function(e) {
        e.preventDefault();
        if (!confirm('Remove this lesson from the course?')) return;
        
        var $item = $(this).closest('.wpa-builder-lesson-item');
        var lessonId = $item.data('id');
        
        $item.css('opacity', 0.5);

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wpa_remove_lesson',
                nonce: wpa_course_vars.nonce,
                lesson_id: lessonId
            },
            success: function(res) {
                if (res.success) {
                    $item.remove();
                } else {
                    $item.css('opacity', 1);
                    alert('Error removing lesson.');
                }
            }
        });
    });

    // 6. Section Title Change
    $canvas.on('change', '.wpa-section-title-input', function() {
        saveStructure();
    });

    // 7. Save Structure
    function saveStructure() {
        var structure = [];
        
        $('.wpa-builder-section').each(function() {
            var $sect = $(this);
            var sectionName = $sect.find('.wpa-section-title-input').val(); // Can be undefined for unassigned
            
            // If it's the unassigned section, name is empty
            if ($sect.hasClass('wpa-unassigned-section')) sectionName = '';

            var lessons = [];
            $sect.find('.wpa-builder-lesson-item').each(function() {
                lessons.push($(this).data('id'));
            });

            structure.push({
                name: sectionName,
                lessons: lessons
            });
        });

        $spinner.addClass('is-active');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wpa_save_course_structure',
                nonce: wpa_course_vars.nonce,
                course_id: courseId,
                structure: structure
            },
            success: function() {
                // Saved silently
            },
            complete: function() {
                $spinner.removeClass('is-active');
            }
        });
    }

    // 8. Bulk Add Logic
    var $bulkArea = $('#wpa-bulk-add-area');
    var $bulkInput = $('#wpa-bulk-lessons-input');
    var $bulkImportBtn = $('#wpa-bulk-import-btn');

    $('#wpa-bulk-add-toggle, #wpa-bulk-cancel-btn').on('click', function(e) {
        e.preventDefault();
        $bulkArea.slideToggle(200);
    });

    $bulkImportBtn.on('click', function(e) {
        e.preventDefault();
        var titles = $bulkInput.val();
        if (!titles.trim()) {
            alert('Please enter lesson titles.');
            return;
        }

        $bulkImportBtn.prop('disabled', true).text('Importing...');
        $spinner.addClass('is-active');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wpa_bulk_add_lessons',
                nonce: wpa_course_vars.nonce,
                course_id: courseId,
                titles: titles
            },
            success: function(res) {
                if (res.success) {
                    $('.wpa-unassigned-section .wpa-section-lessons').append(res.data.html);
                    $bulkInput.val('');
                    $bulkArea.slideUp(200);
                    initSortables();
                    saveStructure();
                } else {
                    alert(res.data);
                }
            },
            complete: function() {
                $bulkImportBtn.prop('disabled', false).text('Import Lessons');
                $spinner.removeClass('is-active');
            }
        });
    });
});