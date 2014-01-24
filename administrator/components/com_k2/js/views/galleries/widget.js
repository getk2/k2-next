define(['text!layouts/galleries/list.html', 'text!layouts/galleries/row.html', 'widgets/widget', 'dispatcher'], function(listTemplate, rowTemplate, K2Widget, K2Dispatcher) {'use strict';

	// Model
	var Gallery = Backbone.Model.extend({
		initialize : function() {
			this.set('cid', this.cid);
		},
		defaults : {
			cid : null,
			upload : null,
			url : null,
			remove : 0
		}
	});

	// Collection
	var Galleries = Backbone.Collection.extend({
		model : Gallery
	});

	// Row view
	var K2ViewGalleriesRow = Marionette.ItemView.extend({
		tagName : 'div',
		template : _.template(rowTemplate),
		events : {
			'click .appRemoveGallery' : 'removeGallery'
		},
		modelEvents : {
			'change' : 'render'
		},
		initialize : function() {
			K2Dispatcher.on('galleries:upload:' + this.model.cid, function(e, data) {
				this.model.set('upload', data.result);
				this.model.set('url', '');
			}, this);
			K2Dispatcher.on('galleries:dropbox:' + this.model.cid, function(url) {
				this.setGalleryFromDropBox(url);
			}, this);
		},
		onDomRefresh : function() {
			K2Widget.updateEvents(this.$el);
		},
		removeGallery : function(event) {
			event.preventDefault();
			this.model.set('remove', 1);
		},
		setGalleryFromDropBox : function(url) {
			var data = {};
			data['url'] = url;
			data['upload'] = this.model.get('upload');
			data[K2SessionToken] = 1;
			var self = this;
			jQuery.ajax({
				dataType : 'json',
				type : 'POST',
				url : 'index.php?option=com_k2&task=galleries.upload&format=json',
				data : data
			}).done(function(data, status, xhr) {
				self.model.set('upload', data);
				self.model.set('path', '');
			}).fail(function(xhr, status, error) {
				K2Dispatcher.trigger('app:message', 'error', xhr.responseText);
			});
		}
	});

	// List view
	var K2ViewGalleries = Marionette.CompositeView.extend({
		template : _.template(listTemplate),
		itemViewContainer : '#appGalleries',
		itemView : K2ViewGalleriesRow,
		events : {
			'click #appAddGallery' : 'addGallery'
		},
		initialize : function(options) {
			this.collection = new Galleries(options.data);
		},
		addGallery : function(event) {
			event.preventDefault();
			this.collection.add({});
		}
	});
	return K2ViewGalleries;
});
