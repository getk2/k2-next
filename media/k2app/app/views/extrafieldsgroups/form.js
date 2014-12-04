define(['marionette', 'text!templates/extrafieldsgroups/form.html', 'dispatcher', 'widget'], function(Marionette, template, K2Dispatcher, K2Widget) {'use strict';
	var K2ViewExtraFieldsGroup = Marionette.ItemView.extend({
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
		onDomRefresh : function() {
			K2Widget.updateEvents(this.$el);
		}
	});
	return K2ViewExtraFieldsGroup;
});
