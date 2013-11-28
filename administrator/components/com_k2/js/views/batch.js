define(['marionette', 'dispatcher', 'text!layouts/batch.html'], function(Marionette, K2Dispatcher, template) {'use strict';
	var K2ViewBatch = Marionette.ItemView.extend({
		template : _.template(template),
		events : {
			'click #appBatchCancelButton' : 'close'
		},
		onBeforeClose : function() {
			jQuery.magnificPopup.close();
		},
		onShow : function() {
			require(['widgets/magnific/jquery.magnific-popup.min', 'css!widgets/magnific/magnific-popup.css'], _.bind(function() {
				jQuery.magnificPopup.open({
					alignTop : true,
					closeBtnInside : true,
					items : {
						src : this.el,
						type : 'inline'
					}
				});
			}, this));
		}
	});
	return K2ViewBatch;
});
