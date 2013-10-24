define(['backbone', 'model'], function(Backbone, K2Model) {'use strict';

	var K2ModelMedia = K2Model.extend({
		
		defaults : {
			id : null
		},
		
		urlRoot : function() {
			return 'index.php?option=com_k2&task=media.sync&format=json'
		},
	});

	return K2ModelMedia;

});
