<?php
$xssl = array (	''	=> __('No','MailPress'),
			'ssl'	=> 'SSL' ,
			'tls'	=> 'TLS' ); 
$xport = array (	'25'		=> __('Default SMTP Port','MailPress'),
			'465'		=> __('Use for SSL/TLS/GMAIL','MailPress'),
			'custom'	=> __('Custom Port: (Use Box)','MailPress')); 
$xauth = array (	''		=> __('No','MailPress'),
			'CRAMMD5'	=> __('CRAM-MD5','MailPress'),
			'LOGIN'	=> __('LOGIN','MailPress'),
			'PLAIN'	=> __('PLAIN','MailPress'),
			'@PopB4Smtp'=> __('POP before SMTP','MailPress')); 
?>
						<div>
							<form name='smtpform' action='' method='post' class='mp_settings'>
								<input type='hidden' name='formname' value='smtpform' />
								<table class='form-table'>
									<tr valign='top'>
										<th scope='row'>
											<?php _e('SMTP Server','MailPress'); ?>  
										</th>
										<td colspan='2'>
											<input type='text' size='25' name='smtp_config[server]' value='<?php echo $smtp_config['server']?>' />
										</td>
									</tr>
									<tr>
										<th>
											<?php _e('Username','MailPress'); ?>  
										</th>
										<td colspan='2'>
											<input type='text' size='25' name='smtp_config[username]' value='<?php echo $smtp_config['username'];?>' />
										</td>
									</tr>
									<tr>
										<th>
											<?php _e('Password','MailPress'); ?>   
										</th>
										<td colspan='2'>
											<input type='password' size='25' name='smtp_config[password]' value='<?php echo $smtp_config['password'];?>' />
										</td>
									</tr>
									<tr>
										<th>
											<?php _e('Use SSL or TLS ?','MailPress'); ?>   
										</th>
										<td colspan='2'>
											<select name='smtp_config[ssl]'>
<?php MP_Admin::select_option($xssl,$smtp_config['ssl']);?>
											</select>
											&nbsp;
<i><?php printf( __('Site registered socket transports are : <b>%1$s</b>', 'MailPress'), (array() == stream_get_transports()) ? __('no', 'MailPress') : implode('</b>, <b>',stream_get_transports())); ?></i>
										</td>
									</tr>
									<tr>
										<th>
											<?php _e('Port','MailPress'); ?>   
										</th>
										<td colspan='2'>
											<select name='smtp_config[port]'>
<?php MP_Admin::select_option($xport,$smtp_config['port']);?>
											</select>
											&nbsp;
											<input type='text' size='4' name='smtp_config[customport]' value='<?php echo $smtp_config['customport']; ?>' />
										</td>
									</tr>
									<tr>
										<th>
											<?php _e('SMTP-AUTH ...','MailPress'); ?>   
										</th>
										<td> 
											<select name='smtp_config[smtp-auth]' id='smtp-auth'>
<?php MP_Admin::select_option($xauth,$smtp_config['smtp-auth']);?>
											</select>
										</td>
										<td id='POP3'<?php if ('@PopB4Smtp' != $smtp_config['smtp-auth']) echo " style='display:none;'"; ?>> 
											<?php _e("POP3 hostname",'MailPress'); ?>
											&nbsp;&nbsp;
											<input type='text' size='25' name='smtp_config[pophost]' value='<?php echo $smtp_config['pophost']?>' />					
										</td>
									</tr>
								</table>
<?php mp_settings_save(); ?>
							</form>
						</div>