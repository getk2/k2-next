define(['marionette', 'text!layouts/extrafields/form.html', 'dispatcher'], function(Marionette, template, K2Dispatcher) {'use strict';
	var K2ViewExtraFields = Marionette.ItemView.extend({
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
	return K2ViewExtraFields;
});
