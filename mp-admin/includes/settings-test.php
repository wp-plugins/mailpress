<?php
$th = new MP_Themes();
$themes = $th->themes; 
if (empty($test['theme'])) $test['theme'] = $themes[$th->current_theme]['Template']; 

$xtheme = $xtemplates = array();
foreach ($themes as $theme)
{
	if ( 'plaintext' == $theme['Template'] ) continue;

	$xtheme[$theme['Template']] = $theme['Template'];
	$templates = $th->get_page_templates_from($theme['Template']);

	$xtemplates[$theme['Template']] = array();
	foreach ($templates as $key => $value)
	{
		$xtemplates[$theme['Template']][$key] = $key;
	}
	if (!empty($xtemplates[$theme['Template']])) ksort($xtemplates[$theme['Template']]);

	array_unshift($xtemplates[$theme['Template']], __('no','MailPress'));
}
?>
						<div>
							<form name='testform' action='' method='post' class='mp_settings'>
								<input type='hidden' name='formname' value='testform' />
								<table class='form-table'>
									<tr>
										<th>
											<?php _e('To','MailPress'); ?> 
										</th>
										<td> 
											<?php _e('Email : ','MailPress'); ?> 
											<input type='text' size='25' name='test[toemail]' value='<?php echo $test['toemail']; ?>'<?php echo (empty($tclass)) ? '' : " class='$tclass'"; ?> />
											&nbsp;&nbsp;&nbsp;
											<?php _e('Name : ','MailPress'); ?> 
											<input type='text' size='25' name='test[toname]' value="<?php echo apply_filters('Mailpress_input_text',$test['toname']); ?>" />
										</td>
									</tr>
									<tr>
										<th scope='row'>
											<?php _e("Advanced options",'MailPress'); ?> 
										</th>
										<td> 
											<?php _e('Theme','MailPress'); ?>
											&nbsp;
											<select name='test[theme]'    id='theme'>
<?php MP_Admin::select_option($xtheme,$test['theme']);?>
											</select>
											&nbsp;
											<?php _e('Template','MailPress'); ?>
											&nbsp;
<?php 
foreach ($xtemplates as $key => $xtemplate)
{
$xx='0';
if ($key == $test['theme']) $xx = $test['template'];
?>
											<select name='test[th][<?php echo $key; ?>][tm]' id='<?php echo $key; ?>' class='<?php if ($key != $test['theme']) echo 'mask ';?>template'>
<?php MP_Admin::select_option($xtemplate,$xx);?>
											</select>
<?php
}
?>
											<br /><br />
											<input name='test[forcelog]' type='checkbox' <?php echo( ($test['forcelog']==true) ? "checked='checked'" : ''); ?> />
											&nbsp;
											<?php _e('Log it','MailPress'); ?>
											<br />
											<input name='test[fakeit]' type='checkbox' <?php echo( ($test['fakeit']==true) ? "checked='checked'" : ''); ?> />
											&nbsp;
											<?php _e('Send it','MailPress'); ?>
											<br />
											<input name='test[archive]' type='checkbox' <?php echo( ($test['archive']==true) ? "checked='checked'" : ''); ?> />
											&nbsp;
											<?php _e('Archive it','MailPress'); ?>
											<br />
											<input name='test[stats]' type='checkbox' <?php echo( ($test['stats']==true) ? "checked='checked'" : ''); ?> />
											&nbsp;
											<?php _e('Include it in statistics','MailPress'); ?>
										</td>
									</tr>

								</table>
								<p class='submit'>
									<input class='button-primary' type='submit' name='Submit' value='<?php  _e('Save','MailPress'); ?>' />
									<input class='button-primary' type='submit' name='Test'   value='<?php  _e('Save &amp; Test','MailPress'); ?>' />
								</p>
							</form>
						</div>