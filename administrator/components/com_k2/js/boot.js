'use strict';
define('jquery', [], function() {
	return window.jQuery;
});
require.config({
	urlArgs : 'v3.0.0',
	paths : {
		backbone : 'lib/backbone-min',
		underscore : 'lib/underscore-min',
		jqueryui : 'lib/jquery-ui.custom.min',
		marionette : 'lib/backbone.marionette.min'
	},
	shim : {
		underscore : {
			exports : '_'
		},
		backbone : {
			deps : ['jquery', 'underscore'],
			exports : 'Backbone'
		},
		marionette : {
			deps : ['underscore', 'backbone'],
			exports : 'Marionette'
		}
	}
});

require(['app'], function(K2) {
	K2.start();
});
