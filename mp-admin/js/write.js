// write
var mp_write = {

	init : function() {
		// close postboxes that should be closed
		jQuery('.if-js-closed').removeClass('if-js-closed').addClass('closed');

		// postboxes
		postboxes.add_postbox_toggles(MP_AdminPageL10n.screen);

		//uploader
		mp_fileupload.init();

		//autosave
		autosave.init();
		jQuery('#title').blur( function() {
			if ( (jQuery("#mail_id").val() > 0) || (jQuery("#title").val().length == 0) ) return; 
			autosave.main(); 
		});

		// custom fields
		jQuery('#the-list').wpList({	
			addAfter: function( xml, s ) {
				jQuery('table#list-table').show();
			}, 
			addBefore: function( s ) {
				s.data += '&mail_id=' + jQuery('#mail_id').val(); 
				return s;
			}
		});

		// control form
		jQuery('form#writeform').submit( function() {
			return mp_write.control();
		});
	},

	control : function() {
		var err = 0;

		// email or list
		document.writeform.toemail.style.border='1px solid #C6D9E9';
		document.writeform.to_list.style.border='1px solid #C6D9E9';

		if (mp_write.is_empty(document.writeform.toemail.value) && (mp_write.is_empty(document.writeform.to_list.value)))
		{
			document.writeform.toemail.style.border='1px solid #f00';
			document.writeform.to_list.style.border='1px solid #f00';
			err++;
		}
		else
		{
			if (!mp_write.is_empty(document.writeform.toemail.value))
			{
				if (!mp_write.is_email(document.writeform.toemail.value))
				{
					document.writeform.toemail.style.border='1px solid #f00';
					err++;
				}
			}
		}
		if ( err == 0 )	return true;

		alert(MP_AdminPageL10n.errmess);
		return false;
	},

	is_empty : function(t) { return (t.length == 0); },
	is_email : function(m) { var pattern = /^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/; return pattern.test(m); },
	is_numeric : function(n) { var pattern = /^[0-9]$/; return pattern.test(n); }
}
jQuery(document).ready( function() { mp_write.init(); });