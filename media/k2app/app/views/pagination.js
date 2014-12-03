define(['marionette', 'text!templates/pagination.html', 'dispatcher', 'session'], function(Marionette, template, K2Dispatcher, K2Session) {'use strict';

	var K2ViewPagination = Marionette.ItemView.extend({

		template : _.template(template),

		initialize : function() {
			var viewMode = K2Session.get('view.mode', 'pagination');
			this.model.set('mode', viewMode);
			K2Dispatcher.on('app:pagination:mode', function(mode) {
				this.model.set('mode', mode);
				if (mode == 'scroll') {
					this.model.set('limit', 50);
					K2Dispatcher.trigger('app:controller:setCollectionState', 'limit', 50);
				} else {
					this.model.set('limit', 10);
					K2Dispatcher.trigger('app:controller:setCollectionState', 'limit', 10);
				}
				K2Session.set('view.mode', mode);
				K2Dispatcher.trigger('app:controller:filter', 'page', 1);
			}, this);
			K2Dispatcher.on('app:pagination:show', _.bind(function() {
				this.$el.show();
			}, this));
			K2Dispatcher.on('app:pagination:hide', _.bind(function() {
				this.$el.hide();
			}, this));
		},

		modelEvents : {
			'change' : 'render'
		},

		events : {
			'change select[name="limit"]' : 'limit',
			'click [data-page]' : 'paginate'
		},

		onRender : function() {
			this.$el.removeClass('scroll-loading');
			this.$('select[name="limit"]').select2({
				minimumResultsForSearch : -1
			});
			jQuery(window).off('scroll');
			if (this.model.get('mode') == 'scroll') {
				var page = this.model.get('pagesCurrent');
				var total = this.model.get('pagesTotal');
				var container = this.$el;
				jQuery(window).scroll(function() {
					if (jQuery(window).scrollTop() + jQuery(window).height() == jQuery(document).height() && total > page) {
						container.addClass('scroll-loading');
						jQuery(window).off('scroll');
						K2Dispatcher.trigger('app:controller:filter', 'page', page + 1, 'merge');
					}
				});
			} else {
				this.$('select[name="limit"]').select2('val', this.model.get('limit'));
			}
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
				var newPage = currentPage - 1;
			} else {
				var newPage = page;
			}
			K2Dispatcher.trigger('app:controller:filter', 'page', newPage);
		}
	});

	return K2ViewPagination;
});
