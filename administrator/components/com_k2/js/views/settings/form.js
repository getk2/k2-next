define(['marionette', 'text!layouts/settings/form.html','dispatcher', 'widgets/widget'], function(Marionette, template, K2Dispatcher, K2Widget) {'use strict';
	var K2ViewSettings = Marionette.ItemView.extend({
		template : _.template(template),
		modelEvents : {
			'change' : 'render'
		},
		onRender : function() {
			K2Widget.updateEvents(this.$el);
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
