var theList; var theExtraList;
jQuery(function($) {

	columns.init(adminmailsL10n.screen);

	if ( 'undefined' == typeof $.fn.pngFix )
		$.fn.pngFix = function() { return this; }

	var thickDims = function() {
		var tbWindow = $('#TB_window');
		var H = $(window).height();
		var W = $(window).width();

		var nW = ((W - 90) > 815) ? 815 : (W - 90);
		var nH = ((H - 60) > 820) ? 820 : (H - 60);
		

		if ( tbWindow.size() ) {
			tbWindow.width( nW ).height( nH );
			$('#TB_iframeContent').width( nW ).height( nH );
			tbWindow.css({'margin-left': '-' + parseInt(( nW / 2),10) + 'px'});
			if ( typeof document.body.style.maxWidth != 'undefined' )
				tbWindow.css({'top':'30px','margin-top':'0'});
		};

		return $('a.thickbox').each( function() {
			var href = $(this).attr('href');
			if ( ! href ) return;
			href = href.replace(/&width=[0-9]+/g, '');
			href = href.replace(/&height=[0-9]+/g, '');
			$(this).attr( 'href', href + '&width=' + ( nW - 20 ) + '&height=' + ( nH - 40 ) );
		});
	};

	thickDims()
	.click( function() {
		$('#TB_title').css({'background-color':'#222','color':'#cfcfcf'});
		$('#TB_closeAjaxWindow').css({'float':'right'});
		$('#TB_ajaxWindowTitle').css({'float':'left'});

		$('#TB_iframeContent').width('100%');
		return false;
	} );

	$(window).resize( function() { thickDims() } );


var dimAfter = function( r, settings ) {
 	var id = jQuery('id',r).text();
 	var item = jQuery('item',r).text();
 	var rc = jQuery('rc',r).text();

	if (rc == 0)
	{
		$('tr#mail-' + id).after(item).remove();
	}
	if (rc == 2)
	{
		$('#the-mail-list tr:first').before(item).add();
	}
	tb_init('tr#mail-' + id + ' a.thickbox');
	thickDims()
	.click( function() {
		$('#TB_title').css({'background-color':'#222','color':'#cfcfcf'});
		$('#TB_closeAjaxWindow').css({'float':'right'});
		$('#TB_ajaxWindowTitle').css({'float':'left'});

		$('#TB_iframeContent').width('100%');
		return false;
	} );
}

var delAfter = function( r, settings ) {
	$('li span.mail-count').each( function() {
		var a = $(this);
		var n = parseInt(a.html(),10);
		n--;
		if ( n < 0 ) { n = 0; }
		a.html( n.toString() );
	});

	if ( theExtraList.size() == 0 || theExtraList.children().size() == 0 ) {
		return;
	}

	theList.get(0).wpList.add( theExtraList.children(':eq(0)').remove().clone() );
	$('#get-extra-mails').submit();
}

theExtraList = $('#the-extra-mail-list').wpList( { alt: '', delColor: 'none', addColor: 'none' } );
theList = $('#the-mail-list').wpList( { alt: '', dimAfter: dimAfter, delAfter: delAfter, addColor: 'none' } );

} );
