define(['marionette', 'text!layouts/extrafieldsgroups/form.html', 'dispatcher'], function(Marionette, template, K2Dispatcher) {'use strict';
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
		},
		updateAssignmentsField : function() {
			var form = this.model.getForm();
			var assignments = form.get('assignments');
			var scope = this.$el.find('#scope').val();
			this.$el.find('#appExtraFieldGroupAssignments').html(assignments[scope]);
			if (this.model.get('scope') === scope) {
				var assignmentsValue = this.model.get('assignments');
				this.$el.find('input[name="assignments[mode]"]').val([assignmentsValue.mode]);
			}
		},
		updateAssignmentsSelection : function() {
			var value = this.$el.find('input[name="assignments[mode]"]:checked').val();
			if (value === 'all') {
				this.$el.find('#appExtraFieldGroupAssignments select').prop('disabled', true);
				this.$el.find('#appExtraFieldGroupAssignments select option').attr('selected', 'selected');
			} else if (value === 'none') {
				this.$el.find('#appExtraFieldGroupAssignments select').prop('disabled', true);
				this.$el.find('#appExtraFieldGroupAssignments select option').removeAttr('selected');
			} else {
				this.$el.find('#appExtraFieldGroupAssignments select').prop('disabled', false);
			}
		}
	});
	return K2ViewExtraFieldsGroup;
});
