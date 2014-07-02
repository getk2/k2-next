(function($){
	var K2Container = $('.jw');
	
	// Sidebar toggling
	K2Container.on('click', '.jw--sidebar--toggle', function(e){
		e.preventDefault();
		$('.jw').toggleClass('open--sidebar');
		$('.jw--sidebar').toggleClass('jw--sidebar__open');
	});
	
	// label toggling.
	K2Container.on('click', '.jw--filter .jw--radio', function(){
		$(this).parent().children('.jw--radio').removeClass('jw--radio__checked');
		$(this).addClass('jw--radio__checked');
	});
	
	// Featured, published togglers.
	K2Container.on('click', '.jw--state--toggler', function(){
		$(this).toggleClass('toggler--active');
	});
	
/*		// Tabs
	K2Container.on('click', '.jw--tabs a', function(event) {
		// Close all modals first
		event.preventDefault();
		jQuery(this).parents('.jw--tabs').find('a').removeClass('jw--tab__active');
		jQuery(this).addClass('jw--tab__active');
		var group = jQuery(this).closest('.jw--tabs').data('group');
		var target = jQuery(this).data('target');
		K2Containerr.find(group).css('display', 'none');
		K2Container.find(target).css('display', 'block');
		//K2Container.tabs[group] = target;
	});
*/	
})(jQuery);