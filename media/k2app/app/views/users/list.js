define(['marionette', 'text!layouts/users/list.html', 'text!layouts/users/row.html', 'dispatcher'], function(Marionette, list, row, K2Dispatcher) {'use strict';
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
		childView : K2ViewUsersRow
	});
	return K2ViewUsers;
});
