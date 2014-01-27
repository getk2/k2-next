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
	
	if(typeof(jQuery.sortable) == 'function') {
		jQuery('.k2FieldItemsMultiple').sortable({
			handle : 'span.k2FieldItemsHandle',
		});
	}

	jQuery('.k2FieldItemsMultiple').on('click', '.k2FieldItemsRemove', function(event) {
		event.preventDefault();
		jQuery(this).parent().remove();
	});

});
