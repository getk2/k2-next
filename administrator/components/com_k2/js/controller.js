'use strict';
define(['underscore', 'backbone', 'marionette', 'dispatcher'], function(_, Backbone, Marionette, K2Dispatcher) {
	var K2Controller = Marionette.Controller.extend({

		// The available views. Any other request returns a 404 error.
		views : Array('items', 'categories', 'tags', 'comments', 'users', 'extrafields', 'information', 'settings'),

		// Holds the current view name.
		view : 'items',

		// Initialize function
		initialize : function() {
			
			// Listener for add events.
			// Redirects to add page.
			K2Dispatcher.on('app:controller:add', function() {
				K2Dispatcher.trigger('app:redirect', this.view + '/add', true);
			}, this);

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

		},

		// Executes the list or form view based on the URL
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
						this.edit();
					} else if (parts.length === 3 && parts[1] === 'edit') {
						this.edit(_.last(parts));
					} else {
						K2Dispatcher.trigger('app:error', 404);
					}
				}
			}
		},

		// List function
		list : function() {
			var self = this;
			require(['collections/' + this.view, 'views/' + this.view + '/list'], function(Collection, View, Subheader) {
				self.collection = new Collection;
				self.collection.fetch({
					success : function() {
						K2Dispatcher.trigger('app:render', new View({
							collection : self.collection
						}), 'content');
					}
				});

			});
		},

		edit : function(id) {
			var self = this;
			require(['models/' + this.view, 'views/' + this.view + '/form'], function(Model, View) {

				// Create the model
				self.model = new Model;

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
						}), 'content');
					}
				});

			});
		},

		save : function(redirect) {
			var view = this.view, model = this.model, isNew = this.model.isNew(), data = jQuery('.jwEditForm').serializeArray();
			model.save(null, {
				data : data,
				silent : true,
				success : function(model) {
					if (redirect === 'list') {
						K2Dispatcher.trigger('app:redirect', view, true);
					} else if (redirect === 'add') {
						K2Dispatcher.trigger('app:redirect', view + '/edit/' + model.get('id'), false);
						K2Dispatcher.trigger('app:redirect', view + '/add', true);
					} else if (redirect === 'edit') {
						model.fetch();
						K2Dispatcher.trigger('app:redirect', view + '/edit/' + model.get('id'), false);
					}
				},
				error : function(model, xhr, options) {
					alert(xhr.responseText);
				}
			});
		},

		destroy : function() {
			var data = [], model = this.model;
			data.push({
				'name' : 'id',
				'value' : this.model.get('id')
			});
			model.destroy({
				data : data,
				silent : true
			});
		},

		close : function() {
			var view = this.view, model = this.model, id = this.model.get('id'), data = [];

			if (model.isNew()) {
				K2Dispatcher.trigger('app:redirect', view, true);
			} else {
				data.push({
					'name' : 'id[]',
					'value' : id
				});
				data.push({
					'name' : 'states[checked_out]',
					'value' : 0
				});
				model.save(null, {
					patch : true,
					silent : true,
					data : data,
					success : function() {
						K2Dispatcher.trigger('app:redirect', view, true);
					}
				});
			}
		}
	});

	return K2Controller;
});
