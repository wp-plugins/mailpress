jQuery(document).ready( function() { jQuery('div#MailPress #submit').click( function() { mailpress_ajax(); 	return false;} ); } );

function mailpress_ajax()
{
	var data = {};
	var div = jQuery('div#MailPress');
	jQuery('form#form [type!=submit]',div).each(function(){
		data[ jQuery(this).attr('name') ] = jQuery(this).val();
	});
	jQuery('div#MailPress div#formdiv').fadeTo(500,0);
 	jQuery('div#MailPress div#loading').fadeTo(500,1);

	jQuery.post(mp_url,data,function(data){mailpress_ajax_response(data);});
}

function mailpress_ajax_response(xd)
{
 	var mess = jQuery('message',xd).text();
 	var email = jQuery('email',xd).text();

	jQuery('div#MailPress form#form [name=email]').val(email);

 	jQuery('div#MailPress div#loading').fadeTo(500,0);
	jQuery('div#MailPress div#message').html(mess).fadeTo(1000,1);

 	setTimeout('mailpress_show_form()',2000);
}
function mailpress_show_form()
{
 	jQuery('div#MailPress div#message').fadeTo(1000,0);
	jQuery('div#MailPress div#formdiv').fadeTo(500,1);
}