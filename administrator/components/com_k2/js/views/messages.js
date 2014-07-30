define(['marionette', 'text!layouts/messages.html', 'dispatcher'], function(Marionette, template, K2Dispatcher) {'use strict';

	var K2ViewMessages = Marionette.ItemView.extend({
		template : _.template(template),
		collectionEvents : {
			'add' : 'render',
			'reset' : 'render'
		},
		initialize : function() {
			K2Dispatcher.on('app:messages:add', function(type, text) {
				this.collection.add({
					id : type + text,
					type : type,
					message : text
				}, {
					merge : true
				});
			}, this);
			K2Dispatcher.on('app:messages:set', function(response) {
				this.collection.set(response.messages);
				response.messages = [];
			}, this);
			K2Dispatcher.on('app:messages:reset', function(response) {
				this.collection.reset(response.messages);
				response.messages = [];
			}, this);
		},
	});
	return K2ViewMessages;
});
