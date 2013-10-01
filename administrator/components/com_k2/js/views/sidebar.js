'use strict';
define(['marionette', 'text!layouts/sidebar.html', 'dispatcher'], function(Marionette, template, K2Dispatcher) {

	var K2ViewSidebar = Marionette.ItemView.extend({

		template : _.template(template),

		modelEvents : {
			'change' : 'render'
		}

	});

	return K2ViewSidebar;
});
