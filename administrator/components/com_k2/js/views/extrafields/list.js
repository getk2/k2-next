define(['marionette', 'text!layouts/extrafields/list.html', 'text!layouts/extrafields/row.html', 'dispatcher', 'session', 'widgets/widget'], function(Marionette, list, row, K2Dispatcher, K2Session, K2Widget) {'use strict';
	var K2ViewExtraFieldsRow = Marionette.ItemView.extend({
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
	var K2ViewExtraFields = Marionette.CompositeView.extend({
		template : _.template(list),
		itemViewContainer : '[data-region="list"]',
		itemView : K2ViewExtraFieldsRow,
		onCompositeCollectionRendered : function() {
			K2Widget.ordering(this.$('[data-region="list"]'), 'ordering', this.collection.getState('sorting') === 'ordering');
		}
	});
	return K2ViewExtraFields;
});
