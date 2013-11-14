define(['backbone', 'marionette', 'dispatcher'], function(Backbone, Marionette, K2Dispatcher) {'use strict';
	var K2Widget = {

		// Adds the widgets to the elements
		updateEvents : function(container) {
			// Copy this to a variable
			var self = this;
			// Browse server
			container.find('input[data-widget]').each(function() {
				self.attachWidget(jQuery(this));
			});

		},

		attachWidget : function(element) {
			var widget = element.data('widget');
			var active = element.data('active');
			if (!active) {
				this[widget](element);
				element.data('active', true);
			}
		},

		browser : function(element) {

			// Create the button
			var button = jQuery('<button>' + l('K2_BROWSE_SERVER') + '</button>');

			// Generate a unique callback
			var callback = 'app:media:' + jQuery.now();

			// Add the click event
			button.on('click', function(event) {
				// Stop the event
				event.preventDefault();

				// Open the modal
				K2Dispatcher.trigger('app:controller:browseServer', {
					callback : callback,
					modal : true
				});
			});
			// Append the button
			element.after(button);

			// Callback when a file is selected
			K2Dispatcher.on(callback, function(path) {
				element.val(path);
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
		},
		ordering : function(element, column, enabled) {
			var el = element.find('table');
			var minimumValue = el.find('input[name="' + column + '[]"]:first').val();
			require(['widgets/sortable/jquery-sortable-min'], function() {
				el.sortable({
					handle : '.appOrderingHandle[data-column="' + column + '"]',
					containerSelector : 'table',
					itemPath : '> tbody',
					itemSelector : 'tr',
					placeholder : '<tr class="placeholder"/>',
					onDrop : function(item, container, _super) {
						var value = minimumValue;
						var keys = [];
						var values = [];
						el.find('input[name="' + column + '[]"]').each(function(index) {
							keys.push(jQuery(this).data('id'));
							values.push(value);
							value++;
						});
						K2Dispatcher.trigger('app:controller:saveOrder', keys, values, column);
						_super(item);
					}
				});

				// Enable or disable the sorting
				if (enabled) {
					el.sortable('enable');
					element.find('input[name="' + column + '[]"]').prop('disabled', false);
					element.find('.appActionSaveOrder[data-column="' + column + '"]').prop('disabled', false);
				} else {
					el.sortable('disable');
					element.find('input[name="' + column + '[]"]').prop('disabled', true);
					element.find('.appActionSaveOrder[data-column="' + column + '"]').prop('disabled', true);
				}

			});
		}
	};
	return K2Widget;
});
