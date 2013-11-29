define(['backbone', 'model', 'dispatcher'], function(Backbone, K2Model, K2Dispatcher) {'use strict';

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

			// Update states from server in order to be available as variables when they have not set by the client
			if (resp.states !== undefined) {
				_.each(resp.states, _.bind(function(value, state) {
					this.setState(state, value);
				}, this));
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

		destroy : function(rows, options) {
			options.data = rows;
			var xhr = this.sync('delete', this, options);
			return xhr;
		},

		batch : function(keys, values, state, options) {
			options || ( options = {});
			options.data || (options.data = []);
			_.each(keys, function(key) {
				options.data.push({
					'name' : 'id[]',
					'value' : key
				});
			});
			_.each(values, function(value) {
				options.data.push({
					'name' : 'states[' + state + '][]',
					'value' : value
				});
			});
			var xhr = this.sync('patch', this, options);
			return xhr;
		},
		
		multibatch : function(keys, states, options) {
			options || ( options = {});
			options.data || (options.data = []);
			_.each(keys, function(key) {
				options.data.push({
					'name' : 'id[]',
					'value' : key
				});
			});
			_.each(states, function(value, state) {
				options.data.push({
					'name' : 'states[' + state + ']',
					'value' : value
				});
			});
			var xhr = this.sync('patch', this, options);
			return xhr;
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
