define(['marionette', 'text!layouts/sidebar.html', 'dispatcher', 'session', 'text!layouts/sidebar_search_row.html'], function(Marionette, template, K2Dispatcher, K2Session, searchRowTemplate) {'use strict';

	var K2ViewSidebarSearchResultsItem = Marionette.ItemView.extend({
		tagName : 'li',
		template : _.template(searchRowTemplate),
		events : {
			'click a[data-action="edit"]' : 'edit'
		},
		edit : function(event) {
			event.preventDefault();
			K2Dispatcher.trigger('app:controller:edit', this.model.get('id'));
		}
	});

	var K2ViewSidebarSearchResults = Marionette.CollectionView.extend({
		tagName : 'ul',
		itemView : K2ViewSidebarSearchResultsItem
	});

	var K2ViewSidebar = Marionette.Layout.extend({

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
			'input input[name="search"]' : 'search',
			'click [data-action="toggle"]' : 'toggleSidebar'
		},

		regions : {
			searchResults : '[data-region="sidebar-search-results"]'
		},

		initialize : function() {
			K2Dispatcher.on('app:update:subheader', function(response) {
				this.model.set({
					'menu' : response.menu.secondary,
					'filters' : response.filters.sidebar,
					'states' : response.states
				});
			}, this);

			K2Dispatcher.on('app:sidebar:search', _.bind(function(collection) {
				_.each(collection.models, function(model) {
					if (!model.has('title') && model.has('name')) {
						model.set('title', model.get('name'), {
							silent : true
						});
					}
				});
				var view = new K2ViewSidebarSearchResults({
					collection : collection
				});
				this.searchResults.show(view);
			}, this));

			K2Dispatcher.on('app:sidebar:layouts:show', _.bind(function() {
				this.$('[data-role="layouts"]').show();
			}, this));

			K2Dispatcher.on('app:sidebar:layouts:hide', _.bind(function() {
				this.$('[data-role="layouts"]').hide();
			}, this));

		},
		onBeforeRender : function() {

		},
		onRender : function() {
			this.$('[data-role="layouts"]').hide();
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
			this.$('input[name="viewMode"][value="' + viewMode + '"]').parent().addClass('jw--radio__checked');
			var itemsLayout = K2Session.get('items.layout', 'default');
			this.$('[data-layout="' + itemsLayout + '"]').addClass('jw--layout-btn__active');
			this.$('input[name="viewMode"][value="' + viewMode + '"]').prop('checked', true);

			if (jQuery('[data-application="k2"]').hasClass('open--sidebar')) {
				this.$('aside').css('transition', 'none').addClass('jw--sidebar__open');
			}
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
			if (search && search.length > 2) {
				K2Dispatcher.trigger('app:controller:search', el.val());
			} else {
				this.searchResults.reset();
			}
		},
		toggleSidebar : function(event) {
			event.preventDefault();
			jQuery('[data-application="k2"]').toggleClass('open--sidebar');
			this.$('aside').css('transition', '').toggleClass('jw--sidebar__open');
		}
	});

	return K2ViewSidebar;
});
