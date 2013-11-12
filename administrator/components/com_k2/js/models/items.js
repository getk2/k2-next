define(['backbone', 'model'], function(Backbone, K2Model) {'use strict';

	var K2ModelItem = K2Model.extend({

		defaults : {
			id : null,
			asset_id : null,
			title : null,
			catid : null,
			state : null,
			publish_up : null,
			publish_down : null,
			created : null,
			created_by : null,
			modified : null,
			modified_by : null,
			access : null,
			ordering : null,
			text : null,
			tagline : null,
			referenceType : null,
			referenceID : null,
			custom : null,
			video : null,
			hits : null,
			language : null,
			params : null
		},

		urlRoot : function() {
			return 'index.php?option=com_k2&task=items.sync&format=json'
		},
	});

	return K2ModelItem;

});
