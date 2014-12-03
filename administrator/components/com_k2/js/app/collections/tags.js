define(['backbone', 'collection', 'models/tags'], function(Backbone, K2Collection, K2ModelTags) {'use strict';
	var K2CollectionTags = K2Collection.extend({
		model : K2ModelTags,
		url : function() {
			return 'index.php?option=com_k2&task=tags.sync&format=json' + this.buildQuery();
		}
	});
	return K2CollectionTags;
});
