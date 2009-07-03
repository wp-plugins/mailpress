<?php include('header.php'); ?>
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
	<body style='display:none;'>
		<pre><?php echo $xml; ?></pre>
<?php do_action('admin_print_footer_scripts'); ?>
	</body>
</html>