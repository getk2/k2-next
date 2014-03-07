define(['marionette', 'text!layouts/extrafields/widget.html', 'widgets/widget', 'dispatcher'], function(Marionette, template, K2Widget, K2Dispatcher) {'use strict';

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
			this.validationErrors = [];
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
			jQuery('.k2ExtraFieldRequired').removeClass('k2ExtraFieldRequired');
			jQuery(document).trigger('K2ExtraFieldsRender');
		},
		onClose : function() {
			jQuery(document).unbind('K2ExtraFieldsValidate');
		},
		validate : function() {
			var result = true;
			jQuery(document).trigger('K2ExtraFieldsValidate', this);
			if(this.validationErrors.length > 0) {
				_.each(this.validationErrors, function(extraFieldId) {
					jQuery('#k2ExtraField'+extraFieldId).addClass('k2ExtraFieldRequired');
				});
				K2Dispatcher.trigger('app:messages:add', 'error', l('K2_EXTRA_FIELDS_REQUIRED'));
				this.validationErrors = [];
				result = false;
			}
			return result;
		},
		addValidationError: function(extraFieldId) {
			this.validationErrors.push(extraFieldId);
		}
	});
	return K2ViewExtraFieldsWidget;
});
