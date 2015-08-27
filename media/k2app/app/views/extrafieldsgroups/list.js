define(['marionette', 'text!templates/extrafieldsgroups/list.html', 'text!templates/extrafieldsgroups/row.html', 'dispatcher', 'views/noresults'], function(Marionette, list, row, K2Dispatcher, K2ViewNoResults) {'use strict';
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
		childViewContainer : '[data-region="list"]',
		childView : K2ViewExtraFieldsGroupsRow,
		emptyView : K2ViewNoResults
	});
	return K2ViewExtraFieldsGroups;
});
