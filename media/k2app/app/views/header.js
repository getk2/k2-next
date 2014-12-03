define(['marionette', 'text!layouts/header.html', 'dispatcher', 'widget', 'controller'], function(Marionette, template, K2Dispatcher, K2Widget, K2Controller) {
	'use strict';

	var K2ViewHeader = Marionette.ItemView.extend({

		template : _.template(template),

		events : {
			'click [data-action="add"]' : 'add',
			'click [data-action="save"]' : 'save',
			'click [data-action="save-and-new"]' : 'saveAndNew',
			'click [data-action="save-and-close"]' : 'saveAndClose',
			'click [data-action="close"]' : 'close'
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
				this.resource = resource;
				this.setActive();
			}, this);
			
			K2Dispatcher.on('app:header:set:resource', function(resource) {
				this.resource = resource;
			}, this);
		},

		onDomRefresh : function() {
			K2Widget.updateEvents(this.$el);
		},
		
		onRender : function() {
			this.setActive();
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

		setActive : function() {
			if(!this.resource) {
				K2Dispatcher.trigger('app:controller:get:resource');
			}
			var resource = this.resource;
			this.$('[data-region="menu-primary"] a').removeClass('active');
			this.$('[href="#' + resource + '"]').addClass('active');
			this.$('[data-region="menu-primary"] li').removeClass('active');
			this.$('[data-region="menu-primary"] a.active').parents('li.jw--haschild').addClass('active');
		}
	});

	return K2ViewHeader;
});
