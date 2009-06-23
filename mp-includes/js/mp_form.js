var mp_form = {
	selectors : {
		submit  : 'div#MailPress #mp_submit',
		form    : 'div#MailPress form#mp-form',
		formdiv : 'div#MailPress div#mp-formdiv',
		loading : 'div#MailPress div#mp-loading',
		message : 'div#MailPress div#mp-message'
	},
	
	ajax : function() {
		var data = {};
		jQuery(mp_form.selectors.form+' [type!=submit]').each(function(){
			data[ jQuery(this).attr('name') ] = jQuery(this).val();
		});
		jQuery(mp_form.selectors.formdiv).fadeTo(500,0);
	 	jQuery(mp_form.selectors.loading).fadeTo(500,1);

		//¤ ajax
		jQuery.ajax({
			data: data,
			beforeSend: null,
			type: "POST",
			url: mp_url,
			success: mp_form.callback
			});
	},

	callback : function(r) {
	 	var mess = jQuery('message',r).text();
	 	var email = jQuery('email',r).text();
	 	var name  = jQuery('name',r).text();

		jQuery(mp_form.selectors.form+' [name=email]').val(email);
		jQuery(mp_form.selectors.form+' [name=name]').val(name);

	 	jQuery(mp_form.selectors.loading).fadeTo(500,0);
		jQuery(mp_form.selectors.message).html(mess).fadeTo(1000,1);

	 	setTimeout('mp_form.show()',2000);
	},

	show : function() {
	 	jQuery(mp_form.selectors.message).fadeTo(1000,0);
		jQuery(mp_form.selectors.formdiv).fadeTo(500,1);
	},

	init : function() {
		jQuery(mp_form.selectors.submit).click( function() { mp_form.ajax(); return false;} );
	}
}
jQuery(document).ready( function() { mp_form.init(); } );