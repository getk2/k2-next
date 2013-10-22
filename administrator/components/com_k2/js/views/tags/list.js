define(['marionette', 'text!layouts/tags/list.html', 'text!layouts/tags/row.html', 'dispatcher'], function(Marionette, list, row, K2Dispatcher) {'use strict';
	var K2ViewTagsRow = Marionette.ItemView.extend({
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
	var K2ViewTags = Marionette.CompositeView.extend({
		template : _.template(list),
		itemViewContainer : 'tbody',
		itemView : K2ViewTagsRow
	});
	return K2ViewTags;
});
