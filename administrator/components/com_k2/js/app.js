'use strict';
define(['marionette', 'router', 'controller', 'dispatcher', 'views/header', 'views/subheader'], function(Marionette, K2Router, K2Controller, K2Dispatcher, HeaderView, SubheaderView) {

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

		// Emulate HTTP
		Backbone.emulateHTTP = true;

		// Emulate JSON
		Backbone.emulateJSON = true;

		// Backbone history
		Backbone.history.start();

		// Render the header view
		K2.header.show(new HeaderView({
			model : new Backbone.Model({
				'menu' : [],
				'actions' : []
			})
		}));

		// Render the subheader view
		K2.subheader.show(new SubheaderView({
			model : new Backbone.Model({
				'title' : '',
				'filters' : [],
				'actions' : []
			})
		}));

		//@TODO Add intializing code for the rest regions.

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

	// Redirect event listener. Updates the browser URL and triggers the router function depending on the trigger variable.
	K2Dispatcher.on('app:redirect', function(url, trigger) {
		K2.router.navigate(url, {
			trigger : trigger
		});
	});

	// Render event listener. Renders a view to a region.
	K2Dispatcher.on('app:render', function(view, region) {
		K2[region].show(view);
	});

	// Update event listener. Triggered when the server response is parsed.
	K2Dispatcher.on('app:update', function(response) {

		K2Dispatcher.trigger('app:update:header', response);
		K2Dispatcher.trigger('app:update:subheader', response);
		K2Dispatcher.trigger('app:update:sidebar', response);

		//@TODO Trigger the rest subevents
	});

	// Return the application instance
	return K2;
});
