define(['marionette', 'text!templates/extrafields/list.html', 'text!templates/extrafields/row.html', 'dispatcher', 'session', 'widget'], function(Marionette, list, row, K2Dispatcher, K2Session, K2Widget) {'use strict';
	var K2ViewExtraFieldsRow = Marionette.ItemView.extend({
		tagName : 'ul',
		template : _.template(row),
		events : {
			'click a[data-action="edit"]' : 'edit'
		},
		edit : function(event) {
			event.preventDefault();
			K2Dispatcher.trigger('app:controller:edit', this.model.get('id'));
		},
		onRender : function() {
			this.$el.attr('data-group', this.model.get('group'));
		}
	});
	var K2ViewExtraFields = Marionette.CompositeView.extend({
		template : _.template(list),
		childViewContainer : '[data-region="list"]',
		childView : K2ViewExtraFieldsRow,
		onRenderCollection : function() {
			var groups = [];
			_.each(this.collection.models, function(model) {
				groups.push(model.get('group'));
			});
			groups = _.uniq(groups);
			var self = this;
			_.each(groups, function(group) {
				self.$('[data-group="' + group + '"]').wrapAll('<div data-sorting-group="' + group + '"></div>');
				var element = self.$('[data-sorting-group="' + group + '"]');
				K2Widget.ordering(element, 'ordering', self.collection.getState('sorting') === 'ordering', {
					containerSelector : '[data-sorting-group="' + group + '"]',
					itemSelector : '[data-group="' + group + '"]'
				});
				self.$('[data-action="save-ordering"]').prop('disabled', self.collection.getState('sorting') !== 'ordering');
			});
		}
	});
	return K2ViewExtraFields;
});
