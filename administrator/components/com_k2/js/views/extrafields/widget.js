define(['marionette', 'text!layouts/extrafields/widget.html'], function(Marionette, template) {'use strict';
	var K2ViewExtraFieldsWidget = Marionette.ItemView.extend({
		template : _.template(template),
		collectionEvents : {
			'reset' : 'render'
		},
		onDomRefresh : function() {
			jQuery(document).trigger('K2ExtraFieldsRender');
		}
	});
	return K2ViewExtraFieldsWidget;
});
