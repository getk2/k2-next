'use strict';
define(['marionette', 'text!layouts/tags/list.html', 'text!layouts/tags/row.html', 'dispatcher'], function(Marionette, list, row, K2Dispatcher) {
	var K2ViewTagsRow = Marionette.ItemView.extend({
		tagName : 'tr',
		template : _.template(row),
		events : {
			'click .jwInlineEdit' : 'edit',
			'blur .jwInlineEdit' : 'save',
		},
		edit : function(event) {
			var el = jQuery(event.currentTarget);
			el.prop('contenteditable', true).focus();
		},
		save : function(event) {
			var el = jQuery(event.currentTarget);
			el.prop('contenteditable', false);
			var value = el.text();
			this.model.save({name:value});
		}
	});
	var K2ViewTags = Marionette.CompositeView.extend({
		template : _.template(list),
		itemViewContainer : 'tbody',
		itemView : K2ViewTagsRow
	});
	return K2ViewTags;
});
