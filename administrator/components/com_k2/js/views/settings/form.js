'use strict';
define(['marionette', 'text!layouts/settings/form.html','dispatcher'], function(Marionette, template, K2Dispatcher) {
	var K2ViewSettings = Marionette.ItemView.extend({
		template : _.template(template),
		modelEvents : {
			'change' : 'render'
		},
		serializeData : function() {
			var data = {
				'row' : this.model.toJSON(),
				'form' : this.model.getForm().toJSON()
			};
			return data;
		},
	});
	return K2ViewSettings;
});
