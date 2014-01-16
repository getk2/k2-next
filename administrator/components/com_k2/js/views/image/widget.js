define(['text!layouts/image/form.html', 'widgets/widget', 'dispatcher'], function(template, K2Widget, K2Dispatcher) {'use strict';

	// Model
	var ImageModel = Backbone.Model.extend({
		initialize : function() {
			this.set('cid', this.cid);
		},
		defaults : {
			id : null,
			temp : null,
			itemId : null,
			type : null,
			flag : 0,
			src : null,
			alt : null,
			caption : null,
			credits : null,
			remove : 0
		},
		urlRoot : 'index.php?option=com_k2&task=images.sync&format=json',
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
			'change:src' : 'render',
			'change:categoryId' : 'render'
		},
		initialize : function(options) {

			this.model = new ImageModel(options.row.get('image'));
			this.model.set('itemId', options.row.get('id'));
			this.model.set('categoryId', options.row.get('catid'));
			this.model.set('type', options.type);

			K2Dispatcher.on('image:select:' + this.model.cid, function(path) {
				this.setImageFromServer(path);
			}, this);

			K2Dispatcher.on('image:upload:' + this.model.cid, function(e, data) {
				this.model.set('temp', data.result.temp);
				this.model.set('flag', 1);
				this.model.set('remove', 0);
				this.model.set('src', data.result.preview);
			}, this);

			this.on('cleanup', function() {
				if (this.model.get('temp')) {
					this.model.set('id', this.model.get('temp'));
					this.model.destroy();
				}
			});

		},
		onDomRefresh : function() {
			K2Widget.updateEvents(this.$el);
		},
		removeImage : function(event) {
			event.preventDefault();
			this.model.set('id', null);
			this.model.set('caption', '');
			this.model.set('credits', '');
			this.model.set('remove', 1);
			this.model.set('flag', 0);
			this.model.set('src', '');

		},
		setImageFromServer : function(path) {
			var data = {};
			data['itemId'] = this.model.get('itemId');
			data['type'] = this.model.get('type');
			data['categoryId'] = this.model.get('categoryId');
			data['path'] = path;
			data[K2SessionToken] = 1;
			var self = this;
			jQuery.ajax({
				dataType : 'json',
				type : 'POST',
				url : 'index.php?option=com_k2&task=images.upload&format=json',
				data : data
			}).done(function(data, status, xhr) {
				self.model.set('temp', data.temp);
				self.model.set('remove', 0);
				self.model.set('flag', 1);
				self.model.set('src', data.preview);
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
