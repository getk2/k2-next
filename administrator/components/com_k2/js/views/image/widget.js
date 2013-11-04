define(['text!layouts/image/form.html', 'widgets/widget', 'dispatcher'], function(template, K2Widget, K2Dispatcher) {'use strict';

	// Model
	var ImageModel = Backbone.Model.extend({
		defaults : {
			itemId : null,
			type : null,
			upload : null,
			image_flag : null,
			image_caption : null,
			imagePreview : null,
			image_credits : null
		},
		urlRoot : 'index.php?option=com_k2&task=image.sync&format=json',
		url : function() {
			var base = _.result(this, 'urlRoot') || _.result(this.collection, 'url') || urlError();
			if (this.isNew())
				return base;
			return base + '&type=' + encodeURIComponent(this.get('type')) + '&itemId=' + encodeURIComponent(this.get('itemId')) + '&upload=' + encodeURIComponent(this.get('upload'));
		}
	});

	// Image view
	var K2ViewImage = Marionette.ItemView.extend({
		tagName : 'div',
		template : _.template(template),
		events : {
			'click #appRemoveImage' : 'removeImage'
		},
		modelEvents : {
			'change' : 'render'
		},
		initialize : function(options) {
			this.model = new ImageModel(options.data.attributes);
			this.model.set('itemId', options.itemId);
			this.model.set('type', options.type);
			K2Dispatcher.on('image:select', function(path) {
				this.setImageFromServer(path);
			}, this);
			K2Dispatcher.on('image:upload', function(e, data) {
				this.model.set('imagePreview', data.result.preview);
				this.model.set('upload', data.result.upload);
				this.model.set('image_flag', 1);
			}, this);
			K2Dispatcher.on('image:delete', function() {
				this.model.destroy();
			}, this);
		},
		onDomRefresh : function() {
			K2Widget.updateEvents(this.$el);
		},
		removeImage : function(event) {
			event.preventDefault();
			this.model.set('image_flag', 0);
			this.model.destroy();
		},
		setImageFromServer : function(path) {
			var data = {};
			data['itemId'] = this.model.get('itemId');
			data['type'] = this.model.get('type');
			data['path'] = path;
			data[K2SessionToken] = 1;
			var self = this;
			jQuery.ajax({
				dataType : 'json',
				type : 'POST',
				url : 'index.php?option=com_k2&task=image.upload&format=json',
				data : data
			}).done(function(data, status, xhr) {
				self.model.set('imagePreview', data.preview);
				self.model.set('upload', data.upload);
				self.model.set('image_flag', 1);
			}).fail(function(xhr, status, error) {
				K2Dispatcher.trigger('app:message', 'error', xhr.responseText);
			});
		},
	});

	return K2ViewImage;
});
