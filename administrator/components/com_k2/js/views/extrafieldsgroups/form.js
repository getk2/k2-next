define(['marionette', 'text!layouts/extrafieldsgroups/form.html', 'dispatcher', 'widgets/widget'], function(Marionette, template, K2Dispatcher, K2Widget) {'use strict';
	var K2ViewExtraFieldsGroup = Marionette.ItemView.extend({
		template : _.template(template),
		modelEvents : {
			'change' : 'render'
		},
		events : {
			'change #scope' : 'updateAssignmentsField',
			'change input[name="assignments[mode]"]' : 'updateAssignmentsSelection'
		},
		serializeData : function() {
			var data = {
				'row' : this.model.toJSON(),
				'form' : this.model.getForm().toJSON()
			};
			return data;
		},
		onDomRefresh : function() {
			this.updateAssignmentsField();
			this.updateAssignmentsSelection();
			K2Widget.updateEvents(this.$el);
		},
		updateAssignmentsField : function() {
			var form = this.model.getForm();
			var assignments = form.get('assignments');
			var scope = this.$('#scope').val();
			this.$('[data-region="extra-field-group-assignements"]').html(assignments[scope]);
			if (this.model.get('scope') === scope) {
				var assignmentsValue = this.model.get('assignments');
				this.$('input[name="assignments[mode]"]').val([assignmentsValue.mode]);
			}
			K2Widget.updateEvents(this.$el);
		},
		updateAssignmentsSelection : function() {
			var active = this.$('input[name="assignments[mode]"]:checked');
			var value = active.val();
			var assignments = this.$('[data-region="extra-field-group-assignements"]');
			if (value === 'all') {
				assignments.find('select').prop('disabled', true);
				assignments.find('select option').attr('selected', 'selected');
				assignments.hide();
			} else if (value === 'none') {
				assignments.find('select').prop('disabled', true);
				assignments.find('select option').removeAttr('selected');
				assignments.hide();
			} else {
				assignments.find('select').prop('disabled', false);
				assignments.show();
			}
		}
	});
	return K2ViewExtraFieldsGroup;
});
