<?php
global $user_ID;

$fields = array('toemail' => __('Email: ', 'MailPress'), 'newsletter' => __('Newsletter: ', 'MailPress'), 'theme' => __('Theme: ', 'MailPress') );

$umeta = get_usermeta($user_ID, '_MailPress_post_' . $post->ID);
$test	= get_option('MailPress_test');

$xnewsletters = array();
global $mp_registered_newsletters;
foreach ($mp_registered_newsletters as $id => $data) $xnewsletters[$id] = $data['desc'];

self::require_class('Themes');
$th = new MP_Themes();
$themes = $th->themes;
	
$xthemes = array('' => '<i>' . __('current', 'MailPress') . '</i>');
foreach ($themes as $theme) $xthemes[$theme['Template']] = $theme['Template'];
unset($xthemes['plaintext']);

$current_theme = $themes[$th->current_theme]['Template'];

if ($umeta)
{
	$toemail	= $umeta['toemail'];
	$newsletter	= $umeta['newsletter'];
	$theme 	= (isset($xthemes[$umeta['theme']])) ? $umeta['theme'] : '';
}
else
{
	$toemail	= $test['toemail'];
	$newsletter = 'new_post';
	$theme 	= '';
}
?>
<div id='MailPress_test'>
	<div>
		<div style='padding:6px;text-align:right;'>
			<div style='position:relative;'>
				<div style='float:left'>
					<div id='MailPress_post_test_loading' style='position:absolute;opacity:0;filter:alpha(opacity=0);'><img src='images/wpspin_light.gif' style='padding-right:5px;' /><?php _e('Sending ...', 'MailPress'); ?></div>
					<div id='MailPress_post_test_ajax'    style='position:relative;absolute;'><br /></div>
				</div>
			</div>
			<div>
				<a style='min-width:80px;text-align:center;' class='mp_meta_box_post button' href='#mp_send'><?php _e('Test', 'MailPress'); ?></a>
			</div>
			<div class="clear"></div>
		</div>
		<div style='padding:6px 0 16px;'>
<?php
foreach ($fields as $field => $label)
{
	if ('newsletter' == $field) $lib = $xnewsletters[$$field];
	elseif ('theme' == $field) $lib = $xthemes[$$field];
	else $lib = $$field;
?>

			<div class='misc-pub-section'>
				<label><?php echo $label; ?></label>
				<b><span id='span_<?php echo $field ?>'> <?php echo $lib; ?></span></b>
				<a href='#mp_<?php echo $field ?>' class="mp-edit-<?php echo $field ?> hide-if-no-js"><?php _e('Edit') ?></a>
				<div id='mp_div_<?php echo $field ?>' class='hide-if-js' style='display:none;line-height:2.5em;margin-top:3px;'>

<?php
	switch ($field)
	{
		case 'toemail' :
?>
					<input id='mp_hidden_<?php echo $field ?>' 	name='mp_hidden_<?php echo $field ?>'	type='hidden' value='<?php echo $$field; ?>' />
					<input id='mp_<?php echo $field ?>' 		name='mp_<?php echo $field ?>' 		type='text'   value="<?php echo $$field; ?>" /> 
<?php
		break;
		case 'toname' :
?>
					<input id='mp_hidden_<?php echo $field ?>' 	name='mp_hidden_<?php echo $field ?>'	type='hidden' value="<?php echo self::input_text($$field); ?>" />
					<input id='mp_<?php echo $field ?>' 		name='mp_<?php echo $field ?>' 		type='text'   value="<?php echo self::input_text($$field); ?>" />
<?php
		break;
		case 'newsletter' :

?>
					<input id='mp_hidden_<?php echo $field ?>' name='mp_hidden_<?php echo $field ?>' type='hidden' value="<?php echo self::input_text($xnewsletters[$$field]); ?>" />
					<select id='mp_<?php echo $field ?>' name='mp_<?php echo $field ?>'>
<?php self::select_option($xnewsletters, $$field);?>
					</select>
<?php
		break;
		case 'theme' :
?>
					<input id='mp_hidden_<?php echo $field ?>' name='mp_hidden_<?php echo $field ?>' type='hidden' value='<?php echo $$field; ?>' />
					<select id='mp_<?php echo $field ?>' name='mp_<?php echo $field ?>'>
<?php self::select_option($xthemes, $theme);?>
					</select>
<?php
		break;
	}
?>
					<a class='mp-save-<?php echo $field ?> hide-if-no-js button' href='#mp_<?php echo $field ?>'><?php _e('OK'); ?></a>
					<a class='mp-cancel-<?php echo $field ?> hide-if-no-js' href='#mp_<?php echo $field ?>'><?php _e('Cancel'); ?></a>
				</div>
			</div>
			<div class="clear"></div>
<?php
}
?>
		</div>
	</div>
</div>