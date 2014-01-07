jQuery(document).ready(function() {
	jQuery('.k2FieldCategoriesFilterEnabled').change(function(event) {
		var target = jQuery(this).data('categories');
		if (jQuery(this).val() == '0') {
			jQuery('select[name="' + target + '"] option').prop('selected', true);
		} else {
			jQuery('select[name="' + target + '"] option').prop('selected', false);
		}
		jQuery('select[name="' + target + '"]').trigger('chosen:updated').trigger('liszt:updated');
	});
});
