'use strict';
define(['underscore', 'backbone', 'marionette', 'dispatcher'], function(_, Backbone, Marionette, K2Dispatcher) {
	var K2Controller = Marionette.Controller.extend({
		
		views : Array('items', 'categories', 'tags', 'comments', 'users', 'extrafields', 'information', 'settings'),
		view : 'items',

		initialize : function() {

			// Listener for save events
			K2Dispatcher.on('app:controller:save', function(redirect) {
				this.save(redirect);
			}, this);
			
			// Listener for close event
			K2Dispatcher.on('app:controller:close', function(redirect) {
				this.close(redirect);
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

		list : function() {
			require(['collections/' + this.view, 'views/' + this.view + '/list'], function(Collection, View) {
				var collection = new Collection;
				collection.fetch({
					success : function() {
						K2Dispatcher.trigger('app:render', new View({
							collection : collection
						}));
					}
				});
			});
		},

		edit : function(id) {
			var self = this;
			require(['models/' + this.view, 'views/' + this.view + '/form'], function(Model, View) {
				// @TODO: Implement checkin
				self.model = new Model;
				if (id) {
					self.model.set('id', id);
				}
				self.model.fetch({
					success : function() {
						K2Dispatcher.trigger('app:render', new View({
							model : self.model
						}));
					}
				});
			});
		},

		save : function(redirect) {
			// This is ugly but we need to do it since we want to keep the traditional Joomla! save operation and add the session token.
			var data = jQuery('.jwEditForm').serializeArray();
			data.push({'name' : K2SessionToken, 'value' : 1});
			this.model.save(null, {
				data : data,
				success : this.onAfterSave(redirect)
			});

		},
		
		destroy : function() {
			var data = [];
			data.push({'name' : 'id', 'value' : this.model.get('id')});
			data.push({'name' : K2SessionToken, 'value' : 1});
			this.model.destroy({data:data});
		},
		
		close : function() {
			// @TODO: Implement checkout
			this.onAfterSave('list');
		},

		onAfterSave : function(redirect) {
			var url;
			switch(redirect) {
				case 'add':
					url = this.view + '/add';
					break;
				case 'edit':
					url = this.view + '/edit/' + this.model.get('id');
					break;
				case 'list' :
					url = this.view;
					break;
			}
			K2Dispatcher.trigger('app:redirect', url);
		}
	});

	return K2Controller;
});
