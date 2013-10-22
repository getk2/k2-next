define(['backbone', 'model'], function(Backbone, K2Model) {'use strict';

	var K2ModelAttachment = K2Model.extend({

		defaults : {
			id : null,
			itemId : null,
			name : null,
			title : null,
			file : null,
			url : null,
			downloads : null
		},

		urlRoot : function() {
			return 'index.php?option=com_k2&task=attachments.sync&format=json'
		},
	});

	return K2ModelAttachment;

});
