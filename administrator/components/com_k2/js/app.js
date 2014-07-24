define(['marionette', 'router', 'controller', 'dispatcher', 'views/header', 'views/subheader', 'views/sidebar', 'ui'], function(Marionette, K2Router, K2Controller, K2Dispatcher, HeaderView, SubheaderView, SidebarView) {'use strict';

	// Override the default Backbone.Sync implementation
	require(['sync']);

	// Bind all jQuery AJAX requests so we can add a class for loading.
	jQuery(document).bind('ajaxSend', function() {
		jQuery('div[data-application="k2"]').addClass('k2-loading');
	}).bind('ajaxComplete', function() {
		jQuery('div[data-application="k2"]').removeClass('k2-loading');
	});

	// Initialize the application
	var K2 = new Marionette.Application();

	// Set the regions
	K2.addRegions({
		messages : '[data-region="messages"]',
		header : '[data-region="header"]',
		sidebar : '[data-region="sidebar"]',
		subheader : '[data-region="subheader"]',
		content : '[data-region="content"]',
		modal : '[data-region="modal"]'
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

	// Render the messages view
	require(['views/messages'], _.bind(function(MessagesView) {
		var messages = new MessagesView({
			collection : new Backbone.Collection
		});
		K2Dispatcher.trigger('app:region:show', messages, 'messages');
	}, this));

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

	// Modal display listener
	K2Dispatcher.on('app:modal', function(view) {

	});

	// Update event listener. Triggered when the server response is parsed.
	K2Dispatcher.on('app:update', function(response) {
		if (response.method === 'GET') {
			// Hide menu and action buttons in modal views
			if (K2.controller.isModal) {
				response.menu.primary = [];
				response.menu.secondary = [];
				response.actions = [];
			}
			// Update the application regions/views
			K2Dispatcher.trigger('app:update:header', response);
			K2Dispatcher.trigger('app:update:subheader', response);
			// Add scripts and styles
			require(_.union(response.styles, response.scripts));
		}
	});

	// Return the application instance
	return K2;
});
