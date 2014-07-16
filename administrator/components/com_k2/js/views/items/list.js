define(['marionette', 'text!layouts/items/list.html', 'text!layouts/items/row.html', 'text!layouts/items/grid_list.html', 'text!layouts/items/grid_row.html', 'text!layouts/items/sortable_list.html', 'text!layouts/items/sortable_row.html', 'text!layouts/items/sortable_row_item.html', 'dispatcher', 'session', 'widgets/widget', 'collections/items', 'collections/categories'], function(Marionette, list, row, gridList, gridRow, sortableList, sortableRow, sortableRowItem, K2Dispatcher, K2Session, K2Widget, K2CollectionItems, K2CollectionCategories) {'use strict';

	// List items layout. It handles the two views for items ( table and sortable list)
	var K2ViewItems = Marionette.Layout.extend({
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
		onClose : function() {
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
			'click a[data-action="edit"]' : 'edit'
		},
		edit : function(event) {
			event.preventDefault();
			K2Dispatcher.trigger('app:controller:edit', this.model.get('id'));
		}
	});

	// Table view
	var K2ViewItemsTable = Marionette.CompositeView.extend({
		template : _.template(list),
		itemViewContainer : '[data-region="list"]',
		itemView : K2ViewItemsTableRow,
		onCompositeCollectionRendered : function() {
			K2Widget.ordering(this.$el, 'featured_ordering', this.collection.getState('sorting') === 'featured_ordering' && this.collection.getState('category') < 2);
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
		itemViewContainer : '[data-region="list"]',
		itemView : K2ViewItemsGridRow,
		onCompositeCollectionRendered : function() {
			K2Widget.ordering(this.$el, 'featured_ordering', this.collection.getState('sorting') === 'featured_ordering');
		}
	});

	// The row view for sortable
	var K2ViewItemsSortableRow = Marionette.Layout.extend({
		tagName : 'li',
		template : _.template(sortableRow),
		regions : {
			childrenRegion : '[data-region="category-children"]',
			itemsRegion : '[data-region="category-items"]'
		},
		initialize : function() {
			this.page = 1;
			this.limit = 5;
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
					update : false
				});
				this.$('[data-action="more"]').show();
				this.expanded = true;
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
		tagName : 'ul',
		template : _.template(sortableList),
		itemViewContainer : '[data-region="list"]',
		itemView : K2ViewItemsSortableRow
	});

	// Sortable view for category children
	var K2ViewItemsSortableCollectionView = Marionette.CollectionView.extend({
		tagName : 'ul',
		itemView : K2ViewItemsSortableRow
	});

	// The row view for grid
	var K2ViewItemsSortableCollectionViewItemsRow = Marionette.ItemView.extend({
		tagName : 'li',
		template : _.template(sortableRowItem)
	});

	// Sortable view for category items
	var K2ViewItemsSortableCollectionViewItems = Marionette.CollectionView.extend({
		tagName : 'ul',
		itemView : K2ViewItemsSortableCollectionViewItemsRow,
		onCollectionRendered : function() {
			var collection = this.collection;
			require(['widgets/sortable/jquery-sortable-min'], _.bind(function() {
				this.$el.sortable({
					handle : '[data-role="ordering-handle"]',
					group : 'k2-items',
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
						_super(item);
					}
				});
			}, this));
		}
	});
	return K2ViewItems;
});
