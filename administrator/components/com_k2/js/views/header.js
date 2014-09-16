define(['marionette', 'text!layouts/header.html', 'dispatcher', 'widgets/widget', 'controller'], function(Marionette, template, K2Dispatcher, K2Widget, K2Controller) {
	'use strict';

	var K2ViewHeader = Marionette.ItemView.extend({

		template : _.template(template),

		events : {
			'click [data-action="add"]' : 'add',
			'click [data-action="save"]' : 'save',
			'click [data-action="save-and-new"]' : 'saveAndNew',
			'click [data-action="save-and-close"]' : 'saveAndClose',
			'click [data-action="close"]' : 'close',
			'click [data-region="menu-primary"] a' : 'setActive'
		},

		modelEvents : {
			'change' : 'render'
		},

		initialize : function() {
			K2Dispatcher.on('app:update:header', function(response) {
				this.model.set({
					'menu' : response.menu.primary,
					'actions' : response.actions
				});
			}, this);

			K2Dispatcher.on('app:menu:active', function(resource) {
				this.$('[data-region="menu-primary"] a').removeClass('active');
				this.$('[href="#' + resource + '"]').addClass('active');
			}, this);
		},

		onDomRefresh : function() {
			K2Widget.updateEvents(this.$el);
			K2Dispatcher.trigger('app:controller:menu:active');
		},

		add : function(event) {
			event.preventDefault();
			K2Dispatcher.trigger('app:controller:add');
		},

		save : function(event) {
			event.preventDefault();
			var el = jQuery(event.currentTarget);
			var resource = el.data('resource');
			if (resource === 'settings') {
				K2Dispatcher.trigger('app:controller:save', 'custom', 'settings');
			} else {
				K2Dispatcher.trigger('app:controller:save', 'edit');
			}
		},

		saveAndNew : function(event) {
			event.preventDefault();
			K2Dispatcher.trigger('app:controller:save', 'add');
		},

		saveAndClose : function(event) {
			event.preventDefault();
			K2Dispatcher.trigger('app:controller:save', 'list');
		},

		close : function(event) {
			event.preventDefault();
			K2Dispatcher.trigger('app:controller:close');
		},

		setActive : function(event) {
			var el = jQuery(event.currentTarget);
			this.$('[data-region="menu-primary"] a').removeClass('active');
			el.addClass('active');
		}
	});

	return K2ViewHeader;
});
