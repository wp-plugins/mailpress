// settings
var mp_settings = {
	
	init : function() {
		jQuery('#example').tabs();
		//general

		// smtp
		jQuery('#smtp-auth').change( function() {
			var a = jQuery(this); 
			if (a.val() == '@PopB4Smtp') jQuery('#POP3').show(); 
			else jQuery('#POP3').hide(); 
		});

		// test
		jQuery('#theme').change( function() {
			var a = jQuery(this); 
			jQuery('.template').hide(); 
			jQuery( '#' + a.val()).show();
		 });

		// subscriptions
		jQuery('.newsletter').change(function(){ 
			if (!this.checked) jQuery('#default_'+this.id).removeAttr('checked'); 
			jQuery('#span_default_'+this.id).toggle(); 
		});
		jQuery('.subscription_mngt').click( function() {
			var a = jQuery(this); 
			jQuery('.toggle').fadeTo(0,0); 
			jQuery( '.' + a.val()).fadeTo(0,1); 
		}); 
	}
}
jQuery(document).ready(function(){ mp_settings.init(); });