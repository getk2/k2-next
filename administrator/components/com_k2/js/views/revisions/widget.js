define(['text!layouts/revisions/form.html', 'dispatcher', 'jqueryui', 'widgets/sliderpips/jquery-ui-slider-pips.min', 'css!widgets/sliderpips/jquery-ui-slider-pips.css', 'css!//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/flick/jquery-ui.min.css'], function(template, K2Dispatcher) {'use strict';

	var K2ViewRevisionsWidget = Marionette.ItemView.extend({
		template : _.template(template),
		initialize : function(options) {
			this.revisions = options.data;
		},
		onDomRefresh : function() {
			
			var el = this.$el.find('div[data-role="revisions-timeline"]');
			var max = this.revisions.length;
			
			
			// First fix jQuery UI/Mootools conflict
			el[0].slide = null;
			// Triggger the slider
			el.slider({
				min : 1,
				max : max,
				step : 1,
				values : [max-1, max]
			});
			
			var labels = [];
			_.each(this.revisions, function(revision) {
				labels.push('<span>' + revision.user + '</span><span>' + revision.date + '</span>');
			});
			
			el.slider("pips" , { rest: "label", labels: labels });
		},
		// Serialize data for view
		serializeData : function() {
			var data = {
				'revisions' : this.revisions
			};
			return data;
		}
	});
	return K2ViewRevisionsWidget;
});
