<?php
global $mp_importers;

if (isset($_GET['mp_import']))
{
	$importer = $_GET['mp_import'];

	// Allow plugins to define importers as well
	if (! is_callable($mp_importers[$importer][2]))
	{
		if (! file_exists(MP_TMP . "mp-admin/includes/options/import/importers/$importer.php"))
		{
			wp_die(__('Cannot load importer.','MailPress'));
		}
		include(MP_TMP . "mp-admin/includes/options/import/importers/$importer.php");
	}

	define('MP_IMPORTING', true);

	self::require_class('Log');
	call_user_func($mp_importers[$importer][2]);
}
else
{
	$importers = self::get_list(); 
?>
<div class="wrap nosubsub">
	<div id="icon-mailpress-tools" class="icon32"><br /></div>
	<h2><?php _e('Import'); ?></h2>
<?php
	if ($importers)
	{
?>
		<p><?php _e('If you have emails in another system, MailPress can import those into this blog. To get started, choose a system to import from below:', 'MailPress'); ?></p>
		<table class="widefat">
			<thead>
				<tr>
<?php 	self::columns_list(); ?>
				</tr>
			</thead>
			<tfoot>
				<tr>
<?php 	self::columns_list(false); ?>
				</tr>
			</tfoot>
			<tbody>
<?php 	foreach ($importers as $id => $data) echo self::get_row( $id, $data ); ?>
			</tbody>
		</table>
<?php
	} else {
?>
		<p><?php _e('No importers available.', 'MailPress'); ?></p>
<?php
	}
}
?>
</div>