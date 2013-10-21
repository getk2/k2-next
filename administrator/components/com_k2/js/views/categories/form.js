define(['marionette', 'text!layouts/categories/form.html', 'dispatcher'], function(Marionette, template, K2Dispatcher) {'use strict';

	// K2 category form view
	var K2ViewCategory = Marionette.ItemView.extend({

		// Template
		template : _.template(template),

		// Model events
		modelEvents : {
			'change' : 'render'
		},

		// UI events
		events : {
			'click #appCategoryImageRemove' : 'removeImage',
			'click #appCategoryImageBrowseServer' : 'browseServerForImage'
		},

		// Initialize
		initialize : function() {

			// Add a listener for the before save event
			K2Dispatcher.on('app:controller:beforeSave', function() {
				this.onBeforeSave();
			}, this);

			// Add a model for image
			this.image = new Backbone.Model();

			// Add a listener for change event
			this.image.on('change', _.bind(function() {
				this.setImagePreview();
			}, this));

			// Add a listener selecting an image from the media manager
			K2Dispatcher.on('app:category:selectImage', function(path) {
				this.setImageFromServer(path);
			}, this);

		},

		// Serialize data for view
		serializeData : function() {
			var data = {
				'row' : this.model.toJSON(),
				'form' : this.model.getForm().toJSON()
			};
			return data;
		},

		// onRender event
		onRender : function() {
			// Update image properties from model properties
			this.image.set({
				value : this.model.get('image'),
				previewURL : this.model.get('imagePreview')
			});
		},

		// OnBeforeSave event
		onBeforeSave : function() {

			// Update form from editor contents
			K2Editor.save('description');
		},

		// OnBeforeClose event ( Marionette.js build in event )
		onBeforeClose : function() {

			// Is it new?
			if (this.model.isNew()) {
				// Delete any uploaded images
				if (this.image.get('value')) {
					this.removeImage();
				}
			}
		},

		// OnDomRefresh event ( Marionette.js build in event )
		onDomRefresh : function() {

			// Initialize the editor
			K2Editor.init();

			// Auto complete fields
			require(['widgets/select2/select2', 'css!widgets/select2/select2.css'], _.bind(function() {

				// Author
				var authorField = this.$el.find('#appCategoryAuthor');
				var authorId = authorField.val();
				var authorName = this.model.get('authorName');
				authorField.select2({
					minimumInputLength : 1,
					width : '300px',
					placeholder : l('K2_SELECT_AUTHOR'),
					initSelection : function(element, callback) {
						if (authorId) {
							var data = {
								id : authorId,
								text : authorName
							};
							callback(data);
						}
					},
					ajax : {
						url : 'index.php?option=com_k2&task=users.search&format=json',
						dataType : 'json',
						quietMillis : 100,
						data : function(term, page) {
							return {
								search : term,
								sorting : 'name',
								limit : 50,
								page : page,
							};
						},
						results : function(data, page) {
							var users = [];
							jQuery.each(data.rows, function(index, row) {
								var tag = {}
								users.push({
									id : row.id,
									text : row.name
								});
							});
							var more = (page * 50) < data.total;
							return {
								results : users,
								more : more
							};
						}
					},
				});

			}, this));

			// Date fields
			require(['widgets/pickadate/picker', 'widgets/pickadate/picker.date', 'widgets/pickadate/picker.time', 'css!widgets/pickadate/themes/default.css', 'css!widgets/pickadate/themes/default.date.css', 'css!widgets/pickadate/themes/default.time.css'], _.bind(function() {
				this.$el.find('.appDatePicker').pickadate({
					format : 'yyyy-mm-dd'
				});
				this.$el.find('.appTimePicker').pickatime({
					format : 'HH:i'
				});
			}, this));

			// Image uploader
			require(['widgets/uploader/jquery.iframe-transport', 'widgets/uploader/jquery.fileupload'], _.bind(function() {
				var self = this;
				var formData = {};
				formData['id'] = self.model.get('id');
				formData['tmpId'] = self.model.get('tmpId');
				formData[K2SessionToken] = 1;
				self.$el.find('#appCategoryImageFile').fileupload({
					dataType : 'json',
					url : 'index.php?option=com_k2&task=categories.addImage&format=json',
					formData : formData,
					done : function(e, data) {
						var response = data.result;
						self.image.set('value', response.value);
						self.image.set('previewURL', response.preview);
					},
					fail : function(e, data) {
						K2Dispatcher.trigger('app:message', 'error', data.jqXHR.responseText);
					}
				});
			}, this));

			// Restore Joomla! modal events
			if ( typeof (SqueezeBox) !== 'undefined') {
				SqueezeBox.initialize({});
				SqueezeBox.assign($$('a.modal-button'), {
					parse : 'rel'
				});
			}
		},

		// Remove image
		removeImage : function(event) {
			if (event !== undefined) {
				event.preventDefault();
			}
			var self = this;
			var formData = {};
			formData['id'] = self.model.get('id');
			formData['tmpId'] = self.model.get('tmpId');
			formData['image'] = jQuery('#appCategoryImageValue').val();
			formData[K2SessionToken] = 1;
			jQuery.ajax({
				dataType : 'json',
				type : 'POST',
				url : 'index.php?option=com_k2&task=categories.removeImage&format=json',
				data : formData
			}).done(function(data, status, xhr) {
				self.image.set('value', '');
				self.image.set('previewURL', '');
			}).fail(function(xhr, status, error) {
				K2Dispatcher.trigger('app:message', 'error', xhr.responseText);
			});
		},

		// Set the image preview depending on the image state
		setImagePreview : function() {
			this.$el.find('#appCategoryImageValue').val(this.image.get('value'));
			this.$el.find('#appCategoryImagePreview').attr('src', this.image.get('previewURL'));
			if (this.image.get('value') === '') {
				this.$el.find('.appCategoryImagePreviewContainer').hide();
			} else {
				this.$el.find('.appCategoryImagePreviewContainer').show();
			}
		},

		browseServerForImage : function(event) {
			event.preventDefault();
			K2Dispatcher.trigger('app:controller:browseServer', {callback : 'app:category:selectImage'});
		},

		setImageFromServer : function(path) {
			var self = this;
			var formData = {};
			formData['id'] = self.model.get('id');
			formData['tmpId'] = self.model.get('tmpId');
			formData['imagePath'] = path;
			formData[K2SessionToken] = 1;
			jQuery.ajax({
				dataType : 'json',
				type : 'POST',
				url : 'index.php?option=com_k2&task=categories.addImage&format=json',
				data : formData
			}).done(function(data, status, xhr) {
				self.image.set('value', data.value);
				self.image.set('previewURL', data.preview);
			}).fail(function(xhr, status, error) {
				K2Dispatcher.trigger('app:message', 'error', xhr.responseText);
			});
		}
	});
	return K2ViewCategory;
});
