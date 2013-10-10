'use strict';
define(['underscore', 'backbone', 'marionette', 'dispatcher', 'session'], function(_, Backbone, Marionette, K2Dispatcher, K2Session) {
	var K2Controller = Marionette.Controller.extend({

		// The available resources for request. Any other request returns a 404 error.
		resources : ['items', 'categories', 'tags', 'comments', 'users', 'extrafields', 'information', 'settings'],

		// Holds the current resource type.
		resource : 'items',

		// Holds the current model instance.
		model : null,

		// Holds the current collection instance.
		collection : null,

		// Initialize function
		initialize : function() {

			// Listener for add event.
			K2Dispatcher.on('app:controller:add', function() {
				this.edit();
			}, this);

			// Listener for edit event.
			K2Dispatcher.on('app:controller:edit', function(id) {
				this.edit(id);
			}, this);

			// Listener for save events.
			K2Dispatcher.on('app:controller:save', function(redirect) {
				this.save(redirect);
			}, this);

			// Listener for close event.
			K2Dispatcher.on('app:controller:close', function() {
				this.close();
			}, this);

			// Listener for toggle state event.
			K2Dispatcher.on('app:controller:toggleState', function(id, state) {
				this.toggleState(id, state);
			}, this);

			// Listener for filter event.
			K2Dispatcher.on('app:controller:filter', function(state, value) {
				this.filter(state, value);
			}, this);

			// Listener for delete event.
			K2Dispatcher.on('app:controller:batchDelete', function(rows) {
				this.batchDelete(rows);
			}, this);

			// Listener for batch toggle state event.
			K2Dispatcher.on('app:controller:batchToggleState', function(rows, state) {
				this.batchToggleState(rows, state);
			}, this);

			// Listener for save ordering
			K2Dispatcher.on('app:controller:saveOrder', function(keys, values, column) {
				this.saveOrder(keys, values, column);
			}, this);

			// Listener for updating the collection states
			K2Dispatcher.on('app:controller:setCollectionState', function(state, value) {
				this.collection.setState(state, value);
			}, this);

		},

		// Executes the request based on the URL.
		execute : function(url) {
			if (!url) {
				this.list(1);
			} else {
				var parts = url.split('/');
				this.resource = _.first(parts);
				if (_.indexOf(this.resources, this.resource) === -1) {
					this.enqueueMessage('error', l('K2_NOT_FOUND'));
				} else {
					if (parts.length === 1) {
						this.list(1);
					} else if (parts.length === 2 && parts[1] === 'add') {
						this.edit();
					} else if (parts.length === 3 && parts[1] === 'edit') {
						this.edit(_.last(parts));
					} else if (parts.length === 3 && parts[1] === 'page') {
						this.list(_.last(parts));
					} else {
						this.enqueueMessage('error', l('K2_NOT_FOUND'));
					}
				}
			}
		},

		// Proxy function for triggering the app:redirect event
		redirect : function(url, trigger) {
			K2Dispatcher.trigger('app:redirect', url, trigger);
		},

		// Proxy function triggering the app:redirect event
		enqueueMessage : function(type, text) {
			K2Dispatcher.trigger('app:message', type, text);
		},

		// Displays a listing page depending on the requested resource type
		list : function(page) {

			// Load the required files
			require(['collections/' + this.resource, 'views/' + this.resource + '/list', 'views/pagination', 'views/list'], _.bind(function(Collection, View, Pagination, Layout) {

				// Determine the page from the previous request
				if (!page && this.collection) {
					page = this.collection.getState('page');
				}

				// Ensure that we have a page number
				if (!page) {
					page = 1;
				}

				// Create the collection
				this.collection = new Collection();

				// Set the page
				this.collection.setState('page', page);

				// Fetch data from server
				this.collection.fetch({

					// Success callback
					success : _.bind(function() {

						// Create view
						var view = new View({
							collection : this.collection
						});

						// Get the pagination model
						var paginationModel = this.collection.getPagination();

						// Pass some data to the pagination model
						paginationModel.set('label', this.resource);
						paginationModel.set('link', this.resource);

						// Create the pagination view
						var pagination = new Pagination({
							model : paginationModel
						});

						// Create the layout
						var layout = new Layout();

						// Render the layout to the page
						K2Dispatcher.trigger('app:region:show', layout, 'content');

						// Render views to the layout
						layout.grid.show(view);
						layout.pagination.show(pagination);

						// Update the URL without triggering the router function
						this.redirect(this.resource + '/page/' + this.collection.getState('page'), false);

					}, this),
					error : _.bind(function(model, xhr, options) {
						this.enqueueMessage('error', xhr.responseText);
					}, this)
				});

			}, this));
		},

		// Displays a form page depending on the requested resource type
		edit : function(id) {

			// Load the required files
			require(['models/' + this.resource, 'views/' + this.resource + '/form'], _.bind(function(Model, View) {

				// Create the model
				this.model = new Model();

				// If an id is provided use it
				if (id) {
					this.model.set('id', id);
				}

				// Fetch the data from server
				this.model.fetch({

					// Success callback
					success : _.bind(function() {

						// Create the view
						var view = new View({
							model : this.model
						});

						// Render the view
						K2Dispatcher.trigger('app:region:show', view, 'content');

						// Determine the new URL
						var suffix = (id) ? '/edit/' + id : '/add';

						// Update the URL without triggering the router function
						this.redirect(this.resource + suffix, false);

					}, this),
					error : _.bind(function(model, xhr, options) {
						this.enqueueMessage('error', xhr.responseText);
					}, this)
				});

			}, this));
		},

		// Sabe function. Saves the model and redirects properly.
		save : function(redirect) {

			// Trigger the onBeforeSave event
			K2Dispatcher.trigger('app:controller:beforeSave');

			// Get the form variables
			var input = jQuery('.appEditForm').serializeArray();

			// Save
			this.model.save(null, {
				data : input,
				silent : true,
				success : _.bind(function(model) {
					if (redirect === 'list') {
						this.list();
					} else if (redirect === 'add') {
						this.edit();
					} else if (redirect === 'edit') {
						this.edit(this.model.get('id'));
					}
				}, this),
				error : _.bind(function(model, xhr, options) {
					this.enqueueMessage('error', xhr.responseText);
				}, this)
			});
		},

		// Close function. Checks in the row and redirects to list.
		close : function() {
			if (this.model.isNew() || !this.model.has('checked_out')) {
				this.list()
			} else {
				this.model.checkin({
					success : _.bind(function() {
						this.list();
					}, this),
					error : _.bind(function(model, xhr, options) {
						this.enqueueMessage('error', xhr.responseText);
					}, this)
				});
			}
		},

		// Toggle state function.
		toggleState : function(id, state) {
			var model = this.collection.get(id);
			model.toggleState(state, {
				success : _.bind(function() {
					this.list();
				}, this),
				error : _.bind(function(model, xhr, options) {
					this.enqueueMessage('error', xhr.responseText);
				}, this)
			});
		},

		saveOrder : function(keys, values, column) {
			this.collection.batch(keys, values, column, {
				success : _.bind(function() {
					this.resetCollection();
				}, this),
				error : _.bind(function(response) {
					this.enqueueMessage('error', response.responseText);
				}, this)
			});
		},

		// Destroy function. Deletes an array of rows and renders again the list.
		batchDelete : function(rows) {
			this.collection.remove(rows, {
				success : _.bind(function() {
					this.list();
				}, this),
				error : _.bind(function(model, xhr, options) {
					this.enqueueMessage('error', xhr.responseText);
				}, this)
			});
		},

		// Filter function. Updates the collection states depending on the filters and renders the list again.
		filter : function(state, value) {
			this.collection.setState(state, value);
			// Go to page 1 for new states except sorting and limit
			if (state !== 'sorting' && state !== 'limit' && state !== 'page') {
				this.collection.setState('page', 1);
			}
			if (state === 'sorting') {
				K2Session.set(this.resource + '.' + state, value);
			}
			this.resetCollection();
		},

		// Reset collection.
		resetCollection : function() {
			this.collection.fetch({
				reset : true,
				success : _.bind(function() {
					this.redirect(this.resource + '/page/' + this.collection.getState('page'), false);
				}, this),
				error : _.bind(function(model, xhr, options) {
					this.enqueueMessage('error', xhr.responseText);
				}, this)
			});
		},

		// Batch function. Updates the collection states depending on the filters and renders the list again.
		batchToggleState : function(rows, state) {
			this.collection.batchToggleState(rows, state, {
				success : _.bind(function() {
					this.list();
				}, this),
				error : _.bind(function(model, xhr, options) {
					this.enqueueMessage('error', xhr.responseText);
				}, this)
			});
		}
	});

	return K2Controller;
});
