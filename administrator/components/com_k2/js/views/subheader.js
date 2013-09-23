'use strict';
define(['marionette', 'text!layouts/subheader.html', 'dispatcher'], function(Marionette, template, K2Dispatcher) {

	var K2ViewSubheader = Marionette.ItemView.extend({

		template : _.template(template),

		events : {
			'change .jwFilters select' : 'filter'
		},

		modelEvents : {
			'change' : 'render'
		},

		initialize : function() {
			K2Dispatcher.on('app:update:subheader', function(response) {
				this.model.set({
					'title' : response.title,
					'filters' : response.filters
				});
			}, this);
		},

		filter : function(event) {
			event.preventDefault();
			K2Dispatcher.trigger('app:controller:filter');
		}
	});

	return K2ViewSubheader;
});
