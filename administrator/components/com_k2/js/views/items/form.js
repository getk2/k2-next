define(['marionette', 'text!layouts/items/form.html', 'dispatcher'], function(Marionette, template, K2Dispatcher) {'use strict';
	// K2 item form view
	var K2ViewItem = Marionette.ItemView.extend({

		// Template
		template : _.template(template),

		// Model events
		modelEvents : {
			'change' : 'render'
		},

		// UI events
		events : {
			'click #appItemImageRemove' : 'removeImage',
			'click #appItemImageBrowseServer' : 'browseServerForImage',
			'click #appActionAddAttachment' : 'addAttachment',
			'click .appItemAttachmentRemove' : 'removeAttachment',
			'click .appItemAttachmentDownload' : 'downloadAttachment',
			'click .appItemAttachmentBrowseServer' : 'browseServerForAttachment',
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

			// Add a model for image
			this.image = new Backbone.Model();

			// Add a listener for change event
			this.image.on('change', _.bind(function() {
				this.setImagePreview();
			}, this));

			// Add a listener selecting an image from the media manager
			K2Dispatcher.on('app:item:selectImage', function(path) {
				this.setImageFromServer(path);
			}, this);

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
				if (this.image.get('value') > 0) {
					this.removeImage();
				}
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
			// Update image properties from model properties
			this.image.set({
				value : this.model.get('image_flag'),
				previewURL : this.model.get('imagePreview')
			});

			// Initialize uploader for existing attachments
			this.$el.find('.appItemAttachment').each(_.bind(function(index, el) {
				this.setUpAttachmentUploader(jQuery(el));
			}, this));

			// Initialize uploader for existing media
			this.$el.find('.appItemMediaEntry').each(_.bind(function(index, el) {
				this.setUpMediaUploader(jQuery(el));
			}, this));
		},

		// OnDomRefresh event ( Marionette.js build in event )
		onDomRefresh : function() {

			// Initialize the editor
			K2Editor.init();

			// Auto complete fields
			require(['widgets/select2/select2', 'css!widgets/select2/select2.css'], _.bind(function() {

				// Tags
				var tagsInput = this.$el.find(this.$el.find('#appItemTags'));
				var tags = [];
				_.each(this.model.get('tags'), function(tag) {
					tags.push(tag.name);
				});
				tagsInput.val(tags.join(','));
				tagsInput.select2({
					tags : tags,
					width : '300px',
					placeholder : l('K2_ENTER_SOME_TAGS'),
					tokenSeparators : [','],
					initSelection : function(element, callback) {
						var data = [];
						jQuery(element.val().split(',')).each(function() {
							data.push({
								id : this,
								text : this
							});
						});
						callback(data);
					},
					createSearchChoice : function(term, data) {
						if (jQuery(data).filter(function() {
							return this.text.localeCompare(term) === 0;
						}).length === 0) {
							return {
								id : term,
								text : term
							};
						}
					},
					ajax : {
						url : 'index.php?option=com_k2&task=tags.search&format=json',
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
							var tags = [];
							jQuery.each(data.rows, function(index, row) {
								var tag = {}
								tags.push({
									id : row.name,
									text : row.name
								});
							});
							var more = (page * 50) < data.total;
							return {
								results : tags,
								more : more
							};
						}
					}
				});

				// Author
				var authorField = this.$el.find('#appItemAuthor');
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
						data : function(term, page) {// page is the one-based page number tracked by Select2
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
				self.$el.find('#appItemImageFile').fileupload({
					dataType : 'json',
					url : 'index.php?option=com_k2&task=items.addImage&format=json',
					formData : function() {
						return [{
							name : 'id',
							value : self.model.get('id')
						}, {
							name : 'tmpId',
							value : self.model.get('tmpId')
						}, {
							name : K2SessionToken,
							value : 1
						}];
					},
					done : function(e, data) {
						var response = data.result;
						self.image.set('value', '1');
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
			formData['image'] = jQuery('#appItemImageValue').val();
			formData[K2SessionToken] = 1;
			jQuery.ajax({
				dataType : 'json',
				type : 'POST',
				url : 'index.php?option=com_k2&task=items.removeImage&format=json',
				data : formData
			}).done(function(data, status, xhr) {
				self.image.set('value', '0');
				self.image.set('previewURL', '');
			}).fail(function(xhr, status, error) {
				K2Dispatcher.trigger('app:message', 'error', xhr.responseText);
			});
		},

		// Set the image preview depending on the image state
		setImagePreview : function() {
			this.$el.find('#appItemImageFlag').val(this.image.get('value'));
			this.$el.find('#appItemImagePreview').attr('src', this.image.get('previewURL'));
			if (this.image.get('value') < 1) {
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
		// Download attachment
		downloadAttachment : function(event) {
			event.preventDefault();
			var el = jQuery(event.currentTarget);
			var url = _.unescape(el.data('url'));
			window.location = url;
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

		browseServerForImage : function(event) {
			event.preventDefault();
			K2Dispatcher.trigger('app:controller:browseServer', {
				callback : 'app:item:selectImage',
				modal : true
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
				self.image.set('value', '1');
				self.image.set('previewURL', data.preview);
			}).fail(function(xhr, status, error) {
				K2Dispatcher.trigger('app:message', 'error', xhr.responseText);
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
