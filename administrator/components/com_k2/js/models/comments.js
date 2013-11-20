define(['backbone', 'model'], function(Backbone, K2Model) {'use strict';

	var K2ModelComments = K2Model.extend({

		defaults : {
			id : null,
			itemId : null,
			userId : null,
			name : null,
			date : null,
			email : null,
			url : null,
			ip : null,
			text : null,
			state : null
		},

		urlRoot : function() {
			return 'index.php?option=com_k2&task=comments.sync&format=json'
		},
	});

	return K2ModelComments;

});
