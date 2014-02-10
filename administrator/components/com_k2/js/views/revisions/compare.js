define(['marionette', 'text!layouts/revisions/compare.html'], function(Marionette, template) {'use strict';
	var K2ViewRevision = Marionette.Layout.extend({
		template : _.template(template),
		initialize : function() {
			this.extraFieldsView = new K2ViewExtraFieldsWidget({
				data : this.model.getForm().get('extraFields'),
				resourceId : this.model.get('id'),
				filterId : this.model.get('id'),
				scope : 'tag'
			});
		},
		modelEvents : {
			'change' : 'render'
		},
		regions : {
			extraFieldsRegion : '#appTagExtraFields'
		},
		onShow : function() {
			this.extraFieldsRegion.show(this.extraFieldsView);
		},
		serializeData : function() {
			var data = {
				'row' : this.model.toJSON(),
				'form' : this.model.getForm().toJSON()
			};
			return data;
		}
	});
	return K2ViewRevision;
});
