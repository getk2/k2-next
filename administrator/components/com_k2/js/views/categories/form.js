'use strict';
define(['marionette', 'text!layouts/categories/form.html', 'dispatcher'], function(Marionette, template, K2Dispatcher) {
	var K2ViewCategory = Marionette.ItemView.extend({
		template : _.template(template),
		modelEvents : {
			'change' : 'render'
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
			K2Editor.save('description');
		},
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
				formData['tmpId'] = this.model.get('tmpId');
				this.$el.find('#appCategoryImageFile').fileupload({
					dataType : 'json',
					url : 'index.php?option=com_k2&task=categories.image&format=json',
					formData : formData,
					done : function(e, data) {
						var response = data.result;
						jQuery('#appCategoryImagePreview').attr('src', response.preview);
						jQuery('#appCategoryImageValue').val(response.value);
					},
					error : function(xhr) {
						K2Dispatcher.trigger('app:message', 'error', xhr.responseText);
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
		}
	});
	return K2ViewCategory;
});
