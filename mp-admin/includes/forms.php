<?php
self::require_class('Forms');

global $action;

$url_parms = self::get_url_parms(array('id', 's', 'apage'));
$h2 = __('Forms','MailPress');

wp_reset_vars(array('action'));
if ('edit' == $action) 
{
	$id = (int) $_GET['id'];
	$form = MP_Forms::get($id);
		
	$h3 = sprintf(__('Update form # %1$s','MailPress'), $id);
	$action = 'edited';
	$cancel = "<input type='submit' class='button' name='cancel' value=\"" . __('Cancel','MailPress') . "\" />\n";
}
else 
{
	$form = new stdClass();
	$h3 = __('Add a form','MailPress');
	$action = self::add_form_id;
	$cancel = '';
}
// form settings tab

$tabs = array('attributes' => __('Attributes', 'MailPress'), 'options' => __('Options', 'MailPress'), 'messages' => __('Messages', 'MailPress'), 'recipient' => __('Recipient', 'MailPress'), 'visitor' => __('Visitor', 'MailPress') );
if ( isset($_GET['action']) && ('edit' == $_GET['action']) ) $tabs['html'] = __('Html', 'MailPress'); 

// Mail themes and templates

self::require_class('Themes');
$th = new MP_Themes();
$themes = $th->themes; 

if (!isset($form->settings['recipient']['theme'])) $form->settings['recipient']['theme'] = $themes[$th->current_theme]['Template'];
if (!isset($form->settings['visitor'  ]['theme'])) $form->settings['visitor'  ]['theme'] = $themes[$th->current_theme]['Template'];

$xtheme = $xtemplates = array();
foreach ($themes as $theme)
{
	if ( 'plaintext' == $theme['Template'] ) continue;

	$xtheme[$theme['Template']] = $theme['Template'];
	$templates = $th->get_page_templates_from($theme['Template']);

	$xtemplates[$theme['Template']] = array();
	foreach ($templates as $key => $value)
	{
		if (strpos($key, 'form') !== 0 ) continue;
		$xtemplates[$theme['Template']][$key] = $key;
	}
	if (!empty($xtemplates[$theme['Template']])) ksort($xtemplates[$theme['Template']]);

	array_unshift($xtemplates[$theme['Template']], __('none','MailPress'));
}

// Form templates

self::require_class('Forms_templates');
$form_templates = new MP_Forms_templates();
$xform_template = $form_templates->get_all();

// Subscribing visitor actions

$xvisitor_subscriptions['0'] = __('no', 'MailPress');
$xvisitor_subscriptions['1'] = __('not active', 'MailPress');
$xvisitor_subscriptions['2'] = __('to be confirmed', 'MailPress');
$xvisitor_subscriptions['3'] = __('active', 'MailPress');

$xvisitor_mail['0'] = __('no', 'MailPress');
$xvisitor_mail['1'] = __('to be confirmed', 'MailPress');
$xvisitor_mail['2'] = __('yes', 'MailPress');


//
// MANAGING MESSAGE
//

$messages[1] = __('Form added.','MailPress');
$messages[2] = __('Form deleted.','MailPress');
$messages[3] = __('Form updated.','MailPress');
$messages[4] = __('Form not added.','MailPress');
$messages[5] = __('Form not updated.','MailPress');
$messages[6] = __('Forms deleted.','MailPress');

if (isset($_GET['message']))
{
	$message = $messages[$_GET['message']];
	$_SERVER['REQUEST_URI'] = remove_query_arg(array('message'), $_SERVER['REQUEST_URI']);
}

//
// MANAGING PAGINATION
//

$url_parms['apage'] = isset($url_parms['apage']) ? $url_parms['apage'] : 1;
do
{
	$start = ( $url_parms['apage'] - 1 ) * 20;
	list($_forms, $total) = self::get_list($start, 25, $url_parms); // Grab a few extra
	$url_parms['apage']--;
} while ( $total <= $start );
$url_parms['apage']++;

$forms 		= array_slice($_forms, 0, 20);
$extra_forms 	= array_slice($_forms, 20);

$page_links = paginate_links	(array(	'base' => add_query_arg( 'apage', '%#%' ),
							'format' => '',
							'total' => ceil( $total / 20),
							'current' => $url_parms['apage']
						)
					);
if ($url_parms['apage'] == 1) unset($url_parms['apage']);
?>
<div class="wrap nosubsub">
	<div id="icon-mailpress-tools" class="icon32"><br /></div>
	<h2><?php echo $h2; ?></h2>
<?php if (isset($message)) self::message($message); ?>
	<form class='search-form topmargin' action='' method='get'>
		<p class='search-box'>
			<input type='hidden' name='page' value='<?php echo MailPress_page_forms; ?>' />
			<input type='text' id='post-search-input' name='s' value='<?php if (isset($url_parms['s'])) echo $url_parms['s']; ?>' class="search-input"  />
			<input type='submit' value="<?php _e( 'Search Forms', 'MailPress' ); ?>" class='button' />
		</p>
	</form>
	<br class='clear' />
	<div id="col-container">
		<div id="col-right">
			<div class="col-wrap">	
				<form id='posts-filter' action='' method='get'>
					<div class='tablenav'>
<?php 	if ( $page_links ) echo "						<div class='tablenav-pages'>$page_links</div>"; ?>
						<div class='alignleft actions'>
							<input type='submit' value="<?php _e('Delete','MailPress'); ?>" name='deleteit' class='button-secondary delete action' />
							<input type='hidden' name='page' value='<?php echo MailPress_page_forms; ?>' />
						</div>
						<br class='clear' />
					</div>
					<div class="clear"></div>
					<table class='widefat'>
						<thead>
							<tr>
<?php self::columns_list(); ?>
							</tr>
						</thead>
						<tfoot>
							<tr>
<?php self::columns_list(false); ?>
							</tr>
						</tfoot>
						<tbody id='<?php echo self::list_id; ?>' class='list:<?php echo self::tr_prefix_id; ?>'>
<?php if ($forms) : ?>
<?php foreach ($forms as $_form) 		echo self::get_row( $_form->id, $url_parms ); ?>
<?php endif; ?>
						</tbody>
<?php if ($extra_forms) : ?>
						<tbody id='<?php echo self::list_id; ?>-extra' class='list:<?php echo self::tr_prefix_id; ?>' style='display: none;'>
<?php
	foreach ($extra_forms as $_form)	echo self::get_row( $_form->id, $url_parms ); ?>
						</tbody>
<?php endif; ?>
					</table>
					<div class='tablenav'>
<?php 	if ( $page_links ) echo "						<div class='tablenav-pages'>$page_links</div>\n"; ?>
						<div class='alignleft actions'>
							<input type='submit' value="<?php _e('Delete','MailPress'); ?>" name='deleteit' class='button-secondary delete' />
						</div>
						<br class='clear' />
					</div>
					<br class='clear' />
				</form>
			</div>
		</div><!-- /col-right -->
		<div id="col-left">
			<div class="col-wrap">
				<div class="form-wrap">
					<h3><?php echo $h3; ?></h3>
					<div id="ajax-response"></div>
					<form name='<?php echo $action; ?>'  id='<?php echo $action; ?>'  method='post' action='' class='<?php echo $action; ?>:<?php echo self::list_id; ?>: validate'>
						<input type='hidden' name='action'   value='<?php echo $action; ?>' />
<?php self::post_url_parms($url_parms, array('id', 's', 'apage')); ?>
						<?php wp_nonce_field('update-' . self::tr_prefix_id); ?>

						<div class="form-field form-required" style='margin:0;padding:0;'>
							<label for='form_label'><?php _e('Label','MailPress'); ?></label>
							<input name='label' id='form_label' type='text' value="<?php if (isset($form->label)) echo self::input_text($form->label); ?>" size='40' aria-required='true' />
							<p>&nbsp;</p>
						</div>

						<div class="form-field" style='margin:0;padding:0;'>
							<span style='float:right'>
								<span class='description'><small><?php _e('template', 'MailPress'); ?></small></span>
								<select id='f_template' name='template' style='margin-right:14px;'>
<?php self::select_option($xform_template, (isset($form->template)) ? $form->template : 'default' ); ?>
								</select>
							</span>
							<label for='form_description' style='display:inline;'><?php _e('Description','MailPress'); ?></label>
							<input name='description' id='form_description' type='text' value="<?php if (isset($form->description)) echo self::input_text($form->description); ?>" size='40' />
							<p><small><?php _e('The description can be use to give further explanations','MailPress'); ?></small></p>
						</div>

						<div id='form_settings' class='form field form_settings' style='margin-top:18px;'>
							<ul>
<?php foreach($tabs as $tab_type => $tab) echo "<li><a href='#settings_tab_$tab_type'><span>$tab</span></a></li>\n"; ?>
							</ul>
							<div style='clear:both;' >
<?php
	foreach($tabs as $tab_type => $tab) 
	{
		echo "								<div id='settings_tab_$tab_type' class='ui-tabs settings_form_tabs settings_$tab_type'>\n";
		switch ($tab_type)
		{
			case 'attributes' : 
?>
									<span class='description'><small>class="</small></span><input type='text' name='settings[attributes][class]' id='form_attribute_class' 	value="<?php if (isset($form->settings['attributes']['class'])) echo self::input_text($form->settings['attributes']['class']); ?>" size='40' style='width:80%'  /><span class='description'><small>"</small></span><br />
									<span class='description'><small>style="</small></span><input type='text' name='settings[attributes][style]' id='form_attribute_style' 	value="<?php if (isset($form->settings['attributes']['style'])) echo self::input_text($form->settings['attributes']['style']); ?>" size='40' style='width:80%'  /><span class='description'><small>"</small></span><br />
									<input type='text' name='settings[attributes][misc]' id='form_attribute_misc' 	value="<?php if (isset($form->settings['attributes']['misc'])) echo self::input_text($form->settings['attributes']['misc']); ?>" size='40' style='width:98%'  /><br />
									<span class='description'><i style='color:#666;font-size:8px;'><?php _e("other attributes except 'name' & 'action'", 'MailPress'); ?></i></span>
<?php
			break;
			case 'options'    : 
?>
									<input type='checkbox' name='settings[options][reset]' id='form_option_reset' value='1'<?php checked('1', ((isset($form->settings['options']['reset'])) ? 1 : 0)); ?> style='width:auto;' />
									<label for='form_option_reset' style='display:inline;'><small><?php _e('Reset after submission', 'MailPress'); ?></small></label>
<?php
			break;
			case 'messages'    : 
?>
									<label for='f_message_ok'><small><?php _e('When processing form is successfull','MailPress'); ?></small></label>
									<input name='settings[message][ok]' id='f_message_ok' type='text' value="<?php if (isset($form->settings['message']['ok'])) echo self::input_text($form->settings['message']['ok']); ?>" size='40' style='width:98%;' />
									<label for='f_message_ko'><small><?php _e('When processing form has failed','MailPress'); ?></small></label>
									<input name='settings[message][ko]' id='f_message_ko' type='text' value="<?php if (isset($form->settings['message']['ko'])) echo self::input_text($form->settings['message']['ko']); ?>" size='40' style='width:98%;' />
<?php
			break;

			case 'recipient'    : 
?>
									<div id='div_form_toemail'>
										<label for='form_toemail'><small><?php _e('Email','MailPress'); ?></small></label>
										<input type="text" id='form_toemail' name='settings[recipient][toemail]' value="<?php if (isset($form->settings['recipient']['toemail'])) echo $form->settings['recipient']['toemail']; ?>" size="40" style='width:auto;' />
									</div>
									<label for='form_toname'><small><?php _e('Name','MailPress'); ?></small></label>
									<input type="text" id='form_toname' name='settings[recipient][toname]' value="<?php if (isset($form->settings['recipient']['toname'])) echo self::input_text($form->settings['recipient']['toname']); ?>" size="40" style='width:auto;' />
									<label for='recipient_theme'><small><?php _e('Mail Theme/Template','MailPress'); ?></small></label>
									<select id='recipient_theme' name='settings[recipient][theme]'>
<?php self::select_option($xtheme, (isset($form->settings['recipient']['theme'])) ? $form->settings['recipient']['theme'] : null ); ?>
									</select>
<?php 
foreach ($xtemplates as $key => $xtemplate)
{
$xx = '0';
if ($key == $form->settings['recipient']['theme'])  if (isset($form->settings['recipient']['template'])) $xx = $form->settings['recipient']['template'];
?>
									<select name='settings[recipient][th][<?php echo $key; ?>][tm]' id='recipient_<?php echo $key; ?>' class='<?php if ($key != $form->settings['recipient']['theme']) echo 'mask ';?>recipient_template'>
<?php self::select_option($xtemplate, $xx);?>
									</select>
<?php
}
			break;
			case 'visitor'    : 
?>
									<label for='visitor_subscription'><small><?php _e('Subscription option','MailPress'); ?></small></label>
									<select id='visitor_subscription' name='settings[visitor][subscription]'>
<?php self::select_option($xvisitor_subscriptions, (isset($form->settings['visitor']['subscription'])) ? $form->settings['visitor']['subscription'] : 0 ); ?>
									</select>
									<small><?php _e('Becomes a subscriber','MailPress'); ?></small>
									<label for='visitor_mail'><small><?php _e('Mail option', 'MailPress'); ?></small></label>
									<select id='visitor_mail' name='settings[visitor][mail]'>
<?php self::select_option($xvisitor_mail, (isset($form->settings['visitor']['mail'])) ? $form->settings['visitor']['mail'] : 0 ); ?>
									</select>
									<small><?php _e('Receives a copy', 'MailPress'); ?></small>
									<div style='margin:0px;padding:0px;border:none;' class='<?php echo (isset($form->settings['visitor']['mail']) && ($form->settings['visitor']['mail'] != '0')) ? '' : 'mask '; ?>visitor_mail_selected'>
										<label for='visitor_theme'><small><?php _e('Mail Theme/Template','MailPress'); ?></small></label>
										<select id='visitor_theme' name='settings[visitor][theme]'>
<?php self::select_option($xtheme, (isset($form->settings['visitor']['theme'])) ? $form->settings['visitor']['theme'] : null ); ?>
										</select>
<?php 
foreach ($xtemplates as $key => $xtemplate)
{
$xx = '0';
if ($key == $form->settings['visitor']['theme'])  if (isset($form->settings['visitor']['template'])) $xx = $form->settings['visitor']['template'];
?>
										<select name='settings[visitor][th][<?php echo $key; ?>][tm]' id='visitor_<?php echo $key; ?>' class='<?php if ($key != $form->settings['visitor']['theme']) echo 'mask ';?>visitor_template'>
<?php self::select_option($xtemplate, $xx);?>
										</select>
<?php
}
?>
									</div>
<?php
			break;
			case 'html'       : 
				MailPress::require_class('Forms_fields');
				$html = $form_templates->get_form_template($form->template);
				if (!$html) $html = '{{form}}';

				$search = $replace = array();
				$search[] = '{{label}}'; 	$replace[] = $form->label;
				$search[] = '{{description}}'; 	$replace[] = $form->description;
				$search[] = '{{form}}'; 	$replace[] = sprintf('%1$s<!-- %2$s --></form>', MP_Forms::get_tag($form, MP_Forms_fields::have_file($form->id)), __('form content', 'MailPress') );
				$search[] = '{{message}}'; 	$replace[] = sprintf ('<!-- %1$s -->', __('ok/ko message', 'MailPress') );
				$html = str_replace($search, $replace, $html );
?>
									<div class="filter-img bkgndc bd1sc" style='font-family:"Courier new", serif;font-size:11px;font-style:normal;'>
										<?php echo htmlspecialchars($html, ENT_QUOTES); ?>
									</div>
									<p><small><?php printf(__('Template : %1$s', 'MailPress'), $form->template); ?></small></p>
<?php
			break;
		}
		echo "								</div>\n";
	}
?>									
							</div>
						</div>
						<p style='margin:15px 0;'>
							<input type='submit' class='button-primary' name='submit' id='form_submit' value="<?php echo $h3; ?>" />
							<?php echo $cancel; ?>	
						</p>
					</form>
				</div>
			</div>
		</div><!-- /col-left -->
	</div><!-- /col-container -->
</div><!-- /wrap -->