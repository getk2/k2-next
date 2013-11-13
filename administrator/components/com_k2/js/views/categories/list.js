define(['marionette', 'text!layouts/categories/list.html', 'text!layouts/categories/row.html', 'dispatcher', 'session', 'widgets'], function(Marionette, list, row, K2Dispatcher, K2Session, K2Widgets) {'use strict';
	var K2ViewCategoriesRow = Marionette.CompositeView.extend({
		tagName : 'li',
		template : _.template(row),
		events : {
			'click a.appEditLink' : 'edit',
		},
		edit : function(event) {
			event.preventDefault();
			K2Dispatcher.trigger('app:controller:edit', this.model.get('id'));
		},
		initialize : function(options) {
			if (options.tree != undefined) {
				console.info(this.model.get('title'));
				var tree = options.tree.where({
					parent_id : this.model.get('id')
				});
				var collection = new Backbone.Collection(tree);
				this.collection = collection;
			}
		},
		onRender : function() {
			this.$el.attr('data-id', this.model.get('id'));
		},
		appendHtml : function(cv, iv) {
			cv.$("ul:first").append(iv.el);
		}
	});
	var K2ViewCategories = Marionette.CompositeView.extend({
		template : _.template(list),
		itemViewContainer : '#appCategories',
		itemView : K2ViewCategoriesRow,
		initialize : function() {
			this.tree = this.collection;
			this.collection = new Backbone.Collection(this.tree.where({
				level : '1'
			}));
		},
		itemViewOptions : function(model, index) {
			var tree = this.tree;
			return {
				tree : tree
			};
		},
		onCollectionRendered : function() {
			var el = this.$el.find('#appCategories');
			require(['widgets/sortable/jquery-sortable-min'], function() {
				el.sortable({
					handle : '.appOrderingHandle',
					onDrop : function(item, container, _super) {
						var id = item.data('id');
						var parent = item.parent();
						var parent_id = parent.data('parent');
						var index = parent.children().index(item);
						if (index == 0) {
							var location = 'first-child';
							var reference_id = parent_id;
						} else {
							var location = 'after';
							var reference_id = item.prev().data('id');
						}
						var data = [{
							name : 'id',
							value : id
						}, {
							name : 'parent_id',
							value : parent_id
						}, {
							name : 'location',
							value : location
						}, {
							name : 'reference_id',
							value : reference_id
						}, {
							name : K2SessionToken,
							value : 1
						}];
						jQuery.ajax({
							dataType : 'json',
							type : 'POST',
							url : 'index.php?option=com_k2&task=categories.saveOrder&format=json',
							data : data
						}).done(function(data, status, xhr) {

						}).fail(function(xhr, status, error) {

						});

						_super(item);
					}
				});
			});
		}
	});
	return K2ViewCategories;
});
