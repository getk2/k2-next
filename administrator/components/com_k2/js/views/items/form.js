define(['dispatcher', 'widgets/widget', 'text!layouts/items/form.html', 'views/extrafields/widget', 'views/attachments/widget', 'views/galleries/widget', 'views/media/widget', 'views/image/widget'], function(K2Dispatcher, K2Widget, template, K2ViewExtraFieldsWidget, K2ViewAttachmentsWidget, K2ViewGalleriesWidget, K2ViewMediaWidget, K2ViewImageWidget) {'use strict';
	// K2 item form view
	var K2ViewItem = Marionette.Layout.extend({

		// Template
		template : _.template(template),

		// Regions
		regions : {
			imageRegion : '#appItemImage',
			attachmentsRegion : '#appItemAttachments',
			galleriesRegion : '#appItemGalleries',
			mediaRegion : '#appItemMedia',
			extraFieldsRegion : '#appItemExtraFields'
		},

		// UI events
		events : {
			'change #catid' : 'updateExtraFields'
		},

		modelEvents : {
			'change' : 'render'
		},

		// Initialize
		initialize : function() {

			// Add a listener for the before save event
			K2Dispatcher.on('app:controller:beforeSave', function() {
				this.onBeforeSave();
			}, this);

			// Image
			this.imageView = new K2ViewImageWidget({
				row : this.model,
				type : 'item'
			});

			// Attachments
			this.attachmentsView = new K2ViewAttachmentsWidget({
				data : this.model.get('attachments'),
				itemId : this.model.get('id'),
				tmpId : this.model.get('tmpId')
			});

			// Galleries
			this.galleriesView = new K2ViewGalleriesWidget({
				data : this.model.get('galleries'),
				itemId : this.model.get('id') || this.model.get('tmpId')
			});

			// Media
			this.mediaView = new K2ViewMediaWidget({
				data : this.model.get('media'),
				itemId : this.model.get('id') || this.model.get('tmpId')
			});

			// Extra fields
			this.extraFieldsView = new K2ViewExtraFieldsWidget({
				data : this.model.getForm().get('extraFields'),
				filterId : this.model.get('catid'),
				resourceId : this.model.get('id'),
				scope : 'item'
			});

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
			// Clean up uploaded files
			if (this.model.isNew()) {
				this.imageView.trigger('delete');
				this.attachmentsView.trigger('delete');
				this.galleriesView.trigger('delete');
				this.mediaView.trigger('delete');
			}
		},

		updateExtraFields : function(event) {
			event.preventDefault();
			this.extraFieldsView.trigger('filter', this.$el.find('#catid').val());
		},

		// OnDomRefresh event ( Marionette.js build in event )
		onDomRefresh : function() {

			// Editor
			K2Editor.init();

			// Restore Joomla! modal events
			if ( typeof (SqueezeBox) !== 'undefined') {
				SqueezeBox.initialize({});
				SqueezeBox.assign($$('a.modal-button'), {
					parse : 'rel'
				});
			}

			// Setup widgets
			K2Widget.updateEvents(this.$el);

			// Proxy event for extra fields custom javascript code
			jQuery(document).trigger('K2ExtraFields');

		},

		onShow : function() {
			// Image
			this.imageRegion.show(this.imageView);

			// Attachments
			this.attachmentsRegion.show(this.attachmentsView);

			// Galleries
			this.galleriesRegion.show(this.galleriesView);

			// Media
			this.mediaRegion.show(this.mediaView);

			// Extra fields
			this.extraFieldsRegion.show(this.extraFieldsView);
		}
	});
	return K2ViewItem;
});
