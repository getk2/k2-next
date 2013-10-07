'use strict';
define(['marionette', 'text!layouts/items/list.html', 'text!layouts/items/row.html', 'dispatcher'], function(Marionette, list, row, K2Dispatcher) {
	var K2ViewItemsRow = Marionette.ItemView.extend({
		tagName : 'tr',
		template : _.template(row),
		events : {
			'click a.jwEditLink' : 'edit',
		},
		edit : function(event) {
			event.preventDefault();
			K2Dispatcher.trigger('app:controller:edit', this.model.get('id'));
		}
	});
	var K2ViewItems = Marionette.CompositeView.extend({
		template : _.template(list),
		itemViewContainer : 'tbody',
		itemView : K2ViewItemsRow,
		onDomRefresh : function() {
			var el = this.$el.find('table');
			var startValue = this.$el.find('input[name="ordering[]"]:first').val();
			var collection = this.collection;
			require(['widgets/sortable/jquery-sortable-min', 'css!widgets/sortable/sortable.css'], function() {
				el.sortable({
					containerSelector : 'table',
					itemPath : '> tbody',
					itemSelector : 'tbody tr',
					placeholder : '<tr class="jwSortingPlaceholder"/>',
					onDrop : function(item, container, _super) {
						var value = startValue;
						el.find('input[name="ordering[]"]').each(function(index) {
							var row = jQuery(this);
							row.val(value);
							collection.get(row.data('id')).set(name, value);
							value++;
						});
						_super(item);
					}
				});
			});
		}
	});
	return K2ViewItems;
});
