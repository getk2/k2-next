define(['backbone', 'model'], function(Backbone, K2Model) {'use strict';

	var K2ModelUtilities = K2Model.extend({

		defaults : {
			id : null
		},

		urlRoot : function() {
			return 'index.php?option=com_k2&task=utilities.sync&format=json';
		},
	});

	return K2ModelUtilities;

});
