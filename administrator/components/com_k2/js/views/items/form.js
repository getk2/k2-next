define(['dispatcher', 'widgets/widget', 'text!layouts/items/form.html', 'views/extrafields/widget', 'views/attachments/widget', 'views/galleries/widget', 'views/media/widget', 'views/image/widget', 'views/revisions/widget'], function(K2Dispatcher, K2Widget, template, K2ViewExtraFieldsWidget, K2ViewAttachmentsWidget, K2ViewGalleriesWidget, K2ViewMediaWidget, K2ViewImageWidget, K2ViewRevisionsWidget) {'use strict';
	// K2 item form view
	var K2ViewItem = Marionette.Layout.extend({

		// Template
		template : _.template(template),

		// Regions
		regions : {
			imageRegion : '[data-region="item-image"]',
			attachmentsRegion : '[data-region="item-attachments"]',
			galleriesRegion : '[data-region="item-galleries"]',
			mediaRegion : '[data-region="item-media"]',
			extraFieldsRegion : '[data-region="item-extra-fields"]',
			revisionsRegion : '[data-region="item-revisions"]'
		},

		// UI events
		events : {
			'change #catid' : 'updateCategory'
		},

		modelEvents : {
			'change' : 'render'
		},

		// Initialize
		initialize : function() {

			// Image view. First override the size with the one from the settings
			if (this.model.get('image')) {
				var images = this.model.get('images');
				this.model.set('image', images['admin']);
			}
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

			// Revisions
			this.revisionsView = new K2ViewRevisionsWidget({
				data : this.model.get('revisions'),
				parent : this
			});
			this.revisionsView.on('restore', _.bind(function(revision) {
				var data = revision.get('data');
				this.$('input[name="title"]').val(data.title);
				var form = this.model.getForm();
				if (form.has('text')) {
					var text = '';
					text += data.introtext;
					if (data.fulltext) {
						text += '<hr id="system-readmore" />' + data.fulltext;
					}
					K2Editor.setContent('text', text);
				} else {
					K2Editor.setContent('introtext', data.introtext);
					K2Editor.setContent('fulltext', data.fulltext);
				}
			}, this));

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
			var form = this.model.getForm();
			if (form.has('text')) {
				K2Editor.save('text');
			} else {
				K2Editor.save('introtext');
				K2Editor.save('fulltext');
			}

			// Validate extra fields
			var result = this.extraFieldsView.validate();

			return result;

		},

		// OnBeforeClose event ( Marionette.js build in event )
		onBeforeClose : function() {
			// Clean up uploaded files
			if (this.model.isNew()) {
				this.imageView.trigger('cleanup');
			}
			// Destroy the editor. This is required by TinyMCE in order to be able to re-initialize with out page refresh.
			if ( typeof (tinymce) != 'undefined') {
				tinymce.remove();
			}
		},

		updateCategory : function(event) {
			event.preventDefault();
			var value = this.$('#catid').val();
			// Extra fields
			this.extraFieldsView.trigger('filter', value);
		},

		onRender : function() {
			this.$('input[name="featured"]').val([this.model.get('featured')]);
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

			// Revisions
			this.revisionsRegion.show(this.revisionsView);
		}
	});
	return K2ViewItem;
});
