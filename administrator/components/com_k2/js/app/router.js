define(['marionette'], function(Marionette) {'use strict';
	var K2Router = Marionette.AppRouter.extend({
		appRoutes : {
			'settings' : 'settings',
			'media' : 'media',
			'information' : 'information',
			'utilities' : 'utilities',
			'modal/[url]' : 'execute',
			'*any' : 'execute'
		}
	});
	return K2Router;
});
