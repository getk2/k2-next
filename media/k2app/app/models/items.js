define(['backbone', 'model', 'dispatcher'], function(Backbone, K2Model, K2Dispatcher) {'use strict';

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
			return 'index.php?option=com_k2&task=items.sync&format=json';
		},

		resetHits : function(counter) {
			var data = [{
				name : 'id',
				value : this.get('id')
			}, {
				name : K2SessionToken,
				value : 1
			}];
			jQuery.ajax({
				dataType : 'json',
				type : 'POST',
				url : 'index.php?option=com_k2&task=items.resetHits&format=json',
				data : data
			}).done(function(xhr, status, error) {
				K2Dispatcher.trigger('app:messages:add', 'message', l('K2_SUCCESSFULLY_RESET_ITEM_HITS'));
				counter.text(0);
			}).fail(function(xhr, status, error) {
				K2Dispatcher.trigger('app:messages:add', 'error', xhr.responseText);
			});
		}
	});

	return K2ModelItem;

});
