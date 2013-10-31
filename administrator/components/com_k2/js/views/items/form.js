define(['marionette', 'text!layouts/items/form.html', 'dispatcher', 'widgets/widget', 'views/extrafields/widget', 'collections/extrafieldswidget', 'views/attachments/widget', 'collections/attachmentswidget'], function(Marionette, template, K2Dispatcher, K2Widget, K2ViewExtraFieldsWidget, K2CollectionExtraFieldsWidget, K2ViewAttachmentsWidget, K2CollectionAttachmentsWidget) {'use strict';
	// K2 item form view
	var K2ViewItem = Marionette.Layout.extend({

		// Template
		template : _.template(template),

		// Regions
		regions : {
			attachmentsRegion : '#appItemAttachments',
			extraFieldsRegion : '#appItemExtraFields'
		},

		// Model events
		modelEvents : {
			'change' : 'render'
		},

		// UI events
		events : {
			'click #appItemImageRemove' : 'removeImage',
			'change #catid' : 'renderExtraFields',

			
			'click #appActionAddMedia' : 'addMedia',
			'click .appItemMediaRemove' : 'removeMedia',
			'click .appItemMediaBrowseServer' : 'browseServerForMedia',
			'click #appActionAddGallery' : 'addGallery',
			'click .appItemGalleryRemove' : 'removeGallery',

		},

		// Initialize
		initialize : function() {

			// Add a listener for the before save event
			K2Dispatcher.on('app:controller:beforeSave', function() {
				this.onBeforeSave();
			}, this);

			// Add a listener for the image upload callback
			K2Dispatcher.on('item:image:upload', function(e, data) {
				this.setImagePreview(data.result.preview, 1);
			}, this);

			// Add a listener for the image select callback
			K2Dispatcher.on('item:image:select', function(path) {
				this.setImageFromServer(path);
			}, this);

			// Setup extra fields collection
			this.extraFieldsCollection = new K2CollectionExtraFieldsWidget([], {
				scope : 'item',
				resourceId : this.model.get('id'),
				filterId : this.model.get('catid')
			});
			this.extraFieldsCollection.fetch({
				reset : true
			});

			// Setup attachments collection
			this.attachmentsCollection = new K2CollectionAttachmentsWidget();
			this.attachmentsCollection.setItemId(this.model.get('id'));
			this.attachmentsCollection.fetch({
				reset : true
			});

			// Add a listener selecting an attachment from the media manager
			K2Dispatcher.on('app:item:selectAttachment', function(path) {
				this.setAttachmentFromServer(path);
			}, this);

			// Add a listener selecting a media file from the media manager
			K2Dispatcher.on('app:item:selectMedia', function(path) {
				this.setMediaFromServer(path);
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

		// OnBeforeSave event
		onBeforeSave : function() {

			// Update form from editor contents
			K2Editor.save('text');
		},

		// OnBeforeClose event ( Marionette.js build in event )
		onBeforeClose : function() {
			//is it new?
			if (this.model.isNew()) {
				// Delete any uploaded images
				this.removeImage();

				// Delete any uploaded attachments
				if (this.$el.find('.appItemAttachmentId').length > 1) {
					this.removeAttachments();
				}

				// Delete any uploaded media files
				if (this.$el.find('.appItemMediaUpload').length > 1) {
					this.removeMediaFolder();
				}

				// Delete any uploaded galleries
				if (this.$el.find('.appItemGalleryUpload').length > 1) {
					this.removeGalleries();
				}
			}
		},

		// onRender event
		onRender : function() {

			// Update radio buttons value
			this.$el.find('input[name="published"]').val([this.model.get('published')]);
			this.$el.find('input[name="featured"]').val([this.model.get('featured')]);

			// Handle image preview
			this.setImagePreview(this.model.get('imagePreview'), this.model.get('image_flag'));

			// Initialize uploader for existing attachments
			this.$el.find('.appItemAttachment').each(_.bind(function(index, el) {
				this.setUpAttachmentUploader(jQuery(el));
			}, this));

			// Initialize uploader for existing media
			this.$el.find('.appItemMediaEntry').each(_.bind(function(index, el) {
				this.setUpMediaUploader(jQuery(el));
			}, this));

		},

		onShow : function() {

			// Show attachments
			this.attachmentsRegion.show(new K2ViewAttachmentsWidget({
				collection : this.attachmentsCollection
			}));

			// Show extra fields
			this.extraFieldsRegion.show(new K2ViewExtraFieldsWidget({
				collection : this.extraFieldsCollection
			}));
		},

		renderExtraFields : function(event) {
			event.preventDefault();
			this.extraFieldsCollection.setOption('filterId', this.$el.find('#catid').val());
			this.extraFieldsCollection.fetch({
				reset : true
			});
		},

		// OnDomRefresh event ( Marionette.js build in event )
		onDomRefresh : function() {

			// Setup widgets
			K2Widget.updateEvents();

			// Proxy event for extra fields custom javascript code
			jQuery(document).trigger('K2ExtraFields');

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
				url : 'index.php?option=com_k2&task=items.addImage&format=json',
				data : formData
			}).done(function(data, status, xhr) {
				self.setImagePreview(data.preview, 1)
			}).fail(function(xhr, status, error) {
				K2Dispatcher.trigger('app:message', 'error', xhr.responseText);
			});
		},

		// Remove image
		removeImage : function(event) {
			if (event !== undefined) {
				event.preventDefault();
			}
			var formData = {};
			formData['id'] = this.model.get('id');
			formData['tmpId'] = this.model.get('tmpId');
			formData[K2SessionToken] = 1;
			var self = this;
			jQuery.ajax({
				dataType : 'json',
				type : 'POST',
				url : 'index.php?option=com_k2&task=items.removeImage&format=json',
				data : formData
			}).done(function(data, status, xhr) {
				self.setImagePreview('', 0);
			}).fail(function(xhr, status, error) {
				K2Dispatcher.trigger('app:message', 'error', xhr.responseText);
			});
		},

		// Set the image preview depending on the image state
		setImagePreview : function(previewUrl, value) {
			this.$el.find('#appItemImageFlag').val(value);
			this.$el.find('#appItemImagePreview').attr('src', previewUrl);
			if (value < 1) {
				this.$el.find('.appItemImagePreviewContainer').hide();
			} else {
				this.$el.find('.appItemImagePreviewContainer').show();
			}
		},

		// Attachment upload event
		setUpAttachmentUploader : function(attachment) {
			var itemId = this.model.get('id');
			require(['widgets/uploader/jquery.iframe-transport', 'widgets/uploader/jquery.fileupload'], function() {
				attachment.find('input[type="file"]').fileupload({
					dataType : 'json',
					url : 'index.php?option=com_k2&task=items.addAttachment&format=json',
					formData : function() {
						return [{
							name : 'id',
							value : attachment.find('input.appItemAttachmentId').val()
						}, {
							name : 'itemId',
							value : itemId
						}, {
							name : 'name',
							value : attachment.find('input.appItemAttachmentName').val()
						}, {
							name : 'title',
							value : attachment.find('input.appItemAttachmentTitle').val()
						}, {
							name : K2SessionToken,
							value : 1
						}];
					},
					done : function(e, data) {
						var response = data.result;
						attachment.find('.appItemAttachmentId').val(response.id);
						attachment.find('.appItemAttachmentDownload').removeAttr('disabled').data('url', response.link);
						attachment.find('.appItemAttachmentRemove').data('id', response.id);
						attachment.find('input.appItemAttachmentName').val(response.name);
						attachment.find('input.appItemAttachmentTitle').val(response.title);
					},
					fail : function(e, data) {
						K2Dispatcher.trigger('app:message', 'error', data.jqXHR.responseText);
					}
				});
			});
		},

		// Add attachment
		addAttachment : function(event) {
			// Prevent default
			event.preventDefault();

			// Get attachment element
			var attachment = this.$el.find('#appItemAttachmentPlaceholder').clone();

			// Prepare the element
			attachment.removeAttr('id');
			attachment.addClass('appItemAttachment');
			attachment.find('input').removeAttr('disabled');

			// Upload event
			this.setUpAttachmentUploader(attachment);

			this.$el.find('#appItemAttachments').append(attachment);
		},

		// Remove atachment
		removeAttachment : function(event) {
			event.preventDefault();
			var self = this;
			var el = jQuery(event.currentTarget);
			var id = el.data('id');
			if (id !== undefined) {
				var formData = {};
				formData['id'] = id;
				formData[K2SessionToken] = 1;
				jQuery.ajax({
					dataType : 'json',
					type : 'POST',
					url : 'index.php?option=com_k2&task=items.removeAttachment&format=json',
					data : formData
				}).done(function(data, status, xhr) {
					el.parents('.appItemAttachment').remove();
				}).fail(function(xhr, status, error) {
					K2Dispatcher.trigger('app:message', 'error', xhr.responseText);
				});
			} else {
				el.parents('.appItemAttachment').remove();
			}
		},
		
		// Remove attachments
		removeAttachments : function() {
			var formData = {};
			formData = this.$el.find('.appItemAttachmentId').serialize() + '&' + K2SessionToken + '=1';
			jQuery.ajax({
				dataType : 'json',
				type : 'POST',
				url : 'index.php?option=com_k2&task=items.removeAttachment&format=json',
				data : formData
			});
		},

		browseServerForAttachment : function(event) {
			event.preventDefault();
			// Mark the current attachment with a class
			var el = jQuery(event.currentTarget).parents('.appItemAttachment').get(0).addClass('appItemCurrentAttachment');
			K2Dispatcher.trigger('app:controller:browseServer', {
				callback : 'app:item:selectAttachment',
				modal : true
			});
		},

		setAttachmentFromServer : function(path) {
			var attachment = this.$el.find('.appItemCurrentAttachment');
			var formData = {};
			formData['id'] = attachment.find('.appItemAttachmentId').val();
			formData['itemId'] = this.model.get('id');
			formData['name'] = attachment.find('.appItemAttachmentName').val();
			formData['title'] = attachment.find('.appItemAttachmentTitle').val();
			formData['attachmentPath'] = path;
			formData[K2SessionToken] = 1;
			jQuery.ajax({
				dataType : 'json',
				type : 'POST',
				url : 'index.php?option=com_k2&task=items.addAttachment&format=json',
				data : formData
			}).done(function(data, status, xhr) {
				attachment.find('.appItemAttachmentId').val(data.id);
				attachment.find('.appItemAttachmentDownload').removeAttr('disabled').data('url', data.link);
				attachment.find('.appItemAttachmentRemove').data('id', data.id);
				attachment.find('input.appItemAttachmentName').val(data.name);
				attachment.find('input.appItemAttachmentTitle').val(data.title);
			}).fail(function(xhr, status, error) {
				K2Dispatcher.trigger('app:message', 'error', xhr.responseText);
			});
		},

		// Add media
		addMedia : function(event) {
			// Prevent default
			event.preventDefault();

			// Get attachment element
			var media = this.$el.find('#appItemMediaPlaceholder').clone();

			// Prepare the element
			media.removeAttr('id');
			media.addClass('appItemMediaEntry');
			media.find('input').removeAttr('disabled');
			media.find('textarea').removeAttr('disabled');

			// Upload event
			this.setUpMediaUploader(media);

			this.$el.find('#appItemMedia').append(media);
		},

		// Remove media
		removeMedia : function(event) {
			event.preventDefault();
			var el = jQuery(event.currentTarget);
			var media = jQuery(el.parents('.appItemMediaEntry').get(0));
			var uploadedFile = media.find('input.appItemMediaUpload').val();
			if (uploadedFile) {
				var data = {};
				data['file'] = uploadedFile;
				data['id'] = this.model.get('id');
				data['tmpId'] = this.model.get('tmpId');
				data[K2SessionToken] = 1;
				jQuery.ajax({
					dataType : 'json',
					type : 'POST',
					url : 'index.php?option=com_k2&task=items.removeMediaFile&format=json',
					data : data
				}).done(function(data, status, xhr) {
					media.remove();
				}).fail(function(xhr, status, error) {
					K2Dispatcher.trigger('app:message', 'error', xhr.responseText);
				});
			} else {
				media.remove();
			}
		},

		// Remove media
		removeMediaFolder : function() {
			var data = 'folder=' + this.model.get('tmpId') + '&' + K2SessionToken + '=1';
			jQuery.ajax({
				dataType : 'json',
				type : 'POST',
				url : 'index.php?option=com_k2&task=items.removeMediaFolder&format=json',
				data : data
			});
		},

		browseServerForMedia : function(event) {
			event.preventDefault();
			// Mark the current media with a class
			var el = jQuery(event.currentTarget).parents('.appItemMediaEntry').get(0).addClass('appItemCurrentMedia');
			K2Dispatcher.trigger('app:controller:browseServer', {
				callback : 'app:item:selectMedia',
				modal : true
			});
		},

		// Media upload event
		setUpMediaUploader : function(media) {
			var id = this.model.get('id');
			var tmpId = this.model.get('tmpId');
			require(['widgets/uploader/jquery.iframe-transport', 'widgets/uploader/jquery.fileupload'], function() {
				media.find('input[type="file"]').fileupload({
					dataType : 'json',
					url : 'index.php?option=com_k2&task=items.addMedia&format=json',
					formData : function() {
						return [{
							name : 'id',
							value : id,
						}, {
							name : 'tmpId',
							value : tmpId
						}, {
							name : 'currentFile',
							value : media.find('input.appItemMediaUpload').val()
						}, {
							name : K2SessionToken,
							value : 1
						}];
					},
					done : function(e, data) {
						var response = data.result;
						media.find('input.appItemMediaUrl').val(response.url);
						media.find('input.appItemMediaUpload').val(response.upload);
					},
					fail : function(e, data) {
						K2Dispatcher.trigger('app:message', 'error', data.jqXHR.responseText);
					}
				});
			});
		},

		setMediaFromServer : function(path) {
			var media = this.$el.find('.appItemCurrentMedia');
			var url = path;
			media.find('input.appItemMediaUrl').val(url);
			var data = {};
			data['file'] = media.find('input.appItemMediaUpload').val();
			data['id'] = this.model.get('id');
			data['tmpId'] = this.model.get('tmpId');
			data[K2SessionToken] = 1;
			jQuery.ajax({
				dataType : 'json',
				type : 'POST',
				url : 'index.php?option=com_k2&task=items.removeMediaFile&format=json',
				data : data
			}).done(function(data, status, xhr) {
				media.find('input.appItemMediaUpload').val('');
			}).fail(function(xhr, status, error) {
				K2Dispatcher.trigger('app:message', 'error', xhr.responseText);
			});
		},

		// Add gallery
		addGallery : function(event) {
			// Prevent default
			event.preventDefault();

			// Get attachment element
			var gallery = this.$el.find('#appItemGalleryPlaceholder').clone();

			// Prepare the element
			gallery.removeAttr('id');
			gallery.addClass('appItemGalleryEntry');
			gallery.find('input').removeAttr('disabled');
			gallery.find('textarea').removeAttr('disabled');

			// Upload event
			this.setUpGalleryUploader(gallery);

			this.$el.find('#appItemGallery').append(gallery);
		},

		// Remove gallery
		removeGallery : function(event) {
			event.preventDefault();
			var el = jQuery(event.currentTarget);
			var gallery = jQuery(el.parents('.appItemGalleryEntry').get(0));
			var uploadedGallery = gallery.find('input.appItemGalleryUpload').val();
			if (uploadedGallery) {
				var data = {};
				data['folder'] = uploadedGallery;
				data['id'] = this.model.get('id');
				data['tmpId'] = this.model.get('tmpId');
				data[K2SessionToken] = 1;
				jQuery.ajax({
					dataType : 'json',
					type : 'POST',
					url : 'index.php?option=com_k2&task=items.removeGallery&format=json',
					data : data
				}).done(function(data, status, xhr) {
					gallery.remove();
				}).fail(function(xhr, status, error) {
					K2Dispatcher.trigger('app:message', 'error', xhr.responseText);
				});
			} else {
				gallery.remove();
			}
		},

		// Remove galleries
		removeGalleries : function() {
			var data = 'folder=' + this.model.get('tmpId') + '&' + K2SessionToken + '=1';
			jQuery.ajax({
				dataType : 'json',
				type : 'POST',
				url : 'index.php?option=com_k2&task=items.removeGalleries&format=json',
				data : data
			});
		},

		// Gallery upload event
		setUpGalleryUploader : function(gallery) {
			var id = this.model.get('id');
			var tmpId = this.model.get('tmpId');
			require(['widgets/uploader/jquery.iframe-transport', 'widgets/uploader/jquery.fileupload'], function() {
				gallery.find('input[type="file"]').fileupload({
					dataType : 'json',
					url : 'index.php?option=com_k2&task=items.addGallery&format=json',
					formData : function() {
						return [{
							name : 'id',
							value : id,
						}, {
							name : 'tmpId',
							value : tmpId
						}, {
							name : 'currentGallery',
							value : gallery.find('input.appItemGalleryUpload').val()
						}, {
							name : K2SessionToken,
							value : 1
						}];
					},
					done : function(e, data) {
						var response = data.result;
						gallery.find('input.appItemGalleryUpload').val(response.upload);
					},
					fail : function(e, data) {
						K2Dispatcher.trigger('app:message', 'error', data.jqXHR.responseText);
					}
				});
			});
		},
	});
	return K2ViewItem;
});
