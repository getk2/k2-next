define(['backbone', 'marionette'], function(Backbone, Marionette) {'use strict';
	var K2Dispatcher = new Backbone.Wreqr.EventAggregator();
	return K2Dispatcher;
});
