define(['marionette', 'text!layouts/items/sortable_list.html', 'text!layouts/items/sortable_row.html', 'dispatcher', 'session', 'widgets/widget'], function(Marionette, list, row, K2Dispatcher, K2Session, K2Widget) {'use strict';

	var K2ViewItemsSortableRow = Marionette.CompositeView.extend({
		tagName : 'li',
		template : _.template(row),
		initialize : function() {
			this.collection = new Backbone.Collection(this.model.get('children'));
		},
		appendHtml : function(compositeView, itemView) {
			compositeView.$('ul.children:first').append(itemView.el);
		}
	});

	var K2ViewItemsSortable = Marionette.CompositeView.extend({
		template : _.template(list),
		itemViewContainer : 'ul#appItems',
		itemView : K2ViewItemsSortableRow,
		initialize : function(options) {
			this.itemsCollection = options.itemsCollection;
		},
		onCompositeCollectionRendered : function() {
			// Enable sorting
			var el = this.$el.find('#appItems');
			var collection = this.collection;
			var items = this.itemsCollection;
			require(['widgets/sortable/jquery-sortable-min'], _.bind(function() {
				el.sortable({
					handle : '.appOrderingHandle',
					onDrop : function(item, container, _super) {
						var id = item.find('input[name="ordering[]"]').data('id');
						var catid = item.data('id');
						var parent = item.parent();
						var newCategory = parent.data('category');
						if (catid != newCategory) {
							items.batch([id], [newCategory], 'catid', {
								success : _.bind(function() {
									var value = 1;
									var keys = [];
									var values = [];
									parent.find('input[name="ordering[]"]').each(function(index) {
										keys.push(jQuery(this).data('id'));
										values.push(value);
										value++;
									});
									K2Dispatcher.trigger('app:controller:saveOrder', keys, values, 'ordering');

								}, this),
								error : _.bind(function(model, xhr, options) {
									this.enqueueMessage('error', xhr.responseText);
								}, this)
							});
						} else {
							var value = 1;
							var keys = [];
							var values = [];
							parent.find('input[name="ordering[]"]').each(function(index) {
								keys.push(jQuery(this).data('id'));
								values.push(value);
								value++;
							});
							K2Dispatcher.trigger('app:controller:saveOrder', keys, values, 'ordering');
						}
						_super(item);
					}
				});
			}, this));
		}
	});
	return K2ViewItemsSortable;
});
