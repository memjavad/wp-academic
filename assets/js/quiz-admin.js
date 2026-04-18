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

    $(document).on('click', '.wpa-quiz-remove', function(e) {
        e.preventDefault();
        if (confirm('Remove this question?')) {
            $(this).closest('.wpa-quiz-item').remove();
            // Re-index? PHP handles array append, but for correctness might want to re-index names.
            // But PHP $_POST['wpa_quiz'] will come as array 0, 1, 3... array_values will fix it if we use foreach.
            // My save logic: foreach ( $_POST['wpa_quiz'] as $item ). This handles gaps.
        }
    });

    // Make Sortable
    $container.sortable({
        handle: '.wpa-quiz-header',
        placeholder: 'ui-sortable-placeholder',
        forcePlaceholderSize: true
    });
});
