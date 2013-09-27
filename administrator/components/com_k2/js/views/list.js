'use strict';
define(['marionette', 'text!layouts/list.html', 'dispatcher'], function(Marionette, template, K2Dispatcher) {

	var K2ListLayout = Marionette.Layout.extend({
		template : _.template(template),
		regions : {
			grid : '.jwGrid',
			pagination : '.jwPagination'
		},
		events : {
			'click .jwSortingButton' : 'sort',
			'click .jwStateToggler' : 'toggleState'
		},
		sort : function(event) {
			event.preventDefault();
			var el = jQuery(event.currentTarget);
			var sorting = el.data('sorting-column');
			K2Dispatcher.trigger('app:controller:filter', 'sorting', sorting);
		},
		toggleState : function(event) {
			event.preventDefault();
			var el = jQuery(event.currentTarget);
			var id = el.data('id');
			var state = el.data('state');
			K2Dispatcher.trigger('app:controller:toggleState',  id, state);
		}
	});

	return K2ListLayout;
});
