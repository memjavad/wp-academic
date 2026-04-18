jQuery(document).ready(function($) {
    $('#wpa-mark-complete').on('click', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var lessonId = $btn.data('lesson');

        if ($btn.hasClass('wpa-btn-completed')) return;

        $btn.prop('disabled', true).text('Saving...');

        $.ajax({
            url: wpa_course_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'wpa_course_mark_complete',
                nonce: wpa_course_vars.nonce,
                lesson_id: lessonId
            },
            success: function(response) {
                if (response.success) {
                    $btn.addClass('wpa-btn-completed').removeClass('wpa-btn-complete');
                    $btn.text(wpa_course_vars.completed_text);
                    
                    // Update sidebar item
                    $('.wpa-sidebar-list li.current-lesson a').prepend('<span class="dashicons dashicons-yes wpa-lesson-check"></span> ');
                } else {
                    $btn.prop('disabled', false).text(wpa_course_vars.complete_text);
                    alert('Error saving progress.');
                }
            }
        });
    });

    // Enrollment
    $('#wpa-enroll-course').on('click', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var courseId = $btn.data('course');

        $btn.prop('disabled', true).text('Enrolling...');

        $.ajax({
            url: wpa_course_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'wpa_course_enroll',
                nonce: wpa_course_vars.enroll_nonce,
                course_id: courseId
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    $btn.prop('disabled', false).text('Start Course');
                    alert('Error enrolling in course.');
                }
            }
        });
    });

    // Quiz Submission
    $('#wpa-quiz-form').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $btn = $form.find('button[type="submit"]');
        var $feedback = $form.find('.wpa-quiz-feedback');
        
        var lessonId = $form.data('lesson');
        var answers = {};
        var count = 0;

        $form.find('input[type="radio"]:checked').each(function() {
            var name = $(this).attr('name'); 
            var match = name.match(/\[(\d+)\]/);
            if (match) {
                answers[match[1]] = $(this).val();
                count++;
            }
        });

        // Check if all questions answered?
        var totalQuestions = $form.find('.wpa-frontend-question').length;
        if (count < totalQuestions) {
            alert('Please answer all questions.');
            return;
        }

        $btn.prop('disabled', true).text('Checking...');

        $.ajax({
            url: wpa_course_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'wpa_course_submit_quiz',
                nonce: wpa_course_vars.nonce,
                lesson_id: lessonId,
                answers: answers
            },
            success: function(response) {
                if (response.success) {
                    $feedback.html('<div class="wpa-quiz-success"><span class="dashicons dashicons-yes"></span> ' + response.data.message + '</div>');
                    $btn.hide();
                    $form.find('input').prop('disabled', true);
                    
                    // Enable Mark Complete Button
                    var $completeBtn = $('#wpa-mark-complete');
                    $completeBtn.prop('disabled', false).css({'opacity': '1', 'pointer-events': 'auto'}).attr('title', '');
                } else {
                    $btn.prop('disabled', false).text('Submit Answer');
                    $feedback.html('<div class="wpa-quiz-error"><span class="dashicons dashicons-no"></span> ' + response.data + '</div>');
                }
            }
        });
    });

    // Sidebar Accordion
    $('.wpa-sidebar-section-toggle').on('click', function() {
        var $title = $(this);
        var $list = $title.next('.wpa-sidebar-list');
        
        $title.toggleClass('collapsed');
        $list.toggleClass('collapsed');
    });

    // Auto-collapse sections that don't have the active lesson
    $('.wpa-sidebar-list').each(function() {
        if ($(this).find('li.current-lesson').length === 0) {
            $(this).addClass('collapsed');
            $(this).prev('.wpa-sidebar-section-title').addClass('collapsed');
        }
    });

    // Focus Mode
    $('#wpa-focus-mode-toggle').on('click', function() {
        $('body').toggleClass('wpa-focus-mode-active');
        $('html').toggleClass('wpa-focus-mode-active'); // Toggle on HTML for better scope control
        
        var $btn = $(this);
        var $icon = $btn.find('span');
        
        if ($('body').hasClass('wpa-focus-mode-active')) {
            $icon.removeClass('dashicons-fullscreen-alt').addClass('dashicons-exit');
            // Store original text if needed, or just append "Exit"
            if ($btn.contents().last()[0].nodeType === 3) {
                $btn.contents().last()[0].nodeValue = ' Exit Focus Mode';
            }
        } else {
            $icon.removeClass('dashicons-exit').addClass('dashicons-fullscreen-alt');
            if ($btn.contents().last()[0].nodeType === 3) {
                $btn.contents().last()[0].nodeValue = ' Focus Mode';
            }
        }
    });

    // Lesson Notes (LocalStorage)
    var noteKey = 'wpa_note_' + window.location.pathname; // Unique key per URL
    var $noteInput = $('#wpa-lesson-notes-input');
    var $noteStatus = $('.wpa-notes-status');
    
    if ($noteInput.length) {
        var savedNote = localStorage.getItem(noteKey);
        if (savedNote) {
            $noteInput.val(savedNote);
        }

        var typingTimer;
        $noteInput.on('input', function() {
            clearTimeout(typingTimer);
            $noteStatus.text('Saving...');
            typingTimer = setTimeout(function() {
                localStorage.setItem(noteKey, $noteInput.val());
                $noteStatus.text('Saved locally');
            }, 1000);
        });
    }

    // Student Dashboard Tabs
    $('.wpa-dash-tab').on('click', function(e) {
        e.preventDefault();
        var tab = $(this).data('tab');
        
        $('.wpa-dash-tab').removeClass('active');
        $(this).addClass('active');
        
        $('.wpa-dash-content').hide();
        $('#wpa-dash-' + tab).fadeIn(200);
    });

    // Slider Navigation Logic
    var sliderWrapper = $('.wpa-hero-slider-wrapper');
    if (sliderWrapper.length) {
        var slider = sliderWrapper.find('.wpa-hero-slider');
        var dots = sliderWrapper.find('.wpa-slider-dot');
        var autoplay = sliderWrapper.data('autoplay');
        var intervalTime = sliderWrapper.data('interval');
        var pauseHover = sliderWrapper.data('pause');
        var intervalId;

        // Helper to get scroll width
        function getSlideWidth() {
            return slider[0].clientWidth;
        }

        // Helper to update active dot
        function updateActiveDot() {
            var scrollLeft = slider.scrollLeft();
            var width = getSlideWidth();
            var index = Math.round(scrollLeft / width);
            dots.removeClass('active');
            dots.eq(index).addClass('active');
        }

        // Scroll Event for Dots
        slider.on('scroll', function() {
            // Debounce slightly for performance if needed, but modern browsers handle this okay
            updateActiveDot();
        });

        // Next Slide Function
        function nextSlide() {
            var width = getSlideWidth();
            var maxScroll = slider[0].scrollWidth - width;
            if (slider.scrollLeft() >= maxScroll - 5) { // Tolerance
                slider.animate({ scrollLeft: 0 }, 500); // Rewind
            } else {
                slider.animate({ scrollLeft: slider.scrollLeft() + width }, 400);
            }
        }

        // Prev Slide Function
        function prevSlide() {
            var width = getSlideWidth();
            if (slider.scrollLeft() <= 0) {
                slider.animate({ scrollLeft: slider[0].scrollWidth - width }, 500); // Go to end
            } else {
                slider.animate({ scrollLeft: slider.scrollLeft() - width }, 400);
            }
        }

        // Button Clicks
        $('.wpa-slider-nav.next').on('click', nextSlide);
        $('.wpa-slider-nav.prev').on('click', prevSlide);

        // Dot Clicks
        dots.on('click', function() {
            var index = $(this).data('index');
            var width = getSlideWidth();
            slider.animate({ scrollLeft: index * width }, 400);
        });

        // Autoplay Logic
        if (autoplay == 1) {
            function startAutoplay() {
                intervalId = setInterval(nextSlide, intervalTime);
            }
            
            function stopAutoplay() {
                clearInterval(intervalId);
            }

            startAutoplay();

            if (pauseHover == 1) {
                sliderWrapper.hover(stopAutoplay, startAutoplay);
            }
        }
    }
});