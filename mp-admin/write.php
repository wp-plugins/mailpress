<?php 
MailPress::require_class('Admin_page');

class MP_AdminPage extends MP_Admin_page
{
	const screen 	= MailPress_page_write;
	const capability 	= 'MailPress_edit_mails';
	const help_url	= 'http://www.mailpress.org/wiki/index.php?title=Help:Admin:New_Mail';

////  Redirect  ////

	public static function redirect() 
	{
		if ( isset($_POST['action']) )    $action = $_POST['action'];
		elseif ( isset($_GET['action']) ) $action = $_GET['action'];  

		if (!isset($action)) return;

		$list_url = self::url(MailPress_mails, self::get_url_parms());

		switch($action) 
		{
			case 'delete' :
				$id = $_GET['id'];
				self::require_class('Mails');
				MP_Mails::delete($id);
				self::mp_redirect($list_url . '&deleted=1');
			break;
			case 'send' :
				$id = $_GET['id'];
				self::require_class('Mails');
				$x = MP_Mails::send_draft($id);
				$url = (is_numeric($x))	? $list_url . '&sent=' . $x : $list_url . '&notsent=1';
				self::mp_redirect($url);
			break;
			case 'draft' :
				self::require_class('Mails');
				$id = (0 == $_POST['id']) ? MP_Mails::get_id(__CLASS__ . ' ' . __METHOD__ . ' ' . self::screen) : $_POST['id'];
				MP_Mails::update_draft($id);

				switch (true)
				{
					case isset($_POST['addmeta']) :
						self::require_class('Mailmeta');
						MP_Mailmeta::add_meta($id);
					break;
					case isset($_POST['updatemeta']) :
						foreach ($_POST['meta'] as $mmeta_id => $meta)
						{
							self::require_class('Mailmeta');
							$meta_key = $meta['key'];
							$meta_value = $meta['value'];
							MP_Mailmeta::update_by_id($mmeta_id , $meta_key, $meta_value);
						}
					break;
					case isset($_POST['deletemeta']) :
						foreach ($_POST['deletemeta'] as $mmeta_id => $x)
						{
							self::require_class('Mailmeta');
							MP_Mailmeta::delete_by_id( $mmeta_id );
						}
					break;
				}

				$parm = "&saved=1";

				if (isset($_POST['send']))
				{
					$x = MP_Mails::send_draft($id);
					if (is_numeric($x))
						if (0 == $x)	$parm = "&sent=0";
						else			$parm = "&sent=$x";
					else				$parm = "&nodest=0";
				}

				$url = (strstr($_SERVER['HTTP_REFERER'], MailPress_edit)) ? MailPress_edit : MailPress_write;
				$url .= "$parm&id=$id";
				self::mp_redirect($url);
			break;
		}
	}

////  Title  ////

	public static function title() { global $title; $title = (isset($_GET['file']) && ('write' == $_GET['file'])) ? __('Edit Mail', MP_TXTDOM) : __('Write Mail', MP_TXTDOM); }

////  Styles  ////

	public static function print_styles($styles = array()) 
	{
		wp_register_style ( self::screen, 		'/' . MP_PATH . 'mp-admin/css/write.css', 	array('thickbox') );

		$styles[] = self::screen;
		parent::print_styles($styles);
	}

////  Scripts  ////

	public static function print_scripts($scripts, $is_footer) 
	{
		wp_register_script( 'mp-ajax-response', 	'/' . MP_PATH . 'mp-includes/js/mp_ajax_response.js', array('jquery'), false, 1);
		wp_localize_script( 'mp-ajax-response', 'wpAjax', array( 
			'noPerm' => __('Email was not sent AND/OR Update database failed', MP_TXTDOM), 
			'broken' => __('An unidentified error has occurred.'), 
			'l10n_print_after' => 'try{convertEntities(wpAjax);}catch(e){};' 
		) );

		wp_register_script( 'mp-autosave', 		'/' . MP_PATH . 'mp-includes/js/autosave.js', array('schedule', 'mp-ajax-response'), false, 1);
		wp_localize_script( 'mp-autosave', 'autosaveL10n', array	( 	
			'autosaveInterval'=> '60', 
			'previewMailText'	=>  __('Preview'), 
			'requestFile' 	=> MP_Action_url, 
			'savingText'	=> __('Saving draft...', MP_TXTDOM)
		) );

		wp_register_script( 'mp-lists', 		'/' . MP_PATH . 'mp-includes/js/mp_lists.js', array('mp-ajax-response'), false, 1);
		wp_localize_script( 'mp-lists', 'wpListL10n', array( 
			'url' => MP_Action_url
		) );

		wp_register_script( 'mp_swf_upload', 	'/' . MP_PATH . 'mp-includes/js/fileupload/swf.js', array('swfupload'), false, 1);
	// these error messages came from the sample swfupload js, they might need changing.
		wp_localize_script( 'mp_swf_upload', 'swfuploadL10n', array(
			'queue_limit_exceeded' => __('You have attempted to queue too many files.'), 
			'file_exceeds_size_limit' => sprintf(__('This file is too big. Your php.ini upload_max_filesize is %s.'), @ini_get('upload_max_filesize')), 
			'zero_byte_file' => __('This file is empty. Please try another.'), 
			'invalid_filetype' => __('This file type is not allowed. Please try another.'), 
			'default_error' => __('An error occurred in the upload. Please try again later.'), 
			'missing_upload_url' => __('There was a configuration error. Please contact the server administrator.'), 
			'upload_limit_exceeded' => __('You may only upload 1 file.'), 
			'http_error' => __('HTTP error.'), 
			'upload_failed' => __('Upload failed.'), 
			'io_error' => __('IO error.'), 
			'security_error' => __('Security error.'), 
			'file_cancelled' => __('File cancelled.'), 
			'upload_stopped' => __('Upload stopped.'), 
			'dismiss' => __('Dismiss'), 
			'crunching' => __('Crunching&hellip;'), 
			'deleted' => __('Deleted'), 
			'l10n_print_after' => 'try{convertEntities(swfuploadL10n);}catch(e){};'
		) );

		wp_register_script( 'mp_html_sifiles', 	'/' . MP_PATH . 'mp-includes/js/fileupload/si.files.js', array(), false, 1);
		wp_register_script( 'mp_html_upload', 	'/' . MP_PATH . 'mp-includes/js/fileupload/htm.js', array('mp_html_sifiles'), false, 1);
		wp_localize_script( 'mp_html_upload', 'htmuploadL10n', array(
			'img' => 'images/wpspin_light.gif', //get_option('siteurl') . '/' . MP_PATH . 'mp-admin/images/loading_small.gif', 
			'iframeurl' => MP_Action_url, 
			'uploading' => __('Uploading ...', MP_TXTDOM), 
			'attachfirst' => __('Attach a file', MP_TXTDOM), 
			'attachseveral' => __('Attach another file', MP_TXTDOM), 
			'l10n_print_after' => 'try{convertEntities(htmuploadL10n);}catch(e){};' 
		) );

		$deps = array('quicktags', 'mp-autosave', 'mp-lists', 'postbox');
		if ( user_can_richedit() )	$deps[] = 'editor';
		$deps[] = 'thickbox';
		$deps[] = (self::flash()) ? 'mp_swf_upload' : 'mp_html_upload';

		wp_register_script( self::screen, 		'/' . MP_PATH . 'mp-admin/js/write.js', $deps, false, 1);
		wp_localize_script( self::screen, 'MP_AdminPageL10n', array( 	
			'errmess' => __('Enter a valid email !', MP_TXTDOM), 
			'screen' => self::screen, 
			'l10n_print_after' => 'try{convertEntities(MP_AdminPageL10n);}catch(e){};' 
		) );

		$scripts[] = self::screen;
		parent::print_scripts($scripts);

		$_footer_enabled = ($GLOBALS['wp_version'] >= '2.8') ? true : false;
		if ($_footer_enabled && !$is_footer) return;

		global $hook_suffix;
		$action = ($_footer_enabled && $is_footer) ? "admin_footer-$hook_suffix" : "admin_head-$hook_suffix" ;
		add_action($action, 'wp_tiny_mce', 25);
		if (self::flash()) add_action($action, array('MP_AdminPage', 'swfupload'));
	}

	public static function flash() 
	{
		// If Mac and mod_security, no Flash. :(
		$flash = (isset($_GET['flash'])) ? $_GET['flash'] : true;
		$flash = ( false !== strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'mac') && apache_mod_loaded('mod_security') ) ? false : $flash;
		return $flash;
	}

	// swfupload
	public static function swfupload() 
	{
		$m = array('mp_swfupload' => array(
				'flash_url' 			=> includes_url('js/swfupload/swfupload.swf'), 

				'button_text' 			=> "<span class='mp_button'>" .  js_escape(__('Attach a file', MP_TXTDOM)) . "</span>", 
				'button_text_style' 		=> '.mp_button { text-align: left; color: #21759B; text-decoration: underline; font-family:"Lucida Grande", "Lucida Sans Unicode", Tahoma, Verdana, sans-serif; } .mp_button:hover {cursor:pointer;}', 
				'another_button_text'		=> "<span class='mp_button'>" .  js_escape(__('Attach another file', MP_TXTDOM)) . "</span>", 


				'button_height'			=> '24', 
				'button_width'			=> '132', 
				'button_image_url'		=> get_option('siteurl') . '/' . MP_PATH . 'mp-includes/images/upload.png', 
				'button_placeholder_id'		=> 'flash-browse-button', 

				'upload_url' 			=> MP_Action_url, 

				'file_post_name'			=> 'async-upload', 
				'file_types'			=> '*.*', 
				'file_size_limit'			=> wp_max_upload_size() . 'b', 

				'post_params'			=> array (
										'action'		=> 'swfu_mail_attachement', 
										'auth_cookie'	=> (is_ssl()) ? $_COOKIE[SECURE_AUTH_COOKIE] : $_COOKIE[AUTH_COOKIE], 
										'_wpnonce'		=> wp_create_nonce('mp_attachement')
									), 

				'custom_settings'			=> array (
										'degraded_element_id' => 'html-upload-ui', // id of the element displayed when swfupload is unavailable
										'swfupload_element_id'=> 'flash-upload-ui' // id of the element displayed when swfupload is available
									), 

				'debug'				=> false
			));
		echo "<script type='text/javascript'>\n/* <![CDATA[ */\n";
		$eol = "";
		foreach ( $m as $var => $val ) {
			echo "var $var = " . self::print_scripts_l10n_val($val);
			$eol = ", \n\t\t";
		}
		echo ";\n";
		echo "/* ]]> */\n</script>";
	}

////  Metaboxes  ////

	public static function screen_meta() 
	{
		$id = (isset($_GET['id'])) ? $_GET['id'] : 0;
		add_meta_box('submitdiv', 		__('Send', MP_TXTDOM), 		array('MP_AdminPage', 'meta_box_submit'), 		self::screen, 'side', 'core');
		add_meta_box('plaintextbox', 		__('Plain Text', MP_TXTDOM), 	array('MP_AdminPage', 'meta_box_plaintext'), 		self::screen, 'normal', 'high');
		add_meta_box('attachementsdiv', 	__('Attachements', MP_TXTDOM), 	array('MP_AdminPage', 'meta_box_attachements'), 	self::screen, 'side', 'core');
		add_meta_box('themesdiv', 		__('Change Theme', MP_TXTDOM), 	array('MP_AdminPage', 'meta_box_themes'), 		self::screen, 'side', 'core');

		if ( current_user_can('MailPress_mail_custom_fields') )
			add_meta_box('customfieldsdiv', 	__('Custom Fields'), 			array('MP_AdminPage', 'meta_box_customfields'), 	self::screen, 'normal', 'core');

		if ($id)
		{
			self::require_class('Mailmeta');
			$rev_ids 	= MP_Mailmeta::get($id, '_MailPress_mail_revisions');
		}
		if (isset($rev_ids))
			add_meta_box('revisionbox', 	__('Mail Revisions', MP_TXTDOM), 	array('MP_AdminPage', 'meta_box_revision'), 		self::screen, 'normal', 'high');

		do_action('MailPress_add_meta_boxes_write', $id, self::screen);

		parent::screen_meta();
	}
/**/
	public static function meta_box_submit($draft) 
	{
		if ($draft)
		{
			if (isset($draft->id))
			{
				if (current_user_can('MailPress_delete_mails')) $delete_url = clean_url(MailPress_mail  ."&amp;action=delete&amp;id=$draft->id");
				$preview_url= clean_url(add_query_arg( array('action' => 'iview', 'id' => $draft->id, 'KeepThis' => 'true', 'TB_iframe' => 'true'), MP_Action_url ));
				$preview	= "<a class='preview button' target='_blank' href='$preview_url'>" . __('Preview') . "</a>";
			}
		}
?>
<div class="submitbox" id="submitpost">
	<div id="minor-publishing">
		<div id="minor-publishing-actions">
			<input type='submit' name='save' id='save-post' class='button button-highlighted' 	value="<?php _e('Save Draft', MP_TXTDOM); ?>"  />
			<span id='previewview27'><?php if (isset($preview)) echo $preview; ?></span>
		</div>
		<div class="clear"><br /><br /><br /><br /><br /></div>
	</div>
	<div id="major-publishing-actions">
		<div id="delete-action">
<?php 	if (isset($delete_url)) : ?>
			<a class='submitdelete' href='<?php echo $delete_url ?>' onclick="if (confirm('<?php echo(js_escape(sprintf( __("You are about to delete this draft '%s'\n  'Cancel' to stop, 'OK' to delete."), $draft->id ))); ?>')) return true; return false;">
				<?php _e('Delete', MP_TXTDOM); ?>
			</a>
<?php		endif; ?>
		</div>
		<div id="publishing-action">
<?php 	if (current_user_can('MailPress_send_mails')) : ?><input id='publish' type='submit' name='send' class='button-primary' value="<?php _e('Send', MP_TXTDOM); ?>" /><?php endif; ?>
		</div>
		<div class="clear"></div>
	</div>
</div>
<?php
	}
/**/
	public static function meta_box_plaintext($draft)
	{
?>
<textarea id='plaintext' name='plaintext' cols='40' rows='1'><?php echo (isset($draft->plaintext)) ? htmlspecialchars(stripslashes($draft->plaintext), ENT_QUOTES) : ''; ?></textarea>
<?php
	}
/**/
	public static function meta_box_revision($draft)
	{
		self::require_class('Mails');
		MP_Mails::list_mail_revisions($draft->id);
	}
/**/
	public static function meta_box_attachements($draft) 
	{
		if ($draft) $draft_id = (isset($draft->id)) ? $draft->id : 0;
		if (self::flash()) 
		{
			$divid = 'flash-upload-ui';
			$divs  = "<div><div id='flash-browse-button'></div></div>";
			$url   = clean_url(add_query_arg('flash', 0));
			$txt   = __('homemade uploader', MP_TXTDOM);
		}
		else
		{
			$divid = 'html-upload-ui';
			$divs  = "<div class='mp_fileupload_txt'><span class='mp_fileupload_txt'></span></div><div class='mp_fileupload_file' id='mp_fileupload_file_div'></div>";
			$url   = clean_url(remove_query_arg('flash'));
			$txt   = __('Flash uploader', MP_TXTDOM);
		}
?>
<script type="text/javascript">
<!--
var draft_id = <?php echo $draft_id; ?>;
//-->
</script>
<div id="attachement-items">
<?php 	self::get_attachements_list($draft_id); ?>
</div>
<div><span id='attachement-errors'></span></div>

<div id='<?php echo $divid; ?>'><?php echo $divs; ?>
	<br class="clear" />
	<p>
		<input type='hidden' name='type_of_upload' value="<?php echo $divid; ?>" />
		<?php printf(__('Problems?  Try the %s.', MP_TXTDOM), sprintf ("<a id='mp_loader_link' href='%1s'>%2s</a>", $url , $txt )); ?>
	</p>
</div>
<?php
	}

	public static function get_attachements_list($draft_id)
	{
		self::require_class('Mailmeta');
		$metas = MP_Mailmeta::has( $draft_id, '_MailPress_attached_file');
		if ($metas) foreach($metas as $meta) self::get_attachement_row($meta);
	}

	public static function get_attachement_row($meta)
	{
		$meta_value = unserialize( $meta['meta_value'] );
		if (!is_file($meta_value['file_fullpath'])) return;
		$href = clean_url(add_query_arg( array('action' => 'attach_download', 'id' => $meta['mmeta_id']), MP_Action_url ));

?>
	<div id='attachement-item-<?php echo $meta['mmeta_id']; ?>' class='attachement-item child-of-<?php echo $meta['mail_id']; ?>'>
		<table cellspacing='0'>
			<tr>
				<td>
					<input type='checkbox' class='mp_fileupload_cb' checked='checked' name='Files[<?php echo $meta['mmeta_id']; ?>]' value='<?php echo $meta['mmeta_id']; ?>' />
				</td>
				<td>&nbsp;<a href='<?php echo $href; ?>' style='text-decoration:none;'><?php echo $meta_value['name']; ?></a></td>
			</tr>
		</table>
	</div>

<?php
	}
/**/
	public static function meta_box_themes($draft)
	{
		$xtheme = array();
		self::require_class('Themes');
		$th = new MP_Themes();
		$themes = $th->themes;

		foreach ($themes as $theme)
		{
			if ( 'plaintext' == $theme['Template'] ) continue;
			$xtheme[$theme['Template']] = $theme['Template'];
		}
        $current_theme = $themes[$th->current_theme]['Template'];
        $current_mail_theme = (isset($draft->theme)) ? $draft->theme : '';
?>
	<p id='MailPress_extra_form_mail_new'>
<?php printf(__('Current theme is : %s', MP_TXTDOM), $current_theme ); ?>
		<br class='clear' />
			<input type='hidden' name='CurrentTheme' value="<?php echo $current_theme; ?>" />
			<select id='Theme' name='Theme'>
				<option value="" style='font-style:italic;'><?php _e('current theme', MP_TXTDOM); ?></option>
<?php self::select_option($xtheme, $current_mail_theme);?>
			</select>
		<br class='clear' />
	</p>
<?php
	}
/**/
	public static function meta_box_customfields($draft)
	{
?>
<div id='postcustomstuff'>
	<div id='ajax-response'></div>
<?php
        $id = (isset($draft->id)) ? $draft->id : '';
		self::require_class('Mailmeta');
		$metadata = MP_Mailmeta::has($id);
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
		<tbody id='the-list' class='list:mailmeta'>
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
		<tbody id='the-list' class='list:mailmeta'>
<?php foreach ( $metadata as $entry ) echo self::meta_box_customfield_row( $entry, $count ); ?>
		</tbody>
	</table>
<?php endif; ?>
<?php
		global $wpdb;
		$keys = $wpdb->get_col( "SELECT meta_key FROM $wpdb->mp_mailmeta GROUP BY meta_key ORDER BY meta_key ASC LIMIT 30" );
		foreach ($keys as $k => $v)
		{
			if ($keys[$k][0] == '_') unset($keys[$k]);
			if ('batch_send' == $v)  unset($keys[$k]);
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
					<input type='submit' id='addmetasub' name='addmailmeta' class='add:the-list:newmeta' tabindex='9' value="<?php _e( 'Add Custom Field' ) ?>" />
					<?php wp_nonce_field( 'add-mailmeta', '_ajax_nonce', false ); ?>
				</td>
			</tr>
		</tbody>
	</table>
</div>
<p><?php _e('Custom fields can be used to add extra metadata to a mail that you can <a href="http://www.mailpress.org" target="_blank">use in your mail</a>.', MP_TXTDOM); ?></p>
<?php
	}

	public static function meta_box_customfield_row( $entry, &$count )
	{
		if ('_' == $entry['meta_key'] { 0 } ) return;

		static $update_nonce = false;
		if ( !$update_nonce ) $update_nonce = wp_create_nonce( 'add-mailmeta' );

		$r = '';
		++ $count;

		if ( $count % 2 )	$style = 'alternate';
		else			$style = '';
	
		$entry['meta_key'] 	= self::input_text($entry['meta_key']);
		$entry['meta_value'] 	= self::input_text($entry['meta_value']); // using a <textarea />
		$entry['mmeta_id'] 	= (int) $entry['mmeta_id'];

		$delete_nonce 		= wp_create_nonce( 'delete-mailmeta_' . $entry['mmeta_id'] );

		$r .= "
			<tr id='mailmeta-{$entry['mmeta_id']}' class='$style'>
				<td class='left'>
					<label class='hidden' for='mailmeta[{$entry['mmeta_id']}][key]'>
" . __( 'Key' ) . "
					</label>
					<input name='mailmeta[{$entry['mmeta_id']}][key]' id='mailmeta[{$entry['mmeta_id']}][key]' tabindex='6' type='text' size='20' value='{$entry['meta_key']}' />
					<div class='submit'>
						<input name='deletemailmeta[{$entry['mmeta_id']}]' type='submit' class='delete:the-list:mailmeta-{$entry['mmeta_id']}::_ajax_nonce=$delete_nonce deletemailmeta' tabindex='6' value='".attribute_escape(__( 'Delete' ))."' />
						<input name='updatemailmeta' type='submit' tabindex='6' value='" . attribute_escape(__( 'Update' )) . "' class='add:the-list:mailmeta-{$entry['mmeta_id']}::_ajax_nonce=$update_nonce updatemailmeta' />
					</div>
" . wp_nonce_field( 'change-mailmeta', '_ajax_nonce', false, false ) . "
				</td>
				<td>
					<label class='hidden' for='mailmeta[{$entry['mmeta_id']}][value]'>
" . __( 'Value' ) . "
					</label>
					<textarea name='mailmeta[{$entry['mmeta_id']}][value]' id='mailmeta[{$entry['mmeta_id']}][value]' tabindex='6' rows='2' cols='30'>{$entry['meta_value']}</textarea>
				</td>
			</tr>
			";
	return $r;
	}

	public static function select_optgroup($list, $selected, $echo = true)
	{
		foreach( $list as $value => $label )
		{
			$_selected = (!is_array($selected)) ? $selected : ( (in_array($value, $selected)) ? $value : null );
			$list[$value] = "<option " . self::selected( (string) $value, (string) $_selected, false, false ) . " value=\"$value\">$label</option>";
		}

		$numeric_done = $mailinglist_done = $newsletter_done = false;

		foreach( $list as $value => $html )
		{
			if (empty($value)) continue;
			switch (true)
			{
				case (is_numeric($value)) :
					if ($numeric_done) continue;
					$list[$value] = '<optgroup label=\'' . __('Subscribers', MP_TXTDOM) . '\'>' . $html;
					$numeric_done = true;
				break;
				case (strpos($value, 'MailPress_mailinglist~') !== false) :
					if ($mailinglist_done) continue;
					$list[$value] = '</optgroup><optgroup label=\'' . __('Mailinglists', MP_TXTDOM) . '\'>' . $html;
					$mailinglist_done = true;
				break;
				case (strpos($value, 'MailPress_newsletter~') !== false) :
					if ($newsletter_done) continue;
					$list[$value] = '</optgroup><optgroup label=\'' . __('Newsletters', MP_TXTDOM) . '\'>' . $html;
					$newsletter_done = true;
				break;
			}
		}

		$x = implode('', $list) . '</optgroup>';

		if (!$echo) return "\n$x\n";
		echo "\n$x\n";
	}

////  Body  ////

	public static function body()
	{
		include (MP_TMP . 'mp-admin/includes/write.php');
	}
}
?>