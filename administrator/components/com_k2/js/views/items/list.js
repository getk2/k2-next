'use strict';
define(['marionette', 'text!layouts/items/list.html', 'text!layouts/items/row.html'], function(Marionette, list, row) {
	var K2ViewItemsRow = Marionette.ItemView.extend({
		tagName : 'tr',
		template : _.template(row),
	});
	var K2ViewItems = Marionette.CompositeView.extend({
		template : _.template(list),
		itemViewContainer : 'tbody',
		itemView : K2ViewItemsRow
	});
	return K2ViewItems;
});
