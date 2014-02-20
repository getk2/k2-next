define(['marionette', 'text!layouts/information.html', 'dispatcher'], function(Marionette, template, K2Dispatcher) {'use strict';

	var K2ViewInformation = Marionette.ItemView.extend({
		template : _.template(template)
	});

	return K2ViewInformation;
});
