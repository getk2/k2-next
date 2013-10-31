define(['marionette', 'text!layouts/extrafields/form.html', 'dispatcher', 'widgets/widget'], function(Marionette, template, K2Dispatcher, K2Widget) {'use strict';
	var K2ViewExtraFields = Marionette.ItemView.extend({
		template : _.template(template),
		modelEvents : {
			'change' : 'render'
		},
		events : {
			'change #type' : 'renderExtraField'
		},
		serializeData : function() {
			var data = {
				'row' : this.model.toJSON(),
				'form' : this.model.getForm().toJSON()
			};
			return data;
		},
		onDomRefresh : function() {
			this.renderExtraField();
			K2Widget.updateEvents();
		},
		renderExtraField : function() {
			var type = this.$el.find('#type').val();
			var form = this.model.getForm();
			var definitions = form.get('definitions');
			this.$el.find('#appExtraFieldDefinition').html(definitions[type]);
		}
	});
	return K2ViewExtraFields;
});
