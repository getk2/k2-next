define(['marionette', 'text!layouts/items/list.html', 'text!layouts/items/row.html', 'dispatcher', 'session', 'widgets/widget'], function(Marionette, list, row, K2Dispatcher, K2Session, K2Widget) {'use strict';

	var K2ViewItemsRow = Marionette.CompositeView.extend({
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
	var K2ViewItems = Marionette.CompositeView.extend({
		template : _.template(list),
		itemViewContainer : 'tbody',
		itemView : K2ViewItemsRow,
		events : {
			'click .appActionSortItems' : 'sortItems'
		},
		collectionEvents : {
			'reset' : 'sortItems'
		},
		onCompositeCollectionRendered : function() {
			// Add sorting
			K2Widget.ordering(this.$el, 'ordering', K2Session.get('items.sorting') === 'featured' && K2Session.get('items.category') < 2);
		},
		sortItems : function() {

			// Detect the current root
			var root = this.collection.getState('category');

			// Items collection
			var items = this.collection;

			// Fetch the categories tree
			require(['collections/categories', 'views/items/sortable'], _.bind(function(K2CollectionCategories, K2ViewItemsSortable) {

				// Create new categories collection
				var categoriesCollection = new K2CollectionCategories();

				// Set states
				categoriesCollection.setState('limit', 0);
				categoriesCollection.setState('page', 1);
				categoriesCollection.setState('language', '');
				categoriesCollection.setState('search', '');

				// Set the tree root if required
				if (root > 1) {
					categoriesCollection.setState('root', root);
				}

				// Fetch the tree
				categoriesCollection.fetch({
					
					// Don't parse!
					parse : false,
					
					silent : true,

					// Success callback
					success : _.bind(function(collection, response) {

						// Populate collection with the data
						categoriesCollection.reset(response.rows, {
							silent : true
						});

						// Set the items for each category
						_.each(categoriesCollection.models, function(categoryModel) {
							categoryModel.set('rows', items.where({
								catid : categoryModel.get('id')
							}));
						});

						// Build the categories tree
						categoriesCollection.buildTree();

						console.info(items);

						// Initialize the sortable view
						var view = new K2ViewItemsSortable({
							collection : categoriesCollection,
							itemsCollection : items
						});

						// Render the view
						this.$el.html(view.render().el);

					}, this)
				});
			}, this));
		}
	});
	return K2ViewItems;
});
