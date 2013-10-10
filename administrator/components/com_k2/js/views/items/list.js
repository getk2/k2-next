'use strict';
define(['marionette', 'text!layouts/items/list.html', 'text!layouts/items/row.html', 'dispatcher', 'session'], function(Marionette, list, row, K2Dispatcher, K2Session) {
	var K2ViewItemsRow = Marionette.ItemView.extend({
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
	var K2ViewItems = Marionette.CompositeView.extend({
		template : _.template(list),
		itemViewContainer : 'tbody',
		itemView : K2ViewItemsRow,
		onCompositeCollectionRendered : function() {
			this.initSorting('ordering', this.collection.getState('sorting') === 'ordering');
		},
		initSorting : function(column, enabled) {
			require(['widgets/sortable/jquery-sortable-min', 'css!widgets/sortable/sortable.css'], _.bind(function() {
				var startValue = 1;
				this.$el.find('table').sortable({
					containerSelector : 'table',
					itemPath : '> tbody',
					itemSelector : 'tbody tr',
					placeholder : '<tr class="appSortingPlaceholder"/>',
					handle : '.appOrderingHandle',
					onDragStart : function(item, container, _super) {
						startValue = container.el.find('input[name="' + column + '[]"]:first').val();
						_super(item, container);
					},
					onDrop : function(item, container, _super) {
						var value = startValue;
						var keys = [];
						var values = [];
						container.el.find('input[name="' + column + '[]"]').each(function(index) {
							var row = jQuery(this);
							keys.push(row.data('id'));
							values.push(value);
							value++;
						});
						_super(item, container);
						K2Dispatcher.trigger('app:controller:saveOrder', keys, values, column);
					}
				});
				if (enabled) {
					this.$el.find('table').sortable('enable');
					this.$el.find('input[name="' + column + '[]"], .appActionSaveOrder').prop('disabled', false);
				} else {
					this.$el.find('table').sortable('disable');
					this.$el.find('input[name="' + column + '[]"], .appActionSaveOrder').prop('disabled', true);
				}
			}, this));
		}
	});
	return K2ViewItems;
});
