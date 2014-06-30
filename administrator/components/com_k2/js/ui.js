(function($){
	// Sidebar toggling
	$('.jw').on('click', '.jw--sidebar--toggle', function(e){
		e.preventDefault();
		$(this).toggleClass('open--sidebar');
		$('.jw--sidebar').toggleClass('jw--sidebar__open');
	});
	
	// Mark the correct labels
	$('.jw').on('load', function(){
		if ($('.controls label.radio').has('input:checked')) {
			$(this).addClass('jw--radio__checked');	
		}
	});
	
	// label toggling.
	$('.jw').on('click', '.controls label.radio', function(){
		$(this).parent().children('label.radio').removeClass('jw--radio__checked');
		$(this).addClass('jw--radio__checked');
	});
	
	// Featured, published togglers.
	$('.jw').on('click', '.jw--state--toggler', function(){
		$(this).toggleClass('toggler--active');
	});
})(jQuery);