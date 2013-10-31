define(['backbone', 'marionette', 'dispatcher'], function(Backbone, Marionette, K2Dispatcher) {'use strict';
	var K2Widget = {

		// Adds the widgets to the elements
		updateEvents : function() {
			// Copy this to a variable
			var self = this;
			// Browse server
			jQuery('input[data-widget="browser"]').each(function() {
				self.browser(jQuery(this));
			});
			// Date picker
			jQuery('input[data-widget="datepicker"]').each(function() {
				self.datepicker(jQuery(this));
			});
			// Date picker
			jQuery('input[data-widget="timepicker"]').each(function() {
				self.timepicker(jQuery(this));
			});
			// User selector
			jQuery('input[data-widget="user"]').each(function() {
				self.user(jQuery(this));
			});
			// Tags selector
			jQuery('input[data-widget="tags"]').each(function() {
				self.tags(jQuery(this));
			});
			// Editor
			jQuery('*[data-widget="editor"]').each(function() {
				self.editor(jQuery(this));
			});
			// Uploader
			jQuery('input[data-widget="uploader"]').each(function() {
				self.uploader(jQuery(this));
			});
		},

		browser : function(element) {
			// Create the button
			var button = jQuery('<button>' + l('K2_BROWSE_SERVER') + '</button>');
			// Add the click event
			button.on('click', function(event) {
				// Stop the event
				event.preventDefault();
				// Add class to the element who triggered the modal
				element.addClass('appBrowseServerActiveField');
				// Open the modal
				K2Dispatcher.trigger('app:controller:browseServer', {
					callback : 'browseServerSelectFile',
					modal : true
				});
			});
			// Append the button
			element.after(button);

			// Callback when a file is selected
			K2Dispatcher.on('browseServerSelectFile', function(path) {
				element.val(path);
				element.removeClass('appBrowseServerActiveField');
				K2Dispatcher.trigger(element.data('callback'), path);
			});
		},

		datepicker : function(element) {
			require(['widgets/pickadate/picker', 'widgets/pickadate/picker.date', 'css!widgets/pickadate/themes/default.css', 'css!widgets/pickadate/themes/default.date.css'], function() {
				element.pickadate({
					format : element.data('format') || 'yyyy-mm-dd'
				});
			});
		},

		timepicker : function(element) {
			require(['widgets/pickadate/picker', 'widgets/pickadate/picker.time', 'css!widgets/pickadate/themes/default.css', 'css!widgets/pickadate/themes/default.time.css'], function() {
				element.pickatime({
					format : element.data('format') || 'HH:i'
				});
			});
		},
		user : function(element) {
			require(['widgets/select2/select2', 'css!widgets/select2/select2.css'], function() {
				var userId = element.val();
				var userName = element.data('name');
				element.select2({
					minimumInputLength : element.data('min') || 1,
					placeholder : element.data('placeholder') || l('K2_SELECT_AUTHOR'),
					initSelection : function(element, callback) {
						if (userId) {
							var data = {
								id : userId,
								text : userName
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
			});
		},
		tags : function(element) {
			require(['widgets/select2/select2', 'css!widgets/select2/select2.css'], function() {
				element.select2({
					tags : element.val().split(','),
					placeholder : element.data('placeholder') || l('K2_ENTER_SOME_TAGS'),
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

			});
		},
		editor : function(element) {
			K2Editor.init();
			// Restore Joomla! modal events
			if ( typeof (SqueezeBox) !== 'undefined') {
				SqueezeBox.initialize({});
				SqueezeBox.assign($$('a.modal-button'), {
					parse : 'rel'
				});
			}
		},
		uploader : function(element) {
			require(['widgets/uploader/jquery.iframe-transport', 'widgets/uploader/jquery.fileupload'], function() {
				element.fileupload({
					dataType : 'json',
					url : element.data('url'),
					formData : function() {
						return [{
							name : K2SessionToken,
							value : 1
						}];
					},
					done : function(e, data) {
						K2Dispatcher.trigger(element.data('callback'), e, data);
					},
					fail : function(e, data) {
						K2Dispatcher.trigger('app:message', 'error', data.jqXHR.responseText);
					}
				});
			});
		}
	};
	return K2Widget;
});
