define(['text!layouts/galleries/list.html', 'text!layouts/galleries/row.html', 'widgets/widget', 'dispatcher'], function(listTemplate, rowTemplate, K2Widget, K2Dispatcher) {'use strict';

	// Model
	var Gallery = Backbone.Model.extend({
		initialize : function() {
			this.set('cid', this.cid);
		},
		idAttribute : 'upload',
		defaults : {
			itemId : null,
			cid : null,
			upload : null,
			url : null
		},
		urlRoot : 'index.php?option=com_k2&task=galleries.sync&format=json',
		url : function() {
			var base = _.result(this, 'urlRoot') || _.result(this.collection, 'url') || urlError();
			if (this.isNew())
				return base;
			return base + '&upload=' + encodeURIComponent(this.get('upload')) + '&itemId=' + encodeURIComponent(this.get('itemId'));
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
		},
		onDomRefresh : function() {
			K2Widget.updateEvents(this.$el);
		},
		removeGallery : function(event) {
			event.preventDefault();
			this.model.destroy({
				wait : true
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
			this.itemId = options.itemId;
			this.collection = new Galleries(options.data);
			_.each(this.collection.models, function(model) {
				model.set('itemId', options.itemId);
			});

			this.on('delete', function() {
				_.each(this.collection.models, function(model) {
					model.destroy({
						wait : true
					});
				});
			});
		},
		addGallery : function(event) {
			event.preventDefault();
			this.collection.add({
				itemId : this.itemId
			});
		}
	});
	return K2ViewGalleries;
});
