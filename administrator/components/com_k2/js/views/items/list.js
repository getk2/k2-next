define(['marionette', 'text!layouts/items/list.html', 'text!layouts/items/row.html', 'text!layouts/items/list_reorder.html', 'text!layouts/items/row_reorder.html', 'dispatcher', 'session', 'widgets'], function(Marionette, list, row, listReorder, rowReorder, K2Dispatcher, K2Session, K2Widgets) {'use strict';

	var K2ViewItemsRowReorder = Marionette.CompositeView.extend({
		tagName : 'li',
		template : _.template(rowReorder),
		initialize : function() {
			this.collection = new Backbone.Collection(this.model.get('children'));
		},
		appendHtml : function(compositeView, itemView) {
			compositeView.$('ul:first').append(itemView.el);
		}
	});

	var K2ViewItemsRow = Marionette.ItemView.extend({
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
			'click .appActionReorder' : 'reorder'
		},
		reorder : function() {
			this.buildTree();
			this.template = _.template(listReorder);
			this.itemViewContainer = '#appItems';
			this.itemView = K2ViewItemsRowReorder;
			this.render();
		},
		buildTree : function() {
			// Rebuild the collection in tree way
			var tree = new Backbone.Collection();
			_.each(this.collection.models, _.bind(function(model) {

				var categories = model.get('categoryPath').split(',');
				_.each(categories, function(alias, index) {
					var children = _.rest(categories, index);
					if (model.get('categoryAlias') === category) {
						children.push(model);
					}
					tree.add({
						id : alias,
						children : children
					}, {
						silent : true,
						merge : true
					});
				});
			}, this));

			this.collection.reset(tree, {
				silent : true
			});
		}
	});
	return K2ViewItems;
});
