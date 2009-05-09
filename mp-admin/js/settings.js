function toggleState (value, elementId) 
{
	var element = document.getElementById(elementId);
	element.disabled = value;
	return true;
}

// settings
jQuery(document).ready(function(){
	jQuery('#example').tabs();
	});

//general
jQuery(document).ready( function(){ 
	jQuery('.subscription_mngt').click( function() {  var a = jQuery(this); jQuery('.toggle').fadeTo(0,0); jQuery( '.' + a.val()).fadeTo(0,1); } ); 
	jQuery('.newsletter').change(function(){ if (!this.checked) jQuery('#default_'+this.id).removeAttr('checked'); jQuery('#span_default_'+this.id).toggle(); });
	}
);

// smtp
jQuery(document).ready( function(){ 
	jQuery('#smtp-auth').change( function() {  var a = jQuery(this); if (a.val() == '@PopB4Smtp') jQuery('#POP3').show(); else jQuery('#POP3').hide(); } ); } );

// test
jQuery(document).ready( function(){ 
	jQuery('#theme').change( function() {  var a = jQuery(this); jQuery('.template').hide(); jQuery( '#' + a.val()).show(); } ); } );
