'use strict';
require.config({
	urlArgs : 'v3.0.0',
	paths : {
		backbone : 'lib/backbone-min',
		underscore : 'lib/underscore-min',
		jquery : 'lib/jquery.min',
		jqueryui : 'lib/jquery-ui.min',
		marionette : 'lib/backbone.marionette.min'
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
