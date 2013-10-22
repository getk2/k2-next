define(['backbone', 'collection', 'models/attachments'], function(Backbone, K2Collection, K2ModelAttachments) {'use strict';
	var K2CollectionAttachments = K2Collection.extend({
		model : K2ModelAttachments,
		url : function() {
			return 'index.php?option=com_k2&task=attachments.sync&format=json' + this.buildQuery();
		}
	});
	return K2CollectionAttachments;
});
