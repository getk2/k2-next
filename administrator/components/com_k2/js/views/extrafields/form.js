define(['marionette', 'text!layouts/extrafields/form.html', 'dispatcher'], function(Marionette, template, K2Dispatcher) {'use strict';
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
		},
		renderExtraField : function() {
			var type = this.$el.find('#type').val();
			var definitions = this.model.get('definitions');
			this.$el.find('#appExtraFieldInput').html(definitions[type]);
		}
	});
	return K2ViewExtraFields;
});
