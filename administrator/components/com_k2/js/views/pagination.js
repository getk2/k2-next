'use strict';
define(['marionette', 'text!layouts/pagination.html', 'dispatcher'], function(Marionette, template, K2Dispatcher) {

	var K2ViewPagination = Marionette.ItemView.extend({

		template : _.template(template),

		modelEvents : {
			'change' : 'render'
		},

		events : {
			'change .appLimit' : 'limit'
		},
		
		onRender : function() {
			this.$el.find('.appLimit').val(this.model.get('limit'));
		}, 

		limit : function(event) {
			var el = jQuery(event.currentTarget);
			var limit = el.val();
			K2Dispatcher.trigger('app:controller:filter', 'limit', limit);
		}
	});

	return K2ViewPagination;
});
