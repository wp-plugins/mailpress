var theList; var theExtraList;
jQuery(function($) {

var dimAfter = function( r, settings ) {
 	var id = jQuery('id',r).text();
 	var item = jQuery('item',r).text();
 	var rc = jQuery('rc',r).text();

	if (rc == 0)
	{
		$('tr#mail-' + id).after(item).remove().fadeIn();
/*
		$('li span.mail-count').each( function() {
			var a = $(this);
			var n = parseInt(a.html(),10);
			n--;
			if ( n < 0 ) { n = 0; }
			a.html( n.toString() );
		});
*/
	}
	if (rc == 2)
	{
		$('#the-mail-list tr:first').before(item).add();
	}
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
