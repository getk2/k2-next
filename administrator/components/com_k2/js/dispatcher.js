'use strict';
define(['backbone', 'marionette'], function(Backbone, Marionette) {
	var K2Dispatcher = new Backbone.Wreqr.EventAggregator();
	return K2Dispatcher;
});
