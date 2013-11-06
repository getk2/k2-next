define(['text!layouts/image/form.html', 'widgets/widget', 'dispatcher'], function(template, K2Widget, K2Dispatcher) {'use strict';

	// Model
	var ImageModel = Backbone.Model.extend({
		initialize : function() {
			this.set('cid', this.cid);
		},
		defaults : {
			id : null,
			tmpId : null,
			itemId : null,
			type : null,
			preview : null,
			caption : null,
			credits : null
		},
		urlRoot : 'index.php?option=com_k2&task=image.sync&format=json',
		url : function() {
			var base = _.result(this, 'urlRoot') || _.result(this.collection, 'url') || urlError();
			if (this.isNew())
				return base;
			return base + '&id=' + encodeURIComponent(this.get('id'));
		},
		sync : function(method, model, options) {
			// Convert any model attributes to data if options data is empty
			if (options.data === undefined) {
				options.data = [];
			}
			_.each(model.attributes, function(value, attribute) {
				options.data.push({
					name : attribute,
					value : value
				});
			});
			return Backbone.sync.apply(this, arguments);
		}
	});

	// Image view
	var K2ViewImage = Marionette.ItemView.extend({
		tagName : 'div',
		template : _.template(template),
		events : {
			'click #appRemoveImage' : 'removeImage',
			'input input[name="image[caption]"]' : 'updateCaption',
			'input input[name="image[credits]"]' : 'updateCredits'
		},
		modelEvents : {
			'change:preview' : 'render'
		},
		initialize : function(options) {

			this.model = new ImageModel(options.row.get('_image'));
			this.model.set('tmpId', options.row.get('tmpId'));
			this.model.set('itemId', options.row.get('id'));
			this.model.set('type', options.type);

			K2Dispatcher.on('image:select:' + this.model.cid, function(path) {
				this.setImageFromServer(path);
			}, this);

			K2Dispatcher.on('image:upload:' + this.model.cid, function(e, data) {
				this.model.set('id', data.result.id);
				this.model.set('preview', data.result.preview);
			}, this);

			this.on('delete', function() {
				this.model.destroy();
			});

		},
		onDomRefresh : function() {
			K2Widget.updateEvents(this.$el);
		},
		removeImage : function(event) {
			event.preventDefault();
			this.model.destroy();
			this.model.set('id', null);
			this.model.set('caption', '');
			this.model.set('credits', '');
			this.model.set('preview', '');

		},
		setImageFromServer : function(path) {
			var data = {};
			data['tmpId'] = this.model.get('tmpId');
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
				self.model.set('id', data.id);
				self.model.set('preview', data.preview);
			}).fail(function(xhr, status, error) {
				K2Dispatcher.trigger('app:message', 'error', xhr.responseText);
			});
		},
		updateCaption : function() {
			this.model.set('caption', this.$el.find('input[name="image[caption]"]').val());
		},
		updateCredits : function() {
			this.model.set('credits', this.$el.find('input[name="image[credits]"]').val());
		}
	});

	return K2ViewImage;
});
