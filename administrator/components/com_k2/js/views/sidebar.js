define(['marionette', 'text!layouts/sidebar.html', 'dispatcher', 'session'], function(Marionette, template, K2Dispatcher, K2Session) {'use strict';

	var K2ViewSidebar = Marionette.ItemView.extend({

		template : _.template(template),

		modelEvents : {
			'change:menu' : 'render',
			'change:filters' : 'render'
		},

		events : {
			'change [data-region="filters"] input' : 'filter',
			'change [data-region="filters"] select' : 'filter',
			'click [data-action="reset"]' : 'resetFilters',
			'click [data-action="set-layout"]' : 'setLayout',
			'change input[name="viewMode"]' : 'setViewMode',
			'input input[name="search"]' : 'search'
		},

		initialize : function() {
			K2Dispatcher.on('app:update:subheader', function(response) {
				this.model.set({
					'menu' : response.menu.secondary,
					'filters' : response.filters.sidebar,
					'states' : response.states
				});
			}, this);

			K2Dispatcher.on('app:sidebar:search:results', function(collection) {
				var resultsContainer = this.$('ul[data-role="search-results"]');
				if (_.size(collection) > 0) {
					resultsContainer.css('display', 'block');
				}
				resultsContainer.empty();
				_.each(collection.models, function(model) {
					resultsContainer.append('<li><a href="' + model.get('editLink') + '">' + model.get('title') + '</a></li>');
				});
			}, this);
		},

		onRender : function() {
			_.each(this.model.get('states'), _.bind(function(value, state) {
				var filter = this.$('[name="' + state + '"]');
				if (filter.attr('type') === 'radio') {
					filter.val([value]);
				} else {
					filter.val(value);
				}
			}, this));
			var viewMode = K2Session.get('view.mode', 'pagination');
			this.$('input[name="viewMode"][value="' + viewMode + '"]').prop('checked', true);
			var itemsLayout = K2Session.get('items.layout', 'default');
			this.$('[data-layout="' + itemsLayout + '"]').addClass('jw--layout-btn__active');
			this.$('input[name="viewMode"][value="' + viewMode + '"]').prop('checked', true);
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
			this.$('[data-role="filter"]').each(function() {
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
			this.$('[data-role="filter"] select').each(function() {
				var el = jQuery(this);
				var value = el.find('option:first').val();
				el.val(value);
				var name = el.attr('name');
				K2Dispatcher.trigger('app:controller:setCollectionState', name, value);
			});
			K2Dispatcher.trigger('app:subheader:resetFilters');
		},

		setViewMode : function(event) {
			event.preventDefault();
			var mode = jQuery('input[name="viewMode"]:checked').val();
			K2Dispatcher.trigger('app:pagination:mode', mode);
		},

		setLayout : function(event) {
			event.preventDefault();
			var layout = jQuery(event.currentTarget).data('layout');
			this.$('[data-layout]').removeClass('jw--layout-btn__active');
			this.$('[data-layout="' + layout + '"]').addClass('jw--layout-btn__active');
			K2Dispatcher.trigger('app:items:layout', layout);
		},

		search : function(event) {
			var el = jQuery(event.currentTarget);
			var search = jQuery.trim(el.val());
			if (search) {
				K2Dispatcher.trigger('app:controller:search', el.val());
			} else {
				this.$('ul[data-role="search-results"]').css('display', 'none');
			}
		}
	});

	return K2ViewSidebar;
});
