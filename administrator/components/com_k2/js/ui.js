(function($){
	var K2Container = $('.jw');


   // Minimal Scrollspy
	//$(".jw--scrollspymenu").each(function( index ) {

		// Cache selectors
		var lastId,
		    topMenu = $('.jw--scrollspymenu'),
		    topMenuHeight = 15,
		    // All list items
		    menuItems = topMenu.find("a");
		    // Anchors corresponding to menu items

		// Bind click handler to menu items so we can get a fancy scroll animation
		K2Container.on('click', '.jw--scrollspymenu a', function(e){
			var href = $(this).attr("href"),
		      offsetTop = (href === "#") ? 0 : $(href).offset().top-topMenuHeight - 120;
		  $('html, body').stop().animate({
		      scrollTop: offsetTop
		  }, 300);
		  e.preventDefault();
		});

		// Bind to scroll
		$(window).scroll(function(){
		   // Get container scroll position
		   var fromTop = $(this).scrollTop()+120;

		   // Get id of current scroll item
		  scrollItems = menuItems.map(function(){
		      var item = $($(this).attr("href"));
		      if (item.length) { return item; }
		    });
		  
		   var cur = scrollItems.map(function(){
		     if ($(this).offset().top < fromTop)
		       return this;
		   });
		   
		   // Get the id of the current element
		   cur = cur[cur.length-1];
		   var id = cur && cur.length ? cur[0].id : "";
			 
			 
		   if (lastId !== id) {
		       lastId = id;
		       // Set/remove active class
		       menuItems
		         .parent().removeClass("active")
		         .end().filter("[href=#"+id+"]").parent().addClass("active");
		   }
		});
//});

	
	// Sidebar toggling
	K2Container.on('click', '.jw--sidebar--toggle', function(e){
		e.preventDefault();
		$('.jw').toggleClass('open--sidebar');
		$('.jw--sidebar').toggleClass('jw--sidebar__open');
	});
	
	// Close it when redirecting to the settings/ info view
	K2Container.on('click', '.jw--inline--menu a', function(){
		$('.jw').removeClass('open--sidebar');
		$('.jw--sidebar').removeClass('jw--sidebar__open');
	});

	
	// label toggling.
	K2Container.on('click', '.jw--radio', function(){
		$(this).parent().children('.jw--radio').removeClass('jw--radio__checked');
		$(this).addClass('jw--radio__checked');
	});
	
	// Featured, published togglers.
	K2Container.on('click', '.jw--state--toggler', function(){
		$(this).toggleClass('toggler--active');
	});
	
	// resetting the filters
	K2Container.on('click', '#jw--filters--reset', function(){
		console.log('clicked');
		$('.jw--filter .jw--radio').removeClass('jw--radio__checked');
		$('.jw--filter .jw--radio[for="state_0"]').addClass('jw--radio__checked');
		$('.jw--filter .jw--radio[for="featured_0"]').addClass('jw--radio__checked');
	});
	
	// Hack to trigger the select boxes when their label is clicked
	$('body').on('click', 'label', function(event) {
		var element = jQuery(this).next();
		if (element.hasClass('select2-container')) {
			if (element.hasClass('select2-dropdown-open')) {
				element.select2('close');
			} else {
				element.select2('open');
			}
		}
	});
	
	
/*// Tabs
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