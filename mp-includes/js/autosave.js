var autosave_bool 	= new Array();
var autosaveOldData 	= new Array();
var autosaveNewData 	= new Array();
var autosave_data 	= new Array();
	autosave_data['toemail'] 	= 'toemail';
	autosave_data['toname'] 	= 'toname';
	autosave_data['to_list'] 	= 'to_list';
	autosave_data['subject'] 	= 'title';
	autosave_data['html'] 		= 'content';
	autosave_data['plaintext']	= 'plaintext';

var autosavePeriodical;
var autosaveOldMessage = '';

var autosave_thickbox_display = false;

//////////////
jQuery(function($) {

	if (jQuery('#autosavenonce').val())
	{

/* when tinyMCE is in use */

		if ( (typeof tinyMCE != "undefined") && tinyMCE.activeEditor && !tinyMCE.activeEditor.isHidden() ) 	
		{
			var ed = tinyMCE.activeEditor;
			if ( 'mce_fullscreen' == ed.id )	tinyMCE.get('content').setContent(ed.getContent({format : 'raw'}), {format : 'raw'});
			tinyMCE.get('content').save();
		}
																	// (bool) is TinyMCE spellcheck is on
/* when tinyMCE is in use */

		autosavePeriodical 	= $.schedule({	time: autosaveL10n.autosaveInterval * 1000, 
									func: function() { autosave_thickbox_display = false; autosave(); }, 
									repeat: true, 
									protect: true
								});
		$("#mail_newform").submit(function() { $.cancel(autosavePeriodical); });			// Disable autosave after the form has been submitted

		autosave_Preview_Click();											// Autosave when the preview button is clicked.

		autosaveOldData = autosave_Data_Retrieve();

		autosaveNewData = autosaveOldData;
	}

});
//////////////
function autosave()
{

/* when tinyMCE is in use */

	var rich  = false;													// (bool) is rich editor enabled and active
	if ( (typeof tinyMCE != "undefined") && tinyMCE.activeEditor && !tinyMCE.activeEditor.isHidden() ) 	
	{
		var rich = true;
		var ed = tinyMCE.activeEditor;
		if ( 'mce_fullscreen' == ed.id )	tinyMCE.get('content').setContent(ed.getContent({format : 'raw'}), {format : 'raw'});
		tinyMCE.get('content').save();
	}
																	// (bool) is TinyMCE spellcheck is on
	autosave_bool['spell'] = ( rich && tinyMCE.activeEditor.plugins.spellchecker && tinyMCE.activeEditor.plugins.spellchecker.active ) ? true : false;
/* when tinyMCE is in use */

	var mail_data = 	{	action: 		"autosave",
					autosavenonce: 	jQuery('#autosavenonce').val(),
					id:  			jQuery("#mail_id").val() || 0,
					revision:  		jQuery("#mail_revision").val() || -1
				};
	var x = 0;
	for (key in autosave_data)
	{
		mail_data[key] = jQuery("#"+autosave_data[key]).val() || "";
		x = x + mail_data[key].length;
	}

	autosaveNewData = autosave_Data_Retrieve();

	autosave_bool['empty'] = (x == 0);
	autosave_bool['nochg'] = autosave_Data_Compare(autosaveOldData,autosaveNewData);
	autosave_bool['thick'] = ( jQuery("#TB_window").css('display') == 'block' );
																	// We always send the ajax request in order to keep the post lock fresh.
	mail_data['autosave'] = 1;												// This (bool) tells whether or not to write the mail to the DB during the ajax request.
	for (key in autosave_bool) if (autosave_bool[key]) mail_data['autosave'] = 0;

	autosaveOldMessage 	= jQuery('#autosave').html();
	autosaveOldData 		= autosaveNewData;
	autosave_Disable_Buttons();
// ajax
	jQuery.ajax({
		data: mail_data,
		beforeSend: (mail_data['autosave'] == 1) ? autosave_Ajax_Loading : null,
		type: "POST",
		url: autosaveL10n.requestFile,
		success: autosave_Ajax_Callback
	});
}

function autosave_Ajax_Loading() 
{
	jQuery('#autosave').html(autosaveL10n.savingText);
}

function autosave_Ajax_Callback(response) 
{
	var message = '';
	var res = wpAjax.parseAjaxResponse(response, 'autosave'); 							// parse the ajax response

	if ( res && res.responses && res.responses.length ) 
	{
		message = res.responses[0].data;

		if ( res.responses[0].supplemental ) 
		{
			jQuery.each(res.responses[0].supplemental, 
					function(selector, value) 
					{
						if ( selector.match(/^replace-/) ) 
						{
							jQuery('#'+selector.replace('replace-', '')).val(value);
						}
					}
			);

			if ( 'disable' == res.responses[0].supplemental['disable_autosave'] ) 
			{
				autosave = function() {};
			}
			if ( '' != res.responses[0].supplemental['tipe'] ) 
			{
				var type 	= res.responses[0].supplemental['tipe'] ;
				var item_id = parseInt( res.responses[0].id );
				if (item_id > 0)
				{
					autosave_Id_Update(item_id, type); 
// ajax
					jQuery.post(autosaveL10n.requestFile, 
							{action: "get-previewlink", id: item_id, getpreviewlinknonce: jQuery('#getpreviewlinknonce').val() }, 
							autosave_Preview_Callback);
				}
				else
				{
					if (autosave_thickbox_display)
					{
						var href = autosave_other_theme(jQuery('#previewview a').attr("href"));
						tb_show(null,href,null);
						autosave_thickbox_display = false;
					}
				}
			}
			else
			{
				if (autosave_thickbox_display)
				{
					var href = autosave_other_theme(jQuery('#previewview a').attr("href"));
					tb_show(null,href,null);
					autosave_thickbox_display = false;
				}
			}
			jQuery.each(	res.responses[0].supplemental, 
						function(selector, value) 
						{
							if ( (selector != 'tipe') && (selector.match(/^replace-/)) ) 
							{
								jQuery('#'+selector.replace('replace-', '')).val(value);
							}
						}
					);
		}
		if ( message ) { jQuery('#autosave').html(message); } 							// update autosave message
		else if ( autosaveOldMessage && res ) { jQuery('#autosave').html( autosaveOldMessage ); }
	}

	autosave_Enable_Buttons(); 												// re-enable disabled form buttons
}
//////////////
function autosave_Data_Retrieve()
{
	var x = new Array();

	for (key in autosave_data)
	{
		x[key] = jQuery("#"+autosave_data[key]).val() || "";
	}

	return x;
}
function autosave_Data_Compare(a,b)
{
	for (key in a) if (a[key] != b[key]) return false;
	return true;
}
//////////////
function autosave_Id_Update(item_id, type) 
{
	if ( isNaN(item_id)) 						return;
	if ( item_id <= 0 )						return;
	var attr = (type == 'mail') ? 'id' : type;
	if ( item_id == parseInt(jQuery('#mail_'+attr).val()) ) 	return;				// no need to do this more than once

	jQuery('#mail_'+attr).val(item_id);
}
//////////////
function autosave_Preview_Click()
{
	jQuery('#previewview a').click(function(e) {
		autosave_thickbox_display = true;
		autosave();
		return false;
	});
}

function autosave_Preview_Callback(previewlink)
{
	var previewText = autosaveL10n.previewMailText;

	jQuery('#previewview').html('<a class="thickbox" target="_blank" href="'+previewlink+'" tabindex="4">'+previewText+'</a>');

	if (autosave_thickbox_display)
	{
		var href = autosave_other_theme(jQuery('#previewview a').attr("href"));
		tb_show(null,href,null);
		autosave_thickbox_display = false;
	}

	autosave_Preview_Click();									// Autosave when the preview button is clicked. 
}
//////////////
function autosave_Enable_Buttons() 
{
	jQuery("#submitpost :button:disabled, #submitpost :submit:disabled").attr('disabled', '');
}

function autosave_Disable_Buttons() 
{
	jQuery("#submitpost :button:enabled, #submitpost :submit:enabled").attr('disabled', 'disabled');
	setTimeout(autosave_Enable_Buttons, 5000); // Re-enable 5 sec later.  Just gives autosave a head start to avoid collisions.
}

function autosave_other_theme(href)
{
	var currenttheme = jQuery('p#MailPress_extra_form_mail_new input[name=CurrentTheme]').val();
	if (currenttheme)
	{
		var selectedtheme = jQuery('p#MailPress_extra_form_mail_new select[name=Theme]').val();
		if (currenttheme != selectedtheme)
		{
			var KeepThis = href.indexOf('&KeepThis');
			hrefnew = href.substring(0,KeepThis)+'&theme='+selectedtheme+href.substring(KeepThis);
			href = hrefnew;
		}
	}
	return href;
}