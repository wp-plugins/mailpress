var mp_taxonomy = {

	addAfter : function( r, settings ) {
		var name, id;

		name = jQuery("<span>" + jQuery('name', r).text() + "</span>").html();
		id = jQuery(MP_AdminPageL10n.tr_prefix_id, r).attr('id');
		options[options.length] = new Option(name, id);

		addAfter2( r, settings );
	},

	addAfter2 : function( x, r ) {
		var t = jQuery(r.parsed.responses[0].data);
	},

	delAfter : function( r, settings ) {
		var id = jQuery(MP_AdminPageL10n.tr_prefix_id, r).attr('id'), o;
		for ( o = 0; o < options.length; o++ ) if ( id == options[o].value ) options[o] = null;
	},

	delBefore : function(s) {
		if ( 'undefined' != showNotice ) return showNotice.warn() ? s : false;
		return s;
	},

	init : function() {

		var options = false;

		if (( typeof(document.forms[MP_AdminPageL10n.add_form_id]) != "undefined" ) && ( document.forms[MP_AdminPageL10n.add_form_id].parent ))
			options = document.forms[MP_AdminPageL10n.add_form_id].parent.options;

		if ( options )
			jQuery('#'+MP_AdminPageL10n.list_id).wpList( { addAfter: mp_taxonomy.addAfter, delBefore: mp_taxonomy.delBefore, delAfter: mp_taxonomy.delAfter } );
		else
			jQuery('#'+MP_AdminPageL10n.list_id).wpList({ addAfter: mp_taxonomy.addAfter2, delBefore: mp_taxonomy.delBefore });

		// delete
		jQuery('.delete a[class^="delete"]').click(function(){return false;});
	}
}
jQuery(document).ready(function(){ mp_taxonomy.init(); });