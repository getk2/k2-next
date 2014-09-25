jQuery(document).ready(function() {

	// Dynamic params in K2
	var group = jQuery('.k2GroupSwitcher').val();
	jQuery('div[data-k2group]').css('display', 'none');
	jQuery('div[data-k2group="' + group + '"]').css('display', 'block');
	jQuery('.k2GroupSwitcher').change(function() {
		var group = jQuery(this).val();
		jQuery('div[data-k2group]').css('display', 'none');
		jQuery('div[data-k2group="' + group + '"]').css('display', 'block');
	});

	// K2 categories field
	jQuery('input[data-categories]').change(function(event) {
		var target = jQuery(this).data('categories');
		if (jQuery(this).val() == '0') {
			jQuery('select[name="' + target + '"] option').prop('selected', true);
		} else {
			jQuery('select[name="' + target + '"] option').prop('selected', false);
		}
		jQuery('select[name="' + target + '"]').trigger('change').trigger('chosen:updated').trigger('liszt:updated');
	});

	jQuery('select[data-mode="k2categoriesmenu"]').change(function(event) {
		var id, value = jQuery(this).val();
		if (value === null || value.length > 1) {
			id = '';
		} else {
			id = value[0];
		}
		jQuery('input[name="jform[request][id]"]').val(id);
	});

	// K2 modal
	jQuery('.k2Modal').click(function(event) {
		event.preventDefault();
		jQuery.magnificPopup.open({
			alignTop : false,
			closeBtnInside : true,
			items : {
				src : this.href,
				type : 'iframe'
			}
		});
	});

	// K2 Items, users and tags fields
	if (jQuery('.k2FieldResourceMultiple').length > 0) {
		jQuery('.k2FieldResourceMultiple').sortable({
			handle : 'span.k2FieldResourceMultipleHandle',
		});
		jQuery('.k2FieldResourceMultiple').on('click', '.k2FieldResourceRemove', function(event) {
			event.preventDefault();
			jQuery(this).parent().remove();
		});
	}
});
