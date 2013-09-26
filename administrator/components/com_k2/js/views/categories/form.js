'use strict';
define(['marionette', 'text!layouts/categories/form.html','dispatcher'], function(Marionette, template, K2Dispatcher) {
	var K2ViewCategory = Marionette.ItemView.extend({
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
	return K2ViewCategory;
});
