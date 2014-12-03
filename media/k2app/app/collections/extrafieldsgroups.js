define(['backbone', 'collection', 'models/extrafieldsgroups'], function(Backbone, K2Collection, K2ModelExtraFieldsGroups) {'use strict';
	var K2CollectionExtraFieldsGroups = K2Collection.extend({
		model : K2ModelExtraFieldsGroups,
		url : function() {
			return 'index.php?option=com_k2&task=extrafieldsgroups.sync&format=json' + this.buildQuery();
		}
	});
	return K2CollectionExtraFieldsGroups;
});
