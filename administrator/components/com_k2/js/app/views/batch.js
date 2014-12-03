define(['marionette', 'dispatcher', 'text!layouts/batch.html', 'widget'], function(Marionette, K2Dispatcher, template, K2Widget) {'use strict';
	var K2ViewBatch = Marionette.ItemView.extend({
		template : _.template(template),
		events : {
			'click [data-action="apply"]' : 'batch',
			'click [data-action="cancel"]' : 'destroy'
		},
		onBeforeDestroy : function() {
			jQuery.magnificPopup.close();
		},
		onShow : function() {
			require(['magnific', 'css!magnificStyle'], _.bind(function() {
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
		onDomRefresh : function() {
			K2Widget.updateEvents(this.$el);
		},
		batch : function(event) {
			event.preventDefault();
			var rows = jQuery('input[data-action="toggle"]:checked').serializeArray();
			var states = {};
			this.$('select, input').each(function() {
				states[jQuery(this).attr('name')] = jQuery(this).val();
			});
			var mode = this.$('input[name="batchMode"]:checked').val();
			K2Dispatcher.trigger('app:controller:batchSetMultipleStates', rows, states, mode);
			this.destroy();
		}
	});
	return K2ViewBatch;
});
