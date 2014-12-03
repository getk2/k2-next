define(['text!templates/galleries/widget.html', 'text!templates/galleries/add.html', 'text!templates/galleries/preview.html', 'widget', 'dispatcher', 'sortable', 'magnific', 'css!magnificStyle'], function(widgetTemplate, addTemplate, previewTemplate, K2Widget, K2Dispatcher) {'use strict';

	// Model
	var Gallery = Backbone.Model.extend({
		initialize : function() {
			this.set('cid', this.cid);
		},
		defaults : {
			cid : null,
			upload : null,
			url : null,
			remove : 0,
			itemId : null
		}
	});

	// Collection
	var Galleries = Backbone.Collection.extend({
		model : Gallery
	});

	// Row view
	var K2ViewGalleriesRow = Marionette.ItemView.extend({
		tagName : 'div',
		getTemplate : function() {
			if (this.model.get('isNew')) {
				return _.template(addTemplate);
			} else {
				return _.template(previewTemplate);
			}
		},
		events : {
			'click [data-action="remove"]' : 'removeGallery',
			'click [data-action="sig"]' : 'openSig'
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
			if (!this.model.get('isNew')) {
				this.$el.attr('data-role', 'sortable-galleries-row');
			} else {
				this.model.set('folder', this.model.get('itemId') + this.model.get('cid'));
			}
		},
		onDomRefresh : function() {
			K2Widget.updateEvents(this.$el);
		},
		removeGallery : function(event) {
			event.preventDefault();
			this.model.set('remove', 1);
		},
		openSig : function(event) {
			event.preventDefault();
			var src = jQuery(event.currentTarget).attr('href');
			jQuery.magnificPopup.open({
				items : {
					src : src
				},
				type : 'iframe'
			});
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
				K2Dispatcher.trigger('app:messages:add', 'error', xhr.responseText);
			});
		}
	});

	// List view
	var K2ViewGalleries = Marionette.CollectionView.extend({
		childView : K2ViewGalleriesRow,
		className : 'k2SortableGalleries',
		onRender : function() {
			this.$el.attr('data-role', 'sortable-galleries');
			this.$el.sortable({
				containerSelector : '[data-role="sortable-galleries"]',
				itemSelector : '[data-role="sortable-galleries-row"]',
				placeholder : '<div class="k2SortingPlaceholder"></div>',
				handle : '[data-role="ordering-handle"]'
			});
		}
	});

	// Layout view
	var K2ViewGalleriesWidget = Marionette.LayoutView.extend({
		template : _.template(widgetTemplate),
		regions : {
			newGalleriesRegion : '[data-region="new-galleries"]',
			existingGalleriesRegion : '[data-region="existing-galleries"]'
		},
		events : {
			'click [data-action="add"]' : 'addGallery'
		},
		initialize : function(options) {
			this.itemId = options.itemId;
			this.model = new Backbone.Model({
				enabled : options.enabled,
			});
			if (options.enabled) {
				this.existingGalleriesCollection = new Galleries(options.data);
				this.existingGalleriesView = new K2ViewGalleries({
					collection : this.existingGalleriesCollection
				});

				this.newGalleriesCollection = new Galleries();
				this.newGalleriesView = new K2ViewGalleries({
					collection : this.newGalleriesCollection
				});
			}
		},
		onShow : function() {
			if (this.model.get('enabled')) {
				this.existingGalleriesRegion.show(this.existingGalleriesView);
				this.newGalleriesRegion.show(this.newGalleriesView);
			}
		},
		addGallery : function(event) {
			event.preventDefault();
			this.newGalleriesCollection.add({
				isNew : true,
				itemId : this.itemId
			});
		}
	});

	return K2ViewGalleriesWidget;
});
