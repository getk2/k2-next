define(['text!layouts/media/list.html', 'text!layouts/media/row.html', 'widgets/widget', 'dispatcher'], function(listTemplate, rowTemplate, K2Widget, K2Dispatcher) {'use strict';

	// Model
	var MediaModel = Backbone.Model.extend({
		initialize : function() {
			this.set('cid', this.cid);
		},
		idAttribute : 'upload',
		defaults : {
			itemId : null,
			cid : null,
			upload : null,
			url : null,
			provider : null,
			id : null,
			embed : null,
			caption : null,
			credits : null
		},
		urlRoot : 'index.php?option=com_k2&task=media.sync&format=json',
		url : function() {
			var base = _.result(this, 'urlRoot') || _.result(this.collection, 'url') || urlError();
			if (this.isNew())
				return base;
			return base + '&upload=' + encodeURIComponent(this.get('upload')) + '&itemId=' + encodeURIComponent(this.get('itemId'));
		}
	});

	// Collection
	var MediaCollection = Backbone.Collection.extend({
		model : MediaModel
	});

	// Row view
	var K2ViewMediaRow = Marionette.ItemView.extend({
		tagName : 'div',
		template : _.template(rowTemplate),
		events : {
			'click .appRemoveMedia' : 'removeMedia'
		},
		modelEvents : {
			'change' : 'render'
		},
		initialize : function() {
			K2Dispatcher.on('media:select:' + this.model.cid, function(url) {
				this.model.set('url', url);
				this.model.set('file', '');
			}, this);
			K2Dispatcher.on('media:upload:' + this.model.cid, function(e, data) {
				this.model.set('upload', data.result.upload);
				this.model.set('url', '');
			}, this);
		},
		onDomRefresh : function() {
			K2Widget.updateEvents(this.$el);
		},
		removeMedia : function(event) {
			event.preventDefault();
			this.model.destroy();
		}
	});

	// List view
	var K2ViewMedia = Marionette.CompositeView.extend({
		template : _.template(listTemplate),
		itemViewContainer : '#appMedia',
		itemView : K2ViewMediaRow,
		events : {
			'click #appAddMedia' : 'addMedia'
		},
		initialize : function(options) {
			this.itemId = options.itemId;
			this.collection = new MediaCollection(options.data);
			_.each(this.collection.models, function(model) {
				model.set('itemId', options.itemId);
			});

			K2Dispatcher.on('media:delete', function() {
				_.each(this.collection.models, function(model) {
					model.destroy();
				});
			}, this);
		},
		addMedia : function(event) {
			event.preventDefault();
			this.collection.add({
				itemId : this.itemId
			});
		}
	});
	return K2ViewMedia;
});
