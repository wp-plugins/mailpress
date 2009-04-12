
jQuery(document).ready( function() {

	//uploader
	mp_fileupload.init();

	//autosave
	autosave.init();
	jQuery('#title').blur( function() { if ( (jQuery("#mail_id").val() > 0) || (jQuery("#title").val().length == 0) ) return; autosave.main(); } );

	// close postboxes that should be closed
	jQuery('.if-js-closed').removeClass('if-js-closed').addClass('closed');

	// show things that should be visible, hide what should be hidden
	jQuery('.hide-if-no-js').show();
	jQuery('.hide-if-js').hide();

	// postboxes
	postboxes.add_postbox_toggles(mailnewL10n.screen);
});

function form_ctrl()
{
	var err = 0;

// email or list
	document.mail_newform.toemail.style.border='1px solid #C6D9E9';
	document.mail_newform.to_list.style.border='1px solid #C6D9E9';

	if (is_empty(document.mail_newform.toemail.value) && (is_empty(document.mail_newform.to_list.value)))
	{
		document.mail_newform.toemail.style.border='1px solid #f00';
		document.mail_newform.to_list.style.border='1px solid #f00';
		err++;
	}
	else
	{
		if (!is_empty(document.mail_newform.toemail.value))
		{
			if (!is_email(document.mail_newform.toemail.value))
			{
				document.mail_newform.toemail.style.border='1px solid #f00';
				err++;
			}
		}
	}
	if ( err == 0 )	return true;

	alert(mailnewL10n.errmess);
	return false;
}

function is_empty(t) {
	return (t.length == 0);
}
function is_email(m) {
	var pattern = /^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/;
	return pattern.test(m);
}
function is_numeric(n) {
	var pattern = /^[0-9]$/;
	return pattern.test(n);
 }