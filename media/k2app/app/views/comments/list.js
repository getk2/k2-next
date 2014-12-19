define(['marionette', 'text!templates/comments/list.html', 'text!templates/comments/row.html', 'dispatcher'], function(Marionette, list, row, K2Dispatcher) {
	'use strict';
	var K2ViewCommentsRow = Marionette.ItemView.extend({
		tagName : 'ul',
		template : _.template(row),
		events : {
			'click a[data-action="edit"]' : 'edit',
			'click a[data-action="reportUser"]' : 'reportUser'
		},
		edit : function(event) {
			event.preventDefault();
			K2Dispatcher.trigger('app:controller:edit', this.model.get('id'));
		},
		reportUser : function(event) {
			event.preventDefault();
			var data = 'id=' + this.model.get('userId') + '&' + K2SessionToken + '=1';
			jQuery.post(K2BasePath + '/index.php?option=com_k2&task=users.report&format=json', data).success(function(data) {
				K2Dispatcher.trigger('app:messages:add', 'message', l('K2_USER_BLOCKED'));
			}).error(function(data) {
				K2Dispatcher.trigger('app:messages:add', 'error', data.responseText);
			});
		},
	});
	var K2ViewComments = Marionette.CompositeView.extend({
		template : _.template(list),
		childViewContainer : '[data-region="list"]',
		childView : K2ViewCommentsRow
	});
	return K2ViewComments;
});
