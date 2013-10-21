'use strict';
define(['marionette', 'text!layouts/categories/list.html', 'text!layouts/categories/row.html', 'dispatcher', 'session'], function(Marionette, list, row, K2Dispatcher, K2Session) {
	var K2ViewCategoriesRow = Marionette.ItemView.extend({
		tagName : 'tr',
		template : _.template(row),
		events : {
			'click a.appEditLink' : 'edit',
		},
		onRender : function() {
			this.el.addClass('appCategoryParent' + this.model.get('parent_id'));
		},
		edit : function(event) {
			event.preventDefault();
			K2Dispatcher.trigger('app:controller:edit', this.model.get('id'));
		}
	});
	var K2ViewCategories = Marionette.CompositeView.extend({
		template : _.template(list),
		itemViewContainer : 'tbody',
		itemView : K2ViewCategoriesRow,
		onCompositeCollectionRendered : function() {
			var groups = [];
			_.each(this.$el.find('tr'), function(tr) {
				var className = jQuery(tr).attr('class');
				if (className !== undefined) {
					groups.push(className);
				}
			});
			groups = _.uniq(groups);
			this.initSorting(this.$el.find('table tbody'), 'ordering', K2Session.get('categories.sorting') === 'ordering', groups);
		},
		initSorting : function(element, column, enabled, groups) {
			if (element.sortable !== undefined) {
				element.unbind();
			}
			require(['widgets/sortable/jquery.sortable'], _.bind(function() {
				var events = [];
				var isUpdating = false;
				_.each(groups, function(group) {
					element.sortable({
						forcePlaceholderSize : true,
						items : 'tbody tr.' + group,
						handle : '.appOrderingHandle',

					}).bind('sortupdate', function(e, ui) {
						var timestamp = e.timeStamp;
						if (_.indexOf(events, timestamp) === -1 && isUpdating === false) {
							isUpdating = true;
							events.push(timestamp);
							var selector = jQuery(ui.item[0]).attr('class');
							var keys = [];
							var values = [];
							var value = 1;
							element.find('tr.' + selector + ' input[name="' + column + '[]"]').each(function(index) {
								var row = jQuery(this);
								keys.push(row.data('id'));
								values.push(value);
								value++;
							});
							K2Dispatcher.trigger('app:controller:saveOrder', keys, values, column);
						}
					});
					if (enabled) {
						element.sortable('enable');
						element.find('input[name="' + column + '[]"], .appActionSaveOrder').prop('disabled', false);
					} else {
						element.sortable('disable');
						element.find('input[name="' + column + '[]"], .appActionSaveOrder').prop('disabled', true);
					}
				});

			}, this));
		}
	});
	return K2ViewCategories;
});
