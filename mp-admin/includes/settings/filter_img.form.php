<?php
$filter_img	= get_option('MailPress_filter_img');
$filter_img['img'] = str_replace('<', '&lt;', $filter_img['img']);
$filter_img['img'] = str_replace('>', '&gt;', $filter_img['img']);
?>
<div id='fragment-MailPress_filter_img'>
	<form name='filter_img.form' action='' method='post' class='mp_settings'>
		<input type='hidden' name='formname' value='filter_img.form' />
		<table class='form-table'>
			<tr valign='top'>
				<th scope='row'><?php _e('&lt;img&gt; defaults','MailPress'); ?></th>
				<td class='field'>
					<table>
						<tr>
							<td class='nobd'><?php _e('Alignment'); ?></td>
							<td class='nobd'>
								<input name='filter_img[align]' type='radio'<?php checked($filter_img['align'],'none'); ?> id='align-none' value='none' />
								<label for='align-none'   class='align image-align-none-label'   style='padding-left:28px; margin-right:1em;'><?php _e('None'); ?></label>
								<input name='filter_img[align]' type='radio'<?php checked($filter_img['align'],'left'); ?>  id='align-left' value='left' />
								<label for='align-left'   class='align image-align-left-label'   style='padding-left:28px; margin-right:1em;'><?php _e('Left'); ?></label>
								<input name='filter_img[align]' type='radio'<?php checked($filter_img['align'],'center'); ?>  id='align-center' value='center' />
								<label for='align-center' class='align image-align-center-label' style='padding-left:28px; margin-right:1em;'><?php _e('Center'); ?></label>
								<input name='filter_img[align]' type='radio'<?php checked($filter_img['align'],'right'); ?> id='align-right' value='right' />
								<label for='align-right'  class='align image-align-right-label'  style='padding-left:28px; margin-right:1em;'><?php _e('Right'); ?></label>
							</td>
						</tr>
						<tr>
							<td class='nobd'><?php _e('style=','MailPress'); ?></td>
							<td class='nobd'>
								<textarea rows='3' cols='61' name='filter_img[extra_style]'  style='font-family:Courier, "Courier New", monospace;'><?php echo htmlspecialchars(stripslashes($filter_img['extra_style']),ENT_QUOTES);?></textarea>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<th scope='row'><?php _e('Enter full &lt;img&gt; html tag','MailPress'); ?></th>
				<td>
					<textarea rows='5' cols='72' name='filter_img[img]'  style='font-family:Courier, "Courier New", monospace;'><?php echo MP_AdminPage::input_text($filter_img['img']); ?></textarea>
				</td>
			</tr>
<?php 
if (!empty($filter_img['img']))
{
?>
			<tr>
				<th scope='row'><?php _e('Filter result','MailPress'); ?></th>
				<td style='font-family:Courier, "Courier New", monospace;'>
					<div class='filter-img bkgndc bd1sc'>
<?php 
	$x = $filter_img['img'];
	$x = stripslashes($x);
	$x = htmlspecialchars_decode($x);
	$x = MailPress_filter_img::img_mail($x);
	$x = str_ireplace('<!-- MailPress_filter_img start -->','',$x);
	$x = str_ireplace('<!-- MailPress_filter_img end -->','',$x);
	$x = htmlspecialchars($x,ENT_QUOTES);
	echo $x;
?>
					</div>
				</td>
			</tr>
<?php } ?>
			<tr valign='top'>
				<th scope='row'><?php _e('Keep url','MailPress'); ?></th>
				<td class='field'>
					<input name='filter_img[keepurl]' type='checkbox'<?php if (isset($filter_img['keepurl'])) checked($filter_img['keepurl'],'on'); ?>  id='attach-none' value='on' style='margin-right:10px;' />
					<label for='attach-none'><?php printf(__('NO mail attachements with site images when full url (<i>&lt;img src="<b>%1$s/...</b>"</i>) is provided.','MailPress'),get_option('siteurl')); ?></label>
				</td>
			</tr>
		</table>
<?php MP_AdminPage::save_button(); ?>
	</form>
</div>