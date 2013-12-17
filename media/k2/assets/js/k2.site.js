// Comments
jQuery(document).ready(function() {

	// Backbone.sync
	// -------------

	// Override of the default Backbone.sync implementation.
	// Enforces Backbone.emulateHTTP = true and Backbone.emulateJSON = true.
	// Copies any model attributes to the data object.

	Backbone.sync = function(method, model, options) {

		// Initialize the options object if it is not set
		options || ( options = {});
		if (options.data === undefined) {
			options.data = [];
		}

		// Detect the request type
		switch (method) {
			case 'create':
				var type = 'POST';
				break;
			case 'update':
				var type = 'PUT';
				break;
			case 'patch':
				var type = 'PATCH';
				break;
			case 'delete':
				var type = 'DELETE';
				break;
			case 'read':
				var type = 'GET';
				break;
		}

		// Request params
		var params = {
			type : (method === 'read') ? 'GET' : 'POST',
			dataType : 'json',
			contentType : 'application/x-www-form-urlencoded',
			url : _.result(model, 'url') || urlError()
		};

		// Convert any model attributes to data
		_.each(options.attrs, function(value, attribute) {
			options.data.push({
				name : 'states[' + attribute + ']',
				value : value
			});
		});

		// For create, update, patch and delete methods pass as aerguments the method and the session token.
		if (method !== 'read') {
			options.data.push({
				name : '_method',
				value : type
			});
			options.data.push({
				name : K2SessionToken,
				value : 1
			});
		}

		// Make the request, allowing the user to override any Ajax options
		var xhr = options.xhr = Backbone.ajax(_.extend(params, options));
		model.trigger('request', model, xhr, options);
		return xhr;

	};

	var comments = jQuery('#k2Comments');
	var K2CommentsItemId = comments.data('item-id');
	var K2CommentsSite = comments.data('site');
	if (K2CommentsItemId) {
		// Comments
		var K2Comments = new Backbone.Marionette.Application();

		K2Comments.addRegions({
			comments : '#k2Comments',
			pagination : '#k2CommentsPagination'
		});

		var K2ViewComment = Marionette.ItemView.extend({
			tagName : 'li',
			template : _.template(jQuery('#k2CommentTemplate').html()),
			events : {
				'click [data-action="publish"]' : 'publish',
				'click [data-action="delete"]' : 'destroy',
				'click [data-action="report"]' : 'report',
				'click [data-action="report.user"]' : 'reportUser',
			},
			publish : function(event) {
				event.preventDefault();
				this.model.save({state : 1}, {wait:true, patch:true});
			},
			destroy : function(event) {
				event.preventDefault();
				this.model.remove();
			},
			report : function(event) {
				event.preventDefault();
				console.info('report');
			},
			reportUser : function(event) {
				event.preventDefault();
				console.info('reportUser');
			}
		});

		var K2ViewComments = Marionette.CollectionView.extend({
			itemView : K2ViewComment,
			tagName : 'ul'
		});

		var K2ViewCommentsPagination = Marionette.ItemView.extend({
			initialize : function(options) {
				this.comments = options.comments;
			},
			template : _.template(jQuery('#k2CommentsPaginationTemplate').html()),
			events : {
				'click a' : 'paginate'
			},
			modelEvents : {
				'change' : 'render'
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
				var limitstart = (newPage * this.model.get('limit')) - this.model.get('limit');
				this.comments.states.set('limitstart', limitstart);
				this.comments.fetch();
			}
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

		K2Comments.addInitializer(function(options) {
			var collection = new K2CollectionComments();
			collection.states.set('itemId', K2CommentsItemId);
			collection.fetch({
				success : function() {
					var view = new K2ViewComments({
						collection : collection
					});
					var pagination = new K2ViewCommentsPagination({
						model : collection.pagination,
						comments : collection
					});
					K2Comments.comments.show(view);
					K2Comments.pagination.show(pagination);
				}
			});
		});
		K2Comments.start();
	}
});

