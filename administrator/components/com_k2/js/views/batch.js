define(['marionette', 'dispatcher', 'text!layouts/batch.html'], function(Marionette, K2Dispatcher, template) {'use strict';
	var K2ViewBatch = Marionette.ItemView.extend({
		template : _.template(template),
		events : {
			'click #appBatchApplyButton' : 'batch',
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
		},
		batch : function(event) {
			event.preventDefault();
			var rows = jQuery('input.appRowToggler:checked').serializeArray();
			var states = {};
			this.$('select').each(function() {
				states[jQuery(this).attr('name')] = jQuery(this).val();
			});
			var mode = this.$('input[name="batchMode"]:checked').val();
			K2Dispatcher.trigger('app:controller:batchSetMultipleStates', rows, states, mode);
			this.close();
		}
	});
	return K2ViewBatch;
});
