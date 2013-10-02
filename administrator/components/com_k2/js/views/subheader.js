'use strict';
define(['marionette', 'text!layouts/subheader.html', 'dispatcher'], function(Marionette, template, K2Dispatcher) {

	var K2ViewSubheader = Marionette.ItemView.extend({

		template : _.template(template),

		events : {
			'change .jwFilters select' : 'filter',
			'change .jwFilters input' : 'filter',
			'click .jwActionToggleState' : 'toggleState',
			'click #jwActionRemove' : 'remove',
			'click .jwActionCloseToolbar' : 'closeToolbar'
		},

		modelEvents : {
			'change' : 'render'
		},

		initialize : function() {
			K2Dispatcher.on('app:update:subheader', function(response) {
				this.model.set({
					'title' : response.title,
					'filters' : response.filters,
					'toolbar' : response.toolbar
				});
			}, this);

			K2Dispatcher.on('app:view:toolbar', function(show) {
				if (show) {
					this.showToolbar();
				} else {
					this.hideToolbar();
				}
			}, this);
		},

		onRender : function() {
			this.$el.find('.jwToolbar').hide();
		},

		filter : function(event) {
			event.preventDefault();
			var el = jQuery(event.currentTarget);
			var state = el.attr('name');
			var value = el.val();
			K2Dispatcher.trigger('app:controller:filter', state, value);
		},

		remove : function(event) {
			event.preventDefault();
			var rows = jQuery('input.jwRowToggler:checked').serializeArray();
			K2Dispatcher.trigger('app:controller:batchDelete', rows);
		},

		toggleState : function(event) {
			event.preventDefault();
			var rows = jQuery('input.jwRowToggler:checked').serializeArray();
			var el = jQuery(event.currentTarget);
			var state = el.data('state');
			K2Dispatcher.trigger('app:controller:batchToggleState', rows, state);
		},

		showToolbar : function() {
			this.$el.find('.jwToolbar').show();
		},

		hideToolbar : function() {
			this.$el.find('.jwToolbar').hide();
		},
		
		closeToolbar : function(event) {
			event.preventDefault();
			K2Dispatcher.trigger('onToolbarClose');
			this.hideToolbar();
		}
	});

	return K2ViewSubheader;
});
