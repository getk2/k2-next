'use strict';
define(['underscore', 'backbone', 'marionette', 'dispatcher'], function(_, Backbone, Marionette, K2Dispatcher) {
	var K2Controller = Marionette.Controller.extend({

		views : Array('items', 'categories', 'tags', 'comments', 'users', 'extrafields', 'information', 'settings'),
		view : 'items',
		rows : [],
		row : {},

		initialize : function() {

			// Listener for save events.
			// Triggers the save function on this controller to perform the save operation.
			K2Dispatcher.on('app:controller:save', function(redirect) {
				this.save(redirect);
			}, this);

			// Listener for close event.
			// Triggers the close function on this controller to perform the chekin operation and redirect to list.
			K2Dispatcher.on('app:controller:close', function(response) {
				this.close();
			}, this);

			// Listener for rows event.
			// It renders the list after a POST request.
			// We use this to avoid the extra HTTP request for rendering the list.
			// This is triggered when the response of the POST request is parsed by the model.
			K2Dispatcher.on('app:controller:list', function(response) {
				this.list(response.rows);
				K2Dispatcher.trigger('app:redirect', this.view, false);
			}, this);

			// Listener for row event
			K2Dispatcher.on('app:controller:edit', function(response) {
				this.edit(null, response.row);
			}, this);

		},

		execute : function(url) {
			if (!url) {
				this.list();
			} else {
				var parts = url.split('/');
				this.view = _.first(parts);
				if (_.indexOf(this.views, this.view) === -1) {
					K2Dispatcher.trigger('app:error', 404);
				} else {
					if (parts.length === 1) {
						this.list();
					} else if (parts.length === 2 && parts[1] === 'add') {
						this.edit(null);
					} else if (parts.length === 3 && parts[1] === 'edit') {
						this.edit(_.last(parts));
					} else {
						K2Dispatcher.trigger('app:error', 404);
					}
				}
			}
		},

		list : function(rows) {
			var self = this;
			require(['collections/' + this.view, 'views/' + this.view + '/list'], function(Collection, View) {
				self.collection = new Collection;
				if (rows) {
					self.collection.reset(rows);
					K2Dispatcher.trigger('app:render', new View({
						collection : self.collection
					}));
				} else {
					self.collection.fetch({
						success : function() {
							K2Dispatcher.trigger('app:render', new View({
								collection : self.collection
							}));
						}
					});
				}
			});
		},

		edit : function(id, row) {
			var self = this;
			require(['models/' + this.view, 'views/' + this.view + '/form'], function(Model, View) {

				// Create the model
				self.model = new Model;
				if (row) {
					self.model.reset(row);
					// Render the view
					K2Dispatcher.trigger('app:render', new View({
						model : self.model
					}));
				} else {
					// If an id is provided use it
					if (id) {
						self.model.set('id', id);
					}
					// Fetch the data from server
					self.model.fetch({
						success : function() {
							// Render the view
							K2Dispatcher.trigger('app:render', new View({
								model : self.model
							}));
						}
					});
				}

			});
		},

		save : function(redirect) {
			var data = jQuery('.jwEditForm').serializeArray();
			data.push({
				'name' : K2SessionToken,
				'value' : 1
			});
			data.push({
				'name' : '_redirect',
				'value' : redirect
			});
			this.model.save(null, {
				data : data
			});
		},

		destroy : function() {
			var data = [];
			data.push({
				'name' : 'id',
				'value' : this.model.get('id')
			});
			data.push({
				'name' : K2SessionToken,
				'value' : 1
			});
			this.model.destroy({
				data : data
			});
		},

		close : function() {

			var data = [];
			data.push({
				'name' : K2SessionToken,
				'value' : 1
			});
			data.push({
				'name' : '_redirect',
				'value' : 'list'
			});
			var id = [this.model.get('id')];
			data.push({
				'name' : 'id[]',
				'value' : id
			});
			data.push({
				'name' : 'states[checked_out]',
				'value' : 0
			});
			var self = this;
			this.model.save(null, {
				patch : true,
				data : data
			});

		}
	});

	return K2Controller;
});
