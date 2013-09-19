'use strict';
define(['underscore', 'backbone', 'marionette', 'dispatcher'], function(_, Backbone, Marionette, K2Dispatcher) {

	var K2Model = Backbone.Model.extend({
		parse : function(resp, options) {
			if (resp.row) {
				K2Dispatcher.trigger('app:menu', resp.menu);
				K2Dispatcher.trigger('app:form', resp.form);
				return resp.row;
			} else {
				return resp;
			}
		},

		url : function() {
			var base = _.result(this, 'urlRoot') || _.result(this.collection, 'url') || urlError();
			base += '&id=' + this.get('id');
			return base;
		},

		sync : function() {
			var type = arguments[0];
			var _method = false;
			if (type === 'create') {
				_method = 'POST';
			} else if (type === 'update') {
				_method = 'PUT';
			} else if (type === 'delete') {
				_method = 'DELETE';
			}
			if (_method) {
				arguments[2].data.push({
					'name' : '_method',
					'value' : _method
				});
			}
			return Backbone.sync.apply(this, arguments);
		}
		
	});

	return K2Model;

});
