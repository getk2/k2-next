define(['marionette', 'text!layouts/subheader.html', 'dispatcher', 'widgets/widget', 'views/toolbar', 'views/filters'], function(Marionette, template, K2Dispatcher, K2Widget, K2ViewToolbar, K2ViewFilters) {'use strict';

	var K2ViewSubheader = Marionette.LayoutView.extend({

		template : _.template(template),

		regions : {
			toolbarRegion : '[data-region="toolbar"]',
			filtersRegion : '[data-region="filters"]'
		},

		modelEvents : {
			'change:title' : 'updateTitle'
		},

		updateTitle : function() {
			this.$('[data-name="title"]').text(this.model.get('title'));
		},

		initialize : function() {

			// Listener for updating subheader related data
			K2Dispatcher.on('app:update:subheader', function(response) {
				this.model.set({
					'title' : response.title
				});
			}, this);

		},

		onDomRefresh : function() {
			K2Widget.updateEvents(this.$el);
		},

		onShow : function() {

			// Show toolbar
			this.toolbarView = new K2ViewToolbar();
			this.toolbarRegion.show(this.toolbarView);

			// Show filters
			this.filtersView = new K2ViewFilters();
			this.filtersRegion.show(this.filtersView);
		},
	});

	return K2ViewSubheader;
});
