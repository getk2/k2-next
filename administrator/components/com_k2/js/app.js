'use strict';
define(['marionette', 'router', 'controller', 'dispatcher', 'views/header'], function(Marionette, K2Router, K2Controller, K2Dispatcher, HeaderView) {

	// Initialize the application
	var K2 = new Marionette.Application();

	// Set the regions
	K2.addRegions({
		header : '#jwHeader',
		subheader : '#jwSubheader',
		sidebar : '#jwSidebar',
		content : '#jwContent',
		pagination : '#jwPagination'
	});

	// On after initialize
	K2.on('initialize:after', function() {
		Backbone.emulateHTTP = true;
		Backbone.emulateJSON = true;
		Backbone.history.start();

		// Render the header. @TODO Add intializing code for the rest regions.
		K2.header.show(new HeaderView({
			model : new Backbone.Model()
		}));
	});

	// Add initializer
	K2.addInitializer(function(options) {

		// Controller
		this.controller = new K2Controller();

		// Router
		this.router = new K2Router({
			controller : this.controller
		});

	});

	// Redirect event
	K2Dispatcher.on('app:redirect', function(url, trigger) {
		K2.router.navigate(url, {
			trigger : trigger
		});
	});

	// Render event
	K2Dispatcher.on('app:render', function(view) {
		K2.content.show(view);
	});

	// Return the application instance
	return K2;
});
