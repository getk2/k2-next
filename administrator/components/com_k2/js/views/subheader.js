define(['marionette', 'text!layouts/subheader.html', 'dispatcher', 'widgets/widget'], function(Marionette, template, K2Dispatcher, K2Widget) {'use strict';

	var K2ViewSubheader = Marionette.ItemView.extend({

		template : _.template(template),

		events : {
			'change .appFilters select' : 'filter',
			'click .appActionSetState' : 'setState',
			'click #appActionRemove' : 'remove',
			'click .appActionCloseToolbar' : 'closeToolbar'
		},

		modelEvents : {
			'change:toolbar' : 'render',
			'change:title' : 'render',
			'change:filters' : 'render'
		},

		initialize : function() {
			K2Dispatcher.on('app:update:subheader', function(response) {
				this.model.set({
					'title' : response.title,
					'filters' : response.filters.header,
					'toolbar' : response.toolbar,
					'states' : response.states
				});
				this.hideToolbar();
			}, this);

			K2Dispatcher.on('app:view:toolbar', function(show) {
				if (show) {
					this.showToolbar();
				} else {
					this.hideToolbar();
				}
			}, this);

			K2Dispatcher.on('app:subheader:resetFilters', function() {

				// Apply select states
				this.$el.find('.appFilters select').each(function() {
					var el = jQuery(this);
					var value = el.find('option:first').val();
					el.select2('val', value);
					K2Dispatcher.trigger('app:controller:setCollectionState', el.attr('name'), value);
				});

				// Always go to first page after reset
				K2Dispatcher.trigger('app:controller:filter', 'page', 1);

			}, this);

			K2Dispatcher.on('app:subheader:sort', function(sorting) {
				this.$el.find('select[name="sorting"]').select2('val', sorting);
			}, this);
		},

		onDomRefresh : function() {
			K2Widget.updateEvents(this.$el);
		},

		onRender : function() {
			this.$el.find('.appToolbar').hide();
			_.each(this.model.get('states'), _.bind(function(value, state) {
				var filter = this.$el.find('[name="' + state + '"]');
				filter.val(value);
			}, this));
			require(['widgets/select2/select2', 'css!widgets/select2/select2.css'], _.bind(function() {
				this.$el.find('.appFilters select').select2();
			}, this));
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
			var rows = jQuery('input.appRowToggler:checked').serializeArray();
			K2Dispatcher.trigger('app:controller:batchDelete', rows);
		},

		setState : function(event) {
			event.preventDefault();
			var rows = jQuery('input.appRowToggler:checked').serializeArray();
			var el = jQuery(event.currentTarget);
			var value = el.data('value');
			var state = el.data('state');
			K2Dispatcher.trigger('app:controller:batchSetState', rows, value, state);
		},

		showToolbar : function() {
			this.$el.find('#appToolbarCounter').text(jQuery('input.appRowToggler:checked').length);
			this.$el.find('.appToolbar').show();
		},

		hideToolbar : function() {
			this.$el.find('.appToolbar').hide();
		},

		closeToolbar : function(event) {
			event.preventDefault();
			K2Dispatcher.trigger('onToolbarClose');
			this.hideToolbar();
		}
	});

	return K2ViewSubheader;
});
