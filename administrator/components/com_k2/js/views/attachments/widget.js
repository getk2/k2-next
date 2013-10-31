define(['marionette', 'text!layouts/attachments/widget.html', 'widgets/widget'], function(Marionette, template, K2Widget) {'use strict';
	var K2ViewAttachmentsWidget = Marionette.ItemView.extend({
		template : _.template(template),
		collectionEvents : {
			'reset' : 'render',
			'add' : 'render',
			'remove' : 'render'
		},
		events : {
			'click #appAddAttachment' : 'addAttachment',
			'click .appRemoveAttachment' : 'removeAttachment',
			'click .appDownloadAttachment' : 'downloadAttachment'
		},
		onDomRefresh : function() {
			K2Widget.updateEvents();
		},
		addAttachment : function(event) {
			event.preventDefault();
			var attachment = new Backbone.Model();
			attachment.set('id', attachment.cid);
			this.collection.add(attachment);
		},
		removeAttachment : function(event) {
			event.preventDefault();
			var element = jQuery(event.currentTarget);
			var attachment = this.collection.get(element.data('id'));
			this.collection.remove(attachment);
		},
		downloadAttachment : function(event) {
			event.preventDefault();
			var element = jQuery(event.currentTarget);
			var attachment = this.collection.get(element.data('id'));
			var url = _.unescape(attachment.get('link'));
			window.location = url;
		}
	});
	return K2ViewAttachmentsWidget;
});
