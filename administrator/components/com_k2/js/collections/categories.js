define(['backbone', 'collection', 'models/categories'], function(Backbone, K2Collection, K2ModelCategories) {'use strict';
	var K2CollectionCategories = K2Collection.extend({
		model : K2ModelCategories,
		url : function() {
			return 'index.php?option=com_k2&task=categories.sync&format=json' + this.buildQuery();
		},
		moveByReference : function(reference_id, location, id) {
			var data = [{
				name : 'id',
				value : id
			}, {
				name : 'location',
				value : location
			}, {
				name : 'reference_id',
				value : reference_id
			}, {
				name : K2SessionToken,
				value : 1
			}];
			jQuery.ajax({
				dataType : 'json',
				type : 'POST',
				url : 'index.php?option=com_k2&task=categories.saveOrder&format=json',
				data : data
			}).done(function(data, status, xhr) {

			}).fail(function(xhr, status, error) {

			});
		}
	});
	return K2CollectionCategories;
});
