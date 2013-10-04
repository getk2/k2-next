'use strict';
define(['marionette', 'text!layouts/items/form.html', 'dispatcher', 'collections/tags'], function(Marionette, template, K2Dispatcher, K2CollectionTags) {
	var K2ViewItem = Marionette.ItemView.extend({
		template : _.template(template),
		modelEvents : {
			'change' : 'render'
		},
		initialize : function() {
			K2Dispatcher.on('app:controller:beforeSave', function() {
				this.onBeforeSave();
			}, this);
			this.tags = new K2CollectionTags();
		},
		serializeData : function() {
			var data = {
				'row' : this.model.toJSON(),
				'form' : this.model.getForm().toJSON()
			};
			return data;
		},
		searchTags : function(event) {
			var el = jQuery(event.currentTarget);
			this.tags.setState('search', el.val());
			this.tags.fetch();
		},
		onBeforeSave : function() {
			K2Editor.save('text');
		},
		onDomRefresh : function() {
			// Initialize the editor
			K2Editor.init();

			// Tags auto complete
			var url = this.tags.url();
			var el = this.$el.find(this.$el.find('#tags'));
			require(['widgets/select2/select2', 'css!widgets/select2/select2.css'], function() {
				el.select2({
					tags : [],
					width : '300px',
					placeholder : l('K2_ENTER_SOME_TAGS'),
					createSearchChoice : function(term, data) {
						if (jQuery(data).filter(function() {
							return this.text.toLowerCase !== term.toLowerCase();
						}).length === 0) {
							return {
								id : term,
								text : term
							};
						}
					},
					ajax : {
						url : url,
						dataType : 'json',
						quietMillis : 100,
						data : function(term, page) {
							return {
								search : term,
								sorting : 'name',
								limit : 50,
								page : page,
							};
						},
						results : function(data, page) {
							var tags = [];
							jQuery.each(data.rows, function(index, row) {
								var tag = {}
								tags.push({
									id : row.name,
									text : row.name
								});
							});
							var more = (page * 50) < data.pagination.total;
							return {
								results : tags,
								more : more
							};
						}
					}
				});
			});

			// Restore Joomla! modal events
			if ( typeof (SqueezeBox) !== 'undefined') {
				SqueezeBox.initialize({});
				SqueezeBox.assign($$('a.modal-button'), {
					parse : 'rel'
				});
			}
		}
	});
	return K2ViewItem;
});
