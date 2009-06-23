<?php 
global $title, $mp_mail;
?>
<div class="wrap">
	<div class="icon32" id="icon-mailpress-mails"><br /></div>
	<h2><?php echo wp_specialchars( $title ); ?></h2>
<?php if (isset($message)) self::message($message); ?>
	<table class='widefat'>
		<thead>
			<tr>
<?php //self::columns(); ?>
<?php self::columns_list(); ?>
			</tr>
		</thead>
		<tbody id='the-mail-list'>
<?php self::get_row( $_GET['id'], array(), false, true); ?>
		</tbody>
	</table>
	<div id="dashboard-widgets-wrap">
		<div id='dashboard-widgets' class='metabox-holder'>
			<div class="postbox-container" style="width: 49%;">
				<div id="normal-sortables" class="meta-box-sortables ui-sortable">
<?php do_meta_boxes( self::screen, 'normal', $mp_mail ); ?>
				</div>
			</div>
			<div class="postbox-container" style="width: 49%;">
				<div id="side-sortables" class="meta-box-sortables ui-sortable">
<?php do_meta_boxes( self::screen, 'side', $mp_mail ); ?>
				</div>
			</div>
		</div>
		<form action='' method='post' style='display:none'>
			<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
			<?php wp_nonce_field( 'meta-box-order',  'meta-box-order-nonce', false );  ?>
		</form>
		<div class="clear"></div>
	</div><!-- dashboard-widgets-wrap -->
</div>