'use strict';
define(['marionette', 'text!layouts/header.html', 'dispatcher'], function(Marionette, template, K2Dispatcher) {

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
		
		add : function(event) {
			event.preventDefault();
			K2Dispatcher.trigger('app:controller:add');
		},

		save : function(event) {
			event.preventDefault();
			K2Dispatcher.trigger('app:controller:save', 'edit');
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
