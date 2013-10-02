'use strict';
define(['marionette', 'text!layouts/items/form.html', 'dispatcher', 'collections/tags'], function(Marionette, template, K2Dispatcher, K2CollectionTags) {
	var K2ViewItem = Marionette.ItemView.extend({
		template : _.template(template),
		modelEvents : {
			'change' : 'render'
		},
		events : {
			'keypress #tags' : 'searchTags'
		},
		initialize : function() {
			K2Dispatcher.on('app:controller:beforeSave', function() {
				this.onBeforeSave();
			}, this);
			this.tags = new K2CollectionTags();
		},
		serializeData : function() {
			var data = {
				'row' : this.model.toJSON(),
				'form' : this.model.getForm().toJSON()
			};
			return data;
		},
		searchTags : function(event) {
			var el = jQuery(event.currentTarget);
			this.tags.setState('search', el.val());
			this.tags.fetch();
		},
		onBeforeSave : function() {
			K2Editor.save('text');
		},
		onDomRefresh : function() {
			K2Editor.init();
			if (typeof(SqueezeBox) !== 'undefined') {
				SqueezeBox.initialize({});
				SqueezeBox.assign($$('a.modal-button'), {
					parse : 'rel'
				});
			}
		}
	});
	return K2ViewItem;
});
