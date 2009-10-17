<?php

$style = '';

$list_url = self::url(MailPress_mails,false,self::get_url_parms());

if ( isset($_POST['action']) )    $action = $_POST['action'];
elseif ( isset($_GET['action']) ) $action = $_GET['action'];  

switch($action) 
{
	case 'iview' :
		global $title;
		$h2 = $title;
		self::require_class('Mails');
		$mail = MP_Mails::get( (isset($_POST['id'])) ? $_POST['id'] : $_GET['id'] );
?>
<div class='wrap'>
	<div id="icon-mailpress-mails" class="icon32"><br /></div>
	<h2><?php echo $h2; ?></h2>
<?php if (isset($message)) self::message($message); ?>
	<form id='mail' name='mail_form' action='' method='post'>

		<input type="hidden" name='id' 		value="<?php echo $mail->id ?>" id='mail_id' />
		<input type="hidden" name='referredby' 	value='<?php echo clean_url($_SERVER['HTTP_REFERER']); ?>' />
		<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
		<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>

		<div id='poststuff' class='metabox-holder has-right-sidebar'>
			<div id="side-info-column" class="inner-sidebar">
<?php $side_meta_boxes = do_meta_boxes(self::screen, 'side', $mail); ?>
			</div>

			<div id="post-body" class="<?php echo $side_meta_boxes ? 'has-sidebar' : ''; ?>">
				<div id="post-body-content" class="has-sidebar-content">
<?php do_meta_boxes(self::screen, 'normal', $mail); ?>
				</div>
			</div>
		</div>
	</form>
</div>
<?php
    break;
}
?>