define(['backbone', 'collection', 'models/extrafields'], function(Backbone, K2Collection, K2ModelExtraFields) {'use strict';
	var K2CollectionExtraFields = K2Collection.extend({
		model : K2ModelExtraFields,
		url : function() {
			return 'index.php?option=com_k2&task=extrafields.sync&format=json' + this.buildQuery();
		}
	});
	return K2CollectionExtraFields;
});
