'use strict';

define(['marionette'], function(Marionette) {
	var K2Router = Marionette.AppRouter.extend({
		appRoutes : {
			'*any' : 'execute'
		}
	});
	return K2Router;
});
