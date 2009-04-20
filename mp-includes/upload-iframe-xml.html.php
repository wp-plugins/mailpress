<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php do_action('admin_xml_ns'); ?> <?php language_attributes(); ?>>
	<head profile="http://gmpg.org/xfn/11">
		<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
		<title></title>
<?php
wp_enqueue_script('jquery');
do_action('admin_print_scripts' );
?>
<script type="text/javascript">
jQuery(document).ready( function() {
	var xml = jQuery('pre').html();
	parent.mp_fileupload.loaded(<?php echo "$draft_id,'$file'"; ?>,xml,<?php echo $id; ?>);
});
</script>
	</head>
	<body style='display:none;'><pre><?php echo $xml; ?></pre></body>
</html>
