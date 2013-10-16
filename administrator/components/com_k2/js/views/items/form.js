'use strict';
define(['marionette', 'text!layouts/items/form.html', 'dispatcher'], function(Marionette, template, K2Dispatcher) {
	var K2ViewItem = Marionette.ItemView.extend({
		template : _.template(template),
		modelEvents : {
			'change' : 'render'
		},
		events : {
			'click #appActionAddAttachment' : 'addAttachment',
			'click .appItemAttachmentRemove' : 'removeAttachment'
		},
		initialize : function() {
			K2Dispatcher.on('app:controller:beforeSave', function() {
				this.onBeforeSave();
			}, this);
		},
		serializeData : function() {
			var data = {
				'row' : this.model.toJSON(),
				'form' : this.model.getForm().toJSON()
			};
			return data;
		},
		onBeforeSave : function() {
			K2Editor.save('text');
		},
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
				var formData = {};
				formData['id'] = this.model.get('id');
				formData[K2SessionToken] = 1;
				this.$el.find('#appItemImageFile').fileupload({
					dataType : 'json',
					url : 'index.php?option=com_k2&task=items.image&format=json',
					formData : formData,
					done : function(e, data) {
						var response = data.result;
						jQuery('#appImagePreview').attr('src', response.preview);
						jQuery('#appItemImageValue').val(response.value);
						jQuery('#appItemImageFlag').val(1);
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

		addAttachment : function(event) {
			event.preventDefault();
			var attachment = this.$el.find('#appItemAttachmentPlaceholder').clone();
			attachment.removeAttr('id');
			attachment.addClass('appItemAttachment');
			attachment.find('input').removeAttr('disabled');
			attachment.on('input').removeAttr('disabled');
			attachment.find('.appItemAttachmentRemove').click(_.bind(function(event) {
				this.removeAttachment(event);
			}, this));
			require(['widgets/uploader/jquery.iframe-transport', 'widgets/uploader/jquery.fileupload'], _.bind(function() {
				var formData = {};
				formData['id'] = this.model.get('id');
				formData[K2SessionToken] = 1;
				attachment.find('input[type="file"]').fileupload({
					dataType : 'json',
					url : 'index.php?option=com_k2&task=attachments.upload&format=json',
					formData : formData,
					done : function(e, data) {
						var response = data.result;
						attachment.find('.appItemAttachmentId').val(response.id);
						attachment.find('.appItemAttachmentRemove').data('id', response.id);
					}
				});
			}, this));
			this.$el.find('#appItemAttachments').append(attachment);
		},

		removeAttachment : function(event) {
			event.preventDefault();
			var el = jQuery(event.currentTarget);
			var id = el.data('id');
			if (id !== undefined) {
				var data = {
					id : id
				};
				data[K2SessionToken] = 1;
				jQuery.ajax({
					type : 'POST',
					url : 'index.php?option=com_k2&task=attachments.remove&format=json',
					data : data,
					success : function() {
						el.parents('.appItemAttachment').remove();
					}
				});
			} else {
				el.parents('.appItemAttachment').remove();
			}
		}
	});
	return K2ViewItem;
});
