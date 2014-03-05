define(['dispatcher', 'widgets/widget', 'text!layouts/attachments/list.html', 'text!layouts/attachments/row.html', 'collections/attachments'], function(K2Dispatcher, K2Widget, listTemplate, rowTemplate, K2CollectionAttachments) {'use strict';

	var K2ViewAttachmentsRow = Marionette.ItemView.extend({
		tagName : 'div',
		template : _.template(rowTemplate),
		events : {
			'click [data-action="remove"]' : 'removeAttachment',
			'click [data-action="download"]' : 'downloadAttachment'
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
		downloadAttachment : function(event) {
			event.preventDefault();
			if (this.model.get('link')) {
				var url = _.unescape(this.model.get('link'));
				window.location = url;
			}
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
				K2Dispatcher.trigger('app:message', 'error', xhr.responseText);
			});
		}
	});

	var K2ViewAttachments = Marionette.CompositeView.extend({
		template : _.template(listTemplate),
		itemViewContainer : '[data-region="attachments"]',
		itemView : K2ViewAttachmentsRow,
		events : {
			'click [data-action="add"]' : 'addAttachment'
		},
		initialize : function(options) {
			this.collection = new K2CollectionAttachments(options.data);
		},
		addAttachment : function(event) {
			event.preventDefault();
			this.collection.add({});
		}
	});
	return K2ViewAttachments;
});
