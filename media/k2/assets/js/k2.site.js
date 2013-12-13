// Comments
jQuery(document).ready(function() {
	var comments = jQuery('#k2Comments');
	var K2CommentsItemId = comments.data('item-id');
	var K2CommentsSite = comments.data('site');
	if (K2CommentsItemId) {
		// Comments
		var K2Comments = new Backbone.Marionette.Application();

		K2Comments.addRegions({
			main : '#k2Comments'
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
				this.model.set('state', 1);
				this.model.save();
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
			itemView : K2ViewComment
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

			urlRoot : function() {
				return K2CommentsSite + '/index.php?option=com_k2&task=comments.sync&format=json&itemId=' + K2CommentsItemId;
			},
		});

		var K2CollectionComments = Backbone.Collection.extend({
			url : function() {
				return K2CommentsSite + '/index.php?option=com_k2&task=comments.read&format=json&itemId=' + K2CommentsItemId;
			}
		});

		K2Comments.addInitializer(function(options) {
			var collection = new K2CollectionComments();
			var view = new K2ViewComments({
				collection : collection
			});
			K2Comments.main.show(view);
			collection.fetch();
		});
		K2Comments.start();
	}
});

