<?php
class MP_Widget extends WP_Widget
{
	function __construct()
	{
		$widget_ops  = array('classname' => 'widget_mailpress', 'description' => __( 'the mailpress subscription form', 'MailPress'));
		$control_ops = array('width' => 400, 'height' => 300);
		parent::__construct('mailpress', 'MailPress', $widget_ops, $control_ops);
	}

	function widget($args, $instance) 
	{
		extract($args);
		$title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title']);

		echo $before_widget;
		if ( !empty( $title ) ) { echo $before_title . stripslashes($title) . $after_title; }

		$instance['widget_id'] = $args['widget_id'];
		self::widget_form($instance); 

		echo $after_widget;
	}

	function update($new_instance, $old_instance) 
	{
		$instance 			= $old_instance;
		$instance['title'] 	= strip_tags($new_instance['title']);
		$instance['txtbutton'] 	= strip_tags($new_instance['txtbutton']);
		$instance['txtsubmgt'] 	= strip_tags($new_instance['txtsubmgt']);
		if (isset($new_instance['css'])) $instance['css'] = true;
   		if (isset($new_instance['jq']))  $instance['jq']  = true;
		if (isset($new_instance['js']))  $instance['js']  = true;
		if (isset($new_instance['urlsubmgt'])) $instance['urlsubmgt'] = true;
		else {unset($instance['urlsubmgt']); $instance['txtsubmgt'] = '';}
		return $instance;
	}

	function form($instance) 
	{
		$instance 	= wp_parse_args( (array) $instance, self::form_defaults() );
 		$title 	= (isset($instance['title'])) ? strip_tags($instance['title']) : '';
		$txtbutton	= strip_tags($instance['txtbutton']);
		$txtsubmgt	= strip_tags($instance['txtsubmgt']);
?>
<p>
	<label for='<?php echo $this->get_field_id('title'); ?>'>
		<?php _e('Title:'); ?> 
		<input type='text' id='<?php echo $this->get_field_id('title'); ?>' name='<?php echo $this->get_field_name('title'); ?>' value="<?php echo esc_attr($title); ?>" class='widefat' />
	</label>
	<br /><br />
	<label for='<?php echo $this->get_field_id('txtbutton'); ?>'>
		<?php _e('Button:'); ?> 
		<input type='text' id='<?php echo $this->get_field_id('txtbutton'); ?>' name='<?php echo $this->get_field_name('txtbutton'); ?>' value="<?php echo esc_attr($txtbutton); ?>" class='widefat' />
	</label>
	<br /><br />
	<label for='<?php echo $this->get_field_id('urlsubmgt'); ?>'>
		<input type='checkbox' id='<?php echo $this->get_field_id('urlsubmgt'); ?>' name='<?php echo $this->get_field_name('urlsubmgt'); ?>'<?php if ($instance['urlsubmgt']) checked(true); ?> onchange="jQuery('#<?php echo $this->get_field_id('txtsubmgt') ; ?>').toggle();" />
		<?php _e("\"Manage your subscription\" link ?", 'MailPress'); ?>
	</label>
	<label for='<?php echo $this->get_field_id('txtsubmgt'); ?>'>
		<input type='text' id='<?php echo $this->get_field_id('txtsubmgt'); ?>' name='<?php echo $this->get_field_name('txtsubmgt'); ?>' value="<?php echo esc_attr($txtsubmgt); ?>" class='widefat<?php if (!$instance['urlsubmgt']) echo ' hide-if-js'; ?>' />
	</label>
	<br />
	<div style='background-color:#f1f1f1;border:solid 1px #ddd;color:#999;padding:3px;'>
		<small>
			<?php _e('Preloaded :', 'MailPress'); ?>
			<label for='<?php echo $this->get_field_id('css'); ?>'>
				<input type='checkbox' id='<?php echo $this->get_field_id('css'); ?>' name='<?php echo $this->get_field_name('css'); ?>'<?php if ($instance['css']) checked(true); ?> /> <?php _e('css', 'MailPress'); ?> 
			</label>
			<label for='<?php echo $this->get_field_id('jq'); ?>'>
				<input type='checkbox' id='<?php echo $this->get_field_id('jq'); ?>' name='<?php echo $this->get_field_name('jq'); ?>'<?php if ($instance['jq']) checked(true); ?> /> <?php _e('jQuery', 'MailPress'); ?> 
			</label>
			<label for='<?php echo $this->get_field_id('js'); ?>'>	
				<input type='checkbox' id='<?php echo $this->get_field_id('js'); ?>' name='<?php echo $this->get_field_name('js'); ?>'<?php if ($instance['js']) checked(true); ?> /> <?php _e('javascript', 'MailPress'); ?>
		</label>
		</small>
	</div>
</p>
<?php
	}

////  Defaults  ////

	public static function form_defaults($options = array()) 
	{
		$defaults = array(	'css'			=> false,
						'jq'			=> false,
						'js'			=> false,
						'urlsubmgt' 	=> false, 
						'txtbutton' 	=> __('Subscribe', 'MailPress'), 
						'txtsubmgt' 	=> __('Manage your subscription', 'MailPress'), 
						'txtloading'	=> __('Loading...', 'MailPress'), 

						'txtfield' 		=> __('Your email', 'MailPress'), 
						'txtfieldname' 	=> __('Your name', 'MailPress'), 
						'txtwait'		=> __('Waiting for ...', 'MailPress'), 
						'txtwaitconf' 	=> __('Waiting for your confirmation', 'MailPress'), 
						'txtallready' 	=> __('You have already subscribed', 'MailPress'), 
						'txtvalidemail' 	=> __('Enter a valid email !', 'MailPress'), 
						'txterrconf' 	=> __('ERROR. resend confirmation email failed', 'MailPress'), 
						'txtdberror' 	=> __('ERROR in the database : subscriber not inserted', 'MailPress'), 

						'txtsubcomment' 	=> __("Subscribe to comments on this post", 'MailPress')
					);

		$defaults = apply_filters('MailPress_form_defaults', $defaults);
		$options  = wp_parse_args( $options, $defaults );
		$options  = apply_filters('MailPress_form_options', $options);
		return $options;
	}

	public static function get_wp_user_unsubscribe_url()
	{
		$url = false;
		$email = MailPress::get_wp_user_email();
		if (MailPress::is_email($email))
		{
			MailPress::require_class('Users');
			$key = MP_Users::get_key_by_email($email);
			if ($key) $url = MP_Users::get_unsubscribe_url($key);
		}
		return $url;
	}


////  Form  ////

	public static function widget_form($options = array()) 
	{
		static $headers = 0;

		global $user_ID;
		$email = $message = $widget_title = '';

		$options  = self::form_defaults($options);

		$id = $options['widget_id'];

		if (isset($_POST['MailPress_submit']) && ($_POST['id'] == $id))
			list($message, $email, $name) = self::insert(false);
		else
		{
			switch (true)
			{
				case ($user_ID != 0 && is_numeric($user_ID) ) :
// user connected, so populate the email field if not already a subscriber !
					$user  = get_userdata($user_ID);
					$email = $user->user_email;
					$name  = $user->display_name;
					MailPress::require_class('Users');
					if ( MP_Users::is_user($email, $user_ID) ) $email = $name = ''; 
				break;
				default :
// user as already commented, so populate the email field if not already a subscriber !
					$email  = (isset($_COOKIE['comment_author_email_' . COOKIEHASH])) ? $_COOKIE['comment_author_email_' . COOKIEHASH] : '';
					$name   = (isset($_COOKIE['comment_author_' . COOKIEHASH])) ? $_COOKIE['comment_author_' . COOKIEHASH] : '';
					MailPress::require_class('Users');
					if ( MP_Users::get_status_by_email($email) == 'active' ) $email='';
				break;
			}
		}

		if ('' == $email) $email = $options['txtfield'];
		if ('' == $name)  $name  = $options['txtfieldname'];

?>

<!-- start of code generated by MailPress -->
<?php
		if (!$headers)
		{
			$headers = 1;
			if (!$options['css']) echo "<link rel='stylesheet' href='" . get_option('siteurl') . '/' . MP_PATH . "mp-includes/css/form.css' type='text/css' />\n";
			if (!$options['jq'])  echo "<script type='text/javascript' src='" . get_option('siteurl') . "/wp-includes/js/jquery/jquery.js?ver=1.2.3'></script>\n";
			if (!$options['js'])  echo "<script type='text/javascript'> var mp_url = '" . MP_Action_url . "';</script>\n";
			if (!$options['js'])  echo "<script type='text/javascript' src='" . get_option('siteurl') . '/' . MP_PATH . "mp-includes/js/mp_form.js'></script>\n";
		}
?>

<div class='MailPress' id='_MP_<?php echo $id; ?>'>
	<div class='mp-container'>
		<div class='mp-message'></div>
		<div class='mp-loading'><img src='<?php echo get_option('siteurl'); ?>/<?php echo MP_PATH; ?>mp-includes/images/loading.gif' alt='<?php  echo $options['txtloading']; ?>' title='<?php  echo $options['txtloading']; ?>' /><?php  echo $options['txtloading']; ?></div>
		<div class='mp-formdiv'>
<?php if ('' != $message) echo $message . "\n"; ?>
			<form class='mp-form' method='post' action=''>
				<input type='hidden' name='action' 	value='add_user_fo' />
				<input type='hidden' name='id' 	value='<?php echo '_MP_' . $id; ?>' />
				<input type='text'   name='email'  	value="<?php echo $email; ?>" class='MailPressFormEmail' size='25' onfocus="if(this.value=='<?php echo js_escape($options['txtfield']); ?>') this.value='';" onblur="if(this.value=='') this.value='<?php echo js_escape($email); ?>';" /><br />
				<input type='text'   name='name'  	value="<?php echo $name;  ?>" class='MailPressFormName'  size='25' onfocus="if(this.value=='<?php echo js_escape($options['txtfieldname']); ?>') this.value='';" onblur="if(this.value=='') this.value='<?php echo js_escape($name); ?>';" /><br />
<?php do_action('MailPress_form', $email, $options); ?>
				<input class='MailPressFormSubmit mp_submit' type='submit' name='MailPress_submit' value="<?php echo MailPress::input_text($options['txtbutton']); ?>" />
			</form>
		</div>
	</div>
<?php 
$url = ($options['urlsubmgt']) ? self::get_wp_user_unsubscribe_url() : false;
if ($url) :
?>
	<div id='mp-urlsubmgt'><a href='<?php echo $url; ?>'><?php echo MailPress::input_text($options['txtsubmgt']); ?></a></div>
<?php
endif;
?>
<?php do_action('MailPress_form_div_misc', $email, $options); ?>
</div>
<!-- end of code generated by MailPress -->
<?php
	}

	public static function insert($ajax = true)
	{
		$bots_useragent = array('googlebot', 'google', 'msnbot', 'ia_archiver', 'lycos', 'jeeves', 'scooter', 'fast-webcrawler', 'slurp@inktomi', 'turnitinbot', 'technorati', 'yahoo', 'findexa', 'findlinks', 'gaisbo', 'zyborg', 'surveybot', 'bloglines', 'blogsearch', 'ubsub', 'syndic8', 'userland', 'gigabot', 'become.com');
		$useragent = $_SERVER['HTTP_USER_AGENT'];
		foreach ($bots_useragent as $bot) if (stristr($useragent, $bot) !== false) return false;				// goodbye bot !

		$message = $email = $name = '';
		$options = self::form_defaults();

		$email = ( isset($_POST['email']) ) ? $_POST['email'] : '';									//has the user entered an email 
		$name  = ( isset($_POST['name']) )  ? $_POST['name']  : '';	

		if ( '' == $email || $options['txtfield'] == $email ) 
		{																		// check for bot
			$message = "<span class='error'>" . $options['txtwait'] . "</span>";
			$email = $options['txtfield'];
		}
		else
		{
			$name = ( '' == $name || $options['txtfieldname'] == $name ) ? '' : $name;
			MailPress::require_class('Users');
			$add = MP_Users::add($email, $name);
			$shortcode_message = apply_filters('MailPress_form_submit', '', $email);
			$message = ($add['result']) ? "<span class='success'>" . $add['message'] . $shortcode_message . "</span><br />" : "<span class='error'>" . $add['message']  . $shortcode_message . "</span><br />";
			$email   = ($add['result']) ? $email : $options['txtfield'];
			$name    = ($add['result']) ? $name  : $options['txtfieldname'];
			if ($add['result']) 
				if ($ajax) 	do_action('MailPress_form_user_added_ajax', $email, $name, $options);
				else 		do_action('MailPress_form_user_added', $email, $name, $options);
		}
		return array($message, $email, $name);
	}
}
?>