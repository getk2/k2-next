define(['text!layouts/media/list.html', 'text!layouts/media/row.html', 'widgets/widget', 'dispatcher'], function(listTemplate, rowTemplate, K2Widget, K2Dispatcher) {'use strict';

	// Model
	var MediaModel = Backbone.Model.extend({
		initialize : function() {
			this.set('cid', this.cid);
		},
		idAttribute: '_id',
		defaults : {
			itemId : null,
			cid : null,
			upload : null,
			url : null,
			provider : null,
			id : null,
			embed : null,
			caption : null,
			credits : null,
			remove : 0
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
			'click [data-action="remove"]' : 'removeMedia'
		},
		modelEvents : {
			'change' : 'render'
		},
		initialize : function() {
			K2Dispatcher.on('media:select:' + this.model.cid, function(url) {
				this.model.set('url', url);
				this.model.set('upload', '');
			}, this);
			K2Dispatcher.on('media:dropbox:' + this.model.cid, function(url) {
				this.setMediaFromDropBox(url);
			}, this);
			K2Dispatcher.on('media:upload:' + this.model.cid, function(e, data) {
				this.model.set('upload', data.result);
				this.model.set('url', '');
			}, this);
		},
		onDomRefresh : function() {
			K2Widget.updateEvents(this.$el);
		},
		removeMedia : function(event) {
			event.preventDefault();
			this.model.set('remove', 1);
		},
		setMediaFromDropBox : function(url) {
			var data = {};
			data['url'] = url;
			data['upload'] = this.model.get('upload');
			data[K2SessionToken] = 1;
			var self = this;
			jQuery.ajax({
				dataType : 'json',
				type : 'POST',
				url : 'index.php?option=com_k2&task=media.upload&format=json',
				data : data
			}).done(function(data, status, xhr) {
				self.model.set('upload', data);
				self.model.set('url', '');
			}).fail(function(xhr, status, error) {
				K2Dispatcher.trigger('app:messages:add', 'error', xhr.responseText);
			});
		}
	});

	// List view
	var K2ViewMedia = Marionette.CompositeView.extend({
		template : _.template(listTemplate),
		itemViewContainer : '[data-region="media"]',
		itemView : K2ViewMediaRow,
		events : {
			'click [data-action="add"]' : 'addMedia'
		},
		initialize : function(options) {
			this.collection = new MediaCollection(options.data);
		},
		addMedia : function(event) {
			event.preventDefault();
			this.collection.add({});
		}
	});
	return K2ViewMedia;
});
