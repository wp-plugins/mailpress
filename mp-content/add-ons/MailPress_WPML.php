<?php
if (class_exists('MailPress') && !class_exists('MailPress_WPML') && defined('ICL_SITEPRESS_VERSION') )
{
/*
Plugin Name: MailPress_WPML
Plugin URI: http://www.mailpress.org
Description: This is just an add-on for MailPress for WPML plugin compatibility.
Author: Charles St-Pierre & Andre Renaut
Version: 5.1
Author URI: http://www.mailpress.org
*/
class MailPress_WPML
{
	const option_name_default = 'MailPress_WPML_mailinglist_per_lang';

	function __construct()
	{
		add_filter('MailPress_action_url_arg', 	array(__CLASS__, 'action_url_arg'), 1);
		add_filter('MP_Users_get_url_general_id',	array(__CLASS__, 'MP_Users_get_url_general_id'));

		add_filter('icl_set_current_language', 	array(__CLASS__, 'set_current_language'), 1000);
		
		add_filter('MP_Widget_save_instance', array(__CLASS__, 'MP_Widget_save_instance'));
		add_filter('MailPress_form_options', array(__CLASS__, 'MailPress_form_options'));
		
		add_filter('MailPress_mailinglist_default', array(__CLASS__, 'set_user_mailinglists_per_lang'));
		
// for wp admin
		if (is_admin())
		{
		// for settings general
			add_action('MailPress_settings_general', array(__CLASS__, 'settings_general'), 20);
		}		
		
	}

//// init, navigation and ajax ////
	
	/*
	 *   Sets lang parameter at the end of MP_Action
	 */
	public static function action_url_arg($args)
	{
		$args['lang'] = ICL_LANGUAGE_CODE;
		return $args;
	}

	/*
	 *   Receive original id and context (page, category) and returns the corresponding id in the right language
	 */
	public static function MP_Users_get_url_general_id($id, $context)
	{
		return (function_exists( 'icl_object_id' )) ? icl_object_id($id, $context) : $id;
	}

	/*
	 *   Manually sets WPML language code from the MP_Action url
	 */

	public static function set_current_language($lang)
	{
		$s = (isset($_SERVER['HTTPS']) && 'on' == $_SERVER['HTTPS']) ? 's' : '';
		$r = 'http' . $s . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$p = explode('?', $r);
		$r = $p[0];
		$u = get_option('siteurl') . '/' . MP_PATH . 'mp-includes/action.php';

		if (isset($_GET['lang']) && $r == $u) return preg_replace("/[^0-9a-zA-Z-]/i", '',$_GET['lang']);

		return $lang;
	}
	
//// string translation ////

	public static function MP_Widget_save_instance($instance)
	{
		
		//$instance['title'] 		= icl_register_string($instance['title']);
		icl_register_string('MailPress','Text button',$instance['txtbutton']);
		icl_register_string('MailPress','Text management',$instance['txtsubmgt']);
		
		return $instance;
	}

	public static function MailPress_form_options($instance)
	{
		//$instance['title'] 		= icl_register_string($instance['title']);
		$instance['txtbutton'] 	= icl_t('MailPress','Text button', $instance['txtbutton']);
		$instance['txtsubmgt'] 	= icl_t('MailPress','Text management', $instance['txtsubmgt']);
		
		return $instance;
	}


//// mp_user ////

	public static function set_user_mailinglists_per_lang( $mailinglist )
	{
		$mailinglist_per_lang = get_option(self::option_name_default);
		if ( isset ( $mailinglist_per_lang [ ICL_LANGUAGE_CODE ] ) ){
			$mailinglist = $mailinglist_per_lang [ ICL_LANGUAGE_CODE ];
		}
		
		return $mailinglist;
	}

	public static function delete_user( $mp_user_id )
	{
		MP_Mailinglists::delete_object( $mp_user_id );

	}


////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////


	// for settings general
		public static function settings_general()
		{
			if (defined('MailPress_page_mailinglists')){
			
				$languages = icl_get_languages();
				$lang_nb = count($languages);
			
				$default_mailinglist	= get_option( MailPress_mailinglist::option_name_default );
				$mailinglist_per_lang	= get_option(self::option_name_default);
				$dropdown_options = array('hide_empty' => 0, 'hierarchical' => true, 'show_count' => 0, 'orderby' => 'name' );
	?>
				<tr><th></th><td></td></tr>
	<?php foreach($languages as $language): 
			$label = $language['translated_name'];
			if ( $language['translated_name'] != $language['native_name'] ) { $label.= ' ('.$language['native_name'].')'; }
			$code = $language['language_code'];
			$name = "default_mailinglist_lang[{$code}]";
			$id = "default_mailinglist_lang_{$code}";
			
			$dropdown_options['selected'] = (isset($mailinglist_per_lang[$code]) ) ? $mailinglist_per_lang[$code] : $default_mailinglist ;
			
			$dropdown_options['name'] = $name;
			
	?>
				<tr valign='top'>
					<th scope='row'><?php printf(__('Mailing lists for %s', MP_TXTDOM),$label); ?></th>
					<td style='padding:0;'>
						<?php MP_Mailinglists::dropdown($dropdown_options); ?>
					</td>
				</tr>
	<?php endforeach; ?>
				<tr class='mp_sep'><th></th><td><input type='hidden' name='mailinglist_per_lang_on' value='on' /></td></tr>
	<?php
			}
		}


}
new MailPress_WPML();
}