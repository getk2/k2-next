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

		// Model events
		modelEvents : {
			'sync' : 'update'
		},

		// UI events
		events : {
			'change #catid' : 'updateExtraFields'
		},

		// Initialize
		initialize : function() {

			// Add a listener for the before save event
			K2Dispatcher.on('app:controller:beforeSave', function() {
				this.onBeforeSave();
			}, this);

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
				K2Dispatcher.trigger('image:delete');
				K2Dispatcher.trigger('attachments:delete');
				K2Dispatcher.trigger('galleries:delete');
				K2Dispatcher.trigger('media:delete');
			}
		},

		update : function() {

			this.render();

			// Determine current itemId
			var itemId = this.model.get('id') || this.model.get('tmpId');

			// Image
			this.imageRegion.show(new K2ViewImageWidget({
				data : this.model,
				itemId : itemId,
				type : 'item'
			}));

			// Attachments
			this.attachmentsRegion.show(new K2ViewAttachmentsWidget({
				data : this.model.get('attachments'),
				itemId : itemId
			}));

			// Galleries
			this.galleriesRegion.show(new K2ViewGalleriesWidget({
				data : this.model.get('galleries'),
				itemId : itemId
			}));

			// Media
			this.mediaRegion.show(new K2ViewMediaWidget({
				data : this.model.get('media'),
				itemId : itemId
			}));
			
			// Extra fields
			this.extraFieldsRegion.show(new K2ViewExtraFieldsWidget({
				data : this.model.getForm().get('extraFields'),
				filterId : this.model.get('catid'),
				resourceId : this.model.get('id'),
				scope : 'item'
			}));

		},

		// onRender event
		onRender : function() {

			// Update radio buttons value
			this.$el.find('input[name="published"]').val([this.model.get('published')]);
			this.$el.find('input[name="featured"]').val([this.model.get('featured')]);
		},

		updateExtraFields : function(event) {
			event.preventDefault();
			K2Dispatcher.trigger('extrafields:update', this.$el.find('#catid').val());
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

		}
	});
	return K2ViewItem;
});
