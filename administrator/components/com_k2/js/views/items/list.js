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
		},
		onRender : function() {
			console.log('Rendered List Row');
		}
	});
	var K2ViewItems = Marionette.CompositeView.extend({
		template : _.template(list),
		itemViewContainer : 'tbody',
		itemView : K2ViewItemsRow,
		onRender : function() {
			console.log('Rendered List');
		}
	});
	return K2ViewItems;
});
