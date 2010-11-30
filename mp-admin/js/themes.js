//themes

var mp_themes = {

	aclass : 'a.thickbox',
	tb     : null,

	init : function() {
		mp_themes.tb = mp_themes.dims();
		mp_themes.tb.click(function() { mp_themes.clicked(this); } );
		jQuery(window).resize( function() { mp_themes.dims(); } );
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

		return jQuery(mp_themes.aclass).each( function() 
		{
			var href = jQuery(this).parents('.available-theme').find('.previewlink').attr('href');
			if ( ! href ) return;
			href = href.replace(/&width=[0-9]+/g, '');
			href = href.replace(/&height=[0-9]+/g, '');
			jQuery(this).attr( 'href', href + '&width=' + ( nW - 20 ) + '&height=' + ( nH - 40 ) );
		});
	},

	clicked : function(_this) {
		var href = jQuery(_this).parents('.available-theme').find('.activatelink');
		var url = href.attr('href');
		var text = href.html();

		jQuery('#TB_title').css({'background-color':'#222','color':'#cfcfcf'});
		jQuery('#TB_closeAjaxWindow').css({'float':'right'});
		jQuery('#TB_ajaxWindowTitle').css({'float':'left'}).html('&nbsp;<a href="' + url + '" target="_top" class="tb-theme-preview-link">' + text + '</a>');
		jQuery('#TB_iframeContent').width('100%')
		return false;
	}
}
jQuery(document).ready( function() { mp_themes.init(); } );