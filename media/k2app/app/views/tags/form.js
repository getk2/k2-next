define(['marionette', 'text!templates/tags/form.html', 'views/extrafields/widget'], function(Marionette, template, K2ViewExtraFieldsWidget) {'use strict';
	var K2ViewTag = Marionette.LayoutView.extend({
		template : _.template(template),
		initialize : function() {
			this.extraFieldsView = new K2ViewExtraFieldsWidget({
				data : this.model.getForm().get('extraFields'),
				resourceId : this.model.get('id'),
				filterId : 0,
				scope : 'tag'
			});
		},
		modelEvents : {
			'change' : 'render'
		},
		regions : {
			extraFieldsRegion : '[data-region="tag-extra-fields"]'
		},
		onShow : function() {
			this.extraFieldsRegion.show(this.extraFieldsView);
			this.extraFieldsView.render();
		},
		serializeData : function() {
			var data = {
				'row' : this.model.toJSON(),
				'form' : this.model.getForm().toJSON()
			};
			return data;
		},
		// OnBeforeSave event
		onBeforeSave : function() {
			
			// Validate extra fields
			var result = this.extraFieldsView.validate();
			
			return result;
		}
	});
	return K2ViewTag;
});
