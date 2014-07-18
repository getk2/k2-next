define(['backbone', 'model'], function(Backbone, K2Model) {'use strict';

	var K2ModelExtraFields = K2Model.extend({

		defaults : {
			id : null,
			title : null
		},

		urlRoot : function() {
			return 'index.php?option=com_k2&task=extrafields.sync&format=json';
		},
	});

	return K2ModelExtraFields;

});
