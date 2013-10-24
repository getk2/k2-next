define(['marionette', 'text!layouts/extrafields/list.html', 'text!layouts/extrafields/row.html', 'dispatcher', 'session', 'widgets'], function(Marionette, list, row, K2Dispatcher, K2Session, K2Widgets) {'use strict';
	var K2ViewExtraFieldsRow = Marionette.ItemView.extend({
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
	var K2ViewExtraFields = Marionette.CompositeView.extend({
		template : _.template(list),
		itemViewContainer : 'tbody',
		itemView : K2ViewExtraFieldsRow,
		onCompositeCollectionRendered : function() {
			K2Widgets.ordering(this.$el.find('table tbody'), 'ordering', K2Session.get('extrafields.sorting') === 'ordering');
		}
	});
	return K2ViewExtraFields;
});
