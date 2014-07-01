define(['text!layouts/revisions/form.html', 'dispatcher', 'jqueryui', 'widgets/sliderpips/jquery-ui-slider-pips.min', 'css!widgets/sliderpips/jquery-ui-slider-pips.css', 'css!//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/flick/jquery-ui.min.css'], function(template, K2Dispatcher) {'use strict';

	// Model
	var Revision = Backbone.Model.extend({
		defaults : {
			id : null,
			date : null,
			data : null,
			user : null
		}
	});

	// Collection
	var Revisions = Backbone.Collection.extend({
		model : Revision
	});

	var K2ViewRevisionsWidget = Marionette.ItemView.extend({
		template : _.template(template),
		initialize : function(options) {
			options.data.reverse();
			this.collection = new Revisions(options.data);
			_.each(this.collection.models, function(model) {
				model.set('data', jQuery.parseJSON(model.get('data')));
			});
		},
		onDomRefresh : function() {

			if (_.size(this.collection) > 2) {
				var el = this.$el.find('div[data-role="revisions-timeline"]');
				var max = this.collection.models.length;

				// First fix jQuery UI/Mootools conflict
				el[0].slide = null;
				// Triggger the slider
				el.slider({
					min : 1,
					max : max,
					step : 1,
					values : [max - 1, max]
				});

				var labels = [];
				_.each(this.collection.models, function(revision) {
					labels.push('<span>' + revision.get('user') + '</span><span>' + revision.get('date') + '</span>');
				});

				el.slider('pips', {
					rest : 'label',
					labels : labels
				});
			}

		},
		// Serialize data for view
		serializeData : function() {
			var data = {
				'revisions' : this.collection,
				'leftRevision' : this.collection.get(2)
			};
			return data;
		}
	});
	return K2ViewRevisionsWidget;
});
