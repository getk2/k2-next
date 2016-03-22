define(['marionette', 'dispatcher', 'text!templates/media/manager.html', 'jqueryui'], function(Marionette, K2Dispatcher, template) {
	'use strict';
	var K2ViewMediaManager = Marionette.ItemView.extend({
		template : _.template(template),
		onRender : function() {
			var requirements = ['elfinder', 'css!//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/smoothness/jquery-ui.css', 'css!elfinderTheme', 'css!elfinderStyle'];
			if (K2MediaManagerLanguage !== 'en') {
				requirements.push('../vendor/elfinder/js/i18n/elfinder.' + K2MediaManagerLanguage);
			}
			require(requirements, _.bind(function() {
				var callback = this.options.callback;
				var modal = this.options.modal;
				var options = {
					url : 'index.php?option=com_k2&task=media.connector&format=json',
					useBrowserHistory : false,
					lang : K2MediaManagerLanguage
				};
				if (modal) {
					options.getFileCallback = function(data) {
						K2Dispatcher.trigger(callback, data.path);
						if (modal) {
							jQuery.magnificPopup.close();
						}
					};
				}
				this.$el.elfinder(options);
			}, this));
		},
		onShow : function() {
			K2Dispatcher.trigger('app:menu:active', 'media');
			if (this.options.modal) {
				require(['magnific', 'css!magnificStyle'], _.bind(function() {
					jQuery.magnificPopup.open({
						alignTop : false,
						closeBtnInside : true,
						items : {
							src : this.el,
							type : 'inline'
						}
					});
				}, this));
			}
		}
	});
	return K2ViewMediaManager;
});
