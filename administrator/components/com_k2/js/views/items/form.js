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
			'click #appActionAddAttachment' : 'addAttachment',
			'click .appItemAttachmentRemove' : 'removeAttachment',
			'click .appItemAttachmentDownload' : 'downloadAttachment'
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
			}
		},

		// onRender event
		onRender : function() {
			// Update image properties from model properties
			this.image.set({
				value : this.model.get('image_flag'),
				previewURL : this.model.get('imagePreview')
			});
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
				var formData = {};
				formData['id'] = self.model.get('id');
				formData['tmpId'] = self.model.get('tmpId');
				formData[K2SessionToken] = 1;
				self.$el.find('#appItemImageFile').fileupload({
					dataType : 'json',
					url : 'index.php?option=com_k2&task=items.addImage&format=json',
					formData : formData,
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

			// Download event
			//attachment.find('.appItemAttachmentDownload').unbind('click').click(function(event) {
			//	event.preventDefault();
			//	this.downloadAttachment(event);
			//});

			// Remove event
			//attachment.find('.appItemAttachmentRemove').unbind('click').click(_.bind(function(event) {
			//	event.preventDefault();
			//	this.removeAttachment(event);
			//}, this));

			// Upload event
			require(['widgets/uploader/jquery.iframe-transport', 'widgets/uploader/jquery.fileupload'], _.bind(function() {
				var self = this;
				attachment.find('input[type="file"]').fileupload({
					dataType : 'json',
					url : 'index.php?option=com_k2&task=items.addAttachment&format=json',
					formData : function() {
						return [{
							name : 'id',
							value : self.model.get('id')
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
			}, this));
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
		}
	});
	return K2ViewItem;
});
