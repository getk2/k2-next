define(['marionette', 'text!layouts/users/form.html', 'dispatcher', 'widgets/widget', 'views/image/widget', 'views/extrafields/widget'], function(Marionette, template, K2Dispatcher, K2Widget, K2ViewImageWidget, K2ViewExtraFieldsWidget) {'use strict';
	var K2ViewUser = Marionette.Layout.extend({
		template : _.template(template),
		// Regions
		regions : {
			imageRegion : '#appUserImage',
			extraFieldsRegion : '#appUserExtraFields'
		},
		modelEvents : {
			'change' : 'render'
		},
		initialize : function() {
			
			// Add a listener for the before save event
			K2Dispatcher.on('app:controller:beforeSave', function() {
				this.onBeforeSave();
			}, this);
			
			// Image
			this.imageView = new K2ViewImageWidget({
				row : this.model,
				type : 'user'
			});
			// Extra fields
			this.extraFieldsView = new K2ViewExtraFieldsWidget({
				data : this.model.getForm().get('extraFields'),
				resourceId : this.model.get('id'),
				filterId : this.model.get('id'),
				scope : 'user'
			});

		},
		serializeData : function() {
			var data = {
				'row' : this.model.toJSON(),
				'form' : this.model.getForm().toJSON()
			};
			return data;
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
			// Extra fields
			this.extraFieldsRegion.show(this.extraFieldsView);
		},
		// OnBeforeSave event
		onBeforeSave : function() {
			// Update form from editor contents
			K2Editor.save('description');
		}
	});
	return K2ViewUser;
});
