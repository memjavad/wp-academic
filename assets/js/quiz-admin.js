jQuery(document).ready(function($) {
    var $container = $('#wpa-quiz-questions-container');
    var $template = $('#wpa-question-template');
    var $addBtn = $('#wpa-add-question-btn');

    if (!$container.length) return;

    $addBtn.on('click', function(e) {
        e.preventDefault();
        var index = $container.children('.wpa-quiz-item').length;
        var html = $template.html().replace(/INDEX/g, index);
        $container.append(html);
    });

    function reindexQuestions() {
        $container.children('.wpa-quiz-item').each(function(index) {
            var $item = $(this);
            $item.attr('data-index', index);

            // Safely update all wpa_quiz inputs
            $item.find('[name^="wpa_quiz["]').each(function() {
                var oldName = $(this).attr('name');
                if (oldName) {
                    var newName = oldName.replace(/wpa_quiz\[.*?\]/, 'wpa_quiz[' + index + ']');
                    $(this).attr('name', newName);
                }
            });
        });
    }

    $(document).on('click', '.wpa-quiz-remove', function(e) {
        e.preventDefault();
        if (confirm('Remove this question?')) {
            $(this).closest('.wpa-quiz-item').remove();
            reindexQuestions();
        }
    });

    // Make Sortable
    $container.sortable({
        handle: '.wpa-quiz-header',
        placeholder: 'ui-sortable-placeholder',
        forcePlaceholderSize: true,
        update: function(event, ui) {
            reindexQuestions();
        }
    });
});
