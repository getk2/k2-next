define(['marionette', 'text!layouts/categories/list.html', 'text!layouts/categories/row.html', 'dispatcher', 'session', 'widgets'], function(Marionette, list, row, K2Dispatcher, K2Session, K2Widgets) {'use strict';
	var K2ViewCategoriesRow = Marionette.ItemView.extend({
		tagName : 'tr',
		template : _.template(row),
		events : {
			'click a.appEditLink' : 'edit',
		},
		onRender : function() {
			this.el.addClass('appCategoryParent' + this.model.get('parent_id'));
		},
		edit : function(event) {
			event.preventDefault();
			K2Dispatcher.trigger('app:controller:edit', this.model.get('id'));
		}
	});
	var K2ViewCategories = Marionette.CompositeView.extend({
		template : _.template(list),
		itemViewContainer : 'tbody',
		itemView : K2ViewCategoriesRow,
		onCompositeCollectionRendered : function() {
			var model = this.collection.at(0);
			if (model && model.get('canSort')) {
				var groups = [];
				_.each(this.$el.find('tr'), function(tr) {
					var className = jQuery(tr).attr('class');
					if (className !== undefined) {
						groups.push(className);
					}
				});
				groups = _.uniq(groups);
				_.each(groups, _.bind(function(group) {
					K2Widgets.ordering(this.$el.find('table tbody'), 'ordering', K2Session.get('categories.sorting') === 'ordering', {
						items : 'tbody tr.' + group
					});
				}, this));
			}

		}
	});
	return K2ViewCategories;
});
