<tr> 
	<th class='thtitle'><?php _e('Comments', MP_TXTDOM); ?></th>
	<td colspan='4'>
		<input type='hidden'   name='comment[on]' value='on' />
		<label>
			<input type='checkbox' name='subscriptions[comment_checked]'<?php checked( get_option(MailPress_comment::option) ); ?> />
			&#160;<?php _e('checked by default', MP_TXTDOM); ?>
		</label>
	</td>
</tr>
<tr class='mp_sep' style='line-height:2px;padding:0;'><td colspan='5' style='line-height:2px;padding:0;'></td></tr>