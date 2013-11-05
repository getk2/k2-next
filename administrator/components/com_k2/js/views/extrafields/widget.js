define(['marionette', 'text!layouts/extrafields/widget.html'], function(Marionette, template) {'use strict';

	var K2CollectionExtraFieldsWidget = Backbone.Collection.extend({
		initialize : function() {
			this.options = {};
		},
		url : function() {
			return 'index.php?option=com_k2&task=extrafields.render&format=json&scope=' + this.options.scope + '&resourceId=' + this.options.resourceId + '&filterId=' + this.options.filterId
		},
		setOption : function(name, value) {
			this.options[name] = value;
		}
	});

	var K2ViewExtraFieldsWidget = Marionette.ItemView.extend({
		template : _.template(template),
		collectionEvents : {
			'reset' : 'render'
		},
		initialize : function(options) {
			this.collection = new K2CollectionExtraFieldsWidget(options.data);
			this.collection.setOption('scope', options.scope);
			this.collection.setOption('filterId', options.filterId);
			this.collection.setOption('resourceId', options.resourceId);
			this.on('filter', function(filterId) {
				this.collection.setOption('filterId', filterId);
				this.collection.fetch({
					reset : true
				});
			});
		},
		onDomRefresh : function() {
			jQuery(document).trigger('K2ExtraFieldsRender');
		}
	});
	return K2ViewExtraFieldsWidget;
});
