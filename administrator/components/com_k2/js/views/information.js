define(['marionette', 'text!layouts/information.html', 'dispatcher', 'widgets/widget'], function(Marionette, template, K2Dispatcher, K2Widget) {'use strict';

	var K2ViewInformation = Marionette.ItemView.extend({
		template : _.template(template),
		onDomRefresh : function() {
			K2Widget.updateEvents(this.$el);
		}
	});

	return K2ViewInformation;
});
