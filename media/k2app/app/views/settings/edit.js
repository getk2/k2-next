define(['marionette', 'text!templates/settings/edit.html', 'dispatcher', 'widget'], function(Marionette, template, K2Dispatcher, K2Widget) {
	'use strict';
	var K2ViewSettings = Marionette.ItemView.extend({
		template : _.template(template),
		modelEvents : {
			'change' : 'render'
		},
		onDomRefresh : function() {
			K2Widget.updateEvents(this.$el);
			var container = this.$el;
			container.off("click", "button");
			container.on("click", ".k2ImageSizesAdd", function(event) {
				event.preventDefault();
				var counter = parseInt(container.find(".k2ImageSizesCounter").val()) + 1;
				var template = container.find(".k2ImageSizesPlaceholder").html();
				var rendered = template.replace(/COUNTER/g, counter);
				var element = jQuery("<li></li>").html(rendered);
				element.find("input").prop("disabled", false);
				container.find("ul").append(element);
				container.find("input[name=counter]").val(counter);
			});
			container.on("click", ".k2ImageSizesRemove", function(event) {
				event.preventDefault();
				jQuery(this).parent().parent().remove();
				var counter = parseInt(container.find(".k2ImageSizesCounter").val()) - 1;
				container.find("input[name=counter]").val(counter);
			});
		},
		serializeData : function() {
			var data = {
				'row' : this.model.toJSON(),
				'form' : this.model.getForm().toJSON()
			};
			return data;
		},
	});
	return K2ViewSettings;
});
