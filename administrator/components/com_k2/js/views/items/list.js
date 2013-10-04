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
			require(['widgets/sortable/jquery-sortable-min', 'widgets/sortable/jquery-sortable-min'], function() {
				console.info('aa');
				el.sortable({
					containerSelector : 'table',
					itemPath : '> tbody',
					itemSelector : 'tr',
					placeholder : '<tr class="placeholder"/>'
				});
			})
		}
	});
	return K2ViewItems;
});
