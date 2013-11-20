define(['marionette', 'text!layouts/header.html', 'dispatcher', 'widgets/widget'], function(Marionette, template, K2Dispatcher, K2Widget) {'use strict';

	var K2ViewHeader = Marionette.ItemView.extend({

		template : _.template(template),

		events : {
			'click #appActionAdd' : 'add',
			'click #appActionSave' : 'save',
			'click #appActionSaveAndNew' : 'saveAndNew',
			'click #appActionSaveAndClose' : 'saveAndClose',
			'click #appActionClose' : 'close'
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
		},

		onDomRefresh : function() {
			K2Widget.updateEvents(this.$el);
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
		}
	});

	return K2ViewHeader;
});
