// user_mailinglists
var mp_user_mailinglists = {

	noSyncChecks : false,

	init : function() {
		// mailinglist tabs
		jQuery('#mailinglist-tabs a').click(function(){
			mp_user_mailinglists.tabs(this);
		});
		if ( getUserSetting('mailinglists') )
			jQuery('#mailinglist-tabs a[href="#mailinglists-pop"]').click();

		// Ajax mailinglist
		jQuery('#newmailinglist').one( 'focus', function() { jQuery(this).val( '' ).removeClass( 'form-input-tip' ) } );
		jQuery('#mailinglist-add-submit').click(function(){jQuery('#newmailinglist').focus();});

		jQuery('#mailinglistchecklist').wpList( {
			alt: '',
			response: 'mailinglist-ajax-response',
			addBefore: mp_user_mailinglists.AddBefore,
			addAfter: mp_user_mailinglists.AddAfter
		} );

		jQuery('#mailinglist-add-toggle').click( function() {
			jQuery('#mailinglist-adder').toggleClass( 'wp-hidden-children' );
			jQuery('#mailinglist-tabs a[href="#mailinglists-all"]').click();
			return false;
		} );

		// Synch both lists
		jQuery('.mailinglistchecklist .popular-mailinglist :checkbox').change( mp_user_mailinglists.syncChecks(this) ).filter( ':checked' ).change();
	},

	tabs : function(_this) {
		var t = jQuery(_this).attr('href');
		jQuery(_this).parent().addClass('tabs').siblings('li').removeClass('tabs');
		jQuery('.tabs-panel').hide();
		jQuery(t).show();
		if ( '#mailinglists-all' == t ) 	deleteUserSetting('mailinglists');
		else						setUserSetting('mailinglists','pop');
		return false;
	},

	AddBefore : function( s ) {
		if ( !jQuery('#newmailinglist').val() ) return false;
		s.data += '&popular_ids=' + mp_user_mailinglists.popular() + '&' + jQuery( '#mailinglistchecklist :checked' ).serialize();	
		return s;
	},

	popular : function() {
		jQuery('#mailinglistchecklist-pop :checkbox').map( function() { 
			return parseInt(jQuery(this).val(), 10); 
		}).get().join(',');
	},

	AddAfter : function( r, s ) {
		var newParent = jQuery('#newmailinglist_parent'), newParentOption = newParent.find( 'option[value="-1"]' );
		jQuery(s.what + ' response_data', r).each( function() {
			mp_user_mailinglists.response(this);
		} );
	},

	response : function(_this) {
		var t = jQuery(jQuery(_this).text());
		t.find( 'label' ).each( function() {
			mp_user_mailinglists.response2(this);
		});
		newParentOption.attr( 'selected', 'selected' );
	},

	response2 : function(_this) {
		var th = jQuery(_this), val = th.find('input').val(), id  = th.find('input')[0].id, name, o;
		jQuery('#' + id).change( function() {
			mp_user_mailinglists.SyncChecks(this);
		}).change();
		if ( newParent.find( 'option[value="' + val + '"]' ).size() )
			return;
		var name = jQuery.trim( th.text() );
		var o = jQuery( '<option value="' +  parseInt( val, 10 ) + '"></option>' ).text( name );
		newParent.prepend( o );
	},

	syncChecks : function(_this) {
		if ( mp_user_mailinglists.noSyncChecks ) return;
		mp_user_mailinglists.noSyncChecks = true;
		var th = jQuery(_this), c = th.is(':checked'), 
		id = th.val().toString();	
		jQuery('#in-mailinglist-' + id + ', #in-popular-mailinglist-' + id).attr( 'checked', c );
		mp_user_mailinglists.noSyncChecks = false;
	}
}
jQuery(document).ready( function() { mp_user_mailinglists.init() });