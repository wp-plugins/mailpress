jQuery(document).ready( function() {
	// close postboxes that should be closed
	jQuery('.if-js-closed').removeClass('if-js-closed').addClass('closed');

	// show things that should be visible, hide what should be hidden
	jQuery('.hide-if-no-js').show();
	jQuery('.hide-if-js').hide();

	// postboxes
	add_postbox_toggles('user');

	// user tabs
	var mailinglistTabs =jQuery('#user-tabs').tabs();
});
