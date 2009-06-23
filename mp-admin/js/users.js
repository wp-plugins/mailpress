// users
var mp_users = {
	theList : null,
	theExtraList : null,

	dimAfter : function( r, settings ) {
	 	var id = jQuery('id',r).text();
	 	var abbr = jQuery('now',r).text();
		var udate  = jQuery('time',r).text();
		var now = "<abbr title='"+abbr+"'>"+udate+"</abbr>";
		jQuery('tr#user-' + id + ' td.column-date').html(now);
		jQuery('li span.user-count').each( function() {
			var a = jQuery(this);
			var n = parseInt(a.html(),10);
			n = n + ( jQuery('#' + settings.element).is('.' + settings.dimClass) ? 1 : -1 );
			if ( n < 0 ) { n = 0; }
			a.html( n.toString() );
		});
		jQuery('.post-com-count span.user-count').each( function() {
			var a = jQuery(this);
			var n = parseInt(a.html(),10);
			var t = parseInt(a.parent().attr('title'), 10);
			if ( jQuery('#' + settings.element).is('.unapproved') ) { // we unapproved a formerly approved user
				n = n - 1;
				t = t + 1;
			} else { // we approved a formerly unapproved user
				n = n + 1;
				t = t - 1;
			}
			if ( n < 0 ) { n = 0; }
			if ( t < 0 ) { t = 0; }
			if ( t >= 0 ) { a.parent().attr('title', MP_AdminPageL10n.pending.replace( /%i%/, t.toString() ) ); }
			if ( 0 === t ) { a.parents('strong:first').replaceWith( a.parents('strong:first').html() ); }
			a.html( n.toString() );
		});
	},

	delAfter : function( r, settings ) {
		jQuery('li span.user-count').each( function() {
			var a = jQuery(this);
			var n = parseInt(a.html(),10);
			n = n + ( jQuery('#' + settings.element).is('.unapproved') ? -1 : 1 );
			if ( n < 0 ) { n = 0; }
			a.html( n.toString() );
		});
		jQuery('.post-com-count span.user-count').each( function() {
			var a = jQuery(this);
			if ( jQuery('#' + settings.element).is('.unapproved') ) { // we deleted an unapproved user, decrement pending title
				var t = parseInt(a.parent().attr('title'), 10);
				if ( t < 1 ) { return; }
				t = t - 1;
				a.parent().attr('title', MP_AdminPageL10n.pending.replace( /%i%/, t.toString() ) );
				if ( 0 === t ) { a.parents('strong:first').replaceWith( a.parents('strong:first').html() ); }
				return;
			}
			var n = parseInt(a.html(),10) - 1;
			a.html( n.toString() );
		});

		if ( mp_users.theExtraList.size() == 0 || mp_users.theExtraList.children().size() == 0 ) {
			return;
		}

		mp_users.theList.get(0).wpList.add( mp_users.theExtraList.children(':eq(0)').remove().clone() );
		jQuery('#get-extra-users').submit();
	},

	init : function() {
		mp_users.theExtraList 	= jQuery('#the-extra-user-list').wpList( { alt: '', delColor: 'none', addColor: 'none' } );
		mp_users.theList 		= jQuery('#the-user-list').wpList( { alt: '', dimAfter: mp_users.dimAfter, delAfter: mp_users.delAfter, addColor: 'none' } );
	}
};
jQuery(document).ready( function() { mp_users.init(); });