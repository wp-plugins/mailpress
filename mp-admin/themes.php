<?php 
MailPress::require_class('Admin_screen');

class MP_AdminPage extends MP_Admin_screen
{
	const screen 	= MailPress_page_themes;
	const capability 	= 'MailPress_switch_themes';

////  Redirect  ////

	public static function redirect() 
	{
		self::require_class('Themes');
		$th = new MP_Themes();

		if ( isset($_GET['action']) ) 
		{
			check_admin_referer('switch-theme_' . $_GET['template']);
			if ('activate' == $_GET['action']) 
			{
				$th->switch_theme($_GET['template'], $_GET['stylesheet']);
				self::mp_redirect(MailPress_themes . '&activated=true');
			}
		}
	}

////  Styles  ////

	public static function print_styles($styles = array()) 
	{
		$styles[] = 'thickbox';
		parent::print_styles($styles);
	}

////  Scripts  ////

	public static function print_scripts() 
	{
		wp_register_script( self::screen, 	'/' . MP_PATH . 'mp-admin/js/themes.js', array( 'thickbox', 'jquery' ), false, 1);

		$scripts[] = self::screen;
		parent::print_scripts($scripts);
	}

////  Body  ////

	public static function body()
	{
		include (MP_TMP . 'mp-admin/includes/themes.php');
	}
}
?>