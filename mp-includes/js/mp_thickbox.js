var mp_thickbox = {

	aclass : 'a.thickbox',
	tb     : '',

	clicked : function() {
		jQuery('#TB_title').css({'background-color':'#222','color':'#cfcfcf'});
		jQuery('#TB_closeAjaxWindow').css({'float':'right'});
		jQuery('#TB_ajaxWindowTitle').css({'float':'left'});
		jQuery('#TB_iframeContent').width('100%');
		mp_thickbox.dims();
		return false;
	},

	dims : function() {
		var thickboxW = jQuery('#TB_window');
		var H = jQuery(window).height();
		var W = jQuery(window).width();

		var nW = ((W - 90) > 815) ? 815 : (W - 90);
		var nH = ((H - 60) > 820) ? 820 : (H - 60);

		if ( thickboxW.size() ) 
		{
			thickboxW.width( nW ).height( nH );
			jQuery('#TB_iframeContent').width( nW ).height( nH );
			thickboxW.css({'margin-left': '-' + parseInt(( nW / 2),10) + 'px'});
			if ( typeof document.body.style.maxWidth != 'undefined' )
				thickboxW.css({'top':'30px','margin-top':'0'});
		};

		return jQuery(mp_thickbox.aclass).each( function() 
		{
			var href = jQuery(this).attr('href');
			if ( ! href ) return;
			href = href.replace(/&width=[0-9]+/g, '');
			href = href.replace(/&height=[0-9]+/g, '');
			jQuery(this).attr( 'href', href + '&width=' + ( nW - 20 ) + '&height=' + ( nH - 40 ) );
		});
	},

	init : function() {
		mp_thickbox.tb = mp_thickbox.dims();
		mp_thickbox.tb.click(function() { mp_thickbox.clicked(); } );
		jQuery(window).resize( function() { mp_thickbox.dims(); } );
	}
}
jQuery(document).ready( function() { mp_thickbox.init(); } );