<?php

global $mp_registered_newsletters;
$default_newsletters = array('new_post'=>__("Per post",'MailPress'),'daily'=>__("Daily",'MailPress'),'weekly'=>__("Weekly",'MailPress'),'monthly'=>__("Monthly",'MailPress'));

?>
						<div>
							<form name='generalform' action='' method='post'  class='mp_settings'>
								<input type='hidden' name='formname' value='generalform' />
								<table class='form-table'>
									<tr valign='top'>
										<th scope='row'><?php _e('All Mails sent from','MailPress'); ?></th>
										<td> 
											<?php _e('Email : ','MailPress'); ?> 
											<input type='text' size='25' name='general[fromemail]' value='<?php echo $mp_general['fromemail']; ?>'<?php echo (empty($gclass)) ? '' : " class='$gclass'"; ?> />
											&nbsp;&nbsp;&nbsp;
											<?php _e('Name : ','MailPress'); ?> 
											<input type='text' size='25' name='general[fromname]'  value="<?php echo apply_filters('Mailpress_input_text',$mp_general['fromname']); ?>" />
										</td>
									</tr>
									<tr valign='top'>
										<th scope='row' style='padding:10px 10px 0 10px;'><?php _e('User manage subscriptions from','MailPress'); ?></th>
										<td style='padding:10px 10px 0 10px;'>
											<table class='general'>

												<tr>
													<td class='pr10 w30'>
														<input value='ajax'    name='general[subscription_mngt]' class='subscription_mngt tog' type='radio' <?php if ($mp_general) checked('ajax',$mp_general['subscription_mngt']); else checked('ajax','ajax'); ?> />
														&nbsp;&nbsp;
														<?php _e('Default','MailPress'); ?>
													</td>
													<td class='pr10 w30'>
														<input value='page_id' name='general[subscription_mngt]' class='subscription_mngt tog' type='radio' <?php checked('page_id',$mp_general['subscription_mngt']); ?> />
														&nbsp;&nbsp;
														<?php _e('Page template','MailPress'); ?>
													</td>
													<td class='pr10 w30'>
														<input value='cat'     name='general[subscription_mngt]' class='subscription_mngt tog' type='radio' <?php checked('cat',$mp_general['subscription_mngt']); ?> />
														&nbsp;&nbsp;
														<?php _e('Category template','MailPress'); ?>
													</td>
												</tr>
												<tr>
													<td></td>
													<td class='pr10 page_id toggle<?php if ('page_id' != $mp_general['subscription_mngt']) echo ' hide'; ?>'>
														<?php _e("Page id",'MailPress'); ?>
														&nbsp;&nbsp;
														<input type='text' size='4' name='page_id' id='page_id' value='<?php echo $pvalue; ?>'<?php echo (empty($pclass)) ? '' : " class='$pclass'"; ?> />
													</td>
													<td class='pr10 cat toggle<?php if ('cat' != $mp_general['subscription_mngt']) echo ' hide'; ?>'>
														<?php _e("Category id",'MailPress'); ?>
														&nbsp;&nbsp;
														<input type='text' size='4' name='cat' id='cat' value='<?php echo $cvalue; ?>'<?php echo (empty($cclass)) ? '' : " class='$cclass'"; ?> />
													</td>
												</tr>
											</table>
										</td>
									</tr>
									<tr>
										<th style='padding:0;'><i><?php _e('Newsletters','MailPress'); ?></i></th>
										<td style='padding:0;'><hr /></td>
									</tr>

									<tr valign='top'>
										<th scope='row'><?php _e('Allow subscriptions to','MailPress'); ?></th>
										<td>
											<table class='general'>
												<tr>
													<td class='pr10'>
														<?php _e('Comments'); ?>
													</td>
													<td colspan='4' class='pr10'>
														<input name='general[subcomment]' type='checkbox' <?php echo( ($mp_general['subcomment']==true) ? "checked='checked'" : ''); ?> />
													</td>
												</tr>
												<tr>
													<td class='field w150'>
														<?php _e("Newsletters",'MailPress'); ?>
													</td>
<?php
	$hidden_newsletters = false;
	foreach ($mp_registered_newsletters as $k => $v)
	{
		if (isset($default_newsletters[$k]))
		{
			$checked = ($mp_general['newsletters'][$k]==true) ? " checked='checked'" : '';
?>
													<td class='field w150'> 
														<input class='newsletter' id='newsletter_<?php echo $k; ?>' name='general[newsletters][<?php echo $k; ?>]' type='checkbox'<?php echo $checked; ?> />
														&nbsp;<?php echo $default_newsletters[$k]; ?>
													</td>
<?php
		}
		else
		{
			if ($mp_general['newsletters'][$k]==true)
			{
					$hidden_newsletters .= "<input name='general[newsletters][" . $k . "]' type='hidden' value='on' />\n";
			}
		}
	}
?>
												</tr>
												<tr>
													<td class='field w150'>
														<?php _e("for new subscription",'MailPress'); ?>
													</td>
<?php
	foreach ($mp_registered_newsletters as $k => $v)
	{
		if (isset($default_newsletters[$k]))
		{
			$style   = ($mp_general['newsletters'][$k]==true) ? '' : " style='display:none;'";
			$checked = (!$v['in']) ? " checked='checked'" : '';
			$checked = (!empty($style)) ? '' : $checked;
?>
													<td class='field w150'> 
														<span id='span_default_newsletter_<?php echo $k; ?>'<?php echo $style; ?>>
															<input  id='default_newsletter_<?php echo $k; ?>' name='general[default_newsletters][<?php echo $k; ?>]' type='checkbox'<?php echo "$checked"; ?> />
															&nbsp;<?php _e('default','MailPress'); ?>
														</span>
													</td>
<?php
		}
		else
		{
			if ($mp_general['default_newsletters'][$k]==true)
			{
					$hidden_newsletters .= "<input name='general[default_newsletters][" . $k . "]' type='hidden' value='on' />\n";
			}
		}
	}
?>
												</tr>
											</table>
<?php
	if ($hidden_newsletters) echo $hidden_newsletters;
?>
										</td>
									</tr>
									<tr valign='top'>
										<th scope='row'><?php _e('Newsletters show at most','MailPress'); ?></th>
										<td>
											<select name='general[post_limits]'>
												<option value="0"></option>
<?php MP_Admin::select_number(1,30,$mp_general['post_limits']); ?>
											</select>
											&nbsp;<?php _e('posts <i>(blank = WordPress Reading setting)</i>','MailPress'); ?>
										</td>
									</tr>
									<tr>
										<th style='padding:0;'><i><?php _e('Admin','MailPress'); ?></i></th>
										<td style='padding:0;'><hr /></td>
									</tr>
									<tr valign='top'>
										<th scope='row'><?php _e('Options','MailPress'); ?></th>
										<td>
											<input name='general[wp_mail]' type='checkbox' <?php echo( ($mp_general['wp_mail']==true) ? "checked='checked'" : ''); ?> />
											&nbsp;<?php _e('MailPress version of wp_mail','MailPress'); ?>
											<br />
<?php if (current_user_can('MailPress_edit_dashboard')) : ?>
											<input name='general[dashboard]' type='checkbox' <?php echo( ($mp_general['dashboard']==true) ? "checked='checked'" : ''); ?> />
											&nbsp;<?php _e('Dashboard widgets','MailPress'); ?>
											<br />
<?php endif; ?>
<?php if (apply_filters('MailPress_force_general_menu',false)) : ?>
											<input name='general[menu]' type='hidden' value='on' />
											<?php printf(__("Due to the use of '%s', MailPress admin menus are forced in one place",'MailPress'),apply_filters('MailPress_force_general_menu','')); ?><br />
<?php else : ?>
											<input name='general[menu]' type='checkbox' <?php echo( ($mp_general['menu']==true) ? "checked='checked'" : ''); ?> />
											&nbsp;<?php _e('MailPress admin menus in one place','MailPress'); ?><br />
<?php endif; ?>
										</td>
									</tr>
									<tr>
										<th scope='row'><?php _e('Logging','MailPress'); ?></th>
										<td>
<?php MP_Log::form('general', $mp_general, __('Mailing log','MailPress') , __('(for <b>ALL</b> mails send through MailPress)','MailPress'), __('Number of Log files : ','MailPress')); ?>
										</td>
									</tr>
<?php do_action('MailPress_settings_general'); ?>
								</table>
<?php if(!$mp_general) { ?>
								<span class='startmsg'><?php _e('You can start to update your SMTP config, once you have saved your General settings','MailPress'); ?></span>
<?php } ?>
<?php mp_settings_save(); ?>
							</form>
						</div>
