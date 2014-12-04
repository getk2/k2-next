define(['text!templates/revisions/edit.html', 'text!templates/revisions/info.html', 'dispatcher', 'jqueryui'], function(template, revisionInfo, K2Dispatcher) {
	'use strict';

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

	// The row view for grid
	var K2ViewRevisionsInfo = Marionette.ItemView.extend({
		template : _.template(revisionInfo),
		initialize : function(options) {
			this.parent = options.parent;
		},
		events : {
			'click [data-action="restore-revision"]' : 'restore'
		},
		restore : function(event) {
			event.preventDefault();
			this.parent.trigger('restore', this.model);
		}
	});

	var K2ViewRevisionsWidget = Marionette.LayoutView.extend({
		template : _.template(template),
		regions : {
			leftRevisionRegion : '[data-region="revisions-left"]',
			rightRevisionRegion : '[data-region="revisions-right"]'
		},
		initialize : function(options) {

			this.size = _.size(options.data);

			if (this.size > 1) {
				options.data.reverse();
				this.collection = new Revisions(options.data);
				_.each(this.collection.models, function(model) {
					model.set('data', jQuery.parseJSON(model.get('data')));
				});
				this.leftRevisionIndex = this.size - 1;
				this.rightRevisionIndex = this.size - 2;
				this.leftRevision = this.collection.models[this.leftRevisionIndex];
				this.rightRevision = this.collection.models[this.rightRevisionIndex];
			}

		},

		onShow : function() {
			if (this.size > 1) {
				this.update();
			}
		},

		update : function() {
			var leftRevisionView = new K2ViewRevisionsInfo({
				model : this.leftRevision,
				parent : this
			});
			this.leftRevisionRegion.show(leftRevisionView);

			var rightRevisionView = new K2ViewRevisionsInfo({
				model : this.rightRevision,
				parent : this
			});
			this.rightRevisionRegion.show(rightRevisionView);
		},

		onDomRefresh : function() {

			if (this.size > 1) {

				// Timeline
				require(['sliderpips', 'sliderpipsStyle', 'css!//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/flick/jquery-ui.min.css'], _.bind(function() {
					var el = this.$el.find('div[data-role="revisions-timeline"]');
					var self = this;
					// First fix jQuery UI/Mootools conflict
					el[0].slide = null;
					// Triggger the slider
					el.slider({
						min : 0,
						max : this.size - 1,
						step : 1,
						values : [this.size - 2, this.size - 1],
						change : function(event, ui) {
							if (ui.handle.hasClass('k2-left-revision')) {
								self.leftRevisionIndex = ui.value;
								self.leftRevision = self.collection.models[self.leftRevisionIndex];
							} else {
								self.rightRevisionIndex = ui.value;
								self.rightRevision = self.collection.models[self.rightRevisionIndex];
							}
							self.update();
							self.$('#k2-compare-title').mergely('lhs', self.leftRevision.get('data').title);
							self.$('#k2-compare-title').mergely('rhs', self.rightRevision.get('data').title);
							self.$('#k2-compare-introtext').mergely('lhs', self.leftRevision.get('data').introtext);
							self.$('#k2-compare-introtext').mergely('rhs', self.rightRevision.get('data').introtext);
							self.$('#k2-compare-fulltext').mergely('lhs', self.leftRevision.get('data').fulltext);
							self.$('#k2-compare-fulltext').mergely('rhs', self.rightRevision.get('data').fulltext);
						},
						create : function(event, ui) {
							el.find('a.ui-slider-handle:first').addClass('k2-right-revision');
							el.find('a.ui-slider-handle:last').addClass('k2-left-revision');
						}
					});

					var labels = [];
					_.each(this.collection.models, function(revision) {
						labels.push('<span>' + revision.get('user') + '</span><span>' + revision.get('date') + '</span>');
					});

					el.slider('pips', {
						rest : 'label',
						labels : labels
					});
				}, this));

				// Compare
				require(['mergelyEditor', 'css!mergelyEditorStyle', 'mergely', 'css!mergelyStyle'], _.bind(function() {
					var lhs = this.leftRevision.get('data');
					var rhs = this.rightRevision.get('data');

					this.$('#k2-compare-title').mergely({
						width : 'auto',
						height : 50,
						cmsettings : {
							readOnly : true,
							lineWrapping : true,

						},
						lhs : function(setValue) {
							setValue(lhs.title);
						},
						rhs : function(setValue) {
							setValue(rhs.title);
						}
					});
					this.$('#k2-compare-introtext').mergely({
						width : 'auto',
						height : 200,
						cmsettings : {
							readOnly : true,
							lineWrapping : true,
						},
						lhs : function(setValue) {
							setValue(lhs.introtext);
						},
						rhs : function(setValue) {
							setValue(rhs.introtext);
						}
					});
					this.$('#k2-compare-fulltext').mergely({
						width : 'auto',
						height : 200,
						cmsettings : {
							readOnly : true,
							lineWrapping : true,
						},
						lhs : function(setValue) {
							setValue(lhs.fulltext);
						},
						rhs : function(setValue) {
							setValue(rhs.fulltext);
						}
					});

					this.$('#k2-compare-title').mergely('resize');
					this.$('#k2-compare-introtext').mergely('resize');
					this.$('#k2-compare-fulltext').mergely('resize');

				}, this));

			}

		},
		// Serialize data for view
		serializeData : function() {
			var data = {
				'revisions' : this.collection,
				'leftRevision' : this.leftRevision,
				'rightRevision' : this.rightRevision
			};
			return data;
		}
	});
	return K2ViewRevisionsWidget;
});
