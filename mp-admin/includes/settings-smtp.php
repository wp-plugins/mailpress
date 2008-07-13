<?php
$xssl = array (	''	=> __('No','MailPress'),
			'ssl'	=> 'SSL' ,
			'tls'	=> 'TLS' ); 
$xport = array (	'25'		=> __('Default SMTP Port','MailPress'),
			'465'		=> __('Use for SSL/TLS/GMAIL','MailPress'),
			'custom'	=> __('Custom Port: (Use Box)','MailPress')); 
?>
						<div>
							<form name='smtpform' action='' method='post'>
								<input type='hidden' name='formname' value='smtpform' />
								<table class='form-table'>
									<tr valign='top'>
										<th scope='row'>
											<?php _e('Server Address','MailPress'); ?>  
										</th>
										<td> 
											<input type='text' size='25' name='smtp_config[server]' value='<?php echo $smtp_config['server']?>' />					
										</td>
									</tr>
									<tr>
										<th>
											<?php _e('Username','MailPress'); ?>  
										</th>
										<td> 
											<input type='text' size='25' name='smtp_config[username]' value='<?php echo $smtp_config['username'];?>' />
										</td>
									</tr>
									<tr>
										<th>
											<?php _e('Password','MailPress'); ?>   
										</th>
										<td> 
											<input type='password' size='25' name='smtp_config[password]' value='<?php echo $smtp_config['password'];?>' />
										</td>
									</tr>
									<tr>
										<th>
											<?php _e('Use SSL or TLS ?','MailPress'); ?>   
										</th>
										<td> 
											<select name='smtp_config[ssl]'>
<?php MP_Admin::select_option($xssl,$smtp_config['ssl']);?>
											</select>
											&nbsp;
<?php printf( __('(Site registered socket transports are : <b>%1$s</b> )', 'MailPress'), (array() == stream_get_transports()) ? __('no', 'MailPress') : implode('</b>, <b>',stream_get_transports())); ?>
										</td>
									</tr>
									<tr>
										<th>
											<?php _e('Port','MailPress'); ?>   
										</th>
										<td> 
											<select name='smtp_config[port]'>
<?php MP_Admin::select_option($xport,$smtp_config['port']);?>
											</select>
											&nbsp;
											<input type='text' size='4' name='smtp_config[customport]' value='<?php echo $smtp_config['customport']; ?>' />
										</td>
									</tr>
								</table>
								<p class='submit'>
									<input type='submit' name='Submit' value='<?php  _e('Save','MailPress'); ?>' />
								</p>
							</form>
						</div>