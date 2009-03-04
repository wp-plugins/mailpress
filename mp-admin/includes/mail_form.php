<?php

$message = '';
if (isset($_GET['sent'])) 	{$message  .=	__('Mail sent', 'MailPress') 					. '<br />'; $err   = true;  }
if (isset($_GET['saved'])) 	{$message  .=	__('Mail saved', 'MailPress') 				. '<br />'; $err   = true;  }
if (isset($_GET['notsent'])) 	{$message  .=	__('Mail NOT sent', 'MailPress') 				. '<br />'; $err   = false; }
if (isset($_GET['nodest'])) 	{$message  .=	__('Mail NOT sent, no recipient', 'MailPress') 		. '<br />'; $err   = false; }
if (!empty($message)) MP_Admin::message($message,$err); 

?>
<div class='wrap'>
<form id='draft' name='draft' action='' method='post'>
	<h2><?php echo $h2; ?></h2>
	<div id='poststuff'>
		<div id='submitcomment' class='submitbox' style='margin-top:13px;'>
			<p class="submit">
				<?php echo $form; ?>
				<?php echo $mailinfo; ?>
			</p>
<?php do_action('MailPress_extra_form_mail_new'); ?>
<?php if (isset($related)) { ?>
			<div class="side-info">
				<h5><?php _e('Related','MailPress'); ?></h5>
				<ul>
					<li><a href="<?php echo $related_url; ?>"><?php echo $related; ?></a></li>
				</ul>
			</div>
<?php } ?>
		</div>
		<div id='post-body'>
			<table class="form-table">
				<tr>
					<th>
						<?php _e('From','MailPress'); ?>
					</th>
					<td>
						<?php echo $from; ?>
					</td>
				</tr>
				<tr>
					<th <?php echo $style; ?>>
						<?php _e('To','MailPress'); ?>
					</th>
					<td>
						<?php echo $to; ?>
					</td>
				</tr>
				<tr>
					<th>
						<?php _e('Subject','MailPress'); ?>
					</th>
					<td>
						<b><?php echo $subject;?></b> 
					</td>
				</tr>
			</table>
		</div>
		<div id='example'>
			<ul class="tablenav ui-tabs-nav">
				<li><a href='#fragment-2'><span><?php _e('Plaintext View','MailPress'); ?></span></a></li>
				<li class='ui-tabs-selected'><a href='#fragment-1'><span><?php _e('Html View','MailPress'); ?></span></a></li>
			</ul>
			<div id='fragment-1'>
				<div style='margin:0;background:#fff;border:1px solid #c0c0c0;padding:5px;'>
					<?php echo $html; ?>
				</div>
			</div>
			<div id='fragment-2'>
				<div style='margin:0;background:#fff;border:1px solid #c0c0c0;padding:5px;'>
					<?php echo $plaintext; ?>
				</div>
			</div>
		</div>
	</div>
</form>
</div>