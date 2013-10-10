define(['marionette', 'text!layouts/pagination.html', 'dispatcher'], function(Marionette, template, K2Dispatcher) {'use strict';

	var K2ViewPagination = Marionette.ItemView.extend({

		template : _.template(template),

		modelEvents : {
			'change' : 'render'
		},

		events : {
			'change .appLimit' : 'limit',
			'click .appPaginationPages a' : 'paginate'
		},

		onRender : function() {
			this.$el.find('.appLimit').val(this.model.get('limit'));
		},

		limit : function(event) {
			var el = jQuery(event.currentTarget);
			var limit = el.val();
			K2Dispatcher.trigger('app:controller:filter', 'limit', limit);
		},

		paginate : function(event) {
			event.preventDefault();
			var el = jQuery(event.currentTarget);
			var page = el.data('page');
			var currentPage = this.model.get('pagesCurrent');
			if (page === 'next') {
				var newPage = currentPage + 1;
			} else if (page === 'previous') {
				var newPage = currentPage + 1;
			} else {
				var newPage = page;
			}
			K2Dispatcher.trigger('app:controller:filter', 'page', newPage);
		}
	});

	return K2ViewPagination;
});
