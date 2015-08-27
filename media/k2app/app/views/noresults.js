define(['marionette', 'text!templates/noresults.html', 'dispatcher'], function(Marionette, template, K2Dispatcher) {'use strict';

	var K2ViewNoResults = Marionette.ItemView.extend({
		template : _.template(template)
	});
	return K2ViewNoResults;
});
