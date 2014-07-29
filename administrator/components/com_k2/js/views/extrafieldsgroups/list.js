define(['marionette', 'text!layouts/extrafieldsgroups/list.html', 'text!layouts/extrafieldsgroups/row.html', 'dispatcher', 'widgets/widget'], function(Marionette, list, row, K2Dispatcher, K2Widget) {'use strict';
	var K2ViewExtraFieldsGroupsRow = Marionette.ItemView.extend({
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
			this.$el.attr('data-scope', this.model.get('scope'));
		}
	});
	var K2ViewExtraFieldsGroups = Marionette.CompositeView.extend({
		template : _.template(list),
		childViewContainer : '[data-region="list"]',
		childView : K2ViewExtraFieldsGroupsRow,
		onRenderCollection : function() {
			var scopes = [];
			_.each(this.collection.models, function(model) {
				scopes.push(model.get('scope'));
			});
			scopes = _.uniq(scopes);
			var self = this;
			_.each(scopes, function(scope) {
				self.$('[data-scope="' + scope + '"]').wrapAll('<div data-group-scope="' + scope + '"></div>');
				var element = self.$('[data-group-scope="' + scope + '"]');
				K2Widget.ordering(element, 'ordering', self.collection.getState('sorting') === 'ordering', {
					containerSelector : '[data-group-scope="' + scope + '"]',
					itemSelector : '[data-scope="' + scope + '"]'
				});
				self.$('[data-action="save-ordering"]').prop('disabled', self.collection.getState('sorting') !== 'ordering');
			});
		}
	});
	return K2ViewExtraFieldsGroups;
});
