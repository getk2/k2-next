'use strict';
define(['marionette', 'text!layouts/pagination.html', 'dispatcher'], function(Marionette, template, K2Dispatcher) {

	var K2ViewPagination = Marionette.ItemView.extend({

		template : _.template(template),

		modelEvents : {
			'change' : 'render'
		}

	});

	return K2ViewPagination;
});
