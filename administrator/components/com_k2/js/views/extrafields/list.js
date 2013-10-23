define(['marionette', 'text!layouts/extrafields/list.html', 'text!layouts/extrafields/row.html', 'dispatcher', 'session'], function(Marionette, list, row, K2Dispatcher, K2Session) {'use strict';
	var K2ViewExtraFieldsRow = Marionette.ItemView.extend({
		tagName : 'tr',
		template : _.template(row),
		events : {
			'click a.appEditLink' : 'edit',
		},
		edit : function(event) {
			event.preventDefault();
			K2Dispatcher.trigger('app:controller:edit', this.model.get('id'));
		}
	});
	var K2ViewExtraFields = Marionette.CompositeView.extend({
		template : _.template(list),
		itemViewContainer : 'tbody',
		itemView : K2ViewExtraFieldsRow,
		onCompositeCollectionRendered : function() {
			this.initSorting(this.$el.find('table tbody'), 'ordering', K2Session.get('extrafields.sorting') === 'ordering');
		},
		initSorting : function(element, column, enabled) {
			if (element.ordering !== undefined) {
				element.ordering('destroy');
				element.unbind();
			}
			require(['widgets/sortable/jquery.sortable'], _.bind(function() {
				var startValue = element.find('input[name="' + column + '[]"]:first').val();
				element.ordering({
					forcePlaceholderSize : true,
					items : 'tbody tr',
					handle : '.appOrderingHandle',
				}).bind('sortupdate', function(e, ui) {
					var value = startValue;
					var keys = [];
					var values = [];
					element.find('input[name="' + column + '[]"]').each(function(index) {
						var row = jQuery(this);
						keys.push(row.data('id'));
						values.push(value);
						value++;
					});
					K2Dispatcher.trigger('app:controller:saveOrder', keys, values, column);
				});
				if (enabled) {
					element.ordering('enable');
					element.find('input[name="' + column + '[]"], .appActionSaveOrder').prop('disabled', false);
				} else {
					element.ordering('disable');
					element.find('input[name="' + column + '[]"], .appActionSaveOrder').prop('disabled', true);
				}
			}, this));
		}
	});
	return K2ViewExtraFields;
});
