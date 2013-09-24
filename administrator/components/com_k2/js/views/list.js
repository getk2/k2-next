'use strict';
define(['marionette', 'text!layouts/list.html', 'dispatcher'], function(Marionette, template, K2Dispatcher) {

	var K2ListLayout = Marionette.Layout.extend({
		template : _.template(template),
		regions : {
			grid : '.jwGrid',
			pagination : '.jwPagination'
		}
	});

	return K2ListLayout;
});
