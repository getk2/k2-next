'use strict';
define(['marionette', 'text!layouts/subheader.html', 'dispatcher'], function(Marionette, template, K2Dispatcher) {

	var K2ViewSubheader = Marionette.ItemView.extend({

		template : _.template(template),

		events : {
			'change .jwFilters select' : 'filter',
			'change .jwFilters input' : 'filter',
			'click .jwBatchToggler' : 'batchToggle'
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

		batchToggle : function(event) {
			event.preventDefault();
			var el = jQuery(event.currentTarget);
			var state = el.data('state');
			var ids = [];
			jQuery('.jwRowToggler:checked').each(function() {
				ids.push(parseInt(jQuery(this).val()));
			});
			K2Dispatcher.trigger('app:controller:batchToggle', ids, state);
		}
	});

	return K2ViewSubheader;
});
