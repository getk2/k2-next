define(['backbone', 'marionette', 'dispatcher'], function(Backbone, Marionette, K2Dispatcher) {'use strict';
	var K2Widget = {

		// Adds the widgets to the elements
		updateEvents : function(container) {
			// Copy this to a variable
			var self = this;
			// Browse server
			container.find('[data-widget]').each(function() {
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
			require(['widgets/pickadate/picker.date', 'css!widgets/pickadate/themes/default.css', 'css!widgets/pickadate/themes/default.date.css'], function(Picker) {
				element.pickadate({
					format : element.data('format') || 'yyyy-mm-dd'
				});
			});
		},

		timepicker : function(element) {
			require(['widgets/pickadate/picker.time', 'css!widgets/pickadate/themes/default.css', 'css!widgets/pickadate/themes/default.time.css'], function(Picker) {
				element.pickatime({
					format : element.data('format') || 'HH:i'
				});
			});
		},
		user : function(element) {
			require(['widgets/select2/select2.min', 'css!widgets/select2/select2.css'], function() {
				var userId = element.val();
				var userName = element.data('name');
				var showNull = element.data('null');
				element.select2({
					minimumInputLength : element.data('min') || 0,
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
							if (showNull) {
								users.push({
									id : 0,
									text : showNull
								});
							}
							jQuery.each(data.rows, function(index, row) {
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
		tag : function(element) {
			require(['widgets/select2/select2.min', 'css!widgets/select2/select2.css'], function() {
				var tagId = element.val();
				var tagName = element.data('name');
				var showNull = element.data('null');
				element.select2({
					minimumInputLength : element.data('min') || 0,
					placeholder : element.data('placeholder') || l('K2_SELECT_TAG'),
					initSelection : function(element, callback) {
						if (tagId) {
							var data = {
								id : tagId,
								text : tagName
							};
							callback(data);
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
							if (showNull) {
								tags.push({
									id : 0,
									text : showNull
								});
							}
							jQuery.each(data.rows, function(index, row) {
								tags.push({
									id : row.id,
									text : row.name
								});
							});
							var more = (page * 50) < data.total;
							return {
								results : tags,
								more : more
							};
						}
					},
				});
			});
		},
		tags : function(element) {
			require(['widgets/select2/select2.min', 'css!widgets/select2/select2.css'], function() {
				var canCreateTag = element.data('create');
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
							if (canCreateTag) {
								return {
									id : term,
									text : term
								};
							} else {
								return null;
							}
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
								var tag = {};
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
						K2Dispatcher.trigger('app:messages:add', 'error', data.jqXHR.responseText);
					}
				});
			});
		},
		ordering : function(element, column, enabled) {
			var minimumValue = element.find('input[name="' + column + '[]"]:first').val();
			require(['widgets/sortable/jquery-sortable-min'], function() {
				element.find('div[data-region="list"]').sortable({
					handle : '[data-role="ordering-handle"][data-column="' + column + '"]',
					containerSelector : 'table',
					itemSelector : 'ul',
					placeholder : '<ul class="k2SortingPlaceholder"/>',
					onDrop : function(item, container, _super) {
						var value = minimumValue;
						var keys = [];
						var values = [];
						element.find('input[name="' + column + '[]"]').each(function(index) {
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
					element.find('div[data-region="list"]').sortable('enable');
					element.find('input[name="' + column + '[]"]').prop('disabled', false);
					element.find('[data-action="save-ordering"][data-column="' + column + '"]').prop('disabled', false);
				} else {
					element.find('div[data-region="list"]').sortable('disable');
					element.find('input[name="' + column + '[]"]').prop('disabled', true);
					element.find('[data-action="save-ordering"][data-column="' + column + '"]').prop('disabled', true);
				}

			});
		},

		tabs : function(element) {
			var navigationContainer = element.find('[data-role="tabs-navigation"]:first');
			var navigationElements = navigationContainer.find('a');
			var contentsContainer = element.find('[data-role="tabs-content"]:first');
			var contentElements = contentsContainer.find('> div');
			contentElements.css('display', 'none');
			contentElements.eq(0).css('display', 'block');
			navigationElements.click(function(event) {
				event.preventDefault();
				var index = navigationElements.index(jQuery(this));
				contentElements.css('display', 'none');
				contentElements.eq(index).css('display', 'block');
			});
		},

		dropbox : function(element) {
			if ( typeof (Dropbox) != 'undefined') {
				var options = {
					linkType : 'direct',
					success : function(files) {
						K2Dispatcher.trigger(element.data('callback'), files[0].link);
					}
				};
				options.multiselect = element.data('multiple') || false;
				var types = element.data('types') || null;
				if (types) {
					var allowed = types.split(',');
					options.extensions = allowed;
				}
				var button = Dropbox.createChooseButton(options);
				element.append(button);
			} else {
				element.remove();
			}
		}
	};
	return K2Widget;
});
