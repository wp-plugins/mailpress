//settings_batch_spool_send

jQuery(document).ready( function(){ 
	jQuery('.submit_spool').click( function() {
		var a = jQuery(this); 
		jQuery('.toggl2').fadeTo(0,0); 
		jQuery( '.' + a.val()).fadeTo(0,1); 
	});
});
