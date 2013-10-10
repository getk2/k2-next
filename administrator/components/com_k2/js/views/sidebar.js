'use strict';
define(['marionette', 'text!layouts/sidebar.html', 'dispatcher', 'session'], function(Marionette, template, K2Dispatcher, K2Session) {

	var K2ViewSidebar = Marionette.ItemView.extend({

		template : _.template(template),

		modelEvents : {
			'change:menu' : 'render',
			'change:filters' : 'render'
		},

		events : {
			'change .appFilters input' : 'filter',
			'click .appActionResetFilters' : 'resetFilters'
		},

		initialize : function() {
			K2Dispatcher.on('app:update:subheader', function(response) {
				this.model.set({
					'menu' : response.menu.secondary,
					'filters' : response.filters.sidebar,
					'states' : response.states
				});
			}, this);
		},

		onRender : function() {
			_.each(this.model.get('states'), _.bind(function(value, state) {
				var filter = this.$el.find('[name="' + state + '"]');
				if (filter.attr('type') === 'radio') {
					filter.val([value]);
				} else {
					filter.val(value);
				}
			}, this));
		},

		filter : function(event) {
			event.preventDefault();
			var el = jQuery(event.currentTarget);
			var name = el.attr('name');
			var value = el.val();
			K2Dispatcher.trigger('app:controller:filter', name, value);
		},

		resetFilters : function(event) {
			event.preventDefault();
			this.$el.find('.appFilter').each(function() {
				var el = jQuery(this).find('input:first');
				var name = el.attr('name');
				var type = el.attr('type');
				if (type === 'radio') {
					el.val(['']);
				} else {
					el.val('');
				}
				K2Dispatcher.trigger('app:controller:setCollectionState', name, '');
			});
			K2Dispatcher.trigger('app:subheader:resetFilters');
		}
	});

	return K2ViewSidebar;
});
