define(['backbone', 'model'], function(Backbone, K2Model) {'use strict';

	var K2ModelInformation = K2Model.extend({
		
		defaults : {
			id : null
		},
		
		urlRoot : function() {
			return 'index.php?option=com_k2&task=information.sync&format=json';
		},
	});

	return K2ModelInformation;

});
