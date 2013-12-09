define(['backbone', 'model'], function(Backbone, K2Model) {'use strict';

	var K2ModelAttachments = K2Model.extend({
		initialize : function() {
			this.set('cid', this.cid);
		},
		defaults : {
			id : null,
			itemId : null,
			tmpId : null,
			name : null,
			title : null,
			file : null,
			url : null,
			downloads : 0
		},
		urlRoot : 'index.php?option=com_k2&task=attachments.sync&format=json',
		url : function() {
			var base = _.result(this, 'urlRoot') || _.result(this.collection, 'url') || urlError();
			if (this.isNew())
				return base;
			return base + '&id=' + encodeURIComponent(this.id);
		},
		sync : function(method, model, options) {
			// Convert any model attributes to data if options data is empty
			if (options.data === undefined) {
				options.data = [];
			}
			_.each(model.attributes, function(value, attribute) {
				options.data.push({
					name : attribute,
					value : value
				});
			});
			return Backbone.sync.apply(this, arguments);
		}
	});

	return K2ModelAttachments;

});
