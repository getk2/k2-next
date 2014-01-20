define(['text!layouts/galleries/list.html', 'text!layouts/galleries/row.html', 'widgets/widget', 'dispatcher'], function(listTemplate, rowTemplate, K2Widget, K2Dispatcher) {'use strict';

	// Model
	var Gallery = Backbone.Model.extend({
		initialize : function() {
			this.set('cid', this.cid);
		},
		defaults : {
			cid : null,
			upload : null,
			url : null
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
			this.model.destroy();
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
