'use strict';
define('jquery', [], function() {
	return window.jQuery;
});
require.config({
	//baseUrl: 'components/com_k2/js', Uncomment this if we build the app into a single file. This way we will have the basic app files loaded in one file while the third-party files will still get loaded during runtime
	urlArgs : 'v3.0.0BETA',
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
