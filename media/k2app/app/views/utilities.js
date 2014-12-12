define(['marionette', 'text!templates/utilities.html', 'dispatcher'], function(Marionette, template, K2Dispatcher) {'use strict';

	var K2ViewUtilities = Marionette.ItemView.extend({
		template : _.template(template),
		events : {
			'click [data-action="import"]' : 'import',
			'click [data-action="delete-orphan-tags"]' : 'deleteOrphanTags',
			'click [data-action="delete-unpublished-comments"]' : 'deleteUnpublishedComments'
		},
		import : function(event) {
			event.preventDefault();
			if (confirm(l('K2_WARNING_YOU_ARE_ABOUT_TO_IMPORT_ALL_SECTIONS_CATEGORIES_AND_ARTICLES_FROM_JOOMLAS_CORE_CONTENT_COMPONENT_COM_CONTENT_INTO_K2_IF_THIS_IS_THE_FIRST_TIME_YOU_IMPORT_CONTENT_TO_K2_AND_YOUR_SITE_HAS_MORE_THAN_A_FEW_THOUSAND_ARTICLES_THE_PROCESS_MAY_TAKE_A_FEW_MINUTES_IF_YOU_HAVE_EXECUTED_THIS_OPERATION_BEFORE_DUPLICATE_CONTENT_MAY_BE_PRODUCED'))) {
				window.open(K2BasePath + '/index.php?option=com_k2&view=import&tmpl=component', 'K2', 'width=640,height=480');
			}
		},
		deleteOrphanTags : function(event) {
			event.preventDefault();
			var button = jQuery(event.currentTarget);
			button.prop('disabled', true);
			jQuery.post('index.php?option=com_k2&task=tags.deleteOrphans&format=json', K2SessionToken + '=1', function(response) {
				K2Dispatcher.trigger('app:messages:set', response);
				button.prop('disabled', false);
			});
		},
		deleteUnpublishedComments : function(event) {
			event.preventDefault();
			if (confirm(l('K2_THIS_WILL_PERMANENTLY_DELETE_ALL_UNPUBLISHED_COMMENTS_ARE_YOU_SURE'))) {
				var button = jQuery(event.currentTarget);
				button.prop('disabled', true);
				jQuery.post('index.php?option=com_k2&task=comments.deleteUnpublished&format=json', K2SessionToken + '=1', function(response) {
					K2Dispatcher.trigger('app:messages:set', response);
					button.prop('disabled', false);
				});
			}
		},
		onShow : function() {
			K2Dispatcher.trigger('app:menu:active', 'utilities');
		}
	});

	return K2ViewUtilities;
});
