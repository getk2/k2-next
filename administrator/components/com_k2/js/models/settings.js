'use strict';
define(['backbone', 'model'], function(Backbone, K2Model) {

	var K2ModelSettings = K2Model.extend({
		
		defaults : {
			id : null
		},
		
		urlRoot : function() {
			return 'index.php?option=com_k2&task=settings.sync&format=json'
		},
	});

	return K2ModelSettings;

});
