jQuery(document).ready(function($) {

	var $wrapper = $('.wpa-glossary-wrapper');
	var $listContainer = $('#wpa-glossary-list');

	// 1. Alphabet Filter Logic
	$(document).on('click', '.wpa-glossary-filter button', function(e) {
		e.preventDefault();
		var $btn = $(this);
		var filter = $btn.data('filter');

		$('.wpa-glossary-filter button').removeClass('active');
		$btn.addClass('active');

		if (filter === 'all') {
			$('.wpa-glossary-group').fadeIn(300);
		} else {
			$('.wpa-glossary-group').hide();
			$(filter).fadeIn(300);
		}
	});

	// 2. Search Logic (Search-as-you-type)
	$('.wpa-glossary-search-input').on('keyup input', function() {
		var keyword = $(this).val().toLowerCase();
		var totalVisible = 0;

		if (keyword !== '') {
			// Show all groups first during search
			$('.wpa-glossary-group').show();
			$('.wpa-glossary-filter button').removeClass('active');
			$('.wpa-glossary-filter button[data-filter="all"]').addClass('active');
		}

		$('.wpa-glossary-group').each(function() {
			var $group = $(this);
			var hasMatch = false;

			$group.find('li').each(function() {
				var text = $(this).text().toLowerCase();
				if (text.indexOf(keyword) !== -1) {
					$(this).show();
					hasMatch = true;
					totalVisible++;
				} else {
					$(this).hide();
				}
			});

			$group.toggle(hasMatch);
		});
	});

	// Accordion Logic
	$(document).on('click', '.wpa-glossary-style-accordion .wpa-glossary-group h3', function() {
		$(this).parent().toggleClass('active').find('ul').slideToggle();
	});

});
