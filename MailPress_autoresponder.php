<?php
if (class_exists('MailPress'))
{
/*
Plugin Name: MailPress_autoresponder
Plugin URI: http://www.mailpress.org
Description: This is just an addon for MailPress to manage autoresponders (based on wp-cron)
Author: Andre Renaut
Version: 4.0
Author URI: http://www.mailpress.org
*/

class MailPress_autoresponder
{
	const taxonomy = 'MailPress_autoresponder';

	const bt = 100;

	function __construct()
	{
// for taxonomy
		register_taxonomy(self::taxonomy, 'MailPress_autoresponder', array('update_count_callback' => array('MailPress_autoresponder', 'update_count_callback')));

		MailPress::require_class('Autoresponders');

// for tracking events to autorespond to
		include(MP_TMP . 'mp-admin/includes/options/autoresponders.php');
		$x = array();
		$autoresponders = MP_Autoresponders::get_all();
		foreach( $autoresponders as $autoresponder )
		{
			if (!isset($autoresponder->description['active'])) continue;
			$id = $autoresponder->description['event'];
			if (isset($mp_autoresponder_registered_events[$id]))
			{
				$event 	= $mp_autoresponder_registered_events[$id]['event'];
				$callback 	= $mp_autoresponder_registered_events[$id]['callback'];
				$x[$event] 	= $callback;
			}
		}
		foreach($x as $e => $c) add_action($e, $c, 8, 2);  		// 2 arguments : mp_user_id, event

// for autoresponder
		add_action('mp_autoresponder_process', 				array('MailPress_autoresponder', 'process'));
		add_action('mp_process_autoresponder', 				array('MailPress_autoresponder', 'process'));

// for wp admin
		if (is_admin())
		{
		// for admin plugin pages
			define ('MailPress_page_autoresponders', 	'mailpress_autoresponders');
		// for admin plugin urls
			$file = 'admin.php';
			define ('MailPress_autoresponders', $file . '?page=' . MailPress_page_autoresponders);
		// for link on plugin page
			add_filter('plugin_action_links', 			array('MailPress_autoresponder', 'plugin_action_links'), 10, 2 );
		// for role & capabilities
			add_filter('MailPress_capabilities', 		array('MailPress_autoresponder', 'capabilities'), 1, 1);
		// for settings
			add_action('MailPress_settings_logs', 		array('MailPress_autoresponder', 'settings_logs'));

		// for load admin page
			add_action('MailPress_load_admin_page', 		array('MailPress_autoresponder', 'load_admin_page'), 10, 1);
			// for ajax
			add_action('mp_action_add_atrspndr', 		array('MailPress_autoresponder', 'mp_action_add_atrspndr'));
			add_action('mp_action_delete_atrspndr', 		array('MailPress_autoresponder', 'mp_action_delete_atrspndr'));

		// for mails list
			add_action('MailPress_get_icon_mails', 		array('MailPress_autoresponder', 'get_icon_mails'), 8, 1);

		// for meta box in write page
			add_action('MailPress_redirect', 			array('MailPress_mailinglist', 'redirect'), 8, 1);
			add_filter('MailPress_styles', 			array('MailPress_autoresponder', 'styles'), 8, 2);
			add_filter('MailPress_scripts', 			array('MailPress_autoresponder', 'scripts'), 8, 2);
			add_action('MailPress_add_meta_boxes_write',	array('MailPress_autoresponder', 'meta_boxes_write'), 8, 2);
			// for ajax
			add_action('mp_action_add_wa', 			array('MailPress_autoresponder', 'mp_action_add_wa'));
			add_action('mp_action_delete_wa', 			array('MailPress_autoresponder', 'mp_action_delete_wa'));
		}
	}

//// Taxonomy ////

	public static function update_count_callback( $autoresponders )
	{
		return 0;
	}


//// Tracking events to autorespond to  ////

	public static function start_user_autoresponder($mp_user_id, $event)
	{
		$x = array();
		include(MP_TMP . 'mp-admin/includes/options/autoresponders.php');
		MailPress::require_class('Autoresponders');

		$autoresponders = MP_Autoresponders::get_all();

		foreach( $autoresponders as $autoresponder )
		{
			if (!isset($autoresponder->description['active'])) continue;
			foreach($mp_autoresponders_by_event[$event] as $k => $v)
			{
				if ($k != $autoresponder->description['event']) continue;
				$_mails = MP_Autoresponders::get_term_objects($autoresponder->term_id);

				if (isset($_mails[0]))
				{
					$term_id = $autoresponder->term_id;

					$time = time();
					$schedule = self::schedule($time, $_mails[0]['schedule']);
					MailPress::require_class('Usermeta');
					$umeta_id = MP_Usermeta::add($mp_user_id, '_MailPress_autoresponder_' . $term_id, $time);

					wp_schedule_single_event($schedule, 'mp_process_autoresponder', 	array('args' => array('umeta_id' => $umeta_id, 'mail_order'=> 0 )));

					MailPress::require_class('Log');
					$trace = new MP_Log('MP_Autoresponder_' . $term_id, ABSPATH . MP_PATH, 'autoresponder', false, 'autoresponder');

					$trace->log('!' . str_repeat( '-', self::bt) . '!');
					$bm = "Batch Report autoresponder #$term_id            umeta_id : $umeta_id  mail_order : 0";
					$bl = strlen($bm);
					$trace->log('!' . str_repeat( ' ', 5) . $bm . str_repeat( ' ', self::bt - 5 - $bl) . '!');
					$trace->log('!' . str_repeat( '-', self::bt) . '!');
					$bm = " mp_user    ! $mp_user_id";
					$bl = strlen($bm);
					$trace->log('!' . $bm . str_repeat( ' ', self::bt - $bl) . '!');
					$bm = " event      ! $event";
					$bl = strlen($bm);
					$trace->log('!' . $bm . str_repeat( ' ', self::bt - $bl) . '!');
					$bm = " 1st sched. ! " . date('Y-m-d H:i:s', $schedule);
					$bl = strlen($bm);
					$trace->log('!' . $bm . str_repeat( ' ', self::bt - $bl) . '!');
					$trace->log('!' . str_repeat( '-', self::bt) . '!');

					$trace->end(true);
				}
			}
		}
	}

////  Autoresponder  ////

	public static function process($args)
	{
		MailPress::no_abort_limit();

		extract($args);		// $umeta_id, $mail_order

		MailPress::require_class('Usermeta');
		$meta = MP_Usermeta::get_by_id($umeta_id);
		$term_id 	= (!$meta) ? 'unknown' : str_replace('_MailPress_autoresponder_', '', $meta->meta_key);

		MailPress::require_class('Log');
		$trace = new MP_Log('MP_Autoresponder_' . $term_id, ABSPATH . MP_PATH, 'autoresponder', false, 'autoresponder');

		$trace->log('!' . str_repeat( '-', self::bt) . '!');
		$bm = "Batch Report autoresponder #$term_id            umeta_id : $umeta_id  mail_order : $mail_order";
		$bl = strlen($bm);
		$trace->log('!' . str_repeat( ' ', 5) . $bm . str_repeat( ' ', self::bt - 5 - $bl) . '!');
		$trace->log('!' . str_repeat( '-', self::bt) . '!');
		$bm = " start      !";
		$bl = strlen($bm);
		$trace->log('!' . $bm . str_repeat( ' ', self::bt - $bl) . '!');

		$trace->end(self::send($args, $trace));
	}

	public static function send($args, $trace)
	{
		MailPress::no_abort_limit();

		extract($args);		// $umeta_id, $mail_order

		MailPress::require_class('Usermeta');
		$meta = MP_Usermeta::get_by_id($umeta_id);
		if (!$meta)
		{
			$bm = "** WARNING *! ** Unable to read table usermeta for id : $umeta_id **";
			$bl = strlen($bm);
			$trace->log('!' . $bm . str_repeat( ' ', self::bt - $bl) . '!');
			$bm = " end        ! Abort";
			$bl = strlen($bm);
			$trace->log('!' . $bm . str_repeat( ' ', self::bt - $bl) . '!');
			$trace->log('!' . str_repeat( '-', self::bt) . '!');
			return false;
		}

		$mp_user_id = $meta->user_id;
		$term_id 	= str_replace('_MailPress_autoresponder_', '', $meta->meta_key);
		$time		= $meta->meta_value;

//		$trace->log("***                  *** autoresponder_id : $term_id, mp_user_id : $mp_user_id");

		MailPress::require_class('Autoresponders');
		$autoresponder = MP_Autoresponders::get($term_id);
		if (!isset($autoresponder->description['active']))
		{
			$bm = "** WARNING *! ** Autoresponder :  $term_id is inactive **";
			$bl = strlen($bm);
			$trace->log('!' . $bm . str_repeat( ' ', self::bt - $bl) . '!');
			$bm = " end        ! Abort";
			$bl = strlen($bm);
			$trace->log('!' . $bm . str_repeat( ' ', self::bt - $bl) . '!');
			$trace->log('!' . str_repeat( '-', self::bt) . '!');
			return false;
		}

		MailPress::require_class('Users');
		$mp_user = MP_Users::get($mp_user_id);
		if (!$mp_user)
		{
			$bm = "** WARNING *! ** mp_user_id : $mp_user_id is not found **";
			$bl = strlen($bm);
			$trace->log('!' . $bm . str_repeat( ' ', self::bt - $bl) . '!');
			$bm = " end        ! Abort";
			$bl = strlen($bm);
			$trace->log('!' . $bm . str_repeat( ' ', self::bt - $bl) . '!');
			$trace->log('!' . str_repeat( '-', self::bt) . '!');
			return false;
		}

		$_mails = MP_Autoresponders::get_term_objects($term_id);
		if (!$_mails)
		{
			$bm = "** WARNING *! ** Autoresponder :  $term_id has no mails **";
			$bl = strlen($bm);
			$trace->log('!' . $bm . str_repeat( ' ', self::bt - $bl) . '!');
			$bm = " end        ! Abort";
			$bl = strlen($bm);
			$trace->log('!' . $bm . str_repeat( ' ', self::bt - $bl) . '!');
			$trace->log('!' . str_repeat( '-', self::bt) . '!');
			return false;
		}
		if (!isset($_mails[$mail_order]))
		{
			$bm = "** WARNING *! ** mail_order : $mail_order NOT in mails to be processed **";
			$bl = strlen($bm);
			$trace->log('!' . $bm . str_repeat( ' ', self::bt - $bl) . '!');
			$bm = " end        ! Abort";
			$bl = strlen($bm);
			$trace->log('!' . $bm . str_repeat( ' ', self::bt - $bl) . '!');
			$trace->log('!' . str_repeat( '-', self::bt) . '!');
			return false;
		}

		$_mail = $_mails[$mail_order];

		MailPress::require_class('Mails');
		$draft = MP_Mails::get($_mail['mail_id']);
		if (!$draft)
		{
			$bm = " processing ! mail_id : " . $_mail['mail_id'] . " NOT in mail table, skip to next mail/schedule if any";
			$bl = strlen($bm);
			$trace->log('!' . $bm . str_repeat( ' ', self::bt - $bl) . '!');
		}

		if (!MP_Mails::send_draft($_mail['mail_id'], false, $mp_user->email, $mp_user->name))
		{
			$bm = " processing ! Sending mail_id : " . $_mail['mail_id'] . " failed, skip to next mail/schedule if any";
			$bl = strlen($bm);
			$trace->log('!' . $bm . str_repeat( ' ', self::bt - $bl) . '!');
		}
		else
		{
			$bm = " processing ! Sending mail_id : " . $_mail['mail_id'] . " successfull ";
			$bl = strlen($bm);
			$trace->log('!' . $bm . str_repeat( ' ', self::bt - $bl) . '!');
		}

		$mail_order++;
		if (!isset($_mails[$mail_order]))
		{
			$bm = " end        ! last mail processed";
			$bl = strlen($bm);
			$trace->log('!' . $bm . str_repeat( ' ', self::bt - $bl) . '!');
			$trace->log('!' . str_repeat( '-', self::bt) . '!');
			return true;
		}

		$schedule = self::schedule($time, $_mails[$mail_order]['schedule']);
		wp_schedule_single_event($schedule, 'mp_process_autoresponder', array('args' => array('umeta_id' => $umeta_id, 'mail_order'=> $mail_order)));

		$bm = " end        !  next mail to be processed : $mail_order scheduled on : " . date('Y-m-d H:i:s', $schedule);
		$bl = strlen($bm);
		$trace->log('!' . $bm . str_repeat( ' ', self::bt - $bl) . '!');
		$trace->log('!' . str_repeat( '-', self::bt) . '!');

		return true;
	}

	public static function schedule($time, $schedule)
	{
		$Y = date('Y', $time);
		$M = date('n', $time) + substr($schedule, 0, 2);
		$D = date('j', $time) + substr($schedule, 2, 2);
		$H = date('G', $time) + substr($schedule, 4, 2);
		$Mn =  date('i', $time);
		$S =  date('s', $time);
		$U =  date('u', $time);

		return mktime($H, $Mn, $S, $M, $D, $Y);
	}

////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////

// for link on plugin page
	public static function plugin_action_links($links, $file)
	{
		return MailPress::plugin_links($links, $file, plugin_basename(__FILE__), '4');
	}

// for role & capabilities
	public static function capabilities($capabilities)
	{
		$capabilities['MailPress_manage_autoresponders'] = array(	'name'	=> __('Autoresponders', 'MailPress'),
												'group'	=> 'mails',
												'menu'	=> 25,

												'parent'	=> false,
												'page_title'=> __('MailPress Autoresponders', 'MailPress'),
												'menu_title'=> __('Autoresponders', 'MailPress'),
												'page'	=> MailPress_page_autoresponders,
												'func'	=> array('MP_AdminPage', 'body')
									);
		return $capabilities;
	}

// for settings
	public static function settings_logs($logs)
	{
		MP_AdminPage::logs_sub_form('autoresponder', $logs, __('Autoresponder','MailPress'), __('Autoresponder log','MailPress'), __('(for <b>ALL</b> Autoresponders through MailPress)','MailPress'), __('Number of Autoresponder log files : ','MailPress'));
	}

// for load admin page
	public static function load_admin_page($page)
	{
		if ($page != MailPress_page_autoresponders) return;
		include (MP_TMP . 'mp-admin/autoresponders.php');
	}
	//¤ for ajax
	public static function mp_action_add_atrspndr() 
	{
		if (!current_user_can('MailPress_manage_autoresponders')) die('-1');

		if ( '' === trim($_POST['name']) ) 
		{
			$x = new WP_Ajax_Response( array(	'what' => 'autoresponder', 
									'id' => new WP_Error( 'autoresponder_name', __('You did not enter a valid autoresponder name.', 'MailPress') )
								   ) );
			$x->send();
		}

		MailPress::require_class('Autoresponders');
		if ( MP_Autoresponders::exists( trim( $_POST['name'] ) ) ) 
		{
			$x = new WP_Ajax_Response( array(	'what' => 'autoresponder', 
									'id' => new WP_Error( __CLASS__ . '::exists', __('The autoresponder you are trying to create already exists.', 'MailPress'), array( 'form-field' => 'name' ) ), 
								  ) );
			$x->send();
		}
	
		$autoresponder = MP_Autoresponders::insert( $_POST, true );

		if ( is_wp_error($autoresponder) ) 
		{
			$x = new WP_Ajax_Response( array(	'what' => 'autoresponder', 
									'id' => $autoresponder
								  ) );
			$x->send();
		}

		if ( !$autoresponder || (!$autoresponder = MP_Autoresponders::get( $autoresponder )) ) 	MailPress::mp_die('0');

		$autoresponder_full_name 	= $autoresponder->name;

		include (MP_TMP . 'mp-admin/autoresponders.php');
		$x = new WP_Ajax_Response( array(	'what' => 'autoresponder', 
								'id' => $autoresponder->term_id, 
								'data' => MP_AdminPage::get_row( $autoresponder, array() ), 
								'supplemental' => array('name' => $autoresponder_full_name, 'show-link' => sprintf(__( 'Autoresponder <a href="#%s">%s</a> added' , 'MailPress'), "autoresponder-$autoresponder->term_id", $autoresponder_full_name))
							  ) );
		$x->send();
		break;
	}

	public static function mp_action_delete_atrspndr() 
	{
		$id = isset($_POST['id'])? (int) $_POST['id'] : 0;
		MailPress::require_class('Autoresponders');
		MailPress::mp_die( MP_Autoresponders::delete($id) ? '1' : '0' );
	}

// for mails list
	public static function get_icon_mails($mail_id)
	{
		MailPress::require_class('Autoresponders');
		if (!MP_Autoresponders::object_have_relations($mail_id)) return;
?>
			<img class='attach' alt="<?php _e('Autoresponder', 'MailPress'); ?>" title="<?php _e('Autoresponder', 'MailPress'); ?>" src='<?php echo get_option('siteurl') . '/' . MP_PATH; ?>/mp-admin/images/autoresponder.png' />
<?php
	}
		
// for meta box in write page
	public static function meta_box_write_parms()
	{
		$x['table_body_id'] = 'wa-list';				// the-list
		$x['ajax_response'] = 'wa-response'; 			// ajar-response
		$x['table_list_id'] = 'wa-list-table';			// list-table

		$x['tr_prefix_id']  = 'wa';

		return $x;
	}

	public static function styles($styles, $screen) 
	{
		if ($screen != MailPress_page_write) return $styles;

		wp_register_style ( MailPress_page_autoresponders, get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/write_autoresponders.css', array(), false, 1);

		$styles[] = MailPress_page_autoresponders;

		return $styles;
	}

	public static function scripts($scripts, $screen) 
	{
		if ($screen != MailPress_page_write) return $scripts;

		wp_register_script( MailPress_page_autoresponders, '/' . MP_PATH . 'mp-admin/js/write_autoresponders.js', array('mp-lists'), false, 1);
		wp_localize_script( MailPress_page_autoresponders, 	'adminautorespondersL10n',	array_merge(	array('pending' => __('%i% pending'), 'screen' => MP_AdminPage::screen),
																			self::meta_box_write_parms(),
																			array('l10n_print_after' => 'try{convertEntities(adminautorespondersL10n);}catch(e){};')
																)
		);
		$scripts[] = MailPress_page_autoresponders;

		return $scripts;
	}

	public static function meta_boxes_write($mail_id, $mp_screen)
	{
		add_meta_box('write_autoresponder', __('Autoresponders', 'MailPress'), array('MailPress_autoresponder', 'meta_box'), MP_AdminPage::screen, 'normal', 'core');
	}
/**/
	public static function meta_box($mail)
	{
		MP_AdminPage::require_class('Autoresponders');
		$parms = self::meta_box_write_parms();
?>
<div id='postcustomstuff'>
	<div id='<?php echo $parms['ajax_response'] ?>'></div>
<?php
        $id = (isset($mail->id)) ? $mail->id : 0;
		$metadata = MP_Autoresponders::get_object_terms($id);
		$count = 0;
		if ( !$metadata ) : $metadata = array(); 
?>
	<table id='<?php echo $parms['table_list_id'] ?>' style='display: none;'>
		<thead>
			<tr>
				<th class='left'><?php _e( 'Autoresponder', 'MailPress' ); ?></th>
				<th><?php _e( 'Schedule', 'MailPress' ); ?></th>
			</tr>
		</thead>
		<tbody id='<?php echo $parms['table_body_id'] ?>' class='list:<?php echo $parms['tr_prefix_id'] ?>'>
			<tr><td></td></tr>
		</tbody>
	</table>
<?php else : ?>
	<table id='<?php echo $parms['table_list_id'] ?>'>
		<thead>
			<tr>
				<th class='left'><?php _e( 'Autoresponder', 'MailPress' ) ?></th>
				<th><?php _e( 'Schedule', 'MailPress' ) ?></th>
			</tr>
		</thead>
		<tbody id='<?php echo $parms['table_body_id'] ?>' class='list:<?php echo $parms['tr_prefix_id'] ?>'>
<?php foreach ( $metadata as $entry ) echo self::meta_box_autoresponder_row( $entry, $count ); ?>
		</tbody>
	</table>
<?php endif; ?>
<?php
	$autoresponders = MP_Autoresponders::get_all();
	foreach( $autoresponders as $autoresponder )
	{
		$_autoresponders[$autoresponder->term_id] = $autoresponder->name;
	}
	if (empty($_autoresponders)) :
?>
	<p>
		<strong>
			<?php _e( 'No autoresponder', 'MailPress') ?>
		</strong>
	</p>
<?php else : ?>
	<p>
		<strong>
			<?php _e( 'Link to :', 'MailPress') ?>
		</strong>
	</p>
	<table id='add_<?php echo $parms['tr_prefix_id']; ?>'>
		<thead>
			<tr>
				<th class='left'><label for='autoresponderselect'><?php _e( 'Autoresponder', 'MailPress' ) ?></label></th>
				<th><label for='metavalue'><?php _e( 'Schedule', 'MailPress' ) ?></label></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td id='newarleft' class='left'>
					<select id='autoresponderselect' name='autoresponderselect' tabindex='7'>
<?php MP_AdminPage::select_option($_autoresponders, false); ?>
					</select>
				</td>
				<td style='vertical-align:top;'>
					<table style='border:none;margin:8px 0 8px 8px;width:95%;'>
						<tbody>
							<tr>
								<td class='arschedule'>
									<?php _e('Month', 'MailPress');?><br />
									<select style='width:auto;margin:0;padding:0;' name='autoresponder[schedule][MM]' >
<?php MP_AdminPage::select_number(0, 12, (isset($month)) ? $month : 0); ?>
									</select>
								</td>
								<td class='arschedule'>
									<?php _e('Day', 'MailPress');?><br />
									<select style='width:auto;margin:0;padding:0;' name='autoresponder[schedule][DD]' >
<?php MP_AdminPage::select_number(0, 31, (isset($days)) ? $days : 0); ?>
									</select>
								</td>
								<td class='arschedule'>
									<?php _e('Hour', 'MailPress');?><br />
									<select style='width:auto;margin:0;padding:0;' name='autoresponder[schedule][HH]' >
<?php MP_AdminPage::select_number(0, 23, (isset($hours)) ? $hours : 0); ?>
									</select>
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
			<tr>
				<td colspan='2' class='submit'>
					<input type='submit' id='addmetasub' name='addwrite_autoresponder' class='add:<?php echo $parms['table_body_id']; ?>:add_<?php echo $parms['tr_prefix_id']; ?>' tabindex='9' value="<?php _e( 'Add', 'MailPress' ) ?>" />
					<?php wp_nonce_field( 'add-write-autoresponder', '_ajax_nonce', false ); ?>
				</td>
			</tr>
		</tbody>
	</table>
<?php endif; ?>
</div>
<?php
	}
	//¤ for ajax
	public static function meta_box_autoresponder_row( $entry, &$count ) 
	{
		MailPress::require_class('Autoresponders');
		$parms = self::meta_box_write_parms();

		static $update_nonce = false;
		if ( !$update_nonce ) $update_nonce = wp_create_nonce( 'add-write-autoresponder' );

		$r = '';
		++ $count;

		if ( $count % 2 )	$style = 'alternate';
		else			$style = '';

		$entry['mmeta_id'] 	= (int) $entry['mmeta_id'];

		$delete_nonce 		= wp_create_nonce( 'delete-write-autoresponder_' . $entry['mmeta_id'] );

		$autoresponders = MP_Autoresponders::get_all();
		foreach( $autoresponders as $autoresponder )
		{
			$_autoresponders[$autoresponder->term_id] = $autoresponder->name;
		}
		$r .= "
			<tr id='{$parms['tr_prefix_id']}-{$entry['mmeta_id']}' class='$style'>
				<td class='left'>
					<select id='write_autoresponder_{$entry['mmeta_id']}_key' name='write_autoresponder[{$entry['mmeta_id']}][key]' tabindex='7'>
" . MailPress::select_option($_autoresponders, $entry['term_id'], false) . "
					</select>
					<div class='submit'>
						<input name='delete_wa-{$entry['mmeta_id']}' type='submit' class='delete:{$parms['table_body_id']}:{$parms['tr_prefix_id']}-{$entry['mmeta_id']}::_ajax_nonce=$delete_nonce delete_wa' tabindex='6' value='".attribute_escape(__( 'Delete' ))."' />
						<input name='update_wa-{$entry['mmeta_id']}' type='submit' tabindex='6' value='".attribute_escape(__( 'Update' ))."' class='add:{$parms['table_body_id']}:{$parms['tr_prefix_id']}-{$entry['mmeta_id']}::_ajax_nonce=$update_nonce update_wa' />
					" . wp_nonce_field( 'change-write_autoresponder', '_ajax_nonce', false, false ) . "
					</div>
				</td>
				<td style='vertical-align:top;'>
					<table style='border:none;margin:8px 0 8px 8px;width:95%;'>
						<tbody>
							<tr>
								<td class='arschedule'>
									" . __('Month', 'MailPress') . "<br />
									<select style='width:auto;margin:0;padding:0;' name='write_autoresponder[" . $entry['mmeta_id'] . "][value][MM]' >
" . MailPress::select_number(0, 12, substr($entry['schedule'], 0, 2), 1, false) . "
									</select>
								</td>
								<td class='arschedule'>
									" . __('Day', 'MailPress') . "<br />
									<select style='width:auto;margin:0;padding:0;' name='write_autoresponder[" . $entry['mmeta_id'] . "][value][DD]' >
" . MailPress::select_number(0, 31, substr($entry['schedule'], 2, 2), 1, false) . "
									</select>
								</td>
								<td class='arschedule'>
									" . __('Hour', 'MailPress') . "<br />
									<select style='width:auto;margin:0;padding:0;' name='write_autoresponder[" . $entry['mmeta_id'] . "][value][HH]' >
" . MailPress::select_number(0, 23, substr($entry['schedule'], 4, 2), 1, false) . "
									</select>
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
			";
		return $r;
	}

// for ajax	

	public static function mp_action_add_wa()
	{
		if ( !current_user_can( 'MailPress_manage_autoresponders') )	die('-1');

		$c = 0;
		$obj_id = (int) $_POST['mail_id'];
		if ($obj_id === 0) MailPress::mp_die('0');

		if ( isset($_POST['autoresponderselect']) || isset($_POST['autoresponder']['schedule']) ) 
		{
			if ( !$mid = self::add_meta( $obj_id ) ) 				MailPress::mp_die('0');

			MailPress::require_class('Mailmeta');
			$meta = MP_Mailmeta::get_by_id( $mid );
			$obj_id = (int) $meta->mail_id;
			$meta = get_object_vars( $meta );

			MailPress::require_class('Autoresponders');


			$x = new WP_Ajax_Response( array(
				'what' => 'write-autoresponder', 
				'id' => $mid, 
				'data' => self::meta_box_autoresponder_row( MP_Autoresponders::get_mmeta_id_term($mid), $c ), 
				'position' => 1, 
				'supplemental' => array('mail_id' => $obj_id)
			) );
		}
		else
		{
			$mid   = (int) array_pop(array_keys($_POST['write_autoresponder']));
			$key   = '_MailPress_autoresponder_' . $_POST['write_autoresponder'][$mid]['key'];
			if (isset($_POST['write_autoresponder'][$mid]['value'])) foreach ($_POST['write_autoresponder'][$mid]['value'] as $k => $v) if ($v <10) $_POST['write_autoresponder'][$mid]['value'][$k] = '0' . $v;
			$value = implode('', $_POST['write_autoresponder'][$mid]['value']);

			MailPress::require_class('Mailmeta');
			if ( !$meta = MP_Mailmeta::get_by_id( $mid ) )			MailPress::mp_die('0');
			if ( !MP_Mailmeta::update_by_id($mid , $key, $value) )	MailPress::mp_die('1'); // We know meta exists; we also know it's unchanged (or DB error, in which case there are bigger problems).
			$meta = MP_Mailmeta::get_by_id( $mid );

			MailPress::require_class('Autoresponders');

			$x = new WP_Ajax_Response( array(
				'what' => 'write-autoresponder', 
				'id' => $mid, 'old_id' => $mid, 
				'data' => self::meta_box_autoresponder_row( MP_Autoresponders::get_mmeta_id_term($mid), $c ), 
				'position' => 0, 
				'supplemental' => array('mail_id' => $meta->mail_id)
			) );
		}
		$x->send();
	}

	public static function add_meta($mail_id)
	{
		$mail_id = (int) $mail_id;
		if (isset($_POST['autoresponder']['schedule'])) foreach ($_POST['autoresponder']['schedule'] as $k => $v) if ($v <10) $_POST['autoresponder']['schedule'][$k] = '0' . $v;

		$metakey 	= isset($_POST['autoresponderselect']) ? '_MailPress_autoresponder_' . trim( $_POST['autoresponderselect'] ) : '';
		$metavalue 	= isset($_POST['autoresponder']['schedule']) ? implode('', $_POST['autoresponder']['schedule']) : '';

		if ( !empty($metavalue)  && !empty ($metakey) ) 
		{
			MailPress::require_class('Mailmeta');
			return MP_Mailmeta::add( $mail_id, $metakey, $metavalue );
		}
		return false;
	}

	public static function mp_action_delete_wa()
	{
		if ( !current_user_can( 'MailPress_manage_autoresponders') )	MailPress::mp_die('-1');

		$id = isset($_POST['id'])? (int) $_POST['id'] : 0;

		MailPress::require_class('Mailmeta');
		if ( !$meta = MP_Mailmeta::get_by_id( $id ) ) 				MailPress::mp_die('1');
		if ( MP_Mailmeta::delete_by_id( $meta->mmeta_id ) )			MailPress::mp_die('1');
		MailPress::mp_die('0');
	}
}

$MailPress_autoresponder = new MailPress_autoresponder();
}
?>