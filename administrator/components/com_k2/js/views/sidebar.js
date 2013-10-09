'use strict';
define(['marionette', 'text!layouts/sidebar.html', 'dispatcher', 'session'], function(Marionette, template, K2Dispatcher, K2Session) {

	var K2ViewSidebar = Marionette.ItemView.extend({

		template : _.template(template),

		modelEvents : {
			'change' : 'render'
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
				});
			}, this);
		},

		onRender : function() {
			this.updateFilterValuesFromSessionValues();
		},

		updateFilterValuesFromSessionValues : function() {
			var prefix = this.options.resource;
			this.$el.find('.appFilter').each(function() {
				var el = jQuery(this).find('input:first');
				var name = el.attr('name');
				var type = el.attr('type');
				var value = K2Session.get(prefix + '.' + name);
				if (type === 'radio') {
					jQuery(this).find('input[name="' + name + '"]').val([value]);
				} else {
					el.val(value);
				}
			});
		},

		filter : function(event) {
			event.preventDefault();
			var el = jQuery(event.currentTarget);
			var name = el.attr('name');
			var value = el.val();
			var prefix = this.options.resource;
			K2Session.set(prefix + '.' + name, value);
			K2Dispatcher.trigger('app:controller:filter', name, value);
		},

		resetFilters : function(event) {

			// Prevent default
			event.preventDefault();

			// Reset filters session values
			var prefix = this.options.resource;
			this.$el.find('.appFilter').each(function() {
				var el = jQuery(this).find('input:first');
				var name = el.attr('name');
				K2Session.set(prefix + '.' + name, '');
				K2Dispatcher.trigger('app:controller:setCollectionState', name, '');
			});
			
			// Update the UI
			this.updateFilterValuesFromSessionValues();

			// Notify the subheader to reset it's own filters also
			K2Dispatcher.trigger('app:subheader:resetFilters');
		}
	});

	return K2ViewSidebar;
});
