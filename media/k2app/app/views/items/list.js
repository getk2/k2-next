define(['marionette', 'text!templates/items/list.html', 'text!templates/items/row.html', 'text!templates/items/grid_list.html', 'text!templates/items/grid_row.html', 'text!templates/items/sortable_list.html', 'text!templates/items/sortable_row.html', 'text!templates/items/sortable_row_item.html', 'dispatcher', 'session', 'widget', 'collections/items', 'collections/categories'], function(Marionette, list, row, gridList, gridRow, sortableList, sortableRow, sortableRowItem, K2Dispatcher, K2Session, K2Widget, K2CollectionItems, K2CollectionCategories) {
	'use strict';

	// List items layout. It handles the two views for items ( table and sortable list)
	var K2ViewItems = Marionette.LayoutView.extend({
		template : _.template('<div data-region="items-inner"></div>'),
		regions : {
			content : '[data-region="items-inner"]',
		},
		initialize : function(options) {
			this.isModal = options.isModal || false;
			this.layout = K2Session.get('items.layout', 'default');
			this.on('setLayout', function(layout) {
				this.layout = layout;
				K2Session.set('items.layout', layout);
				this.setup();
			});
		},
		collectionEvents : {
			'reset' : 'setup'
		},
		onShow : function() {
			K2Dispatcher.trigger('app:sidebar:layouts:show');
			this.setup();
		},
		onDestroy : function() {
			K2Dispatcher.trigger('app:sidebar:layouts:hide');
		},
		setup : function() {

			if (this.collection.getState('sorting') === 'ordering' && this.isModal === false) {

				// Hide the layouts menu
				K2Dispatcher.trigger('app:sidebar:layouts:hide');

				// Hide pagination
				K2Dispatcher.trigger('app:pagination:hide');

				// Create new categories collection
				var categoriesCollection = new K2CollectionCategories;

				// Set states
				categoriesCollection.setState('page', 1);
				categoriesCollection.setState('language', '');
				categoriesCollection.setState('search', '');
				categoriesCollection.setState('persist', 0);

				if (this.collection.getState('category') > 1) {
					categoriesCollection.setState('root', this.collection.getState('category'));
					categoriesCollection.setState('parent', 0);
					categoriesCollection.setState('limit', 1);
				} else {
					categoriesCollection.setState('root', 1);
					categoriesCollection.setState('parent', 1);
					categoriesCollection.setState('limit', 0);
				}

				// Fetch the tree
				categoriesCollection.fetch({
					silent : true,
					parse : false,
					update : false,
					success : _.bind(function(collection, response) {

						// Populate collection with the data
						categoriesCollection.reset(response.rows, {
							silent : true,
							parse : false
						});

						// Build the categories tree
						//categoriesCollection.buildTree();

						var view = new K2ViewItemsSortable({
							collection : categoriesCollection
						});

						this.content.show(view);

					}, this)
				});

			} else {

				// Show the layouts menu
				K2Dispatcher.trigger('app:sidebar:layouts:show');

				// Show pagination
				K2Dispatcher.trigger('app:pagination:show');

				if (this.layout == 'grid') {
					var view = new K2ViewItemsGrid({
						collection : this.collection
					});

				} else {
					var view = new K2ViewItemsTable({
						collection : this.collection
					});
				}
				this.content.show(view);
			}
		}
	});

	// The row view for table
	var K2ViewItemsTableRow = Marionette.ItemView.extend({
		tagName : 'ul',
		template : _.template(row),
		events : {
			'click a[data-action="edit"]' : 'edit',
			'click [data-action="unlock"]' : 'unlock'
		},
		edit : function(event) {
			event.preventDefault();
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
		onRender : function() {
			var enabled = this.model.collection.getState('sorting') === 'featured_ordering' && this.model.collection.getState('category') < 2;
			console.info(enabled);
			if (!enabled) {
				this.$('[data-role="featured_ordering_column"]').hide();
				this.$('.jw--itemtitle').addClass('small-4 large-4');
				this.$('.jw--itemtitle').removeClass('small-3 large-3');
			} else {
				this.$('[data-role="featured_ordering_column"]').show();
				this.$('.jw--itemtitle').removeClass('small-4 large-4');
				this.$('.jw--itemtitle').addClass('small-3 large-3');
			}
		}
	});

	// Table view
	var K2ViewItemsTable = Marionette.CompositeView.extend({
		template : _.template(list),
		childViewContainer : '[data-region="list"]',
		childView : K2ViewItemsTableRow,
		onRender : function() {
			var enabled = this.collection.getState('sorting') === 'featured_ordering' && this.collection.getState('category') < 2;
			K2Widget.ordering(jQuery('[data-region="items-inner"]'), 'featured_ordering', enabled);
			if (!enabled) {
				this.$('[data-role="featured_ordering_column"]').hide();
				this.$('.jw--itemtitle').addClass('small-4 large-4');
				this.$('.jw--itemtitle').removeClass('small-3 large-3');
			} else {
				this.$('[data-role="featured_ordering_column"]').show();
				this.$('.jw--itemtitle').removeClass('small-4 large-4');
				this.$('.jw--itemtitle').addClass('small-3 large-3');
			}
		}
	});

	// The row view for grid
	var K2ViewItemsGridRow = Marionette.ItemView.extend({
		tagName : 'li',
		template : _.template(gridRow),
		events : {
			'click a[data-action="edit"]' : 'edit'
		},
		edit : function(event) {
			event.preventDefault();
			K2Dispatcher.trigger('app:controller:edit', this.model.get('id'));
		}
	});

	// Grid view
	var K2ViewItemsGrid = Marionette.CompositeView.extend({
		template : _.template(gridList),
		childViewContainer : '[data-region="list"]',
		childView : K2ViewItemsGridRow,
		onRenderCollection : function() {
			K2Widget.ordering(this.$('[data-region="list"]'), 'featured_ordering', this.collection.getState('sorting') === 'featured_ordering');
		}
	});

	// The row view for sortable
	var K2ViewItemsSortableRow = Marionette.LayoutView.extend({
		tagName : 'li',
		template : _.template(sortableRow),
		regions : {
			childrenRegion : '[data-region="category-children"]',
			itemsRegion : '[data-region="category-items"]'
		},
		initialize : function() {
			this.page = 1;
			this.limit = 20;
			this.expanded = false;
			this.itemsCollection = new K2CollectionItems;
			this.itemsCollection.setState('sorting', 'ordering');
			this.itemsCollection.setState('recursive', '0');
			this.itemsCollection.setState('category', this.model.get('id'));
			this.itemsCollection.setState('limit', this.limit);
			this.itemsCollection.setState('persist', 0);
			this.childrenCollection = new K2CollectionCategories;
			this.childrenCollection.setState('root', this.model.get('id'));
			this.childrenCollection.setState('parent', this.model.get('id'));
			this.childrenCollection.setState('limit', 0);
			this.childrenCollection.setState('persist', 0);

		},
		onShow : function() {
			this.itemsView = new K2ViewItemsSortableCollectionViewItems({
				collection : this.itemsCollection
			});
			this.itemsRegion.show(this.itemsView);
		},
		events : {
			'click [data-action="expand"]' : 'expand',
			'click [data-action="more"]' : 'more'
		},
		expand : function(event) {
			event.preventDefault();
			event.stopPropagation();
			if (!this.expanded) {
				this.childrenView = new K2ViewItemsSortableCollectionView({
					collection : this.childrenCollection
				});
				this.childrenRegion.show(this.childrenView);
				this.childrenCollection.fetch({
					update : false
				});
				this.itemsView = new K2ViewItemsSortableCollectionViewItems({
					collection : this.itemsCollection
				});
				this.itemsRegion.show(this.itemsView);
				this.itemsCollection.fetch({
					update : false,
					success : _.bind(function(collection) {
						if (collection.models.length > 0) {
							this.$('[data-action="more"]:last').show();
						}
					}, this)
				});
				this.expanded = true;
			} else {
				this.$('[data-region="category-data"]').toggle();
			}
		},
		more : function(event) {
			event.preventDefault();
			event.stopPropagation();
			this.page = this.page + 1;
			this.itemsCollection.setState('page', this.page);
			this.itemsCollection.fetch({
				reset : false,
				remove : false,
				update : false
			});
		}
	});

	// Sortable view
	var K2ViewItemsSortable = Marionette.CompositeView.extend({
		tagName : 'div',
		template : _.template(sortableList),
		childViewContainer : '[data-region="list"]',
		childView : K2ViewItemsSortableRow
	});

	// Sortable view for category children
	var K2ViewItemsSortableCollectionView = Marionette.CollectionView.extend({
		tagName : 'ul',
		childView : K2ViewItemsSortableRow
	});

	// The row view for grid
	var K2ViewItemsSortableCollectionViewItemsRow = Marionette.ItemView.extend({
		tagName : 'li',
		template : _.template(sortableRowItem)
	});

	// Sortable view for category items
	var K2ViewItemsSortableCollectionViewItems = Marionette.CollectionView.extend({
		tagName : 'ul',
		className : 'k2-sortable-items-list',
		childView : K2ViewItemsSortableCollectionViewItemsRow,
		onRender : function() {
			var collection = this.collection;
			require(['sortable'], _.bind(function() {
				jQuery('.k2-sortable-items-list').sortable('destroy');
				jQuery('.k2-sortable-items-list').sortable({
					handle : '[data-role="ordering-handle"]',
					group : 'k2-sortable-group-' + new Date().getTime(),
					onDrop : function(item, container, _super) {
						var parent = item.parent();
						var input = item.find('input[name="ordering[]"]');
						var itemId = input.data('id');
						var currentCategoryId = input.data('category');
						var newCategoryId = parent.parent().data('category');
						if (currentCategoryId != newCategoryId) {
							collection.batch([itemId], [newCategoryId], 'catid', {
								success : _.bind(function() {
									var previous = item.prev();
									var next = item.next();
									if (previous) {
										var value = parseInt(previous.find('input[name="ordering[]"]').val()) + 1;
									} else if (next) {
										var value = parseInt(previous.find('input[name="ordering[]"]').val()) - 1;
									} else {
										var value = 1;
									}
									var keys = [itemId];
									var values = [value];
									input.val(value);
									K2Dispatcher.trigger('app:controller:saveOrder', keys, values, 'ordering', false);
								}, this),
								error : _.bind(function(model, xhr, options) {
									K2Dispatcher.trigger('app:messages:add', error, xhr.responseText);
								}, this)
							});
						} else {
							var keys = [];
							var values = [];
							var list = parent.find('input[name="ordering[]"]');
							list.each(function() {
								var el = jQuery(this);
								keys.push(el.data('id'));
								values.push(parseInt(el.val()));
							});
							values.sort(function(a, b) {
								return a - b;
							});
							var modifiedKeys = [];
							var modifiedValues = [];
							list.each(function(index) {
								var el = jQuery(this);
								if (parseInt(el.val()) !== parseInt(values[index])) {
									modifiedKeys.push(el.data('id'));
									modifiedValues.push(values[index]);
									el.val(values[index]);
								}
							});
							if (modifiedKeys.length > 0) {
								K2Dispatcher.trigger('app:controller:saveOrder', modifiedKeys, modifiedValues, 'ordering', false);
							}
						}
						_super(item, container);
					}
				});

			}, this));
		}
	});
	return K2ViewItems;
});
