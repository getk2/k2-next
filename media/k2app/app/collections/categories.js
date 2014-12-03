define(['backbone', 'collection', 'models/categories'], function(Backbone, K2Collection, K2ModelCategories) {'use strict';
	var K2CollectionCategories = K2Collection.extend({
		model : K2ModelCategories,
		url : function() {
			return 'index.php?option=com_k2&task=categories.sync&format=json' + this.buildQuery();
		},
		buildTree : function() {
			// Rebuild the collection in tree way
			var remove = [];
			_.each(this.models, _.bind(function(model) {
				var children = this.where({
					parent_id : model.get('id')
				});
				model.set('children', children);
				_.each(children, function(child) {
					remove.push(child);
				});
			}, this));
			// Remove the rows we do not need anymore
			this.remove(remove);
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
