'use strict';
define(['marionette', 'router', 'controller', 'dispatcher', 'views/header', 'views/subheader', 'views/sidebar'], function(Marionette, K2Router, K2Controller, K2Dispatcher, HeaderView, SubheaderView, SidebarView) {

	// Backbone.sync
	// -------------

	// Override of the default Backbone.sync implementation.
	// Enforces Backbone.emulateHTTP = true and Backbone.emulateJSON = true.
	// Copies any model attributes to the data object.

	Backbone.sync = function(method, model, options) {

		// Initialize the options object if it is not set
		options || ( options = {});
		if (options.data === undefined) {
			options.data = [];
		}

		// Detect the request type
		switch (method) {
			case 'create':
				var type = 'POST';
				break;
			case 'update':
				var type = 'PUT';
				break;
			case 'patch':
				var type = 'PATCH';
				break;
			case 'delete':
				var type = 'DELETE';
				break;
			case 'read':
				var type = 'GET';
				break;
		}

		// Request params
		var params = {
			type : (method === 'read') ? 'GET' : 'POST',
			dataType : 'json',
			contentType : 'application/x-www-form-urlencoded',
			url : _.result(model, 'url') || urlError()
		};

		// For create, update, patch and delete methods pass as aerguments the method and the session token.
		if (method !== 'read') {
			options.data.push({
				name : '_method',
				value : type
			});
			options.data.push({
				name : K2SessionToken,
				value : 1
			});
		}

		// Convert any model attributes to data
		_.each(options.attrs, function(value, attribute) {
			options.data.push({
				name : 'states[' + attribute + ']',
				value : value
			});
		});

		// Make the request, allowing the user to override any Ajax options
		var xhr = options.xhr = Backbone.ajax(_.extend(params, options));
		model.trigger('request', model, xhr, options);
		return xhr;

	};

	// Initialize the application
	var K2 = new Marionette.Application();

	// Set the regions
	K2.addRegions({
		header : '#appHeader',
		sidebar : '#appSidebar',
		subheader : '#appSubheader',
		content : '#appContent'
	});

	// On after initialize
	K2.on('initialize:after', function() {

		// Backbone history
		Backbone.history.start();

		// Add the language function to the window object so it can be executed in our layouts.
		window.l = function(key) {
			return K2Language[key] || key;
		};
		// String repeat
		window.str_repeat = function(input, multiplier) {
			// http://kevin.vanzonneveld.net
			// +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
			// +   improved by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
			// +   improved by: Ian Carter (http://euona.com/)
			// *     example 1: str_repeat('-=', 10);
			// *     returns 1: '-=-=-=-=-=-=-=-=-=-='

			var y = '';
			while (true) {
				if (multiplier & 1) {
					y += input;
				}
				multiplier >>= 1;
				if (multiplier) {
					input += input;
				} else {
					break;
				}
			}
			return y;

		};

	});

	// Render the header view
	require(['views/header'], _.bind(function(HeaderView) {
		var header = new HeaderView({
			model : new Backbone.Model({
				menu : [],
				actions : []
			})
		});
		K2Dispatcher.trigger('app:region:show', header, 'header');
	}, this));

	// Render the subheader view
	require(['views/subheader'], _.bind(function(SubheaderView) {
		var subheader = new SubheaderView({
			model : new Backbone.Model({
				title : '',
				filters : [],
				toolbar : []
			})
		});
		K2Dispatcher.trigger('app:region:show', subheader, 'subheader');
	}, this));

	// Render the sidebar view
	require(['views/sidebar'], _.bind(function(SidebarView) {
		var sidebar = new SidebarView({
			model : new Backbone.Model({
				menu : [],
				filters : []
			})
		});
		K2Dispatcher.trigger('app:region:show', sidebar, 'sidebar');
	}, this));

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
	K2Dispatcher.on('app:region:show', function(view, region) {
		K2[region].show(view);
	});

	// Reset region event listener. Renders a view to a region.
	K2Dispatcher.on('app:region:reset', function(region) {
		K2[region].reset();
	});

	// Message event listener
	K2Dispatcher.on('app:message', function(type, text) {
		alert('Type:' + type + ' Message: ' + text);
	});

	// Update event listener. Triggered when the server response is parsed.
	K2Dispatcher.on('app:update', function(response) {

		K2Dispatcher.trigger('app:update:header', response);
		K2Dispatcher.trigger('app:update:subheader', response);
		K2Dispatcher.trigger('app:update:sidebar', response);
	});

	// Return the application instance
	return K2;
});
