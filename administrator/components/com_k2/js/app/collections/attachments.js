define(['backbone', 'collection', 'models/attachments'], function(Backbone, K2Collection, K2ModelAttachments) {'use strict';
	var K2CollectionAttachments = K2Collection.extend({
		initialize : function() {
			this.states = new Backbone.Model();
		},
		model : K2ModelAttachments,
		url : function() {
			return 'index.php?option=com_k2&task=attachments.sync&format=json' + this.getQuery();
		},
		setState : function(name, value) {
			this.states.set(name, value);
		},
		getState : function(name) {
			return this.states.get(name);
		},
		getQuery : function() {
			return '&' + jQuery.param(this.states.toJSON());
		},
		parse : function(resp, options) {
			return resp.rows;
		}
	});
	return K2CollectionAttachments;
});
