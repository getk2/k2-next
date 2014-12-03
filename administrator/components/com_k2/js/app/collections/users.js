define(['backbone', 'collection', 'models/users'], function(Backbone, K2Collection, K2ModelUsers) {'use strict';
	var K2CollectionUsers = K2Collection.extend({
		model : K2ModelUsers,
		url : function() {
			return 'index.php?option=com_k2&task=users.sync&format=json' + this.buildQuery();
		}
	});
	return K2CollectionUsers;
});
