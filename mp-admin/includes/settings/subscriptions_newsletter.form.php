<?php
global $mp_general, $mp_subscriptions;
if (!isset($subscriptions)) $subscriptions = $mp_subscriptions;
if (!isset($subscriptions['default_newsletters'])) $subscriptions['default_newsletters'] = array();
?>
<tr>
	<th class='thtitle'><?php _e('Newsletters', MP_TXTDOM); ?></th>
	<td colspan='4'><input type='hidden'   name='newsletter[on]' value='on' /></td>
</tr>
<?php
$col = 4;
$item  = 1;
$row = $col * $item;
$i = $j = $td = $tr = $alt = 0;

global $mp_registered_newsletters;

foreach ($mp_registered_newsletters as $mp_registered_newsletter)
{
	if (intval ($i/$row) == $i/$row ) 
	{
		$alt++;
		$alternate = (1 == $alt) ? "class='bkgndc bd1sc'" : ((($alt/2) != intval($alt/2)) ? "class='bkgndc'" : '');
		$tr = true; 
		$td = 0;
		echo "<tr $alternate><th>";
		echo apply_filters('MailPress_subscriptions_newsletter_th', '** ' . __('Post') . ' **', $mp_registered_newsletter);
		echo "</th>\n";
	}
	if (intval ($j/$item) == $j/$item ) { echo "<td class='field' style=''>\n"; ++$td; }

	$default_style   = (isset($subscriptions['newsletters'][$mp_registered_newsletter['id']])) ? '' : " style='display:none;'" ;
?>
		<label for='newsletter_<?php echo $mp_registered_newsletter['id']; ?>'>
			<input class='newsletter' id='newsletter_<?php echo $mp_registered_newsletter['id']; ?>' name='subscriptions[newsletters][<?php echo $mp_registered_newsletter['id']; ?>]' type='checkbox'<?php checked( isset($subscriptions['newsletters'][$mp_registered_newsletter['id']]) ); ?> />
			&#160;<?php echo $mp_registered_newsletter['descriptions']['admin']; ?>
		</label>
		<br />
		<label for='default_newsletter_<?php echo $mp_registered_newsletter['id']; ?>'>
			<span id='span_default_newsletter_<?php echo $mp_registered_newsletter['id']; ?>'<?php echo $default_style; ?>>
				<input  id='default_newsletter_<?php echo $mp_registered_newsletter['id']; ?>' name='subscriptions[default_newsletters][<?php echo $mp_registered_newsletter['id']; ?>]' type='checkbox'<?php checked( isset($subscriptions['default_newsletters'][$mp_registered_newsletter['id']]) ); ?> />
				&#160;<?php _e('default', MP_TXTDOM); ?>
			</span>
		</label>
<?php
	$j++;
	if (intval ($j/$item) == $j/$item )  echo "</td>\n";
	$i++;
	if (intval ($i/$row) == $i/$row ) { echo "</tr>\n"; $tr = false; }
}
if (intval ($j/$item) != $j/$item )
{
	echo "</td>\n"; 
	while ($td < $item) {echo "<td></td>\n"; ++$td;}
}
if (intval ($i/$row) != $i/$row)  echo "</tr>\n";
?>
<tr class='mp_sep' style='line-height:2px;padding:0;'><td colspan='5' style='line-height:2px;padding:0;'></td></tr>