define(['dispatcher', 'widgets/widget', 'text!layouts/attachments/list.html', 'text!layouts/attachments/row.html', 'collections/attachments'], function(K2Dispatcher, K2Widget, listTemplate, rowTemplate, K2CollectionAttachments) {'use strict';

	var K2ViewAttachmentsRow = Marionette.ItemView.extend({
		tagName : 'div',
		template : _.template(rowTemplate),
		events : {
			'click .appRemoveAttachment' : 'removeAttachment',
			'click .appDownloadAttachment' : 'downloadAttachment'
		},
		modelEvents : {
			'sync' : 'render'
		},
		initialize : function() {
			K2Dispatcher.on('attachments:select:' + this.model.cid, function(url) {
				this.model.set('url', url);
				this.model.set('file', '');
				this.saveAttachment();
			}, this);
			K2Dispatcher.on('attachments:upload:' + this.model.cid, function(e, data) {
				this.model.set('file', data.result);
				this.model.set('url', '');
				this.saveAttachment();
			}, this);
		},
		onDomRefresh : function() {
			K2Widget.updateEvents(this.$el);
		},
		removeAttachment : function(event) {
			event.preventDefault();
			this.model.destroy();
		},
		downloadAttachment : function(event) {
			event.preventDefault();
			if (this.model.get('link')) {
				var url = _.unescape(this.model.get('link'));
				window.location = url;
			}
		},
		saveAttachment : function() {
			this.model.set('name', this.$el.find('input[name="attachments[name][]"]').val());
			this.model.set('title', this.$el.find('input[name="attachments[title][]"]').val());
			this.model.save();
		}
	});

	var K2ViewAttachments = Marionette.CompositeView.extend({
		template : _.template(listTemplate),
		itemViewContainer : '#appAttachments',
		itemView : K2ViewAttachmentsRow,
		events : {
			'click #appAddAttachment' : 'addAttachment'
		},
		initialize : function(options) {
			this.itemId = options.itemId;
			this.collection = new K2CollectionAttachments(options.data);
			this.collection.setState('itemId', this.itemId);
			K2Dispatcher.on('attachments:delete', function() {
				_.each(this.collection.models, function(model) {
					model.destroy();
				});
			}, this);
		},
		addAttachment : function(event) {
			event.preventDefault();
			this.collection.add({
				'itemId' : this.collection.getState('itemId')
			});
		}
	});
	return K2ViewAttachments;
});
