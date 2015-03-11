<?php
class MP_Dashboard_users_map extends MP_dashboard_widget_
{
	const option_name = 'MailPress_dashboard_mp_map';

	var $id = 'mp_users_map';

	function widget()
	{
		global $wpdb, $wp_locale;

		if ( !$options = get_option( self::option_name ) )
		{
			$options['code'] = 'world';
			$options['title'] = __( 'Subscribers - World', MP_TXTDOM );
		}

		$chd = $chld = array();

		$where = "status='active'";
		$where .= ('usa' == $options['code']) ? " AND created_country = 'US' AND created_US_state <> 'ZZ'" : " AND created_country <> 'ZZ'";

		$countalls = $wpdb->get_var("SELECT count(*) FROM $wpdb->mp_users WHERE $where ;");
		$users = $wpdb->get_results("SELECT created_US_state as toto, count(*) as count FROM $wpdb->mp_users WHERE $where GROUP BY created_US_state;");

		foreach($users as $user)
		{
			$chld[] = ('UK' == $user->toto) ? 'GB' : $user->toto;
			$chd[]  = round(100 * $user->count/$countalls);
		}

		$args = array();
		$args['cht']  = 't';
		$args['chs']  = $this->widget_size('440x200');
		$args['chtm'] = $options['code'];
		$args['chf']  = 'bg,s,EAF7FE';
		if (!empty($chld)) $args['chld'] = join('', $chld);
		$args['chco'] = 'ffffff,B5F8C2,294D30';
		$args['chd']  = (empty($chd)) ? 's:_' : 't:' . join(',', $chd);
		$url = esc_url(add_query_arg($args, $this->url));

?>
<div style='text-align:center;'>
<img style='width:100%;' src="<?php echo $url; ?>" alt="<?php echo $options['title']; ?>" />
</div>
<?php
	}

	function control()
	{
		$c= array (	'africa' 		=> __('Africa', MP_TXTDOM),
				'asia'		=> __('Asia', MP_TXTDOM),
				'europe'		=> __('Europe', MP_TXTDOM),
				'middle_east'	=> __('Middle East', MP_TXTDOM),
				'south_america'	=> __('South America', MP_TXTDOM),
				'usa'			=> __('USA', MP_TXTDOM),
				'world'		=> __('World', MP_TXTDOM));

		if ( !$options = get_option( self::option_name ) )
		{
			$options['code'] = 'world';
			$options['title'] = $c[$options['code']];
		}
		if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset($_POST['code']) ) 
		{	
			update_option( self::option_name, array('code' => $_POST['code'] , 'title' => $c[$_POST['code']]) );
			return;
		}
?>
			<select id='code' name='code'>
<?php
			MP_::select_option($c, $options['code']);
?>
			</select>
<?php
	}
}
new MP_Dashboard_users_map(__( 'MailPress - Subscribers Map', MP_TXTDOM ));