define(['backbone', 'collection', 'models/categories'], function(Backbone, K2Collection, K2ModelCategories) {'use strict';
	var K2CollectionCategories = K2Collection.extend({
		model : K2ModelCategories,
		url : function() {
			return 'index.php?option=com_k2&task=categories.sync&format=json' + this.buildQuery();
		}
	});
	return K2CollectionCategories;
});
