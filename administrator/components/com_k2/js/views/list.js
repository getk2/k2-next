define(['marionette', 'text!layouts/list.html', 'dispatcher'], function(Marionette, template, K2Dispatcher) {'use strict';

	var K2ListLayout = Marionette.Layout.extend({
		template : _.template(template),
		regions : {
			grid : '[data-region="grid"]',
			pagination : '[data-region="pagination"]'
		},
		events : {
			'click [data-action="sort"]' : 'sort',
			'click [data-action="toggle-state"]' : 'toggleState',
			'click [data-action="save-ordering"]' : 'saveOrder',
			'change input[data-action="toggle-all"]' : 'toggleRowsSelection',
			'change input[data-action="toggle"]' : 'toggleRowSelection'
		},
		initialize : function() {
			K2Dispatcher.on('onToolbarClose', function() {
				jQuery('input[data-action="toggle-all"]').prop('checked', false);
				jQuery('input[data-action="toggle"]').prop('checked', false);
			});
		},
		sort : function(event) {
			event.preventDefault();
			var el = jQuery(event.currentTarget);
			var sorting = el.data('sorting');
			var current = this.collection.getState('sorting');
			if (sorting != 'ordering' && sorting != 'featured_ordering') {
				if (current == sorting || current == sorting + '.reverse') {
					if (current.indexOf('.reverse') == -1) {
						sorting = current + '.reverse';
					} else {
						sorting = current.replace('.reverse', '');
					}
				}
			}
			K2Dispatcher.trigger('app:subheader:sort', sorting);
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
			K2Dispatcher.trigger('app:controller:saveOrder', keys, values, column, true);
		},
		toggleRowsSelection : function(event) {
			var el = jQuery(event.currentTarget);
			jQuery('input[data-action="toggle"]').prop('checked', el.prop('checked'));
			K2Dispatcher.trigger('app:view:toolbar', el.prop('checked'));
		},
		toggleRowSelection : function(event) {
			var el = jQuery(event.currentTarget);
			var show = (jQuery('input[data-action="toggle"]:checked').length > 0) ? true : false;
			K2Dispatcher.trigger('app:view:toolbar', show);
		}
	});

	return K2ListLayout;
});
