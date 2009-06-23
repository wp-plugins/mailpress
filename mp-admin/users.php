<?php 
require_once(MP_TMP . 'mp-admin/class/MP_Admin_list.class.php');

class MP_AdminPage extends MP_Admin_list
{
	const screen 	= MailPress_page_users;
	const capability 	= 'MailPress_edit_users';

////  Redirect  ////

	public static function redirect() 
	{
		if (!isset($_GET['delete_users'])) return;		// MANAGING CHECKBOX REQUESTS

		$deleted = $activated = $deactivated = 0;

		$url_parms 	= self::get_url_parms();

		foreach ($_GET['delete_users'] as $id)
		{ 							
			switch (true)
			{
				case ( isset( $_GET['deleteit'] )) :
					self::require_class('Users');
					MP_Users::set_status($id, 'delete');
					$deleted++;
				break;
				case (isset( $_GET['activateit'] )) :
					self::require_class('Users');
					MP_Users::set_status($id, 'active');
					$activated++;
				break;
				case (isset( $_GET['deactivateit'] )) :
					self::require_class('Users');
					MP_Users::set_status($id, 'waiting');
					$deactivated++;
				break;
			}
		}
		if ($deleted) 	$url_parms['deleted'] 	= $deleted;
		if ($activated) 	$url_parms['activated'] = $activated;
		if ($deactivated) $url_parms['deactivated'] = $deactivated;
		self::mp_redirect( self::url(MailPress_users, $url_parms) );
	}

////  Styles  ////

	public static function print_styles($styles = array()) 
	{
		wp_register_style ( self::screen, get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/users.css' );

		$styles[] =self::screen;
		parent::print_styles($styles);
	}

//// Scripts ////

	public static function print_scripts() 
	{
		wp_register_script( 'mp-ajax-response', 		'/' . MP_PATH . 'mp-includes/js/mp_ajax_response.js', array('jquery'), false, 1);
		wp_localize_script( 'mp-ajax-response', 		'wpAjax', array(
			'noPerm' => __('Email was not sent AND/OR Update database failed', 'MailPress'), 
			'broken' => __('An unidentified error has occurred.'), 
			'l10n_print_after' => 'try{convertEntities(wpAjax);}catch(e){};' 
		));

		wp_register_script( 'mp-lists', 			'/' . MP_PATH . 'mp-includes/js/mp_lists.js', array('mp-ajax-response'), false, 1);
		wp_localize_script( 'mp-lists', 			'wpListL10n', array( 
			'url' => MP_Action_url
		));

		wp_register_script( self::screen, 	'/' . MP_PATH . 'mp-admin/js/users.js', array('mp-lists'), false, 1);
		wp_localize_script( self::screen, 	'MP_AdminPageL10n', array(
			'pending' => __('%i% pending'), 
			'screen' => self::screen, 
			'l10n_print_after' => 'try{convertEntities(MP_AdminPageL10n);}catch(e){};' 
		));

		$scripts[] = self::screen;
		parent::print_scripts($scripts);
	}

//// Columns ////

	public static function get_columns() 
	{
		$disabled = (!current_user_can('MailPress_delete_users')) ? " disabled='disabled'" : '';
		$columns = array(	'cb' 		=> "<input type='checkbox'$disabled />", 
					'title' 	=> __('E-mail', 'MailPress'), 
					'user_name'	=> __('Name', 'MailPress'), 
					'author' 	=> __('Author'), 
					'date'	=> __('Date'));
		$columns = apply_filters('MailPress_columns_users', $columns);
		return $columns;
	}

//// List ////

	public static function get_list($start, $num, $url_parms) 
	{
		global $wpdb;

		$order = "a.created";

		$where = $tables = '';
		if (isset($url_parms['s']) && !empty($url_parms['s']))
		{
			$s = $wpdb->escape($url_parms['s']);
			if (!empty($where)) $where = $where . ' AND ';
			if ($s) $where .= " (a.email LIKE '%$s%') OR (a.laststatus_IP = '%$s%') OR (a.created_IP like '%$s%')  "; 
		}
		if (isset($url_parms['status']) && !empty($url_parms['status']))
		{
			if (!empty($where)) $where = $where . ' AND ';
			$where .= "a.status = '" . $url_parms['status'] . "'";
		}
		if (isset($url_parms['author']) && !empty($url_parms['author']))
		{
			if (!empty($where)) $where = $where . ' AND ';
			$where .= "( a.created_user_id = " . $url_parms['author'] . "  OR a.laststatus_user_id = " . $url_parms['author'] . " ) ";
		}
		if (isset($url_parms['mailinglist']) && !empty($url_parms['mailinglist']))
		{
			self::require_class('Mailinglists');

			if (!empty($where)) $where = $where . ' AND ';

			$y = MP_Mailinglists::get_children($url_parms['mailinglist'], ', ', '');
			$x = ('' == $y) ? ' = ' . $url_parms['mailinglist'] : ' IN (' . $url_parms['mailinglist'] . $y . ') ';
 
			$tables .= ", $wpdb->term_taxonomy b, $wpdb->term_relationships c";
			$where .= "( b.taxonomy='MailPress_mailing_list' AND b.term_taxonomy_id=c.term_taxonomy_id AND b.term_id " . $x . "  AND a.id = c.object_id)";
		}
		if (isset($url_parms['startwith']) && !empty($url_parms['startwith']))
		{
			if (!empty($where)) $where = $where . ' AND ';
			$where .= "(a.email >= '" . $url_parms['startwith'] . "') ";
			$order = "a.email";
		}

		if ($where) $where = ' WHERE ' . $where;

		$query = "SELECT DISTINCT SQL_CALC_FOUND_ROWS a.id, a.email, a.name, a.status, a.confkey, a.created, a.created_IP, a.created_agent, a.created_user_id, a.created_country, a.created_US_state, a.laststatus, a.laststatus_IP, a.laststatus_agent, a.laststatus_user_id FROM $wpdb->mp_users a $tables $where ORDER BY $order";

		return parent::get_list($start, $num, $query, 'mp_user');
	}

////  Row  ////

	public static function get_row( $id, $url_parms, $checkbox = true ) {

		global $mp_user;

		self::require_class('Users');
		$mp_user = $user = MP_Users::get( $id );
		$the_user_status = $user->status;

// url's
		$args = array();
		$args['id'] 	= $id;
		$edit_url    	= clean_url(self::url( MailPress_user, array_merge($args, $url_parms) ));
		$args['action'] 	= 'activate';
		$activate_url 	= clean_url(self::url( MailPress_user, array_merge($args, $url_parms), "activate-user_$id"));
		$args['action'] 	= 'deactivate';
		$deactivate_url 	= clean_url(self::url( MailPress_user, array_merge($args, $url_parms), "deactivate-user_$id"));
		$args['action'] 	= 'delete';
		$delete_url  	= clean_url(self::url( MailPress_user, array_merge($args, $url_parms), "delete-user_$id"));

		$x 			= (isset($url_parms['s'])) ? $url_parms['s'] : '';
		$url_parms['s'] 	= self::get_user_author_IP();
		$ip_url 		= clean_url(self::url( MailPress_users, $url_parms ));
		$url_parms['s'] 	= $x;

		$author = ( 0 == $user->laststatus_user_id) ? $user->created_user_id : $user->laststatus_user_id;
		if ($author != 0 && is_numeric($author)) {
			unset($url_parms['author']);
			$wp_user = get_userdata($author);
			$author_url = clean_url(self::url( MailPress_users, array_merge( array('author'=>$author), $url_parms) ));
		}
		$write_url 		= clean_url(self::url( MailPress_write, array_merge( array('toemail'=>$user->email), $url_parms) ));
// actions
		$actions = array();
		$actions['edit']      = "<a href='$edit_url'  title='" . sprintf( __('Edit "%1$s"', 'MailPress'), $user->email ) . "'>" . __('Edit') . '</a>';
		$actions['write']     = "<a href='$write_url' title='" . sprintf( __('Write to "%1$s"', 'MailPress'), $user->email ) . "'>" . __('Write', 'MailPress') . '</a>';
		$actions['approve']   = "<a href='$activate_url' 	class='dim:the-user-list:user-$id:unapproved:e7e7d3:e7e7d3:?mode=" . $url_parms['mode'] . "' title='" . sprintf( __('Activate "%1$s"', 'MailPress'), $user->email ) . "'>" . __( 'Activate', 'MailPress' ) 	 . '</a>';
		$actions['unapprove'] = "<a href='$deactivate_url' 	class='dim:the-user-list:user-$id:unapproved:e7e7d3:e7e7d3:?mode=" . $url_parms['mode'] . "' title='" . sprintf( __('Deactivate "%1$s"', 'MailPress'), $user->email ) . "'>" . __( 'Deactivate', 'MailPress' ) . '</a>';
		$actions['delete']    = "<a href='$delete_url' 		class='delete:the-user-list:user-$id submitdelete' title='" . __('Delete this user', 'MailPress' ) . "'>" . __('Delete', 'MailPress') . '</a>';

		if (!current_user_can('MailPress_delete_users')) unset($actions['delete']);

		if (isset($url_parms['status']))
		{
			if ( 'waiting' == $url_parms['status'])
			{
				$actions['approve']   = "<a href='$activate_url' class='delete:the-user-list:user-$id:e7e7d3:action=dim-user'   title='" . __( 'Activate this user', 'MailPress' )   . "'>" . __( 'Activate', 'MailPress' ) 	. '</a>';
				unset($actions['unapprove']);
			}
			if ( 'active' == $url_parms['status'])
			{
				$actions['unapprove'] = "<a href='$deactivate_url' class='delete:the-user-list:user-$id:e7e7d3:action=dim-user' title='" . __( 'Deactivate this user', 'MailPress' ) . "'>" . __( 'Deactivate', 'MailPress' ) . '</a>';
				unset($actions['approve']);
			}
		}

// table row 
//	class
		$row_class = ('waiting' == $the_user_status) ? 'unapproved' : '';
// 	checkbox
		$disabled = (!current_user_can('MailPress_delete_users')) ? " disabled='disabled'" : '';
// 	email
		$email_display = $user->email;
		if ( strlen($email_display) > 40 )	$email_display = substr($email_display, 0, 39) . '...';
//	author
//	date

?>
	<tr id="user-<?php echo $id; ?>" class='<?php echo $row_class; ?>'>
<?php
		$columns = self::get_columns();
		$hidden  = self::get_hidden_columns();

		foreach ( $columns as $column_name => $column_display_name ) 
		{
			$class = "class='$column_name column-$column_name'";

			$style = '';
			if ( in_array($column_name, $hidden) )	$style .= 'display:none;';
			$style = ' style="' . $style . '"';

			$attributes = "$class$style";

			switch ($column_name) 
			{

				case 'cb':
					if ( $checkbox ) : 
?>
		<th class='check-column' scope='row'>
			<input type='checkbox' name='delete_users[]' value='<?php echo $id; ?>'<?php echo $disabled; ?> />
		</th>
<?php
	 				endif;
				break;
				case 'title' :
					$attributes = 'class="username column-username"' . $style;
?>
		<td  <?php echo $attributes ?>>
<?php if (('detail' == $url_parms['mode']) && (get_option('show_avatars'))) echo get_avatar( $user->email, 32 ); else self::flag_IP() ?>
					<strong>
						<a class='row-title' href='<?php echo $edit_url; ?>' title='<?php printf( __('Edit "%1$s"', 'MailPress') ,$user->email ); ?>'>
							<?php echo $email_display; ?>
						</a>
					</strong>
<?php
		if ('detail' == $url_parms['mode'])
		{
?>
					<br />
<?php
		}
?>
<?php echo self::get_actions($actions); ?>
		</td>
<?php
				break;
				case 'user_name' :
?>
		<td  <?php echo $attributes ?>>
			<abbr title="<?php echo self::input_text($user->name, '&amp;'); ?>"><?php echo $user->name; ?></abbr>
		</td>
<?php
				break;
				case 'date' :

					$t_time = self::get_user_date(__('Y/m/d g:i:s A'));
					$m_time = self::get_user_date_raw();
					$time   = self::get_user_date('U');

					$time_diff = time() - $time; 

					if ( $time_diff > 0 && $time_diff < 24*60*60 )	$h_time = sprintf( __('%s ago'), human_time_diff( $time ) );
					elseif ( $time_diff == 0 )				$h_time = __('now', 'MailPress');
					else								$h_time = mysql2date(__('Y/m/d'), $m_time);
?>
		<td  <?php echo $attributes ?>>
			<abbr title="<?php echo $t_time; ?>"><?php echo $h_time; ?></abbr>
		</td>
<?php
				break;
				case 'author' :
?>
		<td  <?php echo $attributes ?>>	
<?php 				if ($author != 0 && is_numeric($author)) { ?>
				<a href='<?php echo $author_url; ?>' title='<?php printf( __('Users by "%1$s"', 'MailPress'), $wp_user->display_name); ?>'><?php echo $wp_user->display_name; ?></a>
<?php 				} else  	_e("(unknown)", 'MailPress'); ?>
		</td>
<?php
				break;
				default:
?>
		<td  <?php echo $attributes ?>>
			<?php	do_action('MailPress_get_row_users', $column_name, $user, $url_parms); ?>
		</td>
<?php
				break;
			}
		}
?>
	</tr>
<?php
	}

	public static function user_date( $d = '' ) {
		echo self::get_user_date( $d );
	}

	public static function get_user_date( $d = '' ) {
		$x = self::get_user_date_raw();
		return ( '' == $d ) ? mysql2date( get_option('date_format'), $x) : mysql2date($d, $x);
	}

	public static function get_user_date_raw() {
		global $mp_user;
		return ( $mp_user->created >= $mp_user->laststatus) ? $mp_user->created : $mp_user->laststatus;
	}

	public static function user_author_IP() {
		echo self::get_user_author_IP();
	}

	public static function get_user_author_IP() {
		global $mp_user;
		$ip = ( '' == $mp_user->laststatus_IP) ? $mp_user->created_IP : $mp_user->laststatus_IP;
		return $ip;
	}

	public static function flag_IP() {
		echo self::get_flag_IP();
	}

	public static function get_flag_IP() {
		global $mp_user;
		return ('ZZ' == $mp_user->created_country) ? '' : "<img class='flag' alt='" . strtolower($mp_user->created_country) . "' src='" . get_option('siteurl') . '/' . MP_PATH . 'mp-admin/images/flag/' . strtolower($mp_user->created_country) . ".gif' />\n";
	}

//// Body ////

	public static function count() 
	{
		global $wpdb;
		$stats = array('waiting' => 0, 'active' => 0);

		$query = "SELECT status, COUNT( * ) AS count FROM $wpdb->mp_users GROUP BY status";
		$counts = $wpdb->get_results( $query );

		if ($counts) foreach( $counts as $count ) $stats[$count->status] = $count->count;

		return (object) $stats;
	}

	public static function alphabet()
	{
		global $wpdb;
		$x = array();

		$query = "SELECT DISTINCT UPPER(SUBSTRING(email, 1, 1)) as letter FROM $wpdb->mp_users ORDER BY 1;";
		$letters = $wpdb->get_results( $query );

		foreach ($letters as $letter) $x[] = $letter->letter;

		return $x;
	}

	public static function body()
	{
		include (MP_TMP . 'mp-admin/includes/users.php');
	}
}
?>