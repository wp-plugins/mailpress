<?php
class MP_Tracking_module_u010 extends MP_tracking_module_sysinfo_
{
	var $id	= 'u010';
	var $context= 'side';
	var $file 	= __FILE__;

	var $item_id = 'user_id';

	function extended_meta_box($tracks)
	{
		$this->_010($tracks);
	}
}
new MP_Tracking_module_u010(__('System info II', MP_TXTDOM));