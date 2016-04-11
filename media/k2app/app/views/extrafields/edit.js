define(['marionette', 'text!templates/extrafields/edit.html', 'dispatcher', 'widget'], function(Marionette, template, K2Dispatcher, K2Widget) {'use strict';
	var K2ViewExtraFields = Marionette.ItemView.extend({
		template : _.template(template),
		modelEvents : {
			'change' : 'render'
		},
		events : {
			'change #type' : 'renderExtraField',
			'change #group' : 'showNewGroupNameBox'
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
			this.showNewGroupNameBox();
		},
		renderExtraField : function() {
			var type = this.$('#type').val();
			var form = this.model.getForm();
			var definitions = form.get('definitions');
			this.$('[data-region="extra-field-definition"]').html(definitions[type]);
			K2Widget.updateEvents(this.$el);
			jQuery(document).trigger('K2ExtraFieldsRender');
		},
		showNewGroupNameBox : function() {
			var state = this.$('#group').val();
			if(state == 0){
				jQuery('#groupContainer').removeClass('hide');
			}else{
				jQuery('#groupContainer').addClass('hide');
			}
		}
	});
	return K2ViewExtraFields;
});
