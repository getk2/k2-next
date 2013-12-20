jQuery(document).ready(function() {

	jQuery('.k2CalendarBlock').on('click', '.calendarNavLink', function(event) {
		event.preventDefault();
		var parentElement = jQuery(this).parent().parent().parent().parent();
		var url = jQuery(this).attr('href');
		parentElement.empty().addClass('k2CalendarLoader');
		jQuery.ajax({
			url : url,
			type : 'get',
			success : function(response) {
				parentElement.html(response);
				parentElement.removeClass('k2CalendarLoader');
			}
		});
	});

	jQuery('div.k2LiveSearchBlock form input[name=searchword]').keyup(function(event) {
		var parentElement = jQuery(this).parent().parent();
		if (jQuery(this).val().length > 3 && event.key != 'enter') {
			jQuery(this).addClass('k2SearchLoading');
			parentElement.find('.k2LiveSearchResults').css('display', 'none').empty();
			parentElement.find('input[name=t]').val(jQuery.now());
			parentElement.find('input[name=format]').val('raw');
			var url = 'index.php?option=com_k2&view=itemlist&task=search&' + parentElement.find('form').serialize();
			parentElement.find('input[name=format]').val('html');
			jQuery.ajax({
				url : url,
				type : 'get',
				success : function(response) {
					parentElement.find('.k2LiveSearchResults').html(response);
					parentElement.find('input[name=searchword]').removeClass('k2SearchLoading');
					parentElement.find('.k2LiveSearchResults').css('display', 'block');
				}
			});
		} else {
			parentElement.find('.k2LiveSearchResults').css('display', 'none').empty();
		}
	});

	var K2CommentsWidget = jQuery('div[data-widget="k2comments"]');
	var K2CommentsItemId = K2CommentsWidget.data('itemid');
	var K2CommentsSite = K2CommentsWidget.data('site');
	if (K2CommentsItemId) {

		// Comments application
		var K2Comments = new Backbone.Marionette.Application();

		// Main region of the application
		K2Comments.addRegions({
			main : 'div[data-widget="k2comments"]'
		});

		var K2ModelComments = Backbone.Model.extend({
			defaults : {
				id : null,
				itemId : null,
				userId : null,
				name : null,
				date : null,
				email : null,
				url : null,
				ip : null,
				text : null,
				state : null
			},

			url : function() {
				var base = _.result(this, 'urlRoot') || _.result(this.collection, 'url') || urlError();
				base += '&id=' + this.id;
				return base;
			},

			urlRoot : function() {
				return K2CommentsSite + '/index.php?option=com_k2&task=comments.sync&format=json';
			},
			parse : function(resp, options) {
				// If response is null then return. This is the case for POST requests
				if (resp === null) {
					return resp;
				}

				// If the response object does not contain a row object then probably it's a flat model and we need to return it.
				if (resp.row === undefined) {
					return resp;
				}

				// Return the row
				return resp.row;

			}
		});

		var K2CollectionComments = Backbone.Collection.extend({
			model : K2ModelComments,
			initialize : function() {
				this.states = new Backbone.Model({
					limitstart : 0
				});
				this.pagination = new Backbone.Model();
			},
			url : function() {
				return K2CommentsSite + '/index.php?option=com_k2&task=comments.sync&format=json' + this.buildQuery();
			},
			parse : function(resp) {
				this.pagination.set(resp.pagination);
				return resp.rows;
			},

			buildQuery : function() {
				var query = '';
				_.each(this.states.attributes, function(value, state) {
					query += '&' + state + '=' + value;
				});
				return query;
			}
		});

		// K2 comments view defnition
		var K2ViewComments = Marionette.ItemView.extend({
			template : _.template(jQuery('#k2CommentsTemplate').html()),
			collectionEvents : {
				'reset' : 'render'
			},
			events : {
				'click [data-action="create"]' : 'create',
				'click [data-action="publish"]' : 'publish',
				'click [data-action="delete"]' : 'destroy',
				'click [data-action="report"]' : 'report',
				'click [data-action="report.send"]' : 'sendReport',
				'click [data-action="report.user"]' : 'reportUser',
				'click [data-role="pagination"] a' : 'paginate',
			},
			initialize : function(options) {
				this.controller = options.controller;
			},
			onRender : function() {
				this.$('form[data-form="report"]').hide();
				this.$('img[data-image-url]').each(function() {
					var src = jQuery(this).data('image-url');
					jQuery(this).attr('src', src);
				});
				this.$('a[data-user-link]').each(function() {
					var href = jQuery(this).data('user-link');
					jQuery(this).attr('href', href);
				});
			},
			create : function(event) {
				event.preventDefault();
				var model = new K2ModelComments;
				var input = this.$('form[data-form="comment"]').serializeArray();
				model.save(null, {
					data : input,
					success : _.bind(function(model) {
						this.controller.comment(model.get('id'));
						this.scrollTo('[data-widget="k2comments"]');
					}, this),
					error : _.bind(function(model, xhr, options) {
						this.message(xhr.responseText);
					}, this)
				});
			},
			publish : function(event) {
				event.preventDefault();
				var el = jQuery(event.currentTarget);
				var id = el.data('id');
				var model = this.collection.get(id);
				model.save({
					state : 1
				}, {
					wait : true,
					patch : true,
					success : _.bind(function(model) {
						model.set('state', 1);
						this.render();
					}, this),
					error : _.bind(function(model, xhr, options) {
						this.message(xhr.responseText);
					}, this)
				});

			},
			destroy : function(event) {
				event.preventDefault();
				var el = jQuery(event.currentTarget);
				var id = el.data('id');
				var model = this.collection.get(id);
				model.destroy({
					success : _.bind(function() {
						this.collection.remove(model);
						if (this.collection.models.length == 0) {
							var limitstart = this.collection.pagination.get('limitstart') - this.collection.pagination.get('limit');
							if (limitstart < 0) {
								limitstart = 0;
							}
							this.collection.states.set('limitstart', limitstart);
						}
						this.controller.list();
					}, this),
					error : _.bind(function(model, xhr, options) {
						this.message(xhr.responseText);
					}, this)
				});
			},
			report : function(event) {
				event.preventDefault();
				var el = jQuery(event.currentTarget);
				var id = el.data('id');
				this.$('form[data-form="report"] input[name="id"]').val(id);
				this.$('form[data-form="report"]').show();
				this.scrollTo('form[data-form="report"]');
			},
			sendReport : function(event) {
				event.preventDefault();
				var form = this.$('form[data-form="report"]');
				jQuery.post( form.attr('action'), form.serializeArray())
				.success(function(data) {
					form.hide();
				}).error(function(data) {
					form.find('[data-role="log"]').text(data.responseText);
				});
			},
			reportUser : function(event) {
				event.preventDefault();
				console.info('reportUser');
			},
			paginate : function(event) {
				event.preventDefault();
				var el = jQuery(event.currentTarget);
				var page = el.data('page');
				var currentPage = this.collection.pagination.get('pagesCurrent');
				if (page === 'next') {
					var newPage = currentPage + 1;
				} else if (page === 'previous') {
					var newPage = currentPage - 1;
				} else {
					var newPage = page;
				}
				var limitstart = (newPage * this.collection.pagination.get('limit')) - this.collection.pagination.get('limit');
				this.collection.states.set('limitstart', limitstart);
				this.collection.states.set('id', 0);
				this.controller.list();
			},
			message : function(message) {
				this.$('form[data-form="comment"] [data-role="log"]').html(message);
			},
			scrollTo : function(selector)
			{
				var html = jQuery('html');
				var element = jQuery(selector);
				html.scrollTop(element.offset().top);
			},
			serializeData : function() {
				var data = {
					'comments' : this.collection.toJSON(),
					'pagination' : this.collection.pagination.toJSON()
				};
				return data;
			}
		});

		var K2ControllerComments = Marionette.Controller.extend({

			initialize : function() {
				this.collection = new K2CollectionComments();
				this.collection.states.set('itemId', K2CommentsItemId);
			},

			list : function() {
				this.collection.fetch({
					success : _.bind(function() {
						this.view = new K2ViewComments({
							collection : this.collection,
							controller : this
						});
						K2Comments.main.show(this.view);
					}, this)
				});
			},
			comment : function(id) {
				
				if (!this.collection.get(id)) {
					this.collection.states.set('id', id);
					this.list();
				}
			}
		});

		var K2RouterComments = Marionette.AppRouter.extend({
			appRoutes : {
				'' : 'list',
				'comment:id' : 'comment',
			}
		});

		K2Comments.addInitializer(function(options) {
			this.controller = new K2ControllerComments();
			this.router = new K2RouterComments({
				controller : this.controller
			});
		});

		// On after initialize
		K2Comments.on('initialize:after', function() {
			Backbone.history.start();
		});
		K2Comments.start();
	}
});

