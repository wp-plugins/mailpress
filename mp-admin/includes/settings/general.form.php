<?php
$mp_general		= get_option('MailPress_general');
$mp_tab 		= (isset($mp_general['tab'])) ? $mp_general['tab'] : '0';

$cvalue = $pvalue = '' ;

$mp_general[ $mp_general['subscription_mngt'] ] = ('ajax' == $mp_general['subscription_mngt']) ? '' : $mp_general['id'] ;
if ('cat' == $mp_general['subscription_mngt']) $cvalue = $mp_general['id']; else $pvalue = $mp_general['id'];
?>
<div>
	<form name='general.form' action='' method='post'  class='mp_settings'>
		<input type='hidden' name='formname' value='general.form' />
		<table class='form-table'>

<!-- From -->

			<tr>
				<th style='padding:0;'><strong><?php _e('From','MailPress'); ?></strong></th>
				<td style='padding:0;'></td>
			</tr>
			<tr valign='top' class='mp_sep'>
				<th scope='row'><?php _e('All Mails sent from','MailPress'); ?></th>
				<td style='padding:0;'>
					<table class='subscriptions' cellspacing='0'>
						<tr>
							<td class='pr10<?php if (isset($fromemailclass)) echo " $form_invalid"; ?>'>
								<?php _e('Email : ','MailPress'); ?> 
								<input type='text' size='25' name='general[fromemail]' value='<?php echo $mp_general['fromemail']; ?>' />
							</td>
							<td class='pr10<?php if (isset($fromnameclass)) echo " $form_invalid"; ?>'>
								<?php _e('Name : ','MailPress'); ?> 
								<input type='text' size='25' name='general[fromname]'  value="<?php echo self::input_text($mp_general['fromname']); ?>" />
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr><th></th><td></td></tr>

<!-- Forms -->

			<tr valign='top'>
				<th style='padding:0;'><strong><?php _e('Forms','MailPress'); ?></strong></th>
				<td style='padding:0;' colspan='4'></td>
			</tr>
			<tr valign='top'>
				<th scope='row'><?php _e(' Manage subscriptions from','MailPress'); ?></th>
				<td style='padding:0;'>
					<table class='subscriptions' cellspacing='0'>
						<tr><td style='margin:0;padding:0;'></td><td colspan='2' style='margin:0;padding:0;'><i><?php _e('For Page and Category templates see samples in mailpress/xtras folder', 'MailPress'); ?></i></td></tr>
						<tr<?php if (isset($subscription_mngtclass)) echo " class='$form_invalid'"; ?>>
							<td class='pr10 w30'>
								<input value='ajax'    id='ajax'   name='general[subscription_mngt]' class='subscription_mngt tog' type='radio' <?php if ($mp_general) checked('ajax',$mp_general['subscription_mngt']); else checked('ajax','ajax'); ?> />
								&nbsp;&nbsp;
								<label for='ajax'><?php _e('Default','MailPress'); ?></label>
							</td>
							<td class='pr10 w30'>
								<input value='page_id' id='pageid' name='general[subscription_mngt]' class='subscription_mngt tog' type='radio' <?php checked('page_id',$mp_general['subscription_mngt']); ?> />
								&nbsp;&nbsp;
								<label for='pageid'><?php _e('Page template','MailPress'); ?></label>
							</td>
							<td class='pr10 w30'>
								<input value='cat'     id='catid'    name='general[subscription_mngt]' class='subscription_mngt tog' type='radio' <?php checked('cat',$mp_general['subscription_mngt']); ?> />
								&nbsp;&nbsp;
								<label for='catid'><?php _e('Category template','MailPress'); ?></label>
							</td>
						</tr>
						<tr>
							<td></td>
							<td class='pr10 page_id toggle<?php if ('page_id' != $mp_general['subscription_mngt']) echo ' hide'; if (isset($pclass)) echo " $form_invalid"; ?>'>
								<?php _e("Page id",'MailPress'); ?>
								&nbsp;&nbsp;
								<input type='text' size='4' name='page_id' id='page_id' value='<?php echo $pvalue; ?>' />
							</td>
							<td class='pr10 cat toggle<?php if ('cat' != $mp_general['subscription_mngt']) echo ' hide'; if (isset($cclass)) echo " $form_invalid"; ?>'>
								<?php _e("Category id",'MailPress'); ?>
								&nbsp;&nbsp;
								<input type='text' size='4' name='cat' id='cat' value='<?php echo $cvalue; ?>' />
							</td>
						</tr>
					</table>
				</td>
			</tr>
<?php	do_action('MailPress_settings_general_forms'); ?>
			<tr valign='top' class='mp_sep' >
				<th scope='row'><?php _e('View mail','MailPress'); ?></th>
				<td>
					<input id='fullscreen' name='general[fullscreen]' type='checkbox' <?php echo( (isset($mp_general['fullscreen'])) ? "checked='checked'" : ''); ?> />
					&nbsp;<label for='fullscreen'><?php _e('View mail in fullscreen','MailPress'); ?></label>
				</td>
			</tr>
			<tr><th></th><td></td></tr>

<!-- Newsletters -->

			<tr>
				<th style='padding:0;'><strong><?php _e('Newsletters','MailPress'); ?></strong></th>
				<td style='padding:0;'></td>
			</tr>
			<tr valign='top' class='mp_sep'>
				<th scope='row'><?php _e('Newsletters show at most','MailPress'); ?></th>
				<td>
					<select name='general[post_limits]'>
<option value="0"></option>
<?php self::select_number(1, 30, (isset($mp_general['post_limits'])) ? $mp_general['post_limits'] : ''); ?>
					</select>
					&nbsp;<?php _e('posts <i>(blank = WordPress Reading setting)</i>','MailPress'); ?>
				</td>
			</tr>
<?php do_action('MailPress_settings_general'); ?>
			<tr><th></th><td></td></tr>

<!-- Admin -->

			<tr>
				<th style='padding:0;'><strong><?php _e('Admin','MailPress'); ?></strong></th>
				<td style='padding:0;'></td>
			</tr>
			<tr valign='top'>
				<th scope='row'><?php _e('Options','MailPress'); ?></th>
				<td>
					<input id='wpmail' name='general[wp_mail]' type='checkbox' <?php echo( (isset($mp_general['wp_mail'])) ? "checked='checked'" : ''); ?> />
					&nbsp;<label for='wpmail'><?php _e('MailPress version of wp_mail','MailPress'); ?></label>
					<br />
					<input id='dshbrd' name='general[dashboard]' type='checkbox' <?php echo( (isset($mp_general['dashboard'])) ? "checked='checked'" : ''); ?> />
					&nbsp;<label for='dshbrd'><?php _e('Dashboard widgets','MailPress'); ?></label>
					<br />
				</td>
			</tr>
<?php do_action('MailPress_settings_general_admin'); ?>
			<tr>
				<th scope='row'><a target='_blank' style='color:#333;' title="<?php _e('get your google map api key !', 'MailPress'); ?>" href='http://www.google.com/apis/maps/signup.html'><?php _e('Google Map API Key', 'MailPress'); ?></a><br /><small><?php _e('(Optional)', 'MailPress'); ?></small></th>
				<td>
					<input type="text" size="90"  name="general[gmapkey]" value="<?php if (isset($mp_general['gmapkey'])) echo $mp_general['gmapkey']; ?>" />

				</td>
			</tr>
			<tr valign='top' class='mp_sep' style='line-height:2px;padding:0;'><th style='line-height:2px;padding:0;'></th><td style='line-height:2px;padding:0;'></td></tr>

		</table>
<?php if(!$mp_general) { ?>
		<span class='startmsg'><?php _e('You can start to update your SMTP config, once you have saved your General settings','MailPress'); ?></span>
<?php } ?>
<?php self::save_button(); ?>
	</form>
</div>