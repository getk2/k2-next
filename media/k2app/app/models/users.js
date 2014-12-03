define(['backbone', 'model'], function(Backbone, K2Model) {'use strict';

	var K2ModelUsers = K2Model.extend({

		defaults : {
			id : null,
			name : null
		},

		urlRoot : function() {
			return 'index.php?option=com_k2&task=users.sync&format=json';
		},
	});

	return K2ModelUsers;

});
