<?php 
require_once(MP_TMP . 'mp-admin/class/MP_Admin_abstract.class.php');

class MP_AdminPage extends MP_Admin_abstract
{
	const screen 	= 'mailpress_mail';
	const capability 	= 'MailPress_tracking_mails';

////  Redirect  ////

	public static function redirect() 
	{
		if ( isset($_POST['action']) )    $action = $_POST['action'];
		elseif ( isset($_GET['action']) ) $action = $_GET['action'];  

		if (!isset($action)) return;

		$list_url = self::url(MailPress_mails, self::get_url_parms());

		self::require_class('Mails');

		switch($action) 
		{
			case 'delete' :
				MP_Mails::delete($_GET['id']);
				self::mp_redirect($list_url . '&deleted=1');
			break;
			case 'send' :
				$id = $_GET['id'];
				$x = MP_Mail::send_draft($_GET['id']);
				$list_url = (is_numeric($x))	? $list_url . '&sent=' . $x : $list_url . '&notsent=1';
				self::mp_redirect($list_url);
			break;
			case 'draft' :
				$id = (0 == $_POST['id']) ? MP_Mails::get_id(__CLASS__ . ' ' . __METHOD__ . ' ' . self::screen) : $_POST['id'];
				MP_Mails::update_draft($id);
				$parm = "&saved=1";
				if (isset($_POST['send']))
				{
					$x = MP_Mails::send_draft($id);
					if (is_numeric($x))
						if (0 == $x)	$parm = "&sent=0";
						else			$parm = "&sent=$x";
					else				$parm = "&nodest=0";
				}
				if     (strstr($_SERVER['HTTP_REFERER'], MailPress_edit))	$url = MailPress_edit  . "$parm&id=$id";
				elseif (strstr($_SERVER['HTTP_REFERER'], MailPress_write))	$url = MailPress_write . "$parm&id=$id";
				else										$url = MailPress_mail  . "&action=view$parm&id=$id";
				self::mp_redirect($url);
			break;
		}
	}

////  Title  ////

	public static function title() { global $title;  $title = __('View Mail', 'MailPress'); }

////  Styles  ////

	public static function print_styles($styles = array()) 
	{
		wp_register_style ( self::screen, get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/mail.css' );

		$styles[] = self::screen;
		parent::print_styles($styles);
	}

//// Scripts ////

	public static function scripts() 
	{
		wp_register_script( self::screen, '/' . MP_PATH . 'mp-admin/js/mail.js', array('jquery-ui-tabs'), false, 1);

		$scripts[] = self::screen;
		parent::print_scripts($scripts);
	}


////  Metaboxes  ////

	public static function screen_meta() 
	{
		add_meta_box('submitdiv',	__('Send', 'MailPress'), array('MP_AdminPage', 'meta_box_submit'), self::screen, 'side', 'core');
		add_meta_box('maildiv', 	__('Mail', 'MailPress'), array('MP_AdminPage', 'meta_box_mail'), 	 self::screen, 'normal', 'core');

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
			<br />
		</div>
		<div class="clear"><br /><br /><br /><br /><br /></div>
	</div>
	<div id="major-publishing-actions">
		<div id="delete-action">
<?php 	if (isset($delete_url)) : ?>
			<a class='submitdelete' href='<?php echo $delete_url ?>' onclick="if (confirm('<?php echo(js_escape(sprintf( __("You are about to delete this draft '%s'\n  'Cancel' to stop, 'OK' to delete."), $draft->id ))); ?>')) return true; return false;">
				<?php _e('Delete', 'MailPress'); ?>
			</a>
<?php		endif; ?>
		</div>
		<div id="publishing-action">
<?php 	if (current_user_can('MailPress_send_mails')) : ?><input id='publish' type='submit' name='send' class='button-primary' value="<?php _e('Send', 'MailPress'); ?>" /><?php endif; ?>
		</div>
		<div class="clear"></div>
	</div>
</div>
<?php
	}
/**/
	public static function meta_box_mail($draft) 
	{
		$args		= array();

		$args['action'] 	= 'iview';
		$args['id']		= $draft->id;
		$args['main_id']	= $draft->id;
?>
<div id="mailbox">
	<iframe id='mailframe' style='width:100%;border:0;height:800px' src='<?php echo clean_url( add_query_arg( $args, MP_Action_url ) ); ?>'></iframe>
</div>
<?php
	}

//// Body ////

	public static function body()
	{
		include (MP_TMP . 'mp-admin/includes/mail.php');
	}
}
?>