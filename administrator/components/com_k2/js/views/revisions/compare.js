define(['marionette', 'text!layouts/revisions/compare.html'], function(Marionette, template) {'use strict';
	var K2ViewRevision = Marionette.Layout.extend({
		template : _.template(template)
	});
	return K2ViewRevision;
});
