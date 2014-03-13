define(['marionette', 'text!layouts/filters.html', 'dispatcher', 'widgets/widget'], function(Marionette, template, K2Dispatcher, K2Widget) {'use strict';

	var K2ViewFilters = Marionette.ItemView.extend({

		template : _.template(template),

		events : {
			'change select' : 'filter',
			'change input' : 'filter',
		},

		modelEvents : {
			'change:filters' : 'render'
		},

		initialize : function() {

			// Model
			this.model = new Backbone.Model({
				filters : [],
				states : []
			});

			// Listener for updating filters
			K2Dispatcher.on('app:update:subheader', function(response) {
				this.model.set({
					'filters' : response.filters.header,
					'states' : response.states,
				});
			}, this);

			K2Dispatcher.on('app:subheader:resetFilters', function() {

				// Apply select states
				this.$('[data-region="filters"] select').each(function() {
					var el = jQuery(this);
					var value = el.find('option:first').val();
					el.select2('val', value);
					K2Dispatcher.trigger('app:controller:setCollectionState', el.attr('name'), value);
				});

				// Author
				this.$('[data-region="filters"] input[name="author"]').select2('data', {
					id : 0,
					text : l('K2_ANY')
				});
				K2Dispatcher.trigger('app:controller:setCollectionState', 'author', 0);
				
				// Tag
				this.$('[data-region="filters"] input[name="tag"]').select2('data', {
					id : 0,
					text : l('K2_ANY')
				});
				K2Dispatcher.trigger('app:controller:setCollectionState', 'tag', 0);

				// Always go to first page after reset
				K2Dispatcher.trigger('app:controller:filter', 'page', 1);

			}, this);

			K2Dispatcher.on('app:subheader:sort', function(sorting) {
				this.$('select[name="sorting"]').select2('val', sorting);
			}, this);
		},

		onRender : function() {

			_.each(this.model.get('states'), _.bind(function(value, state) {
				var filter = this.$('[name="' + state + '"]');
				filter.val(value);
			}, this));

			require(['widgets/select2/select2.min', 'css!widgets/select2/select2.css'], _.bind(function() {
				this.$('[data-region="filters"] select').select2();
				var states = this.model.get('states');
				if (states.authorName !== undefined) {
					this.$('[data-region="filters"] input[name="author"]').select2('data', {
						id : states.author,
						text : states.authorName
					});
				}
				if (states.tagName !== undefined) {
					this.$('[data-region="filters"] input[name="tag"]').select2('data', {
						id : states.tag,
						text : states.tagName
					});
				}
			}, this));
		},

		onDomRefresh : function() {
			K2Widget.updateEvents(this.$el);
		},

		filter : function(event) {
			event.preventDefault();
			var el = jQuery(event.currentTarget);
			var state = el.attr('name');
			var value = el.val();
			K2Dispatcher.trigger('app:controller:filter', state, value);
		},
	});

	return K2ViewFilters;
});
