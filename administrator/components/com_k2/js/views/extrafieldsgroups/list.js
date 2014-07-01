define(['marionette', 'text!layouts/extrafieldsgroups/list.html', 'text!layouts/extrafieldsgroups/row.html', 'dispatcher'], function(Marionette, list, row, K2Dispatcher) {'use strict';
	var K2ViewExtraFieldsGroupsRow = Marionette.ItemView.extend({
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
	var K2ViewExtraFieldsGroups = Marionette.CompositeView.extend({
		template : _.template(list),
		itemViewContainer : '[data-region="list"]',
		itemView : K2ViewExtraFieldsGroupsRow
	});
	return K2ViewExtraFieldsGroups;
});
