define(['backbone', 'collection', 'models/comments'], function(Backbone, K2Collection, K2ModelComments) {'use strict';
	var K2CollectionComments = K2Collection.extend({
		model : K2ModelComments,
		url : function() {
			return 'index.php?option=com_k2&task=comments.sync&format=json' + this.buildQuery();
		}
	});
	return K2CollectionComments;
});
