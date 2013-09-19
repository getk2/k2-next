'use strict';
define(['underscore', 'backbone', 'marionette', 'dispatcher'], function(_, Backbone, Marionette, K2Dispatcher) {

	var K2Model = Backbone.Model.extend({
		parse : function(resp, options) {

			if ( resp.menu !== undefined) {
				K2Dispatcher.trigger('app:set:menu', resp.menu);
			}

			if ( resp.form !== undefined) {
				K2Dispatcher.trigger('app:set:form', resp.form);
			}

			if ( resp.redirect !== undefined) {
				K2Dispatcher.trigger('app:controller:redirect:' + resp.redirect, resp);
			}

			if (resp.row !== undefined) {
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
			} else if (type === 'patch') {
				_method = 'PATCH';
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
