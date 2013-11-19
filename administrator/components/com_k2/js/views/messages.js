define(['marionette', 'text!layouts/messages.html', 'dispatcher'], function(Marionette, template, K2Dispatcher) {'use strict';

	var K2ViewMessages = Marionette.ItemView.extend({
		template : _.template(template),
		collectionEvents : {
			'add' : 'render'
		},
		initialize : function() {
			K2Dispatcher.on('app:update:messages', function(response) {
				this.collection.set(response.messages);
			}, this);
		},
	});
	return K2ViewMessages;
});
