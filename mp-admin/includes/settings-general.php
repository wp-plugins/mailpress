<?php
$xlevel = array (	MP_Log::noMP_Log	=> __('No logging','MailPress') ,
				0	=> __('Mailing log','MailPress') ,
				1 	=> 'E_ERROR', 
				2 	=> 'E_WARNING', 
				4 	=> 'E_PARSE', 
				8 	=> 'E_NOTICE', 
				16 	=> 'E_CORE_ERROR', 
				32 	=> 'E_CORE_WARNING', 
				64 	=> 'E_COMPILE_ERROR', 
				128 	=> 'E_COMPILE_WARNING', 
				256 	=> 'E_USER_ERROR', 
				512 	=> '* E_USER_WARNING *', 			// specific logging available for class MAIL__
				1024 	=> 'E_USER_NOTICE', 
				2048 	=> 'E_STRICT', 
				4096 	=> 'E_RECOVERABLE_ERROR', 
				8191 	=> 'E_ALL' ); 
?>
						<div>
							<form name='generalform' action='' method='post'>
								<input type='hidden' name='formname' value='generalform' />
								<table class='form-table'>
									<tr valign='top'>
										<th scope='row'>
											<?php _e('All Emails send from','MailPress'); ?>
										</th>
										<td> 
											<?php _e('Email : ','MailPress'); ?> 
											<input type='text' size='25' name='general[fromemail]' value='<?php echo $mp_general['fromemail']; ?>' <?php echo $gstyle; ?>/>
											&nbsp;&nbsp;&nbsp;
											<?php _e('Name : ','MailPress'); ?> 
											<input type='text' size='25' name='general[fromname]'  value='<?php echo $mp_general['fromname']; ?>' />
										</td>
									</tr>
									<tr valign='top'>
										<th scope='row'>
											<?php _e('Managing subcription with','MailPress'); ?>
										</th>
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
										<th scope='row'>
											<?php _e('Allow','MailPress'); ?>
										</th>
										<td>
											<input name='general[subcomment]' type='checkbox' <?php echo( ($mp_general['subcomment']==true) ? "checked='checked'" : ''); ?> />
											&nbsp;<?php _e('Subscribe to comments','MailPress'); ?>
											<br/>
											<input name='general[new_post]' type='checkbox' <?php echo( ($mp_general['new_post']==true) ? "checked='checked'" : ''); ?> />
											&nbsp;<?php _e('Mail when new post published','MailPress'); ?>
											<br/>
											<input name='general[daily]' type='checkbox' <?php echo( ($mp_general['daily']==true) ? "checked='checked'" : ''); ?> />
											&nbsp;<?php _e("Mail 'previous day'",'MailPress'); ?>
											<br/>
											<input name='general[weekly]' type='checkbox' <?php echo( ($mp_general['weekly']==true) ? "checked='checked'" : ''); ?> />
											&nbsp;<?php _e("Mail 'previous week'",'MailPress'); ?>
											<br/>
											<input name='general[monthly]' type='checkbox' <?php echo( ($mp_general['monthly']==true) ? "checked='checked'" : ''); ?> />
											&nbsp;<?php _e("Mail 'previous month'",'MailPress'); ?>
										</td>
									</tr>
									<tr valign='top'>
										<th scope='row'>
											<?php _e('Admin','MailPress'); ?>
										</th>
										<td>
											<input name='general[wp_mail]' type='checkbox' <?php echo( ($mp_general['wp_mail']==true) ? "checked='checked'" : ''); ?> />
											&nbsp;<?php _e('MailPress version of wp_mail','MailPress'); ?>
											<br/>
											<input name='general[menu]' type='checkbox' <?php echo( ($mp_general['menu']==true) ? "checked='checked'" : ''); ?> />
											&nbsp;<?php _e('MailPress admin menus in one place','MailPress'); ?>

										</td>
									</tr>
									<tr valign='top'>
										<th scope='row'>
											<?php _e('Logging','MailPress'); ?>
										</th>
										<td>
											<?php _e('Logging level : ','MailPress'); ?>
											<select name='general[level]'>
<?php MP_Admin::select_option($xlevel,$mp_general['level']);?>
											</select> 
											&nbsp;&nbsp;
											<?php _e('(for <b>ALL</b> mails send through MailPress)','MailPress'); ?>
											<br/>
											<?php _e('Number of Log files : ','MailPress'); ?>
											<select name='general[lognbr]'>
<?php MP_Admin::select_number(1,10,$mp_general['lognbr']);?>
											</select>
											<?php _e('(one log file per day)','MailPress'); ?>
											&nbsp;&nbsp;&nbsp;&nbsp;
											<?php _e('Date of last purge','MailPress'); ?>
											<input type='text' size='8' value='<?php echo $mp_general['lastpurge']; ?>' disabled='disabled' />
											<input type='hidden' name='general[lastpurge]' value='<?php echo $mp_general['lastpurge']; ?>' />

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
