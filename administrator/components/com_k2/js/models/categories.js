define(['backbone', 'model'], function(Backbone, K2Model) {'use strict';

	var K2ModelCategory = K2Model.extend({

		defaults : {
			id : null,
			asset_id : null,
			title : null,
			published : null,
			access : null,
			ordering : null,
			language : null,
			params : null
		},

		urlRoot : function() {
			return 'index.php?option=com_k2&task=categories.sync&format=json'
		},
	});

	return K2ModelCategory;

});
