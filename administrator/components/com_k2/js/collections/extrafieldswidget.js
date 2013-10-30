define(['backbone'], function(Backbone) {'use strict';

	var K2CollectionExtraFieldsWidget = Backbone.Collection.extend({
		initialize : function(models, options) {
			this.options = options;
		},
		url : function() {
			return 'index.php?option=com_k2&task=extrafields.render&format=json&scope=' + this.options.scope + '&resourceId=' + this.options.resourceId + '&filterId=' + this.options.filterId
		},
		setOption : function(name, value) {
			this.options[name] = value;
		}
	});
	return K2CollectionExtraFieldsWidget;

});
