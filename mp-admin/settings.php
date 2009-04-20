<?php
if (!current_user_can('MailPress_manage_options')) wp_die(__('You do not have sufficient permissions to access this page.'));


function mp_settings_save()
{
?>
								<p class='submit'>
									<input class='button-primary' type='submit' name='Submit' value='<?php  _e('Save Changes'); ?>' />
								</p>
<?php
}
global $mp_tab, $mp_general, $wpdb;

$cvalue = $pvalue = $cclass = $pclass = $gclass = $tclass = '' ;

$mp_general		= get_option('MailPress_general');
$mp_tab = (isset($mp_general['tab'])) ? $mp_general['tab'] : '0';
if ($mp_general) $mp_general[ $mp_general['subscription_mngt'] ] = ('ajax' == $mp_general['subscription_mngt']) ? '' : $mp_general['id'] ;
if ('cat' == $mp_general['subscription_mngt']) $cvalue = $mp_general['id']; else $pvalue = $mp_general['id'];
$smtp_config 	= get_option('MailPress_smtp_config');
if ((25 != $smtp_config['port']) && (465 != $smtp_config['port'])) {$smtp_config['customport']=$smtp_config['port']; $smtp_config['port']= 'custom';} else $smtp_config['customport']='';
$test		 	= get_option('MailPress_test');

switch (true)
{
	case ($_POST['formname'] == 'generalform'):
		$cvalue = $pvalue = '' ;

		$mp_general		= $_POST['general'];
		$mp_general['tab']= $mp_tab = '0';

		switch ($mp_general['subscription_mngt'])
		{
			case 'ajax' :
				$mp_general['id'] = '';
			break;
			default :
				$mp_general['id'] = $_POST[$mp_general['subscription_mngt']];
				if ('cat' == $mp_general['subscription_mngt']) $cvalue = $mp_general['id']; else $pvalue = $mp_general['id'];
			break;
		}
		switch (true)
		{
			case (('ajax' != $mp_general['subscription_mngt']) && ( !is_numeric($mp_general['id']))) :
				('cat' == $mp_general['subscription_mngt']) ? $cclass = 'redbd': $pclass = 'redbd';
				MP_Admin::message(__('Id should be numeric','MailPress'),false);
			break;
			case ( !MailPress::is_email($mp_general['fromemail']) ) :
				$gclass = 'redbd';
				MP_Admin::message(__('Please, enter a valid email','MailPress'),false);
			break;
			default :
				if (!add_option ('MailPress_general', $mp_general, 'MailPress - general settings' )) update_option ('MailPress_general', $mp_general);
				MP_Admin::message(__('General settings saved','MailPress'));
			break;
		}

	break;
	case ($_POST['formname'] == 'smtpform'):
		$mp_general['tab']	= $mp_tab =  1;

		$smtp_config	= $_POST['smtp_config'];

		if ('custom' == $smtp_config['port']) 	$smtp_config ['port'] = $smtp_config['customport'];
		unset($smtp_config['customport']);

		$smtp_config['username'] = stripslashes($smtp_config['username']);

		if (!add_option ('MailPress_smtp_config', $smtp_config, 'MailPress - SMTP config' )) update_option ('MailPress_smtp_config', $smtp_config);
		if (!add_option ('MailPress_general', $mp_general, 'MailPress - general settings' )) update_option ('MailPress_general', $mp_general);

		MP_Admin::message(__('SMTP settings saved, Test it !!','MailPress'));

		if ((25 != $smtp_config['port']) && (465 != $smtp_config['port'])) {$smtp_config['customport']=$smtp_config['port']; $smtp_config['port']= 'custom';} else $smtp_config['customport']='';
	break;
	case ($_POST['formname'] == 'testform'):
		$mp_general['tab']	= $mp_tab =  3;

		$test 	= $_POST['test'];
		$test['template'] = $test['th'][$test['theme']]['tm'];
		unset($test['th']);

		if (!add_option ('MailPress_test', $test, 'MailPress -  Connection test' )) update_option ('MailPress_test', $test);
		if (!add_option ('MailPress_general', $mp_general, 'MailPress - general settings' )) update_option ('MailPress_general', $mp_general);

		$tclass = '';
		if (isset($_POST['Test']))
		{
			if ( !MailPress::is_email($test['toemail']) )
			{
				MP_Admin::message(__('Please, enter a valid Test email','MailPress'),false);
				$tclass = 'redbd';
			}
			else
			{
				$url = get_bloginfo('siteurl');
				$title = get_bloginfo('name');

				$args->Theme = $test['theme'];
				if ('0' != $test['template']) 	$args->Template = $test['template'];

				$args->id		= MP_Mail::get_id('settings');
	// Set the from name and email
				$args->fromemail 	= $mp_general['fromemail'];
				$args->fromname	= stripslashes($mp_general['fromname']);

	// Set destination address
				$args->toemail 	= $test['toemail'];
				$args->toname	= stripslashes($test['toname']);
				$key			= MP_User::get_key_by_email($args->toemail);
				if ($key)
				{
					$args->viewhtml	 = MP_User::get_view_url($key,$args->id);
					$args->unsubscribe = MP_User::get_unsubscribe_url($key);
					$args->subscribe 	 = MP_User::get_subscribe_url($key);
				}
	// Set mail's subject and body
				$args->subject	= __('Connection test - MailPress - ','MailPress') . ' ' . get_bloginfo('name');

				$args->plaintext   =  "\n\n" . __('This is a test message of MailPress from','MailPress') . ' ' . $url . "\n\n";

				$message  = "<div style='font-family: verdana,geneva;'><br /><br />";
				$message .=  sprintf(__('This is a <blink>test</blink> message of %1$s from %2$s. <br /><br />','MailPress'), ' <b>MailPress</b> ', "<a href='" .  $url . "'>$title</a>");
				$message .= "<br /><br /></div>";

				$args->html       = $message;

				if (isset($test['forcelog'])) 	$args->forcelog = '';
				if (!isset($test['fakeit'])) 		$args->nomail = '';
				if (!isset($test['archive'])) 	$args->noarchive = '';
				if (!isset($test['stats'])) 		$args->nostats = '';

				if (isset($args->Template) && (!in_array($args->Template,array('comments','confirmed','moderate','new_subscriber','new_user')))) 
				{
					$query = "SELECT ID FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'post' ORDER BY RAND() LIMIT 1;";
					$posts = $wpdb->get_results( $query );
					if ($posts)
					{
						query_posts('p='. $posts[0]->ID);
					}
				}

				if (MailPress::mail($args))
					if (!isset($test['fakeit'])) 	MP_Admin::message(__('Test settings saved, Mail not send as required','MailPress'));
					else					MP_Admin::message(__('Test successfull, CONGRATULATIONS !','MailPress'));
				else
					MP_Admin::message(__('FAILED. Check your settings !','MailPress'),false);
			}
		}
		else
		{
			MP_Admin::message(__('Test settings saved','MailPress'));
		}
	break;
	case (isset($_GET['saveg'])) :
		MP_Admin::message(__('General settings saved','MailPress'));
	break;
	default :
		do_action('MailPress_settings_extraform_update');
	break;
}
?>
<div class='wrap'>
	<div id="icon-mailpress-settings" class="icon32"><br /></div>
	<h2><?php _e('MailPress Settings','MailPress'); ?></h2>
	<div id='example'>
		<ul class="bkgndc tablenav<?php if (!$mp_general) echo ' ui-tabs-nav'; ?> " style='padding:3px 8px 0;vertical-align:middle;'>
			<li <?php if ((!$mp_general) || ($mp_tab=='0')) echo " class='ui-tabs-selected'"; ?>><a href='#fragment-1'><span class='button-secondary'><?php _e('General','MailPress'); ?></span></a></li>
<?php
if ($mp_general)
{
?>
			<li <?php if ($mp_tab==1) echo " class='ui-tabs-selected'"; ?>><a href='#fragment-2'><span class='button-secondary'><?php echo apply_filters('MailPress_Swift_Connection_type','SMTP'); ?></span></a></li>
			<li <?php if ($mp_tab==3) echo " class='ui-tabs-selected'"; ?>><a href='#fragment-4'><span class='button-secondary'><?php _e('Test'    ,'MailPress'); ?></span></a></li>
<?php
do_action('MailPress_settings_extraform_tab',$mp_tab);
}
?>
		</ul>
		<div id='fragment-1' style='clear:both;'>
			<?php include('includes/settings-general.php'); ?>
		</div>
<?php
if ($mp_general)
{
?>
		<div id='fragment-2'>
			<?php include(apply_filters('MailPress_Swift_Connection_settings','includes/settings-smtp.php')); ?>
		</div>
		<div id='fragment-4'>
			<?php include('includes/settings-test.php'); ?>
		</div>
<?php
do_action('MailPress_settings_extraform_div');
}
?>
	</div>
</div>
