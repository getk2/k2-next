define(['backbone', 'collection', 'models/usergroups'], function(Backbone, K2Collection, K2ModelUserGroups) {'use strict';
	var K2CollectionUserGroups = K2Collection.extend({
		model : K2ModelUserGroups,
		url : function() {
			return 'index.php?option=com_k2&task=usergroups.sync&format=json' + this.buildQuery();
		}
	});
	return K2CollectionUserGroups;
});
