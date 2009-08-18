<?php 
MailPress::require_class('Admin_page');

class MP_AdminPage extends MP_Admin_page
{
	const screen 	= 'MailPress_page_templates';
	const capability 	= 'MailPress_manage_forms';

////  Redirect  ////

	public static function redirect() 
	{
		if ( isset($_POST['action']) )    $action = $_POST['action'];
		elseif ( isset($_GET['action']) ) $action = $_GET['action'];  

		switch($action) 
		{
			case 'update' :
				$template = $_POST['template'];
				$template_file = MP_TMP . "mp-content/forms/$template.xml";

				$args['action']  = 'edit';
				$args['template']= $template;
				$args['message'] = 2;

				$xml = stripslashes($_POST['newcontent']);

				if (!simplexml_load_string($xml)) 				$args['message'] = 3;
				else if (file_put_contents($template_file, $xml)) 	$args['message'] = 1;
				self::mp_redirect(self::url(MailPress_templates, $args));
			break;
			case 'toedit' :
				$template = $_POST['template'];
				$args['action']  = 'edit';
				$args['template']= $template;
				self::mp_redirect(self::url(MailPress_templates, $args));
			break;
		}
	}

////  Title  ////

	public static function title() { global $title; $title = __('MailPress Forms Templates', 'MailPress'); }

////  Styles  ////

	public static function print_styles($styles = array()) 
	{
/*
		wp_register_style ( self::screen . '_ie',	'/' . MP_PATH . 'mp-admin/css/form_templates_ie.css', false, false, false);
//		global $wp_styles;
//		$wp_styles->registered[self::screen . '_ie']->extra['conditionnal'] = 'lte IE 7';
//		$wp_styles->registered[self::screen . '_ie']->extra['rtl'] = true;
*/

		wp_register_style ( self::screen,		'/' . MP_PATH . 'mp-admin/css/form_templates.css' );
		$styles[] = self::screen;

		parent::print_styles($styles);
	}

//// Scripts ////

	public static function print_scripts($scripts, $footer) 
	{
		if ($footer)
		{
?>
<script type='text/javascript'>
	var cm_editor;
jQuery(document).ready(function(){
	cm_editor = CodeMirror.fromTextArea(
				'newcontent', 
				{
					height	: '450px',
					parserfile	: ['parsexml.js'],
					stylesheet	: ['<?php echo get_option('siteurl') . '/' . MP_PATH; ?>mp-includes/js/codemirror/css/xmlcolors.css'],
					path		:  '<?php echo get_option('siteurl') . '/' . MP_PATH; ?>mp-includes/js/codemirror/js/',
					continuousScanning: 500,
					lineNumbers	: true,
					textWrapping: false
				}
	);
});
</script>
<?php
		}
		wp_register_script( 'mp-codemirror','/' . MP_PATH . 'mp-includes/js/codemirror/js/codemirror.js', false, false, 1);

		wp_register_script( self::screen,	'/' . MP_PATH . 'mp-admin/js/form_templates.js', array('mp-codemirror'), false, 1);

		$scripts[] = self::screen;
		parent::print_scripts($scripts);
	}

//// Body ////

	public static function body()
	{
		include (MP_TMP . 'mp-admin/includes/form_templates.php');
	}
}
?>