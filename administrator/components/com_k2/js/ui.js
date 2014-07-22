(function($) {

	// Container
	var K2Container = jQuery('[data-application="k2"]');

	// Modals
	K2Container.on('click', 'a[data-role="modal"]', function(event) {
		event.preventDefault();
		var src = jQuery(this).attr('href');
		require(['widgets/magnific/jquery.magnific-popup.min', 'css!widgets/magnific/magnific-popup.css'], _.bind(function() {
			jQuery.magnificPopup.open({
				items : {
					src : src
				},
				type : 'image'
			});
		}, this));
	});

	// Hack to trigger the select boxes when their label is clicked
	K2Container.on('click', '[data-region="filters"] label', function(event) {
		var element = jQuery(this).next();
		if (element.hasClass('select2-container')) {
			if (element.hasClass('select2-dropdown-open')) {
				element.select2('close');
			} else {
				element.select2('open');
			}
		}
	});

	// Anchors
	K2Container.on('click', '.jw--scrollspymenu a', function(event) {
		event.preventDefault();
		var topMenuHeight = 15;
		var target = jQuery(this).attr('href');
		var offsetTop = 0;
		if (target !== '#') {
			offsetTop = jQuery(target).offset().top - topMenuHeight - 140;
		}
		jQuery('html, body').stop().animate({
			scrollTop : offsetTop
		}, 300);
	});

	// label toggling.
	K2Container.on('click', '.jw--radio', function() {
		$(this).parent().children('.jw--radio').removeClass('jw--radio__checked');
		$(this).addClass('jw--radio__checked');
	});

	// Bootstrap fallback
	K2Container.on('click', '.radio', function() {
		$(this).parent().children('.radio').removeClass('jw--radio__checked');
		$(this).addClass('jw--radio__checked');
	});

	// Featured, published togglers.
	K2Container.on('click', '.jw--state--toggler', function() {
		$(this).toggleClass('toggler--active');
	});

	// resetting the filters
	K2Container.on('click', '#jw--filters--reset', function() {
		$('.jw--filter .jw--radio').removeClass('jw--radio__checked');
		$('.jw--filter .jw--radio[for="state_0"]').addClass('jw--radio__checked');
		$('.jw--filter .jw--radio[for="featured_0"]').addClass('jw--radio__checked');
	});
	
	K2Container.on('click', '.jw--main--menu li.jw--haschild', function() {
		$(this).toggleClass('jw--activechild');
		$(this).children('ul').toggleClass('jw--visible');
	});
})(jQuery);
