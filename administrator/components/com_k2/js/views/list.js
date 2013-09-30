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
			'click .jwStateToggler' : 'toggleState',
			'click .jwSaveOrderButton' : 'saveOrdering'
		},
		sort : function(event) {
			event.preventDefault();
			var el = jQuery(event.currentTarget);
			var sorting = el.data('sorting');
			K2Dispatcher.trigger('app:controller:filter', 'sorting', sorting);
		},
		toggleState : function(event) {
			event.preventDefault();
			var el = jQuery(event.currentTarget);
			var id = el.data('id');
			var state = el.data('state');
			K2Dispatcher.trigger('app:controller:toggleState', id, state);
		},
		saveOrdering : function(event) {
			event.preventDefault();
			var el = jQuery(event.currentTarget);
			var column = el.data('column');
			var keys = [];
			var values = [];
			jQuery('input[name="' + column + '[]"]').each(function() {
				var row = self.jQuery(this);
				keys.push(row.data('id'))
				values.push(parseInt(row.val()));
			});
			K2Dispatcher.trigger('app:controller:saveOrdering', keys, values, column);
		}
	});

	return K2ListLayout;
});
