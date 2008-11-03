<?php

global $mp_registered_newsletters;
$default_newsletters = array('new_post'=>'','daily'=>'','weekly'=>'','monthly'=>'');

?>
						<div>
							<form name='generalform' action='' method='post'>
								<input type='hidden' name='formname' value='generalform' />
								<table class='form-table'>
									<tr valign='top'>
										<th scope='row'><?php _e('All Emails send from','MailPress'); ?></th>
										<td> 
											<?php _e('Email : ','MailPress'); ?> 
											<input type='text' size='25' name='general[fromemail]' value='<?php echo $mp_general['fromemail']; ?>' <?php echo $gstyle; ?>/>
											&nbsp;&nbsp;&nbsp;
											<?php _e('Name : ','MailPress'); ?> 
											<input type='text' size='25' name='general[fromname]'  value='<?php echo $mp_general['fromname']; ?>' />
										</td>
									</tr>
									<tr valign='top'>
										<th scope='row'><?php _e('Managing subcription with','MailPress'); ?></th>
										<td>
											<table class='general'>

												<tr>
													<td style='padding-right:10px;'>
														<input value='ajax'    name='general[subscription_mngt]' class='subscription_mngt tog' type='radio' <?php if ($mp_general) checked('ajax',$mp_general['subscription_mngt']); else checked('ajax','ajax'); ?> />
														&nbsp;&nbsp;
														<?php _e('Default','MailPress'); ?>
													</td>
													<td></td>
												</tr>
												<tr>
													<td style='padding-right:10px;'>
														<input value='page_id' name='general[subscription_mngt]' class='subscription_mngt tog' type='radio' <?php checked('page_id',$mp_general['subscription_mngt']); ?> />
														&nbsp;&nbsp;
														<?php _e('Page template','MailPress'); ?>
													</td>
													<td style='padding-right:10px;' class='page_id toggle<?php if ('page_id' != $mp_general['subscription_mngt']) echo ' hide'; ?>'>
														<input type='text' size='4' name='page_id' id='page_id' value='<?php echo $pvalue; ?>' <?php echo $pstyle; ?> />
														&nbsp;&nbsp;
														<?php _e("Page id",'MailPress'); ?>
													</td>
												</tr>
												<tr>
													<td style='padding-right:10px;'>
														<input value='cat'     name='general[subscription_mngt]' class='subscription_mngt tog' type='radio' <?php checked('cat',$mp_general['subscription_mngt']); ?> />
														&nbsp;&nbsp;
														<?php _e('Category template','MailPress'); ?>
													</td>
													<td style='padding-right:10px;' class='cat toggle<?php if ('cat' != $mp_general['subscription_mngt']) echo ' hide'; ?>'>
														<input type='text' size='4' name='cat' id='cat' value='<?php echo $cvalue; ?>'  <?php echo $cstyle; ?>/>
														&nbsp;&nbsp;
														<?php _e("Category id",'MailPress'); ?>
													</td>
												</tr>
											</table>
										</td>
									</tr>
									<tr valign='top'>
										<th scope='row'><?php _e('Allow','MailPress'); ?></th>
										<td>
											<input name='general[subcomment]' type='checkbox' <?php echo( ($mp_general['subcomment']==true) ? "checked='checked'" : ''); ?> />
											&nbsp;<?php _e('Subscribe to comments','MailPress'); ?>
<?php
	foreach ($mp_registered_newsletters as $k => $v)
	{
		if (isset($default_newsletters[$k]))
		{
			$checked = ($mp_general['newsletters'][$k]==true) ? "checked='checked'" : '';
?>
											<br/> 
											<input name='general[newsletters][<?php echo $k; ?>]' type='checkbox' <?php echo $checked; ?>/>&nbsp;<?php echo $v['desc']; ?>
<?php
		}
		else
		{
			if ($mp_general['newsletters'][$k]==true)
			{
?>
											<input name='general[newsletters][<?php echo $k; ?>]' type='hidden' value='on'/>
<?php
			}
		}
	}
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
									<tr valign='top'>
										<th scope='row'><?php _e('Admin','MailPress'); ?></th>
										<td>
											<input name='general[wp_mail]' type='checkbox' <?php echo( ($mp_general['wp_mail']==true) ? "checked='checked'" : ''); ?> />
											&nbsp;<?php _e('MailPress version of wp_mail','MailPress'); ?>
											<br/>
<?php if (apply_filters('MailPress_force_general_menu',false)) : ?>
											<input name='general[menu]' type='hidden' value='on' />
											<?php printf(__("Due to the use of '%s', MailPress admin menus are forced in one place",'MailPress'),apply_filters('MailPress_force_general_menu','')); ?>
<?php else : ?>
											<input name='general[menu]' type='checkbox' <?php echo( ($mp_general['menu']==true) ? "checked='checked'" : ''); ?> />
											&nbsp;<?php _e('MailPress admin menus in one place','MailPress'); ?>
<?php endif; ?>
										</td>
									</tr>
									<tr>
										<th scope='row'><?php _e('Logging','MailPress'); ?></th>
										<td>
<?php MP_Log::form('general', $mp_general, __('Mailing log','MailPress') , __('(for <b>ALL</b> mails send through MailPress)','MailPress'), __('Number of Log files : ','MailPress')); ?>
										</td>
									</tr>
								</table>
<?php if(!$mp_general) { ?>
								<span style='text-align:left;font-weight:bold;color:red;'><?php _e('You can start to update your SMTP config, once you have saved your General settings','MailPress'); ?></span>
<?php } ?>
								<p class='submit'>
									<input type='submit' name='Submit' value='<?php  _e('Save','MailPress'); ?>' />
								</p>
							</form>
						</div>
