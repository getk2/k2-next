jQuery(document).ready(function() {

	// Legacy code START
	// Generic function to get URL params passed in .js script include
	function getUrlParams(targetScript, varName) {
		var scripts = document.getElementsByTagName('script');
		var scriptCount = scripts.length;
		for (var a = 0; a < scriptCount; a++) {
			var scriptSrc = scripts[a].src;
			if (scriptSrc.indexOf(targetScript) >= 0) {
				varName = varName.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
				var re = new RegExp("[\\?&]" + varName + "=([^&#]*)");
				var parsedVariables = re.exec(scriptSrc);
				if (parsedVariables !== null) {
					return parsedVariables[1];
				}
			}
		}
	}

	// Maginific popup
	jQuery('.k2Modal').magnificPopup({type:'image'});

	// comments
	jQuery('#comment-form').submit(function(event) {
		event.preventDefault();
		var form = jQuery(this);
		var k2SitePath = getUrlParams('k2.js', 'sitepath');
		form.find('input[name="view"]').remove();
		form.find('input[name="task"]').remove();
		form.find('input[name="option"]').attr('name', '_method').val('POST');
		form.find('input[name="itemID"]').attr('name', 'itemId');
		form.find('textarea[name="commentText"]').attr('name', 'text');
		form.find('input[name="userName"]').attr('name', 'name');
		form.find('input[name="commentEmail"]').attr('name', 'email');
		form.find('input[name="commentURL"]').attr('name', 'url');
		jQuery('#formLog').empty().addClass('formLogLoading');
		jQuery.ajax({
			url : k2SitePath + 'index.php?option=com_k2&task=comments.sync&format=json&id=null',
			type : 'post',
			dataType : 'json',
			data : jQuery('#comment-form').serialize(),
			success : function(response) {
				jQuery('#formLog').removeClass('formLogLoading').html(response.message);
				if ( typeof (Recaptcha) != "undefined") {
					Recaptcha.reload();
				}
				if (response.status) {
					window.location.reload();
				}
			},
			error : function(response) {
				jQuery('#formLog').removeClass('formLogLoading').html(response.responseText);
			}
		});
	});
	// Text Resizer
	jQuery('#fontDecrease').click(function(event) {
		event.preventDefault();
		jQuery('.itemFullText').removeClass('largerFontSize');
		jQuery('.itemFullText').addClass('smallerFontSize');
	});
	jQuery('#fontIncrease').click(function(event) {
		event.preventDefault();
		jQuery('.itemFullText').removeClass('smallerFontSize');
		jQuery('.itemFullText').addClass('largerFontSize');
	});

	// Smooth Scroll
	jQuery('.k2Anchor').click(function(event) {
		event.preventDefault();
		var target = this.hash;
		jQuery('html, body').stop().animate({
			scrollTop : jQuery(target).offset().top
		}, 500);
	});
	// Legacy code END

	// K2 toolbar
	var isK2ModalOpen = false;
	jQuery('[data-role="k2-admin-link"], .itemEditLink a, .catItemAddLink a').click(function(event) {
		event.preventDefault();
		if (!isK2ModalOpen) {
			var src = jQuery(this).attr('href');
			var vw = jQuery(window).width();
			var vh = jQuery(window).height();
			var modal = jQuery('<div id="k2AdminModalContainer"><div id="k2AdminModal"><a href="#">&times;</a><iframe src="' + src + '" style="width:'+(vw-80)+'px;height:'+(vh-160)+'px;"></iframe></div></div>');
			modal.find('a').click(function(event) {
				event.preventDefault();
				modal.remove();
				isK2ModalOpen = false;
			});
			modal.appendTo('body');
			isK2ModalOpen = true;
		}
	});

	// Popup
	jQuery('.k2ClassicPopUp').click(function(event) {
		event.preventDefault();
		var href = jQuery(this).attr('href');
		var width = parseInt(jQuery(this).data('width'));
		var height = parseInt(jQuery(this).data('height'));
		if (!width) {
			width = 900;
		}
		if (!height) {
			height = 600;
		}
		window.open(href, 'K2PopUpWindow', 'width=' + width + ',height=' + height + ',menubar=yes,resizable=yes');
	});

	// Calendar navigation
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

	// AJAX search
	jQuery('div.k2LiveSearchBlock form input[name=searchword]').keyup(function(event) {
		var parentElement = jQuery(this).parent().parent();
		if (jQuery(this).val().length > 3 && event.key != 'enter') {
			jQuery(this).addClass('k2SearchLoading');
			parentElement.find('.k2LiveSearchResults').css('display', 'none').empty();
			parentElement.find('input[name=t]').val(jQuery.now());
			parentElement.find('input[name=format]').val('json');
			var url = K2Site + '/index.php?option=com_k2&view=itemlist&task=search&' + parentElement.find('form').serialize();
			parentElement.find('input[name=format]').val('html');
			jQuery.getJSON(url).done(function(data) {
				var template = jQuery('#k2LiveSearchTemplate');
				var site = template.data('site');
				_.each(data.items, function(item) {
					item.link = item.link.replace(site + '/', '');
				});
				var template = _.template(template.html());
				var compiled = template(data);
				parentElement.find('.k2LiveSearchResults').html(compiled);
				parentElement.find('input[name=searchword]').removeClass('k2SearchLoading');
				parentElement.find('.k2LiveSearchResults').css('display', 'block');
			});
		} else {
			parentElement.find('.k2LiveSearchResults').css('display', 'none').empty();
		}
	});

	// Inline editing. Don't use Backbone, we can do it with a few lines of js
	var elements = jQuery('[data-k2-editable]');
	elements.prop('contenteditable', true);
	if (elements.length > 0) {
		CKEDITOR.disableAutoInline = true;
		CKEDITOR.config.allowedContent = true;
	}
	elements.each(function() {
		if (jQuery(this).data('k2-editable') != 'title') {
			CKEDITOR.inline(this);
		}
	});
	jQuery('[data-k2-editable]').blur(function(event) {
		var el = jQuery(this);
		var property = el.data('k2-editable');
		var id = el.data('k2-item');
		var data = {};
		data['id'] = id;
		data['_method'] = 'PATCH';
		data['states[' + property + ']'] = el.html();
		data[K2SessionToken] = 1;
		jQuery.post(K2SitePath + '/index.php?option=com_k2&task=items.sync&format=json', data).done(function() {
			// @TODO : Inform user that save was succesful
		}).fail(function(response) {
			alert(response.responseText);
		});
	});

	// Comments application
	var K2CommentsWidget = jQuery('div[data-widget="k2comments"]');
	var K2CommentsItemId = K2CommentsWidget.data('itemid');
	if (K2CommentsItemId) {

		// Comments application
		var K2Comments = new Marionette.Application();

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
				return K2SitePath + '/index.php?option=com_k2&task=comments.sync&format=json';
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
				return K2SitePath + '/index.php?option=com_k2&task=comments.sync&format=json' + this.buildQuery();
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
				'click [data-action="delete"]' : '_destroy',
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
			onDomRefresh : function() {
				if ( typeof (K2ShowRecaptcha) === 'function') {
					K2ShowRecaptcha();
				}
			},
			create : function(event) {
				event.preventDefault();
				var model = new K2ModelComments;
				var input = this.$('form[data-form="comments"]').serializeArray();
				model.save(null, {
					data : input,
					success : _.bind(function(model) {
						this.controller.comment(model.get('id'));
						this.scrollTo('div[data-widget="k2comments"]');
					}, this),
					error : _.bind(function(model, xhr, options) {
						this.message(xhr.responseText);
						if ( typeof (Recaptcha) !== 'undefined') {
							Recaptcha.reload();
						}
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
			_destroy : function(event) {
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
				jQuery.post(form.attr('action'), form.serializeArray()).success(function(data) {
					form.hide();
				}).error(function(data) {
					form.find('[data-role="log"]').text(data.responseText);
				});
			},
			reportUser : function(event) {
				event.preventDefault();
				var el = jQuery(event.currentTarget);
				var data = 'id=' + el.data('id') + '&' + K2SessionToken + '=1';
				jQuery.post(K2SitePath + '/index.php?option=com_k2&task=users.report&format=json', data).success(function(data) {
					el.hide();
				}).error(function(data) {
					el.parent().append('<span>' + data.responseText + '</span>');
				});
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
				this.$('form[data-form="comments"] [data-role="log"]').html(message);
			},
			scrollTo : function(selector) {
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
				'itemCommentsAnchor' : 'list',
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
		K2Comments.on('start', function() {
			Backbone.history.start();
		});
		K2Comments.start();
	}
});

