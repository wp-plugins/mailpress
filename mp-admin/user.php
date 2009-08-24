<?php
MailPress::require_class('Admin_page');

class MP_AdminPage extends MP_Admin_page
{
	const screen 	= 'mailpress_user';
	const capability 	= 'MailPress_edit_users';

////  Redirect  ////

	public static function redirect() 
	{
		$action = false;
		if ( isset($_POST['action']) )    $action = $_POST['action'];
		elseif ( isset($_GET['action']) ) $action = $_GET['action'];

		$list_url = self::url(MailPress_users, self::get_url_parms());

		switch($action) 
		{
			case 'delete':
				$id = $_GET['id'];
				self::require_class('Users');
				MP_Users::set_status( $id, 'delete' );
				self::mp_redirect($list_url);
			break;
			case 'activate':
				$id = $_GET['id'];
				self::require_class('Users');
				MP_Users::set_status( $id, 'active' );
				self::mp_redirect($list_url);
			break;
			case 'deactivate':
				$id = $_GET['id'];
				self::require_class('Users');
				MP_Users::set_status( $id, 'waiting' );
				self::mp_redirect($list_url);
			break;
			case 'unbounce':
				$id = $_GET['id'];
				self::require_class('Users');
				MP_Users::set_status( $id, 'waiting' );
				self::require_class('Usermeta');
				MP_Usermeta::delete($id, '_MailPress_bounce_handling');
				self::mp_redirect($list_url);
			break;
			default :
				do_action('MailPress_redirect', self::screen);
			break;
		} 
	}

////  Xmlns  ////

	public static function admin_xml_ns()
	{
		global $mp_general;
		if (!isset($mp_general['gmapkey']) || empty($mp_general['gmapkey'])) return;
		echo "xmlns:v=\"urn:schemas-microsoft-com:vml\"";

		if (class_exists('MailPress_tracking'))
		{
			MailPress::require_class('Tracking_modules');
			$MP_Tracking_modules = new MP_Tracking_modules('user');
		}
	}

////  Title  ////

	public static function title() { global $title; $title = __('MailPress User', 'MailPress'); }

////  Styles  ////

	public static function print_styles($styles = array()) 
	{
		wp_register_style (self::screen, 	'/' . MP_PATH . 'mp-admin/css/user.css', 	array('thickbox') );

		$styles[] = self::screen;
		parent::print_styles($styles);
	}

////  Scripts  ////

	public static function print_scripts() 
	{
		global $mp_general;
		if (isset($mp_general['gmapkey']) && !empty($mp_general['gmapkey']))
		{
		// google map
			wp_register_script( 'google-map',	'http://maps.google.com/maps?file=api&amp;v=2&amp;sensor=false&amp;key=' . $mp_general['gmapkey'], false, false, 1);

			$color 	= ('fresh' == get_user_option('admin_color')) ? '' : '_b';
			$pathimg 	= MP_TMP . 'mp-admin/images/map_control' . $color . '.png';
			$color 	= (is_file($pathimg)) ? $color : '';

		// mp-gmap2
			wp_register_script( 'mp-gmap2',	'/' . MP_PATH . 'mp-includes/js/mp_gmap2.js', array('google-map', 'schedule'), false, 1);
			wp_localize_script( 'mp-gmap2', 	'mp_gmapL10n', array(
				'url'		=> get_option( 'siteurl' ) . '/' . MP_PATH . 'mp-admin/images/',
				'ajaxurl'	=> MP_Action_url,
				'color'	=> $color,
				'zoomwide'	=> js_escape(__('zoom -', 'MailPress')),
				'zoomtight'	=> js_escape(__('zoom +', 'MailPress')),
				'center'	=> js_escape(__('center', 'MailPress')),
				'changemap'	=> js_escape(__('change map', 'MailPress'))
			));

			$deps[] = 'mp-gmap2';
		}

		wp_register_script( 'mp-ajax-response', 	'/' . MP_PATH . 'mp-includes/js/mp_ajax_response.js', array('jquery'), false, 1);
		wp_localize_script( 'mp-ajax-response', 	'wpAjax', array( 	
			'noPerm' => __('Update database failed', 'MailPress'), 
			'broken' => __('An unidentified error has occurred.'), 
			'l10n_print_after' => 'try{convertEntities(wpAjax);}catch(e){};' 
		));
		$deps[] = 'jquery-ui-tabs';

		wp_register_script( 'mp-lists', 		'/' . MP_PATH . 'mp-includes/js/mp_lists.js', array('mp-ajax-response'), false, 1);
		wp_localize_script( 'mp-lists', 		'wpListL10n', array(
			'url' => MP_Action_url
		));
		$deps[] = 'mp-lists';
		$deps[] = 'postbox';

		wp_register_script( 'mp-thickbox', 		'/' . MP_PATH . 'mp-includes/js/mp_thickbox.js', array('thickbox'), false, 1);
		$deps[] = 'mp-thickbox';

		wp_register_script( self::screen, 		'/' . MP_PATH . 'mp-admin/js/user.js', $deps, false, 1);
		wp_localize_script( self::screen, 		'MP_AdminPageL10n',  array(
			'screen' => self::screen
		));

		$scripts[] = self::screen;
		parent::print_scripts($scripts);
	}

////  Metaboxes  ////

	public static function screen_meta() 
	{
		global $mp_general;

		$id = (isset($_GET['id'])) ? $_GET['id'] : 0;
		add_meta_box('submitdiv', 		__('Save', 'MailPress'), array('MP_AdminPage', 'meta_box_submit'), self::screen, 'side', 'core');

		if ( current_user_can('MailPress_user_custom_fields') )
			add_meta_box('customfieldsdiv', 	__('Custom Fields'), 	array('MP_AdminPage', 'meta_box_customfields'), self::screen, 'normal', 'core');
		else
		{
			if ($id)
			{
				self::require_class('Usermeta');
				$metas = MP_Usermeta::get($id);
				if ($metas) 
				{
					if (!is_array($metas)) $metas = array($metas);
					foreach ($metas as $meta)
					{
						if ($meta->meta_key[0] == '_') continue;
						add_meta_box('customfieldsdiv', 	__('Custom Fields'), 	array('MP_AdminPage', 'meta_box_browse_customfields'), self::screen, 'normal', 'core');
						break;
					}
				}
			}
		}

		if (isset($mp_general['gmapkey']) && !empty($mp_general['gmapkey']))
		{
			add_meta_box('IP_info', __('IP info', 'MailPress'), array('MP_AdminPage', 'meta_box_IP_info'), MP_AdminPage::screen, 'side', 'core');
		}

		do_action('MailPress_add_meta_boxes_user', $id, self::screen);

		parent::screen_meta();
	}
/**/
	public static function meta_box_submit($mp_user) 
	{
		$url_parms 	= self::get_url_parms();
		if (current_user_can('MailPress_delete_users')) 	$delete_url =   clean_url(self::url(MailPress_user . "&amp;action=delete&amp;id=$mp_user->id",   $url_parms));
		if ('bounced' == $mp_user->status)				$unbounce_url = clean_url(self::url(MailPress_user . "&amp;action=unbounce&amp;id=$mp_user->id", $url_parms));
?>
<div class="submitbox" id="submitpost">
	<div id="minor-publishing">
		<div id="misc-publishing-actions">
			<div id="unbounce-action" style='padding-left:6px;'>
<?php 	if (isset($unbounce_url)) : ?>
				<a class='submitunbounce' style='padding:1px 2px;' href='<?php echo $unbounce_url ?>' onclick="return (confirm('<?php echo(js_escape(sprintf( __("You are about to unbounce this MailPress user '%s'\n  'Cancel' to stop, 'OK' to unbounce.", 'MailPress'), $mp_user->id ))); ?>'));">
					<?php _e('Unbounce', 'MailPress'); ?>
				</a>
<?php		endif; ?>
			</div>
			<br /><br />
		</div>
		<div id="minor-publishing-actions">
		</div>
		<div class="clear"></div>
	</div>
	<div id="major-publishing-actions">
		<div id="delete-action">
<?php 	if ($delete_url) : ?>
			<a class='submitdelete' href='<?php echo $delete_url ?>' onclick="return (confirm('<?php echo(js_escape(sprintf( __("You are about to delete this MailPress user '%s'\n  'Cancel' to stop, 'OK' to delete.", 'MailPress'), $mp_user->id ))); ?>'));">
				<?php _e('Delete', 'MailPress'); ?>
			</a>
<?php		endif; ?>
		</div>
		<div id="publishing-action">
			<input id='publish' type="submit" name='save' class='button-primary' value="<?php _e('Save', 'MailPress'); ?>" />
		</div>
	<div class="clear"></div>
	</div>
</div>
<?php
	}
/**/
	public static function meta_box_browse_customfields($mp_user)
	{
?>
<div id="user-import">
<?php
		$header = true;
		self::require_class('Usermeta');
		$metas = MP_Usermeta::get($mp_user->id);

		if ($metas)
		{
			if (!is_array($metas)) $metas = array($metas);

			foreach ($metas as $meta)
			{
				if ($meta->meta_key[0] == '_') continue;
	
				if ($header)
				{
					$header = false;
?>
	<table class='form-table'>
		<thead>
			<tr>
				<td style='border-bottom:none;padding:0px;width:20px;'>
				</td>
				<td style='border-bottom:none;padding:0px;'>
					<b><?php _e('Key') ?></b>
				</td>
				<td style='border-bottom:none;padding:0px;'>
					<b><?php _e('Value') ?></b>
				</td>
			</tr>
		</thead>
		<tbody>
<?php
				}
?>
			<tr>
				<td style='border-bottom:none;padding:0px;width:20px;'></td>
				<td style='border-bottom:none;line-height:0.8em;padding:0px;'>
					<input style='padding:3px;margin:0 10px 0 0;width:250px;' type='text' disabled='disabled' value="<?php echo $meta->meta_key; ?>" />
				</td>
				<td style='border-bottom:none;line-height:0.8em;padding:0px;'>
					<input style='padding:3px;margin:0 10px 0 0;width:250px;' type='text' disabled='disabled' value="<?php echo $meta->meta_value; ?>" />
				</td>
			</tr>
<?php
			}
		}
	
		if ($header) 	_e('No meta data', 'MailPress');
		else
		{
?>
			<tr>
				<td style='border-bottom:none;padding:0px;width:20px;'>&nbsp;</td>
				<td style='border-bottom:none;padding:0px;width:20px;'></td>
				<td style='border-bottom:none;padding:0px;width:20px;'></td>
			</tr>
		</tbody>
	</table>
<?php
		}
?>
</div>
<?php
	}
/**/
	public static function meta_box_customfields($mp_user)
	{
?>
<div id='postcustomstuff'>
	<div id='ajax-response'></div>
<?php
		MP_AdminPage::require_class('Usermeta');
		$metadata = MP_Usermeta::has($mp_user->id);
		$count = 0;
		if ( !$metadata ) : $metadata = array(); 
?>
	<table id='list-table' style='display: none;'>
		<thead>
			<tr>
				<th class='left'><?php _e( 'Name' ); ?></th>
				<th><?php _e( 'Value' ); ?></th>
			</tr>
		</thead>
		<tbody id='the-list' class='list:usermeta'>
			<tr><td></td></tr>
		</tbody>
	</table>
<?php else : ?>
	<table id='list-table'>
		<thead>
			<tr>
				<th class='left'><?php _e( 'Name' ) ?></th>
				<th><?php _e( 'Value' ) ?></th>
			</tr>
		</thead>
		<tbody id='the-list' class='list:usermeta'>
<?php foreach ( $metadata as $entry ) echo self::meta_box_customfield_row( $entry, $count ); ?>
		</tbody>
	</table>
<?php endif; ?>
<?php
		global $wpdb;
		$keys = $wpdb->get_col( "SELECT meta_key FROM $wpdb->mp_usermeta GROUP BY meta_key ORDER BY meta_key ASC LIMIT 30" );
		foreach ($keys as $k => $v)
		{
			if ($keys[$k][0] == '_') unset($keys[$k]);
		}
?>
	<p>
		<strong>
			<?php _e( 'Add new custom field:' ) ?>
		</strong>
	</p>
	<table id='newmeta'>
		<thead>
			<tr>
				<th class='left'>
					<label for='metakeyselect'>
						<?php _e( 'Name' ) ?>
					</label>
				</th>
				<th>
					<label for='metavalue'>
						<?php _e( 'Value' ) ?>
					</label>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td id='newmetaleft' class='left'>
<?php 
		if ( $keys ) 
		{ 
?>
					<select id='metakeyselect' name='metakeyselect' tabindex='7'>
						<option value="#NONE#"><?php _e( '- Select -' ); ?></option>
<?php
			foreach ( $keys as $key ) 
			{
				$key = self::input_text($key);
				echo "\n<option value=\"$key\">$key</option>";
			}
?>
					</select>
					<input class='hide-if-js' type='text' id='metakeyinput' name='metakeyinput' tabindex='7' value='' />
					<a href='#postcustomstuff' class='hide-if-no-js' onclick="jQuery('#metakeyinput, #metakeyselect, #enternew, #cancelnew').toggle();return false;">
					<span id='enternew'><?php _e('Enter new'); ?></span>
					<span id='cancelnew' class='hidden'><?php _e('Cancel'); ?></span></a>
<?php 
		} 
		else 
		{ 
?>
					<input type='text' id='metakeyinput' name='metakeyinput' tabindex='7' value='' />
<?php 
		} 
?>
				</td>
				<td>
					<textarea id='metavalue' name='metavalue' rows='2' cols='25' tabindex='8'></textarea>
				</td>
			</tr>
			<tr>
				<td colspan='2' class='submit'>
					<input type='submit' id='addmetasub' name='addmeta' class='add:the-list:newmeta' tabindex='9' value="<?php _e( 'Add Custom Field' ) ?>" />
					<?php wp_nonce_field( 'add-usermeta', '_ajax_nonce', false ); ?>
				</td>
			</tr>
		</tbody>
	</table>
</div>
<p><?php _e('Custom fields can be used to add extra metadata to a user that you can <a href="http://www.mailpress.org" target="_blank">use in your mail</a>.', 'MailPress'); ?></p>
<?php
	}

	public static function meta_box_customfield_row( $entry, &$count )
	{
		if ('_' == $entry['meta_key'] { 0 } ) return;

		static $update_nonce = false;
		if ( !$update_nonce ) $update_nonce = wp_create_nonce( 'add-usermeta' );

		$r = '';
		++ $count;

		if ( $count % 2 )	$style = 'alternate';
		else			$style = '';
	
		$entry['meta_key'] 	= self::input_text($entry['meta_key']);
		$entry['meta_value'] 	= self::input_text($entry['meta_value']); // using a <textarea />
		$entry['umeta_id'] 	= (int) $entry['umeta_id'];

		$delete_nonce 		= wp_create_nonce( 'delete-usermeta_' . $entry['umeta_id'] );

		$r .= "
			<tr id='usermeta-{$entry['umeta_id']}' class='$style'>
				<td class='left'>
					<label class='hidden' for='usermeta[{$entry['umeta_id']}][key]'>
" . __( 'Key' ) . "
					</label>
					<input name='usermeta[{$entry['umeta_id']}][key]' id='usermeta[{$entry['umeta_id']}][key]' tabindex='6' type='text' size='20' value='{$entry['meta_key']}' />
					<div class='submit'>
						<input name='deleteusermeta[{$entry['umeta_id']}]' type='submit' class='delete:the-list:usermeta-{$entry['umeta_id']}::_ajax_nonce=$delete_nonce deleteusermeta' tabindex='6' value='".attribute_escape(__( 'Delete' ))."' />
						<input name='updateusermeta' type='submit' tabindex='6' value='" . attribute_escape(__( 'Update' )) . "' class='add:the-list:usermeta-{$entry['umeta_id']}::_ajax_nonce=$update_nonce updateusermeta' />
					</div>
" . wp_nonce_field( 'change-usermeta', '_ajax_nonce', false, false ) . "
				</td>
				<td>
					<label class='hidden' for='usermeta[{$entry['umeta_id']}][value]'>
" . __( 'Value' ) . "
					</label>
					<textarea name='usermeta[{$entry['umeta_id']}][value]' id='usermeta[{$entry['umeta_id']}][value]' tabindex='6' rows='2' cols='30'>{$entry['meta_value']}</textarea>
				</td>
			</tr>
			";
		return $r;
	}
/**/
	public static function meta_box_IP_info($mp_user)
	{

	// meta_box_IP_info_user_settings
		$u['meta_box_IP_info_user_settings'] = get_user_option('_MailPress_meta_box_IP_info');
		if (!$u['meta_box_IP_info_user_settings']) $u['meta_box_IP_info_user_settings'] = array('center_lat' => 48.8352, 'center_lng' => 2.4718, 'zoomlevel' => 3, 'maptype' => 'NORMAL');
		$u['meta_box_IP_info_user_settings']['prefix'] = 'meta_box_IP_info';
?>
<script type='text/javascript'>
/* <![CDATA[ */
<?php
		$eol = "";
		foreach ( $u as $var => $val ) {
			echo "var $var = " . MP_AdminPage::print_scripts_l10n_val($val);
			$eol = ",\n\t\t";
		}
		echo ";\n";

	// meta_box_IP_info
		$ip = ( '' == $mp_user->laststatus_IP) ? $mp_user->created_IP : $mp_user->laststatus_IP;
		self::require_class('Ip');
		$x  = MP_Ip::get_all($ip);

		if (isset($x['geo']))
		{
			$m['meta_box_IP_info'] = $x['geo'];
			$eol = "";
			foreach ( $m as $var => $val ) {
				echo "var $var = " . self::print_scripts_l10n_val($val);
				$eol = ", \n\t\t";
			}
			echo ";\n";	
?>
/* ]]> */
</script>
		<div id='meta_box_IP_info_map'></div>
<?php 		foreach($u['meta_box_IP_info_user_settings'] as $k => $v)
			{
	                if ('prefix' == $k) continue;
?>
		<input type='hidden' id='meta_box_IP_info_<?php echo $k; ?>' value="<?php echo $v; ?>" />
<?php
			}
		}

		if (isset($x['html']))
		{
			echo $x['html'];
		}
		if (isset($x['provider']))
		{
			printf("<div><p style='margin:3px;'><i><small>" . '%1$s' . "</small></i></p></div>\n", sprintf(__('ip data provided by %1$s', 'MailPress'), sprintf('<a href="%1$s">%2$s</a>', $x['provider']['credit'], $x['provider']['credit'])));
		}
	}
////  Body  ////

	public static function body()
	{
		include (MP_TMP . 'mp-admin/includes/user.php');
	}
}
?>