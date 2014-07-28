define(['text!layouts/media/widget.html', 'text!layouts/media/add.html', 'text!layouts/media/preview.html', 'widgets/widget', 'dispatcher', 'widgets/sortable/jquery-sortable-min'], function(widgetTemplate, addTemplate, previewTemplate, K2Widget, K2Dispatcher) {'use strict';

	// Model
	var MediaModel = Backbone.Model.extend({
		initialize : function() {
			this.set('cid', this.cid);
		},
		idAttribute : '_id',
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
		getTemplate : function() {
			if (this.model.get('isNew')) {
				return _.template(addTemplate);
			} else {
				return _.template(previewTemplate);
			}
		},
		events : {
			'click [data-action="remove"]' : 'removeMedia'
		},
		modelEvents : {
			'change' : 'render'
		},
		initialize : function(options) {
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

			if (!this.model.get('isNew')) {
				this.$el.attr('data-role', 'sortable-media-row');
			}
		},
		onDomRefresh : function() {
			K2Widget.updateEvents(this.$el);
			this.$('select[name="provider"]').attr('name', 'media[' + this.model.get('cid') + '][provider]');
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
	var K2ViewMedia = Marionette.CollectionView.extend({
		initialize : function(options) {
			this.sortable = options.sortable;
		},
		childView : K2ViewMediaRow,
		onRender : function() {
			if (this.sortable) {
				this.$el.attr('data-role', 'sortable-media');
				this.$el.sortable({
					containerSelector : '[data-role="sortable-media"]',
					itemSelector : '[data-role="sortable-media-row"]',
					placeholder : '<div class="k2SortingPlaceholder"></div>',
					handle : '[data-role="ordering-handle"]'
				});
			}

		}
	});

	var K2ViewMediaWidget = Marionette.LayoutView.extend({
		template : _.template(widgetTemplate),
		regions : {
			newMediaRegion : '[data-region="new-media"]',
			existingMediaRegion : '[data-region="existing-media"]'
		},
		events : {
			'click [data-action="add"]' : 'addMedia'
		},
		initialize : function(options) {
			this.providers = options.providers;
			this.model = new Backbone.Model({
				enabled : options.enabled
			});
			if (options.enabled) {
				this.existingMediaCollection = new MediaCollection(options.data);
				this.existingMediaView = new K2ViewMedia({
					collection : this.existingMediaCollection,
					sortable : true
				});

				this.newMediaCollection = new MediaCollection();
				this.newMediaView = new K2ViewMedia({
					collection : this.newMediaCollection,
					sortable : false
				});
			}
		},
		onShow : function() {
			if (this.model.get('enabled')) {
				this.newMediaRegion.show(this.newMediaView);
				this.existingMediaRegion.show(this.existingMediaView);
			}
		},
		addMedia : function(event) {
			event.preventDefault();
			this.newMediaCollection.add({
				isNew : true,
				providers : this.providers
			});
		}
	});
	return K2ViewMediaWidget;
});
