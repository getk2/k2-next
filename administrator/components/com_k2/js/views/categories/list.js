'use strict';
define(['marionette', 'text!layouts/categories/list.html', 'text!layouts/categories/row.html', 'dispatcher'], function(Marionette, list, row, K2Dispatcher) {
	var K2ViewCategoriesRow = Marionette.ItemView.extend({
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
	var K2ViewCategories = Marionette.CompositeView.extend({
		template : _.template(list),
		itemViewContainer : 'tbody',
		itemView : K2ViewCategoriesRow
	});
	return K2ViewCategories;
});
