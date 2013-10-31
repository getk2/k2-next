define(['backbone'], function(Backbone) {'use strict';

	var K2CollectionAttachmentsWidget = Backbone.Collection.extend({
		url : function() {
			return 'index.php?option=com_k2&task=attachments.sync&format=json&itemId='+ this.itemId;
		},
		setItemId : function(itemId) {
			this.itemId = itemId;
		},
		parse : function(resp) {
			return resp.rows;
		}
	});
	return K2CollectionAttachmentsWidget;

});
