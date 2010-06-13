<?php 
MailPress::require_class('Admin_page');

class MP_Admin_page_list extends MP_Admin_page
{

//// Screen Options ////

	public static function screen_meta() 
	{
		add_filter('manage_' . MP_AdminPage::screen . '_columns', array('MP_AdminPage', 'get_columns'));

		parent::screen_meta();
	}

//// Columns ////

	public static function columns_list($id = true)
	{
		$columns = MP_AdminPage::get_columns();
		$hidden  = MP_AdminPage::get_hidden_columns();
		foreach ( $columns as $key => $display_name ) 
		{
			$thid  = ( $id ) ? " id='$key'" : '';
			$class = ( 'cb' === $key ) ? " class='check-column'" : " class='manage-column column-$key'";
			$style = ( in_array($key, $hidden) ) ? " style='display:none;'" : '';

			echo "<th scope='col'$thid$class$style>$display_name</th>";
		} 
	}

	public static function get_hidden_columns()
	{
		return get_hidden_columns(MP_AdminPage::screen);
	}

//// List ////

	public static function get_list($start, $num, $query, $cache_name)
	{
		global $wpdb;

		$start = abs( (int) $start );
		$num = (int) $num;

		$rows = $wpdb->get_results( "$query LIMIT $start, $num" );

		self::update_cache($rows, $cache_name);

		$total = $wpdb->get_var( "SELECT FOUND_ROWS()" );

		return array($rows, $total);
	}

//// Row ////

	public static function get_actions($actions)
	{
		$count = count($actions);
		$i = 0;
		$out = "<div class='row-actions'>";
		foreach ( $actions as $action => $link ) 
		{
			++$i;
			( $i == $count ) ? $sep = '' : $sep = ' | ';
			$out .= "<span class='$action'>$link$sep</span>";
		}
		$out .= '</div>';
		return $out;
	}

////  ////

	public static function update_cache($xs, $y) 
	{
		foreach ( (array) $xs as $x ) wp_cache_add($x->id, $x, $y);
	}
}
?>