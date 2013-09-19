'use strict';
define(['marionette', 'text!layouts/items/form.html'], function(Marionette, template) {
	var K2ViewItem = Marionette.ItemView.extend({
		template : _.template(template),
		modelEvents : {
			'change' : 'render'
		}
	});
	return K2ViewItem;
});
