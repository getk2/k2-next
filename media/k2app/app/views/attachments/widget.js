define(['dispatcher', 'widget', 'text!layouts/attachments/widget.html', 'text!layouts/attachments/add.html', 'text!layouts/attachments/table.html', 'text!layouts/attachments/preview.html', 'collections/attachments', 'sortable'], function(K2Dispatcher, K2Widget, widgetTemplate, addTemplate, tableTemplate, previewTemplate, K2CollectionAttachments) {'use strict';

	var K2ViewAttachmentsRow = Marionette.ItemView.extend({
		tagName : 'div',
		template : _.template(addTemplate),
		events : {
			'click [data-action="remove"]' : 'removeAttachment'
		},
		modelEvents : {
			'change' : 'render'
		},
		initialize : function() {
			K2Dispatcher.on('attachments:select:' + this.model.cid, function(url) {
				this.model.set('path', url);
				this.model.set('file', '');
			}, this);
			K2Dispatcher.on('attachments:dropbox:' + this.model.cid, function(url) {
				this.setFileFromDropBox(url);
			}, this);
			K2Dispatcher.on('attachments:upload:' + this.model.cid, function(e, data) {
				this.model.set('file', data.result);
				this.model.set('path', '');
			}, this);
		},
		onDomRefresh : function() {
			K2Widget.updateEvents(this.$el);
		},
		removeAttachment : function(event) {
			event.preventDefault();
			this.model.set('remove', 1);
		},
		setFileFromDropBox : function(url) {
			var data = {};
			data['url'] = url;
			data['file'] = this.model.get('file');
			data[K2SessionToken] = 1;
			var self = this;
			jQuery.ajax({
				dataType : 'json',
				type : 'POST',
				url : 'index.php?option=com_k2&task=attachments.upload&format=json',
				data : data
			}).done(function(data, status, xhr) {
				self.model.set('file', data);
				self.model.set('path', '');
			}).fail(function(xhr, status, error) {
				K2Dispatcher.trigger('app:messages:add', 'error', xhr.responseText);
			});
		}
	});

	var K2ViewAttachments = Marionette.CollectionView.extend({
		childView : K2ViewAttachmentsRow
	});

	var K2ViewAttachmentsPreviewRow = Marionette.ItemView.extend({
		tagName : 'tr',
		template : _.template(previewTemplate),
		events : {
			'click [data-action="edit"]' : 'editAttachment',
			'click [data-action="download"]' : 'downloadAttachment',
			'click [data-action="remove"]' : 'removeAttachment'
		},
		modelEvents : {
			'change' : 'render'
		},
		editAttachment : function(event) {
			event.preventDefault();
			this.$el.find('input[reaonly]').prop('readonly', false);
		},
		downloadAttachment : function(event) {
			event.preventDefault();
			if (this.model.get('link')) {
				var url = _.unescape(this.model.get('link'));
				window.location = url;
			}
		},
		removeAttachment : function(event) {
			event.preventDefault();
			this.model.set('remove', 1);
		}
	});
	var K2ViewAttachmentsPreview = Marionette.CompositeView.extend({
		template : _.template(tableTemplate),
		childViewContainer : '[data-region="list"]',
		childView : K2ViewAttachmentsPreviewRow,
		onRender : function() {
			if (this.collection.models.length > 0) {
				this.$el.show();
				this.$el.find('table').sortable({
					containerSelector : 'table',
					itemPath : '> tbody',
					itemSelector : 'tr',
					placeholder : '<tr class="k2SortingPlaceholder"><td colspan="6"></td></tr>',
					handle : '[data-role="ordering-handle"]'
				});
			} else {
				this.$el.hide();
			}
		}
	});

	var K2ViewAttachmentsWidget = Marionette.LayoutView.extend({
		template : _.template(widgetTemplate),
		regions : {
			newAttachmentsRegion : '[data-region="new-attachments"]',
			existingAttachmentsRegion : '[data-region="existing-attachments"]'
		},
		events : {
			'click [data-action="add"]' : 'addAttachment'
		},
		initialize : function(options) {
			this.existingAttachmentsCollection = new K2CollectionAttachments(options.data);
			this.existingAttachmentsView = new K2ViewAttachmentsPreview({
				collection : this.existingAttachmentsCollection
			});

			this.newAttachmentsCollection = new K2CollectionAttachments();
			this.newAttachmentsView = new K2ViewAttachments({
				collection : this.newAttachmentsCollection
			});

		},
		onShow : function() {
			this.existingAttachmentsRegion.show(this.existingAttachmentsView);
			this.newAttachmentsRegion.show(this.newAttachmentsView);

		},
		addAttachment : function(event) {
			event.preventDefault();
			this.newAttachmentsCollection.add({
				isNew : true
			});
		}
	});

	return K2ViewAttachmentsWidget;
});
