define(['marionette', 'text!layouts/404.html'], function(Marionette, template) {'use strict';

	var K2View404 = Marionette.ItemView.extend({
		template : _.template(template)
	});

	return K2View404;
});
