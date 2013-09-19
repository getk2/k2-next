'use strict';
define(['backbone', 'collection', 'models/items'], function(Backbone, K2Collection, K2ModelItems) {
	var K2CollectionItems = K2Collection.extend({
		model : K2ModelItems,
		url : function() {
			return 'index.php?option=com_k2&task=items.sync&format=json' + this.buildQuery();
		}
	});
	return K2CollectionItems;
});
