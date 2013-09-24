'use strict';
define(['backbone', 'model', 'dispatcher'], function(Backbone, K2Model, K2Dispatcher) {

	var K2Collection = Backbone.Collection.extend({

		initialize : function() {
			this.states = new Backbone.Model;
			this.pagination = new Backbone.Model;
		},

		parse : function(resp, options) {

			// If response is null then return. This occurs on POST requests.
			if (resp === null) {
				return resp;
			}

			if (resp.rows === undefined) {
				return resp;
			}

			// Attach the pagination object to the collection in order to be available later.
			if (resp.pagination !== undefined) {
				this.setPagination(resp.pagination);
			}

			// Trigger the update event to notify the application.
			K2Dispatcher.trigger('app:update', resp);

			// Return the rows
			return resp.rows;
		},

		setPagination : function(pagination) {
			this.pagination.set(pagination);
		},

		getPagination : function() {
			return this.pagination;
		},

		setState : function(state, value) {
			this.states.set(state, value);
		},

		getState : function(state) {
			return this.states.get(state);
		},

		filter : function(elements) {
			var self = this;
			elements.each(function() {
				var el = jQuery(this);
				self.setState(el.attr('name'), el.val());
			});
			this.fetch({
				reset : true
			});
		},

		batch : function(models, method, states) {
			var data = {
				_method : method,
				models : new Array,
				states : new Array
			};
			_.each(models, function(model) {
				data['models'].push(JSON.stringify(model));
			});
			_.each(states, function(state) {
				data['states'].push(JSON.stringify(state));
			});
			var self = this;
			jQuery.ajax({
				type : 'POST',
				url : self.url() + '&' + K2SessionToken + '=1',
				data : data,
				success : function(response) {
					self.parse(response);
					self.reset(response.rows);
				},
				dataType : 'json'
			});
		},

		buildQuery : function() {
			var query = '';
			_.each(this.states.attributes, function(value, state) {
				query += '&' + state + '=' + value;
			});
			return query;
		}
	});

	return K2Collection;

});
