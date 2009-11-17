<?php
if (!isset($_POST['formname']) || ('subscriptions.form' != $_POST['formname'])) $subscriptions = get_option('MailPress_subscriptions');

if (!isset($subscriptions['default_newsletters'])) $subscriptions['default_newsletters'] = array();
?>
<div>
	<form name='subscriptions.form' action='' method='post' class='mp_settings'>
		<input type='hidden' name='formname' value='subscriptions.form' />
		<table class='form-table'>

<!-- Comments -->

			<tr valign='top'>
				<th style='padding:0;'><strong><?php _e('Comments','MailPress'); ?></strong></th>
				<td style='padding:0;' colspan='4'></td>
			</tr>
			<tr valign='top' class='mp_sep'>
				<th scope='row'><?php _e('Allow subscriptions to','MailPress'); ?></th>
				<td>
					<label for='comment_subscription'>
						<input id='comment_subscription' name='subscriptions[subcomment]' type='checkbox' <?php echo( ($subscriptions['subcomment']==true) ? "checked='checked'" : ''); ?> />
						&nbsp;<?php _e('Comments'); ?>
					</label>
				</td>
				<td colspan='3'></td>
			</tr>
			<tr><th></th><td colspan='4'></td></tr>

<!-- Newsletters -->

			<tr valign='top'>
				<th style='padding:0;' ><strong><?php _e('Newsletters','MailPress'); ?></strong></th>
				<td style='padding:0;' colspan='4'></td>
			</tr>
			<tr valign='top'>
				<th scope='row'>
					<?php _e("Allow subscriptions to",'MailPress'); ?>
				</th>
				<td colspan='4'></td>
			</tr>
<?php
	$col = 4;
	$item  = 1;
	$row = $col * $item;
	$i = $j = $td = $tr = $alt = 0;

	global $mp_registered_newsletters;

	foreach ($mp_registered_newsletters as $mp_registered_newsletter)
	{
		if (intval ($i/$row) == $i/$row ) 
		{
			$alt++;
			$alternate = (($alt/2) != intval($alt/2)) ? "class='bkgndc'" : '';
			$tr = true; 
			$td = 0;
			$blog = (isset($mp_registered_newsletter['params']['catname'])) ? '' : '_blog' ;
			$th =  (isset($mp_registered_newsletter['params']['catname'])) ? "<tr valign='top' $alternate><th scope='row'>" . $mp_registered_newsletter['params']['catname'] . "</th>\n" : "<tr valign='top' class='bkgndc bd1sc'><th scope='row'>" . __("** Blog **",'MailPress') . "</th>\n";
			echo $th;
		}
		if (intval ($j/$item) == $j/$item ) { echo "<td class='field' style=''>\n"; ++$td; }

		$default_style   = (isset($subscriptions['newsletters'][$mp_registered_newsletter['id']])) ? '' : " style='display:none;'" ;
		$default_checked = (isset($subscriptions['default_newsletters'][$mp_registered_newsletter['id']])) ? " checked='checked'" : '';
?>
								<label for='newsletter_<?php echo $mp_registered_newsletter['id'].$blog; ?>'>
									<input class='newsletter' id='newsletter_<?php echo $mp_registered_newsletter['id'].$blog; ?>' name='subscriptions[newsletters][<?php echo $mp_registered_newsletter['id']; ?>]' type='checkbox' <?php echo( (isset($subscriptions['newsletters'][$mp_registered_newsletter['id']])) ? "checked='checked'" : ''); ?> />
									&nbsp;<?php echo $mp_registered_newsletter['desc']; ?>
								</label>
								<br />
								<label for='default_newsletter_<?php echo $mp_registered_newsletter['id'].$blog; ?>'>
									<span id='span_default_newsletter_<?php echo $mp_registered_newsletter['id'].$blog; ?>'<?php echo $default_style; ?>>
										<input  id='default_newsletter_<?php echo $mp_registered_newsletter['id'].$blog; ?>' name='subscriptions[default_newsletters][<?php echo $mp_registered_newsletter['id']; ?>]' type='checkbox'<?php echo "$default_checked"; ?> />
										&nbsp;<?php _e('default','MailPress'); ?>
									</span>
								</label>
<?php
		$j++;
		if (intval ($j/$item) == $j/$item )  echo "</td>\n";
		$i++;
		if (intval ($i/$row) == $i/$row ) { echo "</tr>\n"; $tr = false; }
	}
	if (intval ($j/$item) != $j/$item )
	{
		echo "</td>\n"; 
		while ($td < $item) {echo "<td></td>\n"; ++$td;}
	}
	if (intval ($i/$row) != $i/$row)  echo "</tr>\n";
?>
			<tr valign='top' style='line-height:10px;padding:0;'><td colspan='5' style='line-height:10px;padding:0;'>&nbsp;</td></tr>
			<tr valign='top' class='mp_sep' style='line-height:2px;padding:0;'><td colspan='5' style='line-height:2px;padding:0;'></td></tr>

<!-- What else ? -->

<?php	do_action('MailPress_settings_subscriptions'); ?>

		</table>
<?php MP_AdminPage::save_button(); ?>
	</form>
</div>