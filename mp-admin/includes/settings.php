<?php
global $wpdb, $mp_general, $mp_subscriptions;

$_tabs['general'] = __('General', MP_TXTDOM);

if (isset($_POST['formname']))
{
	$form_invalid = 'form-invalid';
	$no_error = true;

	if (substr($_POST['formname'], -5) == '.form') include('settings/' . substr($_POST['formname'], 0, -5) . '.php');
}

if ($mp_general)
{
	$t = apply_filters('MailPress_Swift_Connection_type', 'SMTP');
	$_tabs['connection_' . strtolower($t)] = $t;
	$_tabs = apply_filters('MailPress_settings_tab_connection', $_tabs);

	$_tabs['test'] = __('Test', MP_TXTDOM);

	$_tabs = apply_filters('MailPress_settings_tab', $_tabs);

	$_tabs['logs'] = __('Logs', MP_TXTDOM);
}

$tab_active = (isset($mp_general['tab'])) ? $mp_general['tab'] : 'general';
if (!isset($_POST['formname']))
{
	$parms = MP_AdminPage::get_url_parms(array('tab'));
	if (!empty($parms) && isset($parms['tab'])) $tab_active = $parms['tab'];
}

$divs = array();
?>
<div class='wrap'>
	<div id='icon-mailpress-settings' class='icon32'><br /></div>
	<h1><?php _e('MailPress Settings', MP_TXTDOM); ?></h1>
<?php if (isset($message)) MP_AdminPage::message($message, $no_error); ?>
	<div id='settings-tabs'>
		<ul>
<?php 
	$i = $i_tab = 0;
	foreach($_tabs as $_tab => $desc)
	{
		if ($tab_active == $_tab) $i_tab = $i;
		echo "\t\t\t<li style='float:left;' ><a href='#fragment-$_tab' title='" . esc_attr($desc) . "'><span class='button-secondary'>$desc</span></a></li>\n";
		$i++;
	}
	wp_localize_script( MailPress_page_settings, 'MP_AdminPage_var', array( 'the_tab' => $i_tab ) );
?>
		</ul>
<?php
	foreach($_tabs as $_tab => $desc)
	{
?>
		<div class='fragments' id='fragment-<?php echo $_tab; ?>'>
			<?php include("settings/$_tab.form.php"); ?>
		</div>
<?php
	}
?>
	</div>
</div>