'use strict';
define(['marionette', 'text!layouts/items/form.html', 'dispatcher'], function(Marionette, template, K2Dispatcher) {
	var K2ViewItem = Marionette.ItemView.extend({
		initialize : function() {
			K2Dispatcher.on('app:controller:beforeSave', function() {
				this.onBeforeSave();
			}, this);
		},
		template : _.template(template),
		modelEvents : {
			'change' : 'render'
		},
		serializeData : function() {
			var data = {
				'row' : this.model.toJSON(),
				'form' : this.model.getForm().toJSON()
			};
			return data;
		},
		onBeforeSave : function() {
			K2Editor.save('text');
		},
		onDomRefresh : function() {
			K2Editor.init();
		}
	});
	return K2ViewItem;
});
