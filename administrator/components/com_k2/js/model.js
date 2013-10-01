'use strict';
define(['underscore', 'backbone', 'marionette', 'dispatcher'], function(_, Backbone, Marionette, K2Dispatcher) {

	var K2Model = Backbone.Model.extend({

		initialize : function() {
			this.form = new Backbone.Model;
		},

		parse : function(resp, options) {
			// If response is null then return. This is the case for POST requests
			if (resp === null) {
				return resp;
			}

			// If the response object does not contain a row object then probably it's a flat model and we need to return it.
			if (resp.row === undefined) {
				return resp;
			}

			// Attach the form object to the model in order to be available later.
			if (resp.form !== undefined) {
				this.setForm(resp.form);
			}

			// Trigger the update event to notify the generic application layouts for changes.
			K2Dispatcher.trigger('app:update', resp);

			// Return the row
			return resp.row;

		},

		url : function() {
			var base = _.result(this, 'urlRoot') || _.result(this.collection, 'url') || urlError();
			base += '&id=' + this.get('id');
			return base;
		},

		setForm : function(form) {
			this.form.set(form);
		},

		getForm : function() {
			return this.form;
		},

		checkin : function(options) {
			var params = {
				silent : true,
				wait : true,
				patch : true
			}
			_.extend(params, options);
			this.save({
				'checked_out' : 0
			}, params);
		},

		toggleState : function(state, options) {
			var params = {
				silent : true,
				wait : true,
				patch : true
			}
			_.extend(params, options);
			var attrs = {};
			attrs[state] = (this.get(state) > 0 ) ? 0 : 1;
			this.save(attrs, params);
		}
	});

	return K2Model;

});
