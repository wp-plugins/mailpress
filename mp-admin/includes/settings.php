<?php
global $wpdb, $mp_tab, $mp_general;

if (isset($_POST['formname']))
{
	$form_invalid = 'form-invalid';

	switch ($_POST['formname'])
	{
		case 'general.form' :
			include('settings/general.php');
		break;
		case 'smtp.form' :
			include('settings/smtp.php');
		break;
		case 'test.form' :
			include('settings/test.php');
		break;
		case 'subscriptions.form' :
			include('settings/subscriptions.php');
		break;
		case 'logs.form' :
			include('settings/logs.php');
		break;
		default :
			do_action('MailPress_settings_update');
		break;
	}
}
$mp_tab = (isset($mp_general['tab'])) ? $mp_general['tab'] : 0 ;
$class = " class='ui-tabs-selected'";
?>
<div class='wrap'>
	<div id='icon-mailpress-settings' class='icon32'><br /></div>
	<h2><?php _e('MailPress Settings', MP_TXTDOM); ?></h2>
	<div id='example'>
		<ul class='bkgndc tablenav<?php if (!$mp_general) echo ' ui-tabs-nav'; ?>' style='padding:3px 8px 0;vertical-align:middle;'>
			<li <?php if ((!$mp_general) || ($mp_tab=='0')) echo $class;?>><a href='#fragment-0'><span class='button-secondary'><?php _e('General', MP_TXTDOM); ?></span></a></li>
<?php if ($mp_general) : ?>
			<li <?php if ($mp_tab==1) echo $class;?>><a href='#fragment-1'><span class='button-secondary'><?php echo apply_filters('MailPress_Swift_Connection_type', 'SMTP'); ?></span></a></li>
			<li <?php if ($mp_tab==2) echo $class;?>><a href='#fragment-2'><span class='button-secondary'><?php _e('Test'    , MP_TXTDOM); ?></span></a></li>
			<li <?php if ($mp_tab==3) echo $class;?>><a href='#fragment-3'><span class='button-secondary'><?php _e('Subscriptions'    , MP_TXTDOM); ?></span></a></li>
<?php do_action('MailPress_settings_tab', $mp_tab, $class); ?>
			<li <?php if ($mp_tab==4) echo $class;?>><a href='#fragment-4'><span class='button-secondary'><?php _e('Logs'    , MP_TXTDOM); ?></span></a></li>
<?php endif; ?>
		</ul>
		<div id='fragment-0' style='clear:both;'>
			<?php include('settings/general.form.php'); ?>
		</div>
<?php if ($mp_general) : ?>
		<div id='fragment-1'>
			<?php include(apply_filters('MailPress_Swift_Connection_settings', 'settings/smtp.form.php')); ?>
		</div>
		<div id='fragment-2'>
			<?php include('settings/test.form.php'); ?>
		</div>
		<div id='fragment-3'>
			<?php include('settings/subscriptions.form.php'); ?>
		</div>
<?php do_action('MailPress_settings_div'); ?>
		<div id='fragment-4'>
			<?php include('settings/logs.form.php'); ?>
		</div>
<?php endif; ?>
	</div>
</div>