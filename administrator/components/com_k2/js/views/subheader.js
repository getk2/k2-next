'use strict';
define(['marionette', 'text!layouts/subheader.html', 'dispatcher'], function(Marionette, template, K2Dispatcher) {

	var K2ViewSubheader = Marionette.ItemView.extend({

		template : _.template(template),

		events : {
			'change .jwFilters select' : 'filter',
			'change .jwFilters input' : 'filter',
			'click .jwBatchToggler' : 'batch',
			'click #jwDeleteButton' : 'destroy'
		},

		modelEvents : {
			'change' : 'render'
		},

		initialize : function() {
			K2Dispatcher.on('app:update:subheader', function(response) {
				this.model.set({
					'title' : response.title,
					'filters' : response.filters,
					'toolbar' : response.toolbar
				});
			}, this);
		},

		filter : function(event) {
			event.preventDefault();
			var el = jQuery(event.currentTarget);
			var state = el.attr('name');
			var value = el.val();
			K2Dispatcher.trigger('app:controller:filter', state, value);
		},

		destroy : function(event) {
			event.preventDefault();
			var rows = jQuery('input.jwRowToggler:checked').serializeArray();
			K2Dispatcher.trigger('app:controller:destroy', rows);
		},
		
		batch : function(event) {
			event.preventDefault();
			var rows = jQuery('input.jwRowToggler:checked').serializeArray();
			var el = jQuery(event.currentTarget);
			var state = el.data('state');
			K2Dispatcher.trigger('app:controller:batch', rows, state);
		}
	});

	return K2ViewSubheader;
});
