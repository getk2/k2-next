define(['marionette', 'dispatcher'], function(Marionette, K2Dispatcher) {'use strict';
	var K2Widgets = {

		isUpdatingOrdering : false,

		ordering : function(element, column, enabled, options) {

			var params = {
				forcePlaceholderSize : true,
				items : 'tbody tr',
				handle : '.appOrderingHandle'
			}
			_.extend(params, options);

			if (element.ordering !== undefined) {
				element.ordering('destroy');
				element.unbind();
			}
			var minValue = element.find('input[name="' + column + '[]"]:first').val();
			require(['widgets/sortable/jquery.sortable'], _.bind(function() {
				element.ordering(params).bind('sortupdate', _.bind(function(e, ui) {
					if (this.isUpdatingOrdering === false) {
						this.isUpdatingOrdering = true;
						var value = minValue;
						var keys = [];
						var values = [];
						element.find('input[name="' + column + '[]"]').each(function(index) {
							var row = jQuery(this);
							keys.push(row.data('id'));
							values.push(value);
							value++;
						});
						K2Dispatcher.trigger('app:controller:saveOrder', keys, values, column);
					}
				}, this)).bind('sortstart', _.bind(function() {
					this.isUpdatingOrdering = false;
				}, this));
				if (enabled) {
					element.ordering('enable');
					element.find('input[name="' + column + '[]"], .appActionSaveOrder').prop('disabled', false);
				} else {
					element.ordering('disable');
					element.find('input[name="' + column + '[]"], .appActionSaveOrder').prop('disabled', true);
				}
			}, this));

		}
	};
	return K2Widgets;
});
