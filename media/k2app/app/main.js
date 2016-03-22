'use strict';
define('jquery', [], function() {
	return window.jQuery;
});
require.config({
	//baseUrl: 'components/com_k2/js', Uncomment this if we build the app into a single file. This way we will have the basic app files loaded in one file while the third-party files will still get loaded during runtime
	urlArgs : function() {
		return '?t=' + Date.now();
	},
	waitSeconds : 10,
	paths : {
		'backbone' : '../vendor/backbone/backbone-min',
		'collapse' : '../vendor/datetimepicker/collapse',
		'datetimepicker' : '../vendor/datetimepicker/bootstrap-datetimepicker.min',
		'datetimepickerStyle' : '../vendor/datetimepicker/css/bootstrap-datetimepicker.css',
		'datetimepickerStyleStandalone' : '../vendor/datetimepicker/css/bootstrap-datetimepicker-standalone.css',
		'datetimepickerLocale' : '../vendor/datetimepicker/locale/'+ K2DateTimePickerLanguage,
		'elfinder' : '../vendor/elfinder/js/elfinder.min',
		'elfinderTheme' : '../vendor/elfinder/css/theme.css',
		'elfinderStyle' : '../vendor/elfinder/css/elfinder.min.css',
		'jqueryui' : '../vendor/jqueryui/jquery-ui.custom.min',
		'jquery-mousewheel' : '../vendor/jqueryui/jquery.mousewheel.min',
		'magnific' : '../vendor/magnific/jquery.magnific-popup.min',
		'magnificStyle' : '../vendor/magnific/magnific-popup.css',
		'marionette' : '../vendor/marionette/backbone.marionette.min',
		'mergely' : '../vendor/mergely/mergely.min',
		'mergelyStyle' : '../vendor/mergely/mergely.css',
		'mergelyEditor' : '../vendor/mergely/codemirror.min',
		'mergelyEditorStyle' : '../vendor/mergely/codemirror.css',
		'moment' : '../vendor/datetimepicker/moment.min',
		'nprogress': '../vendor/nprogress/nprogress',
		'css' : '../vendor/require/css',
		'text' : '../vendor/require/text',
		'select2' : '../vendor/select2/select2.min',
		'sliderpips' : '../vendor/sliderpips/jquery-ui-slider-pips.min',
		'sliderpipsStyle' : '../vendor/sliderpips/jquery-ui-slider-pips.css',
		'sortable' : '../vendor/sortable/jquery-sortable-min',
		'tipr' : '../vendor/tipr/tipr.min',
		'transition' : '../vendor/datetimepicker/transition',
		'underscore' : '../vendor/underscore/underscore-min',
		'uploader' : '../vendor/uploader/jquery.fileupload',
		'uploaderIframe' : '../vendor/uploader/jquery.iframe-transport',
		'jquery.ui.widget' : '../vendor/uploader/vendor/jquery.ui.widget'
	}
});

require(['app'], function(K2) {
	K2.start();
});
