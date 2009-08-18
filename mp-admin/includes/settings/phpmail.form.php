<?php
$connection_phpmail = get_option('MailPress_connection_phpmail');
?>
<div id='fragment-MailPress_connection_phpmail'>
	<form name='connection_phpmail.form' action='' method='post'>
		<input type='hidden' name='formname' value='connection_phpmail.form' />
		<table class='form-table'>
			<tr valign='top'>
				<th scope='row'><?php _e('Additional_parameters','MailPress'); ?></th>
				<td class='field'>
					<input type='text' size='75' name='connection_phpmail[addparm]' value="<?php echo $connection_phpmail['addparm']; ?>" />
					<br />
					<?php  printf(__("(optional) Specify here the 5th parameter of php <a href='%s'>mail()</a> function",'MailPress'),__('http://fr.php.net/manual/en/function.mail.php','MailPress')); ?>
				</td>
			</tr>
		</table>
<?php MP_AdminPage::save_button(); ?>
	</form>
</div>