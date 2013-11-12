define(['backbone', 'model'], function(Backbone, K2Model) {'use strict';

	var K2ModelTag = K2Model.extend({

		defaults : {
			id : null,
			name : null,
			alias : null,
			state : null
		},

		urlRoot : function() {
			return 'index.php?option=com_k2&task=tags.sync&format=json'
		},
	});

	return K2ModelTag;

});
