'use strict';
define(['underscore', 'backbone', 'marionette', 'dispatcher'], function(_, Backbone, Marionette, K2Dispatcher) {
	var K2Controller = Marionette.Controller.extend({

		// The available views. Any other request returns a 404 error.
		views : Array('items', 'categories', 'tags', 'comments', 'users', 'extrafields', 'information', 'settings'),

		// Holds the current view name.
		view : 'items',

		// Initialize function
		initialize : function() {

			// Listener for add event.
			K2Dispatcher.on('app:controller:add', function() {
				K2Dispatcher.trigger('app:redirect', this.view + '/add', true);
			}, this);

			// Listener for save events.
			K2Dispatcher.on('app:controller:save', function(redirect) {
				this.save(redirect);
			}, this);

			// Listener for close event.
			K2Dispatcher.on('app:controller:close', function(response) {
				this.close();
			}, this);

			// Listener for filter event.
			K2Dispatcher.on('app:controller:filter', function(state, value) {
				this.filter(state, value);
			}, this);

			// Listener for delete event.
			K2Dispatcher.on('app:controller:destroy', function(data) {
				this.destroy(data);
			}, this);

		},

		// Executes the list or form view based on the URL
		execute : function(url) {
			if (!url) {
				this.list(1);
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
					} else if (parts.length === 3 && parts[1] === 'page') {
						this.list(_.last(parts));
					} else {
						K2Dispatcher.trigger('app:error', 404);
					}
				}
			}
		},

		redirect : function(url, trigger) {
			K2Dispatcher.trigger('app:redirect', url, trigger);
		},

		// List function
		list : function(page) {
			require(['collections/' + this.view, 'views/' + this.view + '/list', 'views/pagination', 'views/list'], _.bind(function(Collection, View, Pagination, Layout) {
				var layout = new Layout;
				this.collection = new Collection;
				if (page) {
					this.collection.setState('page', page);
				}
				this.collection.fetch({
					success : _.bind(function() {

						// Render the layout
						K2Dispatcher.trigger('app:render', layout, 'content');

						// The list view
						var view = new View({
							collection : this.collection
						});

						// The pagination view
						this.model.set('label', this.view);
						this.model.set('link', this.view);
						var pagination = new Pagination({
							model : this.model
						});

						// Assign views to the layout
						layout.grid.show(view);
						layout.pagination.show(pagination);

					}, this)
				});

			}), this);
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
			var input = jQuery('.jwEditForm').serializeArray();
			this.model.save(null, {
				data : input,
				silent : true,
				success : _.bind((function(model) {
					if (redirect === 'list') {
						this.redirect(this.view + '/page/' + this.getPageNumber(), true);
					} else if (redirect === 'add') {
						this.redirect(this.view + '/edit/' + this.model.get('id'), false);
						this.redirect(this.view + '/add', true);
					} else if (redirect === 'edit') {
						this.model.fetch();
						this.redirect(this.view + '/edit/' + this.model.get('id'), false);
					}
				}), this),
				error : function(model, xhr, options) {
					alert(xhr.responseText);
				}
			});
		},

		close : function() {
			var url = this.view + '/page/' + this.getPageNumber();
			if (this.model.isNew()) {
				this.redirect(url, true)
			} else {
				this.model.checkout({
					success : _.bind(function() {
						this.redirect(url, true);
					}, this)
				});
			}
		},

		destroy : function(data) {
			var page = this.getPageNumber();
			this.collection.remove(data, {
				success : _.bind(function() {
					this.list();
				}, this)
			});
		},

		filter : function(state, value) {
			var url;
			this.collection.setState(state, value);
			this.collection.setState('page', 1);
			url = this.view + '/page/1';
			this.collection.fetch({
				reset : true,
				success : function() {
					K2Dispatcher.trigger('app:redirect', url, false);
				}
			});
		},

		getPageNumber : function() {
			return (this.collection === undefined) ? 1 : this.collection.getState('page');
		}
	});

	return K2Controller;
});
