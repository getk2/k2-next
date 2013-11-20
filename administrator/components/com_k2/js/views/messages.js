define(['marionette', 'text!layouts/messages.html', 'dispatcher'], function(Marionette, template, K2Dispatcher) {'use strict';

	var K2ViewMessages = Marionette.ItemView.extend({
		template : _.template(template),
		collectionEvents : {
			'reset' : 'render'
		},
		initialize : function() {
			K2Dispatcher.on('app:message', function(type, text) {
				this.collection.reset({
					type : type,
					message : text
				});
			}, this);
			K2Dispatcher.on('app:update:messages', function(response) {
				this.collection.reset(response.messages);
			}, this);
		},
	});
	return K2ViewMessages;
});
