define(['marionette', 'text!layouts/usergroups/list.html', 'text!layouts/usergroups/row.html', 'dispatcher'], function(Marionette, list, row, K2Dispatcher) {'use strict';
	var K2ViewUserGroupsRow = Marionette.ItemView.extend({
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
	var K2ViewUserGroups = Marionette.CompositeView.extend({
		template : _.template(list),
		itemViewContainer : '[data-region="list"]',
		itemView : K2ViewUserGroupsRow
	});
	return K2ViewUserGroups;
});
