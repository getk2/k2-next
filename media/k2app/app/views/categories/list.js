define(['marionette', 'text!templates/categories/list.html', 'text!templates/categories/row.html', 'dispatcher', 'session', 'widget'], function(Marionette, list, row, K2Dispatcher, K2Session, K2Widget) {'use strict';
	var K2ViewCategoriesRow = Marionette.CompositeView.extend({
		tagName : 'li',
		template : _.template(row),
		events : {
			'click a[data-action="edit"]' : 'edit',
			'click a[data-action="toggle-state-category"]' : 'toggleState',
			'click [data-action="unlock"]' : 'unlock'
		},
		edit : function(event) {
			event.preventDefault();
			event.stopPropagation();
			K2Dispatcher.trigger('app:controller:edit', this.model.get('id'));
		},
		unlock : function(event) {
			event.preventDefault();
			this.model.checkin({
				success : _.bind(function() {
					this.model.set('isLocked', false);
					this.render();
				}, this),
				error : _.bind(function(model, xhr, options) {
					this.enqueueMessage('error', xhr.responseText);
				}, this)
			});
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
		attachHtml : function(compositeView, childView) {
			compositeView.$('ul:first').append(childView.el);
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
		childViewContainer : '[data-region="categories"]',
		childView : K2ViewCategoriesRow,
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
		onRenderCollection : function() {
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
