<?php
if (!isset($_POST['formname']) || ('test.form' != $_POST['formname'])) $test = get_option('MailPress_test');

MP_AdminPage::require_class('Themes');
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

	array_unshift($xtemplates[$theme['Template']], __('none', MP_TXTDOM));
}
?>
<div>
	<form name='test.form' action='' method='post' class='mp_settings'>
		<input type='hidden' name='formname' value='test.form' />
		<table class='form-table'>
			<tr>
				<th>
					<?php _e('To', MP_TXTDOM); ?> 
				</th>
				<td style='padding:0;'>
					<table class='subscriptions' cellspacing='0'>
						<tr>
							<td class='pr10<?php if (isset($toemailclass)) echo " $form_invalid"; ?>'>
								<?php _e('Email : ', MP_TXTDOM); ?> 
								<input type='text' size='25' name='test[toemail]' value='<?php echo $test['toemail']; ?>' />
							</td>
							<td class='pr10<?php if (isset($tonameclass)) echo " $form_invalid"; ?>'>
								<?php _e('Name : ', MP_TXTDOM); ?> 
								<input type='text' size='25' name='test[toname]' value="<?php echo MP_AdminPage::input_text($test['toname']); ?>" />
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<th scope='row'>
					<?php _e("Advanced options", MP_TXTDOM); ?> 
				</th>
				<td> 
					<?php _e('Theme', MP_TXTDOM); ?>
					&nbsp;
					<select name='test[theme]'    id='theme'>
<?php MP_AdminPage::select_option($xtheme,$test['theme']);?>
					</select>
					&nbsp;
					<?php _e('Template', MP_TXTDOM); ?>
					&nbsp;
<?php 
foreach ($xtemplates as $key => $xtemplate)
{
$xx='0';
if ($key == $test['theme']) $xx = $test['template'];
?>
					<select name='test[th][<?php echo $key; ?>][tm]' id='<?php echo $key; ?>' class='<?php if ($key != $test['theme']) echo 'mask ';?>template'>
<?php MP_AdminPage::select_option($xtemplate,$xx);?>
					</select>
<?php
}
?>
					<br /><br />
					<input name='test[forcelog]' id='forcelog' type='checkbox' <?php echo( ($test['forcelog']==true) ? "checked='checked'" : ''); ?> />
					&nbsp;
					<label for='forcelog'><?php _e('Log it', MP_TXTDOM); ?></label>
					<br />
					<input name='test[fakeit]' id='fakeit' type='checkbox' <?php echo( ($test['fakeit']==true) ? "checked='checked'" : ''); ?> />
					&nbsp;
					<label for='fakeit'><?php _e('Send it', MP_TXTDOM); ?></label>
					<br />
					<input name='test[archive]' id='archive' type='checkbox' <?php echo( ($test['archive']==true) ? "checked='checked'" : ''); ?> />
					&nbsp;
					<label for='archive'><?php _e('Archive it', MP_TXTDOM); ?></label>
					<br />
					<input name='test[stats]' id='stats' type='checkbox' <?php echo( ($test['stats']==true) ? "checked='checked'" : ''); ?> />
					&nbsp;
					<label for='stats'><?php _e('Include it in statistics', MP_TXTDOM); ?></label>
				</td>
			</tr>

		</table>
		<p class='submit'>
			<input class='button-primary' type='submit' name='Submit' value='<?php  _e('Save', MP_TXTDOM); ?>' />
			<input class='button-primary' type='submit' name='Test'   value='<?php  _e('Save &amp; Test', MP_TXTDOM); ?>' />
		</p>
	</form>
</div>