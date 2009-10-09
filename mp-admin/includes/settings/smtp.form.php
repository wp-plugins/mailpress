<?php
$xssl = array (	''	=> __('No','MailPress'),
			'ssl'	=> 'SSL' ,
			'tls'	=> 'TLS' ); 
$xport = array (	'25'		=> __('Default SMTP Port','MailPress'),
			'465'		=> __('Use for SSL/TLS/GMAIL','MailPress'),
			'custom'	=> __('Custom Port: (Use Box)','MailPress')); 

$smtp_config 	= get_option('MailPress_smtp_config');
$smtp_config['customport']='';
if (!in_array($smtp_config['port'], array(25, 465))) 
{
	$smtp_config['customport'] = $smtp_config['port']; 
	$smtp_config['port'] = 'custom';
}

if (isset($pophostclass)) $smtp_config['smtp-auth'] = '@PopB4Smtp';
?>
<div>
	<form name='smtp.form' action='' method='post' class='mp_settings'>
		<input type='hidden' name='formname' value='smtp.form' />
		<table class='form-table'>
			<tr valign='top'<?php if (isset($serverclass)) echo " class='form-invalid'"; ?>>
				<th scope='row'>
					<?php _e('SMTP Server','MailPress'); ?>  
				</th>
				<td colspan='2'>
					<input type='text' size='25' name='smtp_config[server]' value='<?php echo $smtp_config['server']?>' />
				</td>
			</tr>
			<tr<?php if (isset($usernameclass)) echo " class='form-invalid'"; ?>>
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
					<input type='password' size='25' name='smtp_config[password]' value="<?php echo $smtp_config['password'];?>" />
				</td>
			</tr>
			<tr>
				<th>
					<?php _e('Use SSL or TLS ?','MailPress'); ?>   
				</th>
				<td colspan='2'<?php if (isset($customportclass)) echo " class='form-invalid'"; ?>>
					<select name='smtp_config[ssl]'>
<?php MP_AdminPage::select_option($xssl,$smtp_config['ssl']);?>
					</select>
					&nbsp;
<i><?php printf( __('Site registered socket transports are : <b>%1$s</b>', 'MailPress'), (array() == stream_get_transports()) ? __('none', 'MailPress') : implode('</b>, <b>',stream_get_transports())); ?></i>
				</td>
			</tr>
			<tr>
				<th>
					<?php _e('Port','MailPress'); ?>   
				</th>
				<td colspan='2'>
					<select name='smtp_config[port]'>
<?php MP_AdminPage::select_option($xport,$smtp_config['port']);?>
					</select>
					&nbsp;
					<input type='text' size='4' name='smtp_config[customport]' value='<?php echo $smtp_config['customport']; ?>' />
				</td>
			</tr>
			<tr>
				<th>
					<label for='smtp-auth'><?php _e('Pop before Smtp','MailPress'); ?></label>
				</th>
				<td> 
					<input type='checkbox' name='smtp_config[smtp-auth]' id='smtp-auth' value='@PopB4Smtp'<?php if (isset($smtp_config['smtp-auth']) && ('@PopB4Smtp' == $smtp_config['smtp-auth'])) checked(true); ?> />
				</td>
				<td id='POP3'<?php  echo (isset($smtp_config['smtp-auth']) && ('@PopB4Smtp' == $smtp_config['smtp-auth'])) ? '' : " style='display:none;'"; if (isset($pophostclass)) echo " class='form-invalid'"; ?>> 
					<?php _e("POP3 hostname",'MailPress'); ?>
					&nbsp;&nbsp;
					<input type='text' size='25' name='smtp_config[pophost]' value='<?php if (isset($smtp_config['pophost'])) echo $smtp_config['pophost']; ?>' />
					<?php _e("port",'MailPress'); ?>
					&nbsp;&nbsp;
					<input type='text' size='4' name='smtp_config[popport]'  value='<?php if (isset($smtp_config['popport'])) echo $smtp_config['popport']; ?>' />
				</td>
			</tr>
		</table>
<?php MP_AdminPage::save_button(); ?>
	</form>
</div>