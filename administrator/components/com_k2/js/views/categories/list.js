define(['marionette', 'text!layouts/categories/list.html', 'text!layouts/categories/row.html', 'dispatcher', 'session', 'widgets'], function(Marionette, list, row, K2Dispatcher, K2Session, K2Widgets) {'use strict';
	var K2ViewCategoriesRow = Marionette.CompositeView.extend({
		tagName : 'li',
		template : _.template(row),
		initialize : function() {
			this.collection = new Backbone.Collection(this.model.get('children'));
		},
		onRender : function() {
			this.$el.attr('data-id', this.model.get('id'));
		},
		appendHtml : function(compositeView, itemView) {
			compositeView.$('ul:first').append(itemView.el);
		}
	});
	var K2ViewCategories = Marionette.CompositeView.extend({
		template : _.template(list),
		itemViewContainer : '#appCategories',
		itemView : K2ViewCategoriesRow,
		collectionEvents : {
			'reset' : 'buildTree',
		},
		initialize : function() {
			this.buildTree();
		},
		buildTree : function() {
			// Rebuild the collection in tree way
			_.each(this.collection.models, _.bind(function(model) {
				var children = this.collection.where({
					parent_id : model.get('id')
				});
				model.set('children', children);
			}, this));
			this.collection.reset(this.collection.where({
				level : '1'
			}), {
				silent : true
			});
		},
		onCompositeCollectionRendered : function() {

			// Enable sorting
			var el = this.$el.find('#appCategories');
			var collection = this.collection;
			require(['widgets/sortable/jquery-sortable-min'], function() {
				el.sortable({
					handle : '.appOrderingHandle',
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
			});
		}
	});
	return K2ViewCategories;
});
