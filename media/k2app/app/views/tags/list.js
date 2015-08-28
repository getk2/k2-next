define(['marionette', 'text!templates/tags/list.html', 'text!templates/tags/row.html', 'dispatcher', 'views/noresults'], function(Marionette, list, row, K2Dispatcher, K2ViewNoResults) {'use strict';
	var K2ViewTagsRow = Marionette.ItemView.extend({
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
	var K2ViewTags = Marionette.CompositeView.extend({
		template : _.template(list),
		childViewContainer : '[data-region="list"]',
		childView : K2ViewTagsRow,
		emptyView : K2ViewNoResults
	});
	return K2ViewTags;
});
