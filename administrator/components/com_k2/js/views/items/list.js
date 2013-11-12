define(['marionette', 'text!layouts/items/list.html', 'text!layouts/items/row.html', 'dispatcher', 'session', 'widgets'], function(Marionette, list, row, K2Dispatcher, K2Session, K2Widgets) {'use strict';
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
			var model = this.collection.at(0);
			if (model && model.get('canSort')) {
				K2Widgets.ordering(this.$el.find('table tbody'), 'ordering', K2Session.get('items.sorting') === 'ordering');
			}
		}
	});
	return K2ViewItems;
});
