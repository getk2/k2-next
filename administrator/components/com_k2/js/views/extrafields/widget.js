define(['marionette', 'text!layouts/extrafields/widget.html', 'widgets/widget'], function(Marionette, template, K2Widget) {'use strict';

	var K2CollectionExtraFieldsWidget = Backbone.Collection.extend({
		initialize : function() {
			this.options = {};
		},
		url : function() {
			return 'index.php?option=com_k2&task=extrafields.render&format=json&scope=' + this.options.scope + '&resourceId=' + this.options.resourceId + '&filterId=' + this.options.filterId;
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
			K2Widget.updateEvents(this.$el);
			jQuery(document).trigger('K2ExtraFieldsRender');
		},
		/*validate : function() {
			_.each(this.collection.models, function(group) {
				var fields = group.get('fields');
				_.each(fields, function(field) {
					if(field.required > 0) {
						var el = jQuery('[name="extra_fields['+field.id+'][value]"]');
						console.info(el.val());
						if(el.val() == '')
						{
							alert('Required!');
						}
					}
				});
			});
		}*/
	});
	return K2ViewExtraFieldsWidget;
});
