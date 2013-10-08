'use strict';
define(['marionette', 'text!layouts/list.html', 'dispatcher'], function(Marionette, template, K2Dispatcher) {

	var K2ListLayout = Marionette.Layout.extend({
		template : _.template(template),
		regions : {
			grid : '.appGrid',
			pagination : '.appPagination'
		},
		events : {
			'click .appActionSort' : 'sort',
			'click .appActionToggleState' : 'toggleState',
			'click .appActionSaveOrder' : 'saveOrder',
			'change #appRowsToggler' : 'toggleRowsSelection',
			'change .appRowToggler' : 'toggleRowSelection'
		},
		initialize : function() {
			K2Dispatcher.on('onToolbarClose', function() {
				jQuery('#appRowsToggler').prop('checked', false);
				jQuery('.appRowToggler').prop('checked', false);
			});
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
		saveOrder : function(event) {
			event.preventDefault();
			var el = jQuery(event.currentTarget);
			var column = el.data('column');
			var keys = [];
			var values = [];
			jQuery('input[name="' + column + '[]"]').each(function() {
				var row = self.jQuery(this);
				keys.push(row.data('id'));
				values.push(parseInt(row.val()));
			});
			K2Dispatcher.trigger('app:controller:saveOrder', keys, values, column);
		},
		toggleRowsSelection : function(event) {
			var el = jQuery(event.currentTarget);
			jQuery('.appRowToggler').prop('checked', el.prop('checked'));
			K2Dispatcher.trigger('app:view:toolbar', el.prop('checked'));
		},
		toggleRowSelection : function(event) {
			var el = jQuery(event.currentTarget);
			var show = (jQuery('.appRowToggler:checked').length > 0) ? true : false;
			K2Dispatcher.trigger('app:view:toolbar', show);
		}
	});

	return K2ListLayout;
});
