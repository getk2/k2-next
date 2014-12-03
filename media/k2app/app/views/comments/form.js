define(['marionette', 'text!layouts/comments/form.html'], function(Marionette, template) {'use strict';
	var K2ViewComment = Marionette.ItemView.extend({
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
		}
	});
	return K2ViewComment;
});
