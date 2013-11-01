define(['text!layouts/galleries/list.html', 'text!layouts/galleries/row.html', 'widgets/widget', 'dispatcher'], function(listTemplate, rowTemplate, K2Widget, K2Dispatcher) {'use strict';

	var K2ViewGalleriesRow = Marionette.ItemView.extend({
		tagName : 'div',
		template : _.template(rowTemplate),
		events : {
			'click .appRemoveGallery' : 'removeGallery'
		},
		modelEvents : {
			'sync' : 'render'
		},
		onDomRefresh : function() {
			K2Widget.updateEvents(this.$el);
		},
		removeGallery : function(event) {
			event.preventDefault();
			this.model.destroy();
		}
	});

	var K2ViewGalleries = Marionette.CompositeView.extend({
		template : _.template(listTemplate),
		itemViewContainer : '#appGalleries',
		itemView : K2ViewGalleriesRow,
		events : {
			'click #appAddGallery' : 'addGallery'
		},
		initialize : function() {
			K2Dispatcher.on('galleries:delete', function() {
				_.each(this.collection.models, function(model) {
					model.destroy();
				});
			}, this);
		},
		addGallery : function(event) {
			event.preventDefault();
			this.collection.add({});
		}
	});
	return K2ViewGalleries;
});
