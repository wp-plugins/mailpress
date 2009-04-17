<?php
class MP_Usermeta
{

	public static function add( $mp_user_id, $meta_key, $meta_value ) 
	{
		global $wpdb;
		if ( !is_numeric( $mp_user_id ) ) return false;
		$meta_key = preg_replace('|[^a-z0-9_]|i', '', $meta_key);

		if ( is_string($meta_value) )$meta_value = stripslashes($meta_value);
		$meta_value = maybe_serialize($meta_value);

		if (empty($meta_value)) return false;

		$wpdb->query( $wpdb->prepare("INSERT INTO $wpdb->mp_usermeta ( user_id, meta_key, meta_value ) VALUES ( %d, %s, %s )", $mp_user_id, $meta_key, $meta_value) );

		return $wpdb->insert_id;
	}

	public static function has( $mp_user_id , $meta_key=false ) 
	{
		global $wpdb;

		$x = ($meta_key) ? "AND meta_key = '".$meta_key."'" : ''; 

		return $wpdb->get_results( $wpdb->prepare("SELECT * FROM $wpdb->mp_usermeta WHERE user_id = %d $x ORDER BY meta_key, umeta_id", $mp_user_id ), ARRAY_A );
	}

	public static function get( $mp_user_id, $meta_key = '', $meta_value = '') 
	{
		global $wpdb;
		$mp_user_id = (int) $mp_user_id;

		if ( !$mp_user_id ) return false;

		if ( !empty($meta_key) ) 
		{
			$meta_key = preg_replace('|[^a-z0-9_]|i', '', $meta_key);
			if ( !empty($meta_value) ) 
			{
				$metas = $wpdb->get_col( $wpdb->prepare("SELECT meta_value FROM $wpdb->mp_usermeta WHERE user_id = %s AND meta_key = %s AND meta_value = %s", $mp_user_id, $meta_key, $meta_value) );
			}
			else
			{
				$metas = $wpdb->get_col( $wpdb->prepare("SELECT meta_value FROM $wpdb->mp_usermeta WHERE user_id = %d AND meta_key = %s", $mp_user_id, $meta_key) );
			}
		}
		else
		{
			$metas = $wpdb->get_results( $wpdb->prepare("SELECT meta_key, meta_value FROM $wpdb->mp_usermeta WHERE user_id = %d", $mp_user_id) );
		}

		if ( empty($metas) ) 
		{
		 	if ( empty($meta_key) ) return array();
			else			return '';
		}

		$metas = array_map('maybe_unserialize', $metas);

		if ( count($metas) == 1 ) 	return $metas[0];
		else					return $metas;
	}

	public static function get_by_id( $uid ) 
	{
		global $wpdb;
		$uid = (int) $uid;
	
		$meta = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $wpdb->mp_usermeta WHERE umeta_id = %d", $uid) );
		if ( is_serialized_string( $meta->meta_value ) )	$meta->meta_value = maybe_unserialize( $meta->meta_value );
		return $meta;
	}

	public static function update($user_id, $meta_key, $meta_value, $prev_value = '') 
	{
		global $wpdb;

		// expected_slashed ($meta_key)
		$meta_key = stripslashes($meta_key);
//		$meta_key = preg_replace('|[^a-z0-9_]|i', '', $meta_key);

		if ( ! $wpdb->get_var( $wpdb->prepare( "SELECT meta_key FROM $wpdb->mp_usermeta WHERE meta_key = %s AND user_id = %d", $meta_key, $user_id ) ) ) {
			return self::add($user_id, $meta_key, $meta_value);
		}

		$meta_value = maybe_serialize($meta_value);

		$data  = compact( 'meta_value' );
		$where = compact( 'meta_key', 'user_id' );

		if ( !empty( $prev_value ) ) 
		{
			$prev_value = maybe_serialize($prev_value);
			$where['meta_value'] = $prev_value;
		}

		$wpdb->update( $wpdb->mp_usermeta, $data, $where );
		return true;
	}

	public static function update_by_id($umeta_id, $meta_key, $meta_value) 
	{
		global $wpdb;
		if ( !is_numeric( $umeta_id ) ) return false;

		$meta_value = maybe_serialize($meta_value);
		$meta_key = preg_replace('|[^a-z0-9_]|i', '', $meta_key);

		$data  = compact( 'meta_key', 'meta_value' );
		$where = compact( 'umeta_id' );

		$wpdb->update( $wpdb->mp_usermeta, $data, $where );
		return true;
	}

	public static function delete( $mp_user_id, $meta_key, $meta_value = '' ) 
	{
		global $wpdb;
		if ( !is_numeric( $mp_user_id ) ) return false;
		$meta_key = preg_replace('|[^a-z0-9_]|i', '', $meta_key);

		if ( is_array($meta_value) || is_object($meta_value) ) $meta_value = serialize($meta_value);
		$meta_value = trim( $meta_value );

		if ( ! empty($meta_value) ) 	$wpdb->query( $wpdb->prepare("DELETE FROM $wpdb->mp_usermeta WHERE user_id = %d AND meta_key = %s AND meta_value = %s", $mp_user_id, $meta_key, $meta_value) );
		elseif ( ! empty($meta_key) ) $wpdb->query( $wpdb->prepare("DELETE FROM $wpdb->mp_usermeta WHERE user_id = %d AND meta_key = %s", $mp_user_id, $meta_key) );
		else					$wpdb->query( $wpdb->prepare("DELETE FROM $wpdb->mp_usermeta WHERE user_id = %d", $mp_user_id) );

		return true;
	}

	public static function delete_by_id( $uid ) 
	{
		global $wpdb;
		if ( !is_numeric( $uid ) ) return false;

		$wpdb->query( $wpdb->prepare("DELETE FROM $wpdb->mp_usermeta WHERE umeta_id = %d", $uid) );

		return true;
	}
}
?>