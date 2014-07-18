define(['marionette', 'text!layouts/categories/list.html', 'text!layouts/categories/row.html', 'dispatcher', 'session', 'widgets/widget'], function(Marionette, list, row, K2Dispatcher, K2Session, K2Widget) {'use strict';
	var K2ViewCategoriesRow = Marionette.CompositeView.extend({
		tagName : 'li',
		template : _.template(row),
		events : {
			'click a[data-action="edit"]' : 'edit',
			'click a[data-action="toggle-state-category"]' : 'toggleState'
		},
		edit : function(event) {
			event.preventDefault();
			event.stopPropagation();
			K2Dispatcher.trigger('app:controller:edit', this.model.get('id'));
		},
		initialize : function() {
			this.collection = new Backbone.Collection(this.model.get('children'));
		},
		onRender : function() {
			// If the row is a nested one but it's parent is missing from the view, then add the extra padding
			if (this.model.get('isOrphanNested')) {
				this.$el.css('padding-left', ((this.model.get('level') - 1) * 25) + 'px');
			}
			this.$el.attr('data-id', this.model.get('id'));
		},
		appendHtml : function(compositeView, itemView) {
			compositeView.$('ul:first').append(itemView.el);
		},
		toggleState : function(event) {
			event.preventDefault();
			event.stopPropagation();
			var el = jQuery(event.currentTarget);
			var id = el.data('id');
			var state = el.data('state');
			K2Dispatcher.trigger('app:controller:toggleState', id, state, this.model);
		}
	});
	var K2ViewCategories = Marionette.CompositeView.extend({
		template : _.template(list),
		itemViewContainer : '[data-region="categories"]',
		itemView : K2ViewCategoriesRow,
		collectionEvents : {
			'reset' : 'buildTree',
		},
		initialize : function() {
			this.buildTree();
		},
		buildTree : function() {

			// Rebuild the collection in tree way
			this.collection.buildTree();

			// Mark rows that are nested but their parents are missing from the view
			_.each(this.collection.models, function(model) {
				if (model.get('level') > 1) {
					model.set('isOrphanNested', true);
				}
			});

		},
		onCompositeCollectionRendered : function() {
			var collection = this.collection;
			var el = this.$('[data-region="categories"]');
			K2Widget.ordering(el, 'ordering', true, {
				containerSelector : 'ul',
				itemSelector : 'li',
				handle : '[data-role="ordering-handle"]',
				placeholder : '<li class="k2SortingPlaceholder"/>',
				onDrop : function(item, container, _super) {
					var id = item.data('id');
					var parent = item.parent();
					var parent_id = parent.data('parent');
					var index = parent.children().index(item);
					if (index === 0) {
						var location = 'first-child';
						var reference_id = parent_id;
					} else {
						var location = 'after';
						var reference_id = item.prev().data('id');
					}
					collection.moveByReference(reference_id, location, id);
					_super(item);
				}
			});
		}
	});
	return K2ViewCategories;
});
