// Comments
jQuery(document).ready(function() {
	jQuery('#k2CommentsForm').submit(function(event) {
		event.preventDefault();
		jQuery('#k2CommentsFormLog').empty().addClass('k2CommentsFormLoading');
		jQuery.ajax({
			url : jQuery('#k2CommentsForm').attr('action'),
			type : 'post',
			dataType : 'json',
			data : jQuery('#k2CommentsForm').serialize(),
			success : function(response) {
				jQuery('#k2CommentsFormLog').removeClass('k2CommentsFormLoading').html(response.message);
				if ( typeof (Recaptcha) != "undefined") {
					Recaptcha.reload();
				}
				if (response.refresh) {
					window.location.reload();
				}
			}
		});
	});
});

