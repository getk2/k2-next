(function($){
	// Sidebar toggling
	$('.jw').on('click', '.jw--sidebar--toggle', function(e){
		e.preventDefault();
		$(this).toggleClass('open--sidebar');
		$('.jw--sidebar').toggleClass('jw--sidebar__open');
	});
	
	// label toggling.
	$('.jw').on('click', '.jw--filter .jw--radio', function(){
		$(this).parent().children('.jw--radio').removeClass('jw--radio__checked');
		$(this).addClass('jw--radio__checked');
	});
	
	// Featured, published togglers.
	$('.jw').on('click', '.jw--state--toggler', function(){
		$(this).toggleClass('toggler--active');
	});
})(jQuery);