define(['marionette', 'text!layouts/header.html', 'dispatcher', 'widgets/widget'], function(Marionette, template, K2Dispatcher, K2Widget) {'use strict';

	var K2ViewHeader = Marionette.ItemView.extend({

		template : _.template(template),

		events : {
			'click [data-action="add"]' : 'add',
			'click [data-action="save"]' : 'save',
			'click [data-action="save-and-new"]' : 'saveAndNew',
			'click [data-action="save-and-close"]' : 'saveAndClose',
			'click [data-action="close"]' : 'close',
			'click [data-action="import"]' : 'import'
		},

		modelEvents : {
			'change' : 'render'
		},

		initialize : function() {
			K2Dispatcher.on('app:update:header', function(response) {
				this.model.set({
					'menu' : response.menu.primary,
					'actions' : response.actions
				});
			}, this);
		},

		onDomRefresh : function() {
			K2Widget.updateEvents(this.$el);
		},

		add : function(event) {
			event.preventDefault();
			K2Dispatcher.trigger('app:controller:add');
		},

		save : function(event) {
			event.preventDefault();
			var el = jQuery(event.currentTarget);
			var resource = el.data('resource');
			if (resource === 'settings') {
				K2Dispatcher.trigger('app:controller:save', 'custom', 'settings');
			} else {
				K2Dispatcher.trigger('app:controller:save', 'edit');
			}
		},

		saveAndNew : function(event) {
			event.preventDefault();
			K2Dispatcher.trigger('app:controller:save', 'add');
		},

		saveAndClose : function(event) {
			event.preventDefault();
			K2Dispatcher.trigger('app:controller:save', 'list');
		},

		close : function(event) {
			event.preventDefault();
			K2Dispatcher.trigger('app:controller:close');
		},

		import : function(event) {
			event.preventDefault();
			if (confirm(l('K2_WARNING_YOU_ARE_ABOUT_TO_IMPORT_ALL_SECTIONS_CATEGORIES_AND_ARTICLES_FROM_JOOMLAS_CORE_CONTENT_COMPONENT_COM_CONTENT_INTO_K2_IF_THIS_IS_THE_FIRST_TIME_YOU_IMPORT_CONTENT_TO_K2_AND_YOUR_SITE_HAS_MORE_THAN_A_FEW_THOUSAND_ARTICLES_THE_PROCESS_MAY_TAKE_A_FEW_MINUTES_IF_YOU_HAVE_EXECUTED_THIS_OPERATION_BEFORE_DUPLICATE_CONTENT_MAY_BE_PRODUCED'))) {
				K2Dispatcher.trigger('app:controller:import');
			}
		}
	});

	return K2ViewHeader;
});
