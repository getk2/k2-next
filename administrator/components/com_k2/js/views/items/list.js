define(['marionette', 'text!layouts/items/list.html', 'text!layouts/items/row.html', 'text!layouts/items/grid_list.html', 'text!layouts/items/grid_row.html', 'text!layouts/items/sortable_list.html', 'text!layouts/items/sortable_row.html', 'dispatcher', 'session', 'widgets/widget', 'collections/items'], function(Marionette, list, row, gridList, gridRow, sortableList, sortableRow, K2Dispatcher, K2Session, K2Widget, K2CollectionItems) {'use strict';

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

				var masterCollection = this.collection;

				// Fetch the categories tree
				require(['collections/categories'], _.bind(function(K2CollectionCategories) {

					// Create new categories collection
					var categoriesCollection = new K2CollectionCategories;

					// Set states
					categoriesCollection.setState('limit', 0);
					categoriesCollection.setState('page', 1);
					categoriesCollection.setState('language', '');
					categoriesCollection.setState('search', '');
					categoriesCollection.setState('root', masterCollection.getState('category'));

					// Fetch the tree
					categoriesCollection.fetch({
						silent : true,
						parse : false,
						success : _.bind(function(collection, response) {

							// Populate collection with the data
							categoriesCollection.reset(response.rows, {
								silent : true,
								parse : false
							});

							// Set the items for each category
							_.each(categoriesCollection.models, _.bind(function(categoryModel) {
								categoryModel.set('rows', []);
							}, this));

							// Build the categories tree
							categoriesCollection.buildTree();

							var view = new K2ViewItemsSortable({
								collection : categoriesCollection,
								itemsCollection : masterCollection
							});

							this.content.show(view);

						}, this)
					});
				}, this));

			} else {

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
			K2Widget.ordering(this.$el, 'featured_ordering', this.collection.getState('sorting') === 'featured_ordering' && this.collection.getState('category') < 2);
		}
	});

	// The row view for sortable
	var K2ViewItemsSortableRow = Marionette.CompositeView.extend({
		tagName : 'li',
		template : _.template(sortableRow),
		initialize : function() {
			this.collection = new Backbone.Collection(this.model.get('children'));
			this.page = 0;
		},
		appendHtml : function(compositeView, itemView) {
			compositeView.$('[data-region="category-children"]:first').append(itemView.el);
		},
		events : {
			'click [data-action="expand"]' : 'loadCategoryItems'
		},
		modelEvents : {
			'change' : 'render'
		},
		loadCategoryItems : function(event) {
			event.preventDefault();
			event.stopPropagation();
			var categoryItemsCollection = new K2CollectionItems();
			categoryItemsCollection.on('sync', function(collection) {
				var rows = this.model.get('rows');
				_.each(collection.models, function(model) {
					rows.push(model);
				});
				this.model.set('rows', []);
				this.model.set('rows', rows);
			}, this);
			this.page = this.page + 1;
			categoryItemsCollection.setState('limit', 5);
			categoryItemsCollection.setState('page', this.page);
			categoryItemsCollection.setState('category', this.model.get('id'));
			//categoryItemsCollection.setState('search', '');
			//categoryItemsCollection.setState('state', '');
			//categoryItemsCollection.setState('featured', '');
			//categoryItemsCollection.setState('language', '');
			//categoryItemsCollection.setState('access', '');
			//categoryItemsCollection.setState('tag', '');
			//categoryItemsCollection.setState('author', '');
			categoryItemsCollection.fetch({
				reset : false,
				remove : false,
				error : _.bind(function(collection, xhr, options) {
					K2Dispatcher.trigger('app:messages:add', 'error', xhr.responseText);
				}, this)
			});
		}
	});

	// Sortable view
	var K2ViewItemsSortable = Marionette.CompositeView.extend({
		template : _.template(sortableList),
		itemViewContainer : '[data-region="items"]',
		itemView : K2ViewItemsSortableRow,
		initialize : function(options) {
			this.itemsCollection = options.itemsCollection;
		},
		onCompositeCollectionRendered : function() {

			// Enable sorting
			var el = this.$('[data-region="items"]');
			var items = this.itemsCollection;
			require(['widgets/sortable/jquery-sortable-min'], _.bind(function() {
				el.sortable({
					handle : '[data-role="ordering-handle"]',
					onDrop : function(item, container, _super) {
						var parent = item.parent();
						var itemId = item.find('input[name="ordering[]"]').data('id');
						var currentCategoryId = item.data('category');
						var newCategoryId = parent.data('category');
						var ordering = parent.find('li').index(item) + 1;
						if (currentCategoryId != newCategoryId) {
							items.batch([itemId], [newCategoryId], 'catid', {
								success : _.bind(function() {
									var keys = [itemId];
									var values = [ordering];
									K2Dispatcher.trigger('app:controller:saveOrder', keys, values, 'ordering', false);
								}, this),
								error : _.bind(function(model, xhr, options) {
									K2Dispatcher.trigger('app:messages:add', error, xhr.responseText);
								}, this)
							});
						} else {
							var keys = [itemId];
							var values = [ordering];
							K2Dispatcher.trigger('app:controller:saveOrder', keys, values, 'ordering', false);
						}
						_super(item);
					}
				});
			}, this));
		}
	});

	return K2ViewItems;
});
