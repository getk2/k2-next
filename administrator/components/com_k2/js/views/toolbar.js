'use strict';
define(['marionette', 'text!layouts/toolbar.html', 'dispatcher'], function(Marionette, template, K2Dispatcher) {

	var K2ViewToolbar = Marionette.ItemView.extend({

		template : _.template(template),

		events : {
			'click .jwBatchToggler' : 'batchToggle',
			'click #jwDeleteButton' : 'batchDelete',
			'click #jwBatchButton' : 'batchWindow',
			'click .jwToolbarCancel' : 'cancel'
		},

		modelEvents : {
			'change' : 'render'
		},

		initialize : function() {
			K2Dispatcher.on('app:update:toolbar', function(response) {
				this.model.set('toolbar', response.toolbar);
			}, this);
		}

	});

	return K2ViewToolbar;
});
