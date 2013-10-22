define(['marionette', 'text!layouts/tags/form.html', 'dispatcher'], function(Marionette, template, K2Dispatcher) {'use strict';
	var K2ViewTag = Marionette.ItemView.extend({
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
	return K2ViewTag;
});
