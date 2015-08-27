define(['marionette', 'text!templates/users/list.html', 'text!templates/users/row.html', 'dispatcher', 'views/noresults'], function(Marionette, list, row, K2Dispatcher, K2ViewNoResults) {'use strict';
	var K2ViewUsersRow = Marionette.ItemView.extend({
		tagName : 'ul',
		template : _.template(row),
		events : {
			'click a[data-action="edit"]' : 'edit'
		},
		edit : function(event) {
			event.preventDefault();
			K2Dispatcher.trigger('app:controller:edit', this.model.get('id'));
		}
	});
	var K2ViewUsers = Marionette.CompositeView.extend({
		template : _.template(list),
		childViewContainer : '[data-region="list"]',
		childView : K2ViewUsersRow,
		emptyView : K2ViewNoResults
	});
	return K2ViewUsers;
});
