'use strict';
require.config({
	urlArgs : 'v3.0.0',
	paths : {
		backbone : ['backbone', '//cdnjs.cloudflare.com/ajax/libs/backbone.js/1.0.0/backbone'],
		underscore : '//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.4.4/underscore',
		jquery : '//ajax.googleapis.com/ajax/libs/jquery/1.8/jquery',
		jqueryui : '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min',
		marionette : '//cdnjs.cloudflare.com/ajax/libs/backbone.marionette/1.1.0-bundled/backbone.marionette'
	},
	shim : {
		jquery : {
			exports : 'jQuery'
		},
		underscore : {
			exports : '_'
		},
		backbone : {
			deps : ['jquery', 'underscore'],
			exports : 'Backbone'
		},
		marionette : {
			deps : ['jquery', 'underscore', 'backbone'],
			exports : 'Marionette'
		}
	}
});

require(['jquery', 'backbone', 'underscore', 'marionette', 'app'], function($, Backbone, _, Marionette, K2) {
	K2.start();
});
