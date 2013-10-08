'use strict';
define(['marionette', 'text!layouts/sidebar.html', 'dispatcher'], function(Marionette, template, K2Dispatcher) {

	var K2ViewSidebar = Marionette.ItemView.extend({

		template : _.template(template),

		modelEvents : {
			'change' : 'render'
		},

		events : {
			'change #jwSearch' : 'search'
		},

		search : function(event) {
			event.preventDefault();
			var el = jQuery(event.currentTarget);
			var value = el.val();
			K2Dispatcher.trigger('app:controller:filter', 'search', value);
		}
	});

	return K2ViewSidebar;
});
